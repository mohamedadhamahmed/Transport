<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CreditTransaction;
use App\Models\Employee;
use App\Models\EmployeeBonus;
use App\Models\EmployeeLoan;
use App\Models\FinancialAccount;
use App\Models\PayrollEmployeeLine;
use App\Models\PayrollLoanDeduction;
use App\Models\PayrollPosting;
use App\Services\Hr\HrAccountService;
use App\Services\Hr\PayrollCalculator;
use App\Support\AccountEffect;
use App\Support\OperationType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * كشف الرواتب الشهري - "الورقة اللي بتتسلّم مع الفلوس" لكل موظف: الراتب
 * الأساسي + البدلات + أي مكافأة شهرية يدوية + الأوفرتايم، ناقص خصومات
 * الغياب بدون إذن والتأخير والإجازة بدون راتب (كلها محسوبة أصلاً يوميًا
 * في جدول attendances عن طريق AttendanceCalculator - هنا بس بيتجمّعوا
 * لشهر كامل، راجع PayrollCalculator للتفاصيل) + خصم أقساط السلف
 * (EmployeeLoan::plannedDeduction()) النشطة على الموظف.
 *
 * الترحيل المحاسبي (postMonth) اختياري ومنفصل عن مجرد عرض/طباعة الكشف:
 * بيقفل الحسابات الشخصية الأربعة بتاعة كل موظف (راتب/مكافأة/خصم حضور/
 * ذمة السلف - راجع HrAccountService::ensureSalaryAccount وأخواتها)، كل
 * واحد بمبلغه هو بالظبط مش رقم إجمالي مشترك، عشان رصيد كل موظف يتحدّث
 * فعليًا على حسابه هو، مش بس رقم معروض في الشاشة.
 */
class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('payroll.view');

        [$monthStart, $monthEnd, $month] = $this->resolveMonth($request->input('month'));

        $branches = Branch::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_number', 'branch_id']);

        $rows = app(PayrollCalculator::class)->calculateForMonth(
            $monthStart,
            $monthEnd,
            $request->input('branch_id') ?: null,
            $request->input('employee_id') ?: null
        );

        $totals = [
            'basic_salary' => round($rows->sum('basic_salary'), 2),
            'allowances' => round($rows->sum('allowances'), 2),
            'bonus' => round($rows->sum('bonus'), 2),
            'overtime_amount' => round($rows->sum('overtime_amount'), 2),
            'absence_deduction' => round($rows->sum('absence_deduction'), 2),
            'late_deduction' => round($rows->sum('late_deduction'), 2),
            'unpaid_leave_deduction' => round($rows->sum('unpaid_leave_deduction'), 2),
            'attendance_deductions' => round($rows->sum('attendance_deductions'), 2),
            'loan_deduction' => round($rows->sum('loan_deduction'), 2),
            'total_deductions' => round($rows->sum('total_deductions'), 2),
            'gross_pay' => round($rows->sum('gross_pay'), 2),
            'net_pay' => round($rows->sum('net_pay'), 2),
        ];

        $treasuryAccounts = FinancialAccount::whereIn('parent_account_number', [4, 5])
            ->orderBy('name')
            ->get(['id', 'name']);

        $posting = PayrollPosting::whereDate('month', $monthStart->toDateString())->first();

        return view('payroll.index', compact('rows', 'month', 'branches', 'employees', 'totals', 'treasuryAccounts', 'posting'));
    }

    /**
     * قسيمة راتب فردية قابلة للطباعة - "الورقة اللي بتتسلّم مع الفلوس"
     * لموظف واحد بس، بنفس أرقام كشف الشهر (نفس PayrollCalculator).
     */
    public function slip(Request $request, Employee $employee)
    {
        $this->authorize('payroll.view');

        [$monthStart, $monthEnd, $month] = $this->resolveMonth($request->input('month'));

        $row = app(PayrollCalculator::class)->calculateForEmployee($employee, $monthStart, $monthEnd);

        return view('payroll.slip', compact('employee', 'row', 'month', 'monthStart'));
    }

    public function storeBonus(Request $request)
    {
        $this->authorize('payroll.view');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $monthDate = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();

        EmployeeBonus::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'month' => $monthDate->toDateString()],
            [
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]
        );

        return redirect()->route('payroll.index', ['month' => $validated['month']])->with('success', __('payroll.bonus_saved_success'));
    }

    /**
     * ترحيل شهر - اختيار حساب الخزينة بقى اختياري: لو اتحدد، صافي
     * الراتب بيتصرف منه فورًا (زي السلوك القديم بالظبط). لو اتسابه
     * فاضي، معناها الراتب "اتأخر" - صافي الراتب بيتقيّد كمستحق على
     * حساب "مستحقات رواتب الموظفين" بدل ما يتصرف فعليًا، ولحد ما تختار
     * تسدده لاحقًا من شاشة الرواتب (راجع payAccruedPosting).
     */
    public function postMonth(Request $request, HrAccountService $accounts)
    {
        $this->authorize('payroll.view');

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'treasury_account_id' => ['nullable', 'integer', 'exists:financialaccount,id'],
        ]);

        $monthStart = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        if (PayrollPosting::whereDate('month', $monthStart->toDateString())->exists()) {
            abort(422, __('payroll.already_posted'));
        }

        $treasuryAccountId = $validated['treasury_account_id'] ?? null;

        if ($treasuryAccountId) {
            $isValidTreasuryAccount = FinancialAccount::where('id', $treasuryAccountId)
                ->whereIn('parent_account_number', [4, 5])
                ->exists();
            if (!$isValidTreasuryAccount) {
                abort(422, __('payroll.invalid_treasury_account'));
            }
        }

        $rows = app(PayrollCalculator::class)->calculateForMonth($monthStart, $monthEnd);

        $totalGross = round($rows->sum('gross_pay'), 2);
        $totalBonus = round($rows->sum('bonus'), 2);
        $totalAttendanceDeductions = round($rows->sum('attendance_deductions'), 2);
        $totalLoanDeductions = round($rows->sum('loan_deduction'), 2);
        $totalDeductions = round($totalAttendanceDeductions + $totalLoanDeductions, 2);
        $totalNet = round($rows->sum('net_pay'), 2);

        DB::transaction(function () use ($monthStart, $rows, $treasuryAccountId, $totalGross, $totalBonus, $totalAttendanceDeductions, $totalLoanDeductions, $totalDeductions, $totalNet, $accounts) {
            $documentNumber = PayrollPosting::nextDocumentNumber();

            $posting = PayrollPosting::create([
                'document_number' => $documentNumber,
                'month' => $monthStart->toDateString(),
                'treasury_account_id' => $treasuryAccountId,
                'funding_source' => $treasuryAccountId ? PayrollPosting::FUNDING_SOURCE_TREASURY : PayrollPosting::FUNDING_SOURCE_PAYABLE,
                'total_gross' => $totalGross,
                'total_bonus' => $totalBonus,
                'total_deductions' => $totalDeductions,
                'total_loan_deductions' => $totalLoanDeductions,
                'total_net' => $totalNet,
                'paid_at' => $treasuryAccountId ? now() : null,
                'created_by' => Auth::id(),
            ]);

            if ($totalGross > 0) {
                $this->postJournal($documentNumber, $posting->id, $treasuryAccountId, $rows, $totalNet, $accounts);
            }

            if ($totalLoanDeductions > 0) {
                foreach ($rows as $row) {
                    if ($row['loan_deduction'] > 0) {
                        $this->postLoanDeductionsForEmployee($row['employee'], $documentNumber, $posting->id, $accounts);
                    }
                }
            }
        });

        return redirect()->route('payroll.index', ['month' => $validated['month']])->with('success', __('payroll.posted_success'));
    }

    /**
     * تسديد ترحيل مستحق (اتقيّد على "مستحقات رواتب الموظفين" وقت
     * الترحيل من غير اختيار خزينة) - مدين المستحقات (تقفيل الالتزام) /
     * دائن حساب الخزينة الفعلي اللي اتصرف منه دلوقتي. بيسجل حساب
     * الخزينة وتاريخ الدفع الفعلي على صف الترحيل نفسه (treasury_account_id
     * و paid_at) عشان يبقى واضح إمتى وإزاي اتصرف فعليًا.
     */
    public function payAccruedPosting(Request $request, PayrollPosting $payrollPosting, HrAccountService $accounts)
    {
        $this->authorize('payroll.view');

        if (!$payrollPosting->isAwaitingPayment()) {
            abort(422, __('payroll.not_awaiting_payment'));
        }

        $validated = $request->validate([
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
        ]);

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (!$isValidTreasuryAccount) {
            abort(422, __('payroll.invalid_treasury_account'));
        }

        DB::transaction(function () use ($payrollPosting, $validated, $accounts) {
            $payableAccount = FinancialAccount::lockForUpdate()->findOrFail($accounts->payrollPayableAccountId());
            $treasury = FinancialAccount::lockForUpdate()->findOrFail($validated['treasury_account_id']);

            $amount = (float) $payrollPosting->total_net;

            $payableNewBalance = AccountEffect::apply($payableAccount, $amount, 0);
            $treasuryNewBalance = AccountEffect::apply($treasury, 0, $amount);

            $note = __('payroll.payable_settlement_note', ['document' => $payrollPosting->document_number]);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $payableAccount->id,
                'recive_amount' => $amount,
                'note' => $note,
                'currentblance' => $payableNewBalance,
                'branchs_id' => $payableAccount->branchs_id,
                'debtor' => $amount,
                'creditor' => 0,
                'invoice_number' => $payrollPosting->document_number,
                'operation_type' => OperationType::PAYROLL,
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
                'invoice_number' => $payrollPosting->document_number,
                'operation_type' => OperationType::PAYROLL,
                'date_export' => now(),
            ]);

            $payrollPosting->update([
                'treasury_account_id' => $validated['treasury_account_id'],
                'paid_at' => now(),
            ]);
        });

        return redirect()->route('payroll.index', ['month' => $payrollPosting->month->format('Y-m')])->with('success', __('payroll.payable_settled_success'));
    }

    /**
     * خصم أقساط السلف النشطة (EmployeeLoan::plannedDeduction()) لموظف
     * واحد وقت ترحيل الشهر - دائن حساب الموظف نفسه (نفس حساب "ذمم
     * الموظف" اللي اتصرفت منه السلفة أصلاً)، بدون أي طرف مدين إضافي هنا
     * لأن الطرف المدين مغطّى بالفعل بقيد "رواتب الموظفين" الإجمالي في
     * postJournal (راجع تعليق postJournal للتوازن الكامل). كل خصم بيتسجل
     * في payroll_loan_deductions عشان destroyPosting يقدر يرجعه بالظبط،
     * وبيحدّث paid_amount على السلفة نفسها (وبيقفلها تلقائيًا لو اتسددت
     * بالكامل).
     */
    private function postLoanDeductionsForEmployee(Employee $employee, string $documentNumber, int $payrollPostingId, HrAccountService $accounts): void
    {
        $loans = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', EmployeeLoan::STATUS_ACTIVE)
            ->lockForUpdate()
            ->get();

        if ($loans->isEmpty()) {
            return;
        }

        $employeeAccount = FinancialAccount::lockForUpdate()->findOrFail($accounts->ensureEmployeeAccount($employee)->id);
        $note = __('payroll.loan_deduction_note', ['document' => $documentNumber]);

        foreach ($loans as $loan) {
            $amount = $loan->plannedDeduction();
            if ($amount <= 0) {
                continue;
            }

            $newBalance = AccountEffect::apply($employeeAccount, 0, $amount);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $employeeAccount->id,
                'recive_amount' => $amount,
                'note' => $note,
                'currentblance' => $newBalance,
                'branchs_id' => $employeeAccount->branchs_id,
                'debtor' => 0,
                'creditor' => $amount,
                'invoice_number' => $documentNumber,
                'operation_type' => OperationType::EMPLOYEE_LOAN_SETTLEMENT,
                'date_export' => now(),
            ]);

            PayrollLoanDeduction::create([
                'payroll_posting_id' => $payrollPostingId,
                'employee_loan_id' => $loan->id,
                'employee_id' => $employee->id,
                'amount' => $amount,
            ]);

            $newPaid = round((float) $loan->paid_amount + $amount, 2);
            $loan->paid_amount = $newPaid;
            if ($newPaid >= round((float) $loan->amount, 2)) {
                $loan->status = EmployeeLoan::STATUS_SETTLED;
                $loan->settled_at = now();
            }
            $loan->save();
        }
    }

    /**
     * إلغاء ترحيل شهر - بيرجع أثر القيد الإجمالي (رواتب/مكافآت/خصومات
     * حضور/الطرف الدائن للصافي) بالكامل، وبيرجع كل خصم سلفة اتنفذ في
     * الترحيل ده لصاحبه بالظبط (باستخدام تفاصيل payroll_loan_deductions
     * - مش تقدير أو إعادة حساب)، قبل ما يمسح صف الترحيل نفسه. لو السلفة
     * كانت اتقفلت (settled) بسبب الخصم ده، بترجع active تاني عشان تفضل
     * قابلة للخصم/التسوية من جديد.
     *
     * لو الترحيل كان مستحق (funding_source=payable)، الطرف الدائن
     * للصافي كان "مستحقات رواتب الموظفين" مش خزينة - فبيترجع هو، مش أي
     * حساب خزينة. ولو كان اتسدد فعليًا كمان (paid_at موجود)، بيترجع
     * قيد التسديد نفسه (مدين المستحقات / دائن الخزينة) قبل ما يترجع
     * قيد الاستحقاق الأصلي، عشان رصيد الخزينة ورصيد المستحقات الاتنين
     * يفضلوا صح بعد الإلغاء.
     */
    public function destroyPosting(PayrollPosting $payrollPosting)
    {
        $this->authorize('payroll.view');

        DB::transaction(function () use ($payrollPosting) {
            $accounts = app(HrAccountService::class);

            // 1) رجوع كل بند شخصي (راتب/مكافأة/خصم حضور) لصاحبه بالظبط -
            // على حسابه الشخصي هو (مش حساب مجموعة مشترك زي قبل)، باستخدام
            // تفاصيل payroll_employee_lines لا إعادة حساب.
            foreach ($payrollPosting->employeeLines()->lockForUpdate()->get() as $line) {
                $employee = $line->employee;
                $amount = (float) $line->amount;
                if (!$employee || $amount <= 0) {
                    continue;
                }

                $personalAccount = match ($line->type) {
                    PayrollEmployeeLine::TYPE_SALARY => $accounts->ensureSalaryAccount($employee),
                    PayrollEmployeeLine::TYPE_BONUS => $accounts->ensureBonusAccount($employee),
                    PayrollEmployeeLine::TYPE_DEDUCTION => $accounts->ensureDeductionAccount($employee),
                    default => null,
                };
                if (!$personalAccount) {
                    continue;
                }

                $personalAccount = FinancialAccount::lockForUpdate()->find($personalAccount->id);
                if (!$personalAccount) {
                    continue;
                }

                if ($line->type === PayrollEmployeeLine::TYPE_DEDUCTION) {
                    AccountEffect::reverse($personalAccount, 0, $amount);
                } else {
                    AccountEffect::reverse($personalAccount, $amount, 0);
                }
            }
            $payrollPosting->employeeLines()->delete();

            // 2) رجوع الطرف الدائن الإجمالي بتاع الصافي (خزينة أو مستحقات)
            // - ده فضل تجميعي عمدًا (راجع تعليق HrAccountService عن
            // payrollPayableAccountId)، فبيترجع كإجمالي واحد زي قبل.
            if ((float) $payrollPosting->total_gross > 0) {
                $treasury = $payrollPosting->treasury_account_id
                    ? FinancialAccount::lockForUpdate()->find($payrollPosting->treasury_account_id)
                    : null;

                $totalNet = (float) $payrollPosting->total_net;

                if ($payrollPosting->funding_source === PayrollPosting::FUNDING_SOURCE_PAYABLE) {
                    // الأصل اتقيّد على "مستحقات رواتب الموظفين" وقت الترحيل
                    // (مش خزينة) - نرجعه هو، مش الخزينة.
                    $payableAccount = FinancialAccount::lockForUpdate()->find($accounts->payrollPayableAccountId());
                    if ($payableAccount) {
                        AccountEffect::reverse($payableAccount, 0, $totalNet);

                        // لو كان اتسدد فعليًا قبل كده (paid_at موجود)، ده معناه
                        // فيه قيد تسديد منفصل (مدين المستحقات / دائن الخزينة -
                        // راجع payAccruedPosting) لازم يترجع كمان قبل ما نمسح
                        // الترحيل، وإلا هيفضل رصيد الخزينة والمستحقات غلط.
                        if ($payrollPosting->isPaid() && $treasury) {
                            AccountEffect::reverse($payableAccount, $totalNet, 0);
                            AccountEffect::reverse($treasury, 0, $totalNet);
                        }
                    }
                } elseif ($treasury) {
                    AccountEffect::reverse($treasury, 0, $totalNet);
                }
            }

            foreach ($payrollPosting->loanDeductions()->lockForUpdate()->get() as $deduction) {
                $loan = EmployeeLoan::lockForUpdate()->find($deduction->employee_loan_id);
                if (!$loan) {
                    continue;
                }

                $employeeAccount = FinancialAccount::lockForUpdate()->find($accounts->ensureEmployeeAccount($loan->employee)->id);
                if ($employeeAccount) {
                    AccountEffect::reverse($employeeAccount, 0, (float) $deduction->amount);
                }

                $loan->paid_amount = max(0, round((float) $loan->paid_amount - (float) $deduction->amount, 2));
                if ($loan->status === EmployeeLoan::STATUS_SETTLED && $loan->paid_amount < round((float) $loan->amount, 2)) {
                    $loan->status = EmployeeLoan::STATUS_ACTIVE;
                    $loan->settled_at = null;
                }
                $loan->save();
            }

            $payrollPosting->loanDeductions()->delete();
            $payrollPosting->delete();
        });

        return redirect()->back()->with('success', __('payroll.posting_cancelled_success'));
    }

    /**
     * قيد ترحيل الرواتب - بقى شخصي لكل موظف على حسابه هو مش على حساب
     * مجموعة مشترك: مدين حساب "راتب الموظف" الشخصي (HrAccountService::
     * ensureSalaryAccount) بالراتب الأساسي + البدلات + الأوفرتايم بس
     * (gross ناقص المكافأة)، ومدين حساب "مكافأة الموظف" الشخصي
     * (ensureBonusAccount) بمكافأته الشهرية اليدوية (EmployeeBonus) لو
     * موجودة، ودائن حساب "خصم الموظف" الشخصي (ensureDeductionAccount)
     * بخصومات حضوره بس (غياب/تأخير/إجازة بدون راتب - مش السلف) - كل ده
     * لكل موظف على حدة، وبيتسجل تفصيليًا في payroll_employee_lines عشان
     * destroyPosting يقدر يرجع كل بند لصاحبه بالظبط.
     *
     * الطرف الدائن الإجمالي بس (صافي كل الرواتب مع بعض) فضل تجميعي واحد
     * عمدًا - حساب الخزينة المختار (أو "مستحقات رواتب الموظفين" لو
     * $treasuryAccountId فاضي - يعني الراتب اتأخر ولسه ما اتصرفش، راجع
     * postMonth) - لإنه بيمثل حركة نقدية/التزام واحد فعلي، مش ذمة تجاه
     * موظف بعينه. خصم السلف نفسه بيترحّل في أرجل منفصلة لكل موظف على
     * حساب ذمته هو (راجع postLoanDeductionsForEmployee) - فمجموع كل
     * الأرجل الدائنة (خصومات حضور كل الموظفين + خصومات السلف لكل
     * الموظفين + الصافي) = مجموع الأرجل المدينة (رواتب + مكافآت كل
     * الموظفين) = الإجمالي gross، فالقيد الكامل متوازن رغم إنه بيتسجل في
     * أكتر من استدعاء.
     *
     * @param \Illuminate\Support\Collection $rows نفس صفوف PayrollCalculator::calculateForMonth.
     */
    private function postJournal(string $documentNumber, int $payrollPostingId, ?int $treasuryAccountId, \Illuminate\Support\Collection $rows, float $totalNet, HrAccountService $accounts): void
    {
        $note = $treasuryAccountId
            ? __('payroll.posting_note', ['document' => $documentNumber])
            : __('payroll.accrual_note', ['document' => $documentNumber]);

        foreach ($rows as $row) {
            $employee = $row['employee'];
            $baseSalary = round((float) $row['gross_pay'] - (float) $row['bonus'], 2);
            $bonus = round((float) $row['bonus'], 2);
            $attendanceDeduction = round((float) $row['attendance_deductions'], 2);

            if ($baseSalary > 0) {
                $this->postEmployeeLine($accounts->ensureSalaryAccount($employee), $employee, $payrollPostingId, PayrollEmployeeLine::TYPE_SALARY, $baseSalary, true, $documentNumber, $note);
            }

            if ($bonus > 0) {
                $this->postEmployeeLine($accounts->ensureBonusAccount($employee), $employee, $payrollPostingId, PayrollEmployeeLine::TYPE_BONUS, $bonus, true, $documentNumber, __('payroll.bonus_posting_note', ['document' => $documentNumber]));
            }

            if ($attendanceDeduction > 0) {
                $this->postEmployeeLine($accounts->ensureDeductionAccount($employee), $employee, $payrollPostingId, PayrollEmployeeLine::TYPE_DEDUCTION, $attendanceDeduction, false, $documentNumber, $note);
            }
        }

        $netAccount = $treasuryAccountId
            ? FinancialAccount::lockForUpdate()->findOrFail($treasuryAccountId)
            : FinancialAccount::lockForUpdate()->findOrFail($accounts->payrollPayableAccountId());

        $netAccountNewBalance = AccountEffect::apply($netAccount, 0, $totalNet);

        CreditTransaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $netAccount->id,
            'recive_amount' => $totalNet,
            'note' => $note,
            'currentblance' => $netAccountNewBalance,
            'branchs_id' => $netAccount->branchs_id,
            'debtor' => 0,
            'creditor' => $totalNet,
            'invoice_number' => $documentNumber,
            'operation_type' => OperationType::PAYROLL,
            'date_export' => now(),
        ]);
    }

    /**
     * تنفيذ بند شخصي واحد (راتب/مكافأة/خصم حضور) لموظف واحد وقت ترحيل
     * الشهر - بيقفل الحساب الشخصي، يطبّق أثره المدين/الدائن، يسجّل حركة
     * القيد، وبيحفظ تفاصيله في payroll_employee_lines عشان destroyPosting
     * يقدر يرجعه بالظبط لاحقًا.
     */
    private function postEmployeeLine(FinancialAccount $personalAccount, Employee $employee, int $payrollPostingId, string $type, float $amount, bool $isDebit, string $documentNumber, string $note): void
    {
        $personalAccount = FinancialAccount::lockForUpdate()->findOrFail($personalAccount->id);

        $newBalance = $isDebit
            ? AccountEffect::apply($personalAccount, $amount, 0)
            : AccountEffect::apply($personalAccount, 0, $amount);

        CreditTransaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $personalAccount->id,
            'recive_amount' => $amount,
            'note' => $note,
            'currentblance' => $newBalance,
            'branchs_id' => $personalAccount->branchs_id,
            'debtor' => $isDebit ? $amount : 0,
            'creditor' => $isDebit ? 0 : $amount,
            'invoice_number' => $documentNumber,
            'operation_type' => OperationType::PAYROLL,
            'date_export' => now(),
        ]);

        PayrollEmployeeLine::create([
            'payroll_posting_id' => $payrollPostingId,
            'employee_id' => $employee->id,
            'type' => $type,
            'amount' => $amount,
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveMonth(?string $month): array
    {
        $month = $month ?: now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return [$monthStart, $monthEnd, $month];
    }
}
