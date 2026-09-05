<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\FinancialAccount;
use App\Services\Hr\HrAccountService;
use App\Support\AccountEffect;
use App\Support\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * السلف والعهد (loans/custodies) - إدارة كاملة (إضافة/تسوية/إلغاء) مع
 * قيود محاسبية حقيقية، بنفس أسلوب VoucherController@store بالظبط:
 *
 *  - الصرف (store): مدين حساب ذمم الموظف (HrAccountService::ensureEmployeeAccount)
 *    / دائن حساب الخزينة المختار (لازم يكون تحت parent_account_number
 *    4 أو 5 زي أي سند صرف عادي - نفس التحقق الموجود في VoucherController).
 *  - التسوية (settle): عكس الاتجاه بالظبط - مدين الخزينة / دائن حساب
 *    الموظف (المبلغ رجع أو اتخصم من راتبه).
 *  - الإلغاء (destroy): بيرجع أثر الصرف الأصلي بالكامل (AccountEffect::reverse)
 *    قبل ما يمسح الصف - مينفعش يتمسح سجل له أثر محاسبي فعلي من غير
 *    عكسه الأول.
 *
 * كل عملية بتتسجل صفين في credittransaction (زي VoucherController) عشان
 * تظهر في كشف حساب الموظف وكشف حساب الخزينة سوا.
 */
class EmployeeLoanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('employee_loans.view');

        $query = EmployeeLoan::with(['employee', 'treasuryAccount']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $loans = $query->orderByDesc('date')->orderByDesc('id')->paginate(20)->withQueryString();
        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_number']);
        $treasuryAccounts = FinancialAccount::whereIn('parent_account_number', [4, 5])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employee-loans.index', compact('loans', 'employees', 'treasuryAccounts'));
    }

    public function store(Request $request, HrAccountService $accounts)
    {
        $this->authorize('employee_loans.create');

        $validated = $this->validated($request);

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (!$isValidTreasuryAccount) {
            abort(422, __('employee_loans.invalid_treasury_account'));
        }

        $amount = round((float) $validated['amount'], 2);
        $documentNumber = EmployeeLoan::nextDocumentNumber($validated['type']);

        DB::transaction(function () use ($validated, $accounts, $amount, $documentNumber) {
            $employee = Employee::findOrFail($validated['employee_id']);
            $employeeAccount = $accounts->ensureEmployeeAccount($employee);

            $loan = EmployeeLoan::create([
                'document_number' => $documentNumber,
                'employee_id' => $employee->id,
                'type' => $validated['type'],
                'treasury_account_id' => $validated['treasury_account_id'],
                'amount' => $amount,
                'monthly_installment' => $validated['monthly_installment'] ?? null,
                'date' => $validated['date'],
                'description' => $validated['description'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => EmployeeLoan::STATUS_ACTIVE,
                'created_by' => Auth::id(),
            ]);

            if ($amount > 0) {
                $this->postMovement($employeeAccount, $validated['treasury_account_id'], $amount, $documentNumber, false);
            }
        });

        return redirect()->route('employee-loans.index')->with('success', __('employee_loans.created_success'));
    }

    /**
     * تسوية السلفة/العهدة (المتبقي منها فعليًا - remainingAmount()، مش
     * المبلغ الأصلي بالكامل، عشان لو جزء منها كان اتخصم من الرواتب قبل
     * كده عن طريق PayrollController@postMonth ميتحسبش مرتين).
     */
    public function settle(Request $request, EmployeeLoan $loan, HrAccountService $accounts)
    {
        $this->authorize('employee_loans.create');

        if (!$loan->isActive()) {
            abort(422, __('employee_loans.already_settled'));
        }

        $validated = $request->validate([
            'settled_treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
        ]);

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['settled_treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (!$isValidTreasuryAccount) {
            abort(422, __('employee_loans.invalid_treasury_account'));
        }

        DB::transaction(function () use ($loan, $validated, $accounts) {
            $employeeAccount = $accounts->ensureEmployeeAccount($loan->employee);
            $remaining = $loan->remainingAmount();

            if ($remaining > 0) {
                $this->postMovement($employeeAccount, $validated['settled_treasury_account_id'], $remaining, $loan->document_number, true);
            }

            $loan->update([
                'paid_amount' => $loan->amount,
                'status' => EmployeeLoan::STATUS_SETTLED,
                'settled_at' => now(),
                'settled_treasury_account_id' => $validated['settled_treasury_account_id'],
            ]);
        });

        return redirect()->route('employee-loans.index')->with('success', __('employee_loans.settled_success'));
    }

    /**
     * إلغاء سلفة/عهدة - بيرجع أثرها المحاسبي بالكامل الأول (لو كانت لسه
     * active وليها مبلغ فعلي)، وبيرفض الإلغاء لو أصلاً اتسوّت (عشان
     * ميحصلش عكس مزدوج للأثر). لو جزء منها اتخصم قبل كده من الرواتب
     * (paid_amount > 0)، الحذف بيترفض برضه - لازم تتسوّى (settle) بدل
     * ما تتمسح، عشان الخصومات اللي اتعملت في كشوف رواتب سابقة تفضل
     * صحيحة ومتسجلة.
     */
    public function destroy(EmployeeLoan $loan, HrAccountService $accounts)
    {
        $this->authorize('employee_loans.create');

        if (!$loan->isActive()) {
            abort(422, __('employee_loans.cannot_delete_settled'));
        }

        if ((float) $loan->paid_amount > 0) {
            abort(422, __('employee_loans.cannot_delete_partially_paid'));
        }

        DB::transaction(function () use ($loan, $accounts) {
            if ((float) $loan->amount > 0) {
                $employeeAccount = $accounts->ensureEmployeeAccount($loan->employee);
                $treasury = FinancialAccount::lockForUpdate()->findOrFail($loan->treasury_account_id);

                // عكس القيد الأصلي بالظبط: كان مدين الموظف / دائن الخزينة،
                // فبنرجعه مدين الخزينة / دائن الموظف.
                AccountEffect::reverse($employeeAccount, (float) $loan->amount, 0);
                AccountEffect::reverse($treasury, 0, (float) $loan->amount);
            }

            $loan->delete();
        });

        return redirect()->route('employee-loans.index')->with('success', __('employee_loans.deleted_success'));
    }

    /**
     * تطبيق أثر صرف/تسوية سلفة على حساب الموظف والخزينة + تسجيل صفين
     * في credittransaction - نفس بنية VoucherController@store بالظبط.
     *
     * $isSettlement = false: صرف (مدين الموظف / دائن الخزينة).
     * $isSettlement = true: تسوية (مدين الخزينة / دائن الموظف).
     */
    private function postMovement(FinancialAccount $employeeAccount, int $treasuryAccountId, float $amount, string $documentNumber, bool $isSettlement): void
    {
        $treasury = FinancialAccount::lockForUpdate()->findOrFail($treasuryAccountId);
        $employeeAccount = FinancialAccount::lockForUpdate()->findOrFail($employeeAccount->id);

        $employeeDebit = $isSettlement ? 0 : $amount;
        $employeeCredit = $isSettlement ? $amount : 0;
        $treasuryDebit = $isSettlement ? $amount : 0;
        $treasuryCredit = $isSettlement ? 0 : $amount;

        $employeeNewBalance = AccountEffect::apply($employeeAccount, $employeeDebit, $employeeCredit);
        $treasuryNewBalance = AccountEffect::apply($treasury, $treasuryDebit, $treasuryCredit);

        $note = $isSettlement
            ? __('employee_loans.settlement_note', ['document' => $documentNumber])
            : __('employee_loans.disbursement_note', ['document' => $documentNumber]);

        $operationType = $isSettlement ? OperationType::EMPLOYEE_LOAN_SETTLEMENT : OperationType::EMPLOYEE_LOAN;

        CreditTransaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $employeeAccount->id,
            'recive_amount' => $amount,
            'note' => $note,
            'currentblance' => $employeeNewBalance,
            'branchs_id' => $employeeAccount->branchs_id,
            'debtor' => $employeeDebit,
            'creditor' => $employeeCredit,
            'invoice_number' => $documentNumber,
            'operation_type' => $operationType,
            'date_export' => now(),
        ]);

        CreditTransaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $treasury->id,
            'recive_amount' => $amount,
            'note' => $note,
            'currentblance' => $treasuryNewBalance,
            'branchs_id' => $treasury->branchs_id,
            'debtor' => $treasuryDebit,
            'creditor' => $treasuryCredit,
            'invoice_number' => $documentNumber,
            'operation_type' => $operationType,
            'date_export' => now(),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'in:loan,custody'],
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'monthly_installment' => ['nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
