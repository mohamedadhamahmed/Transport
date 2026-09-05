<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\Employee;
use App\Models\EndOfServiceSettlement;
use App\Models\FinancialAccount;
use App\Services\Hr\EndOfServiceCalculator;
use App\Services\Hr\HrAccountService;
use App\Support\AccountEffect;
use App\Support\OperationType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * مكافأة نهاية الخدمة - حساب رسمي بنظام العمل السعودي (راجع
 * EndOfServiceCalculator للصيغة الكاملة) + ترحيل قيد محاسبي حقيقي:
 * مدين حساب "مكافأة نهاية الخدمة" الشخصي بتاع الموظف نفسه
 * (HrAccountService::ensureEosAccount - تحت الأب الحقيقي id=142)
 * / دائن حساب الخزينة المختار - بنفس أسلوب سند الصرف العادي
 * (VoucherController/EmployeeLoanController) لإن المكافأة هنا بتترحّل
 * وتتصرف دفعة واحدة وقت التسوية، مش بنظام استحقاق شهري.
 */
class EndOfServiceController extends Controller
{
    public function index()
    {
        $this->authorize('end_of_service.view');

        $settlements = EndOfServiceSettlement::with(['employee', 'paymentAccount'])
            ->orderByDesc('termination_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('end-of-service.index', compact('settlements'));
    }

    public function create()
    {
        $this->authorize('end_of_service.view');

        $employees = Employee::where('status', '!=', Employee::STATUS_TERMINATED)->orderBy('name')->get();
        $treasuryAccounts = FinancialAccount::whereIn('parent_account_number', [4, 5])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('end-of-service.create', compact('employees', 'treasuryAccounts'));
    }

    public function store(Request $request, EndOfServiceCalculator $calculator, HrAccountService $accounts)
    {
        $this->authorize('end_of_service.view');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'termination_date' => ['required', 'date'],
            'termination_type' => ['required', 'in:resignation,termination,contract_end,termination_for_cause,death'],
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'wage_override' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (!$isValidTreasuryAccount) {
            abort(422, __('end_of_service.invalid_treasury_account'));
        }

        $employee = Employee::findOrFail($validated['employee_id']);
        $terminationDate = Carbon::parse($validated['termination_date']);

        $result = $calculator->calculate(
            $employee,
            $terminationDate,
            $validated['termination_type'],
            isset($validated['wage_override']) ? (float) $validated['wage_override'] : null
        );

        DB::transaction(function () use ($validated, $employee, $terminationDate, $result, $accounts) {
            $documentNumber = EndOfServiceSettlement::nextDocumentNumber();

            $settlement = EndOfServiceSettlement::create([
                'document_number' => $documentNumber,
                'employee_id' => $employee->id,
                'termination_type' => $validated['termination_type'],
                'hire_date' => $employee->hire_date,
                'termination_date' => $terminationDate,
                'years_of_service' => $result['years_of_service'],
                'wage_basis' => $result['wage_basis'],
                'gross_amount' => $result['gross_amount'],
                'applied_percentage' => $result['applied_percentage'],
                'net_amount' => $result['net_amount'],
                'payment_treasury_account_id' => $validated['treasury_account_id'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            if ((float) $settlement->net_amount > 0) {
                $this->postSettlement($settlement, $accounts);
            }

            $employee->update([
                'status' => Employee::STATUS_TERMINATED,
                'termination_date' => $terminationDate,
            ]);
        });

        return redirect()->route('end-of-service.index')->with('success', __('end_of_service.created_success', ['amount' => number_format($result['net_amount'], 2)]));
    }

    /**
     * إلغاء تسوية - بيرجع أثرها المحاسبي بالكامل الأول (لو كان فيها
     * مبلغ فعلي اتصرف) قبل ما يمسح الصف. حالة الموظف (terminated) مش
     * بترجع تلقائيًا - الإدارة تقدر تفعّله يدويًا من شاشة الموظفين لو
     * الإلغاء كان بسبب غلط في البيانات مش في قرار إنهاء الخدمة نفسه.
     */
    public function destroy(EndOfServiceSettlement $endOfServiceSettlement)
    {
        $this->authorize('end_of_service.view');

        DB::transaction(function () use ($endOfServiceSettlement) {
            if ((float) $endOfServiceSettlement->net_amount > 0 && $endOfServiceSettlement->payment_treasury_account_id) {
                $expenseAccount = app(HrAccountService::class)->ensureEosAccount($endOfServiceSettlement->employee);
                $expenseAccount = FinancialAccount::lockForUpdate()->find($expenseAccount->id);
                $treasury = FinancialAccount::lockForUpdate()->find($endOfServiceSettlement->payment_treasury_account_id);

                if ($expenseAccount) {
                    AccountEffect::reverse($expenseAccount, (float) $endOfServiceSettlement->net_amount, 0);
                }
                if ($treasury) {
                    AccountEffect::reverse($treasury, 0, (float) $endOfServiceSettlement->net_amount);
                }
            }

            $endOfServiceSettlement->delete();
        });

        return redirect()->route('end-of-service.index')->with('success', __('end_of_service.deleted_success'));
    }

    private function postSettlement(EndOfServiceSettlement $settlement, HrAccountService $accounts): void
    {
        $expenseAccountId = $accounts->ensureEosAccount($settlement->employee)->id;
        $expenseAccount = FinancialAccount::lockForUpdate()->findOrFail($expenseAccountId);
        $treasury = FinancialAccount::lockForUpdate()->findOrFail($settlement->payment_treasury_account_id);

        $amount = (float) $settlement->net_amount;

        $expenseNewBalance = AccountEffect::apply($expenseAccount, $amount, 0);
        $treasuryNewBalance = AccountEffect::apply($treasury, 0, $amount);

        $note = __('end_of_service.settlement_note', ['document' => $settlement->document_number]);

        CreditTransaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $expenseAccount->id,
            'recive_amount' => $amount,
            'note' => $note,
            'currentblance' => $expenseNewBalance,
            'branchs_id' => $expenseAccount->branchs_id,
            'debtor' => $amount,
            'creditor' => 0,
            'invoice_number' => $settlement->document_number,
            'operation_type' => OperationType::END_OF_SERVICE,
            'date_export' => now(),
        ]);

        CreditTransaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $treasury->id,
            'recive_amount' => $amount,
            'note' => $note,
            'currentblance' => $treasuryNewBalance,
            'branchs_id' => $treasury->branchs_id,
            'debtor' => 0,
            'creditor' => $amount,
            'invoice_number' => $settlement->document_number,
            'operation_type' => OperationType::END_OF_SERVICE,
            'date_export' => now(),
        ]);
    }
}
