<?php

namespace App\Services\Hr;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeBonus;
use App\Models\EmployeeLoan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * حساب كشف راتب شهر معيّن لموظف واحد أو لكل الموظفين - مستخدمة من
 * PayrollController في شاشة الكشف الإجمالي (index) وقسيمة الراتب
 * الفردية (slip) سوا عشان الأرقام تفضل متطابقة بين الاثنين، بنفس فكرة
 * AttendanceCalculator المشتركة بين الإدخال اليدوي والاستيراد.
 *
 * كل بيانات الخصم (غياب/تأخير/إجازة بدون راتب) والإضافة (أوفرتايم)
 * أصلاً محسوبة ومسجلة يوميًا في جدول attendances (عمود discount_amount/
 * overtime_amount) - الحاسبة هنا بتجمّعها فقط لشهر كامل وتوزّعها على
 * 3 بنود خصم منفصلة حسب حالة اليوم:
 *   - غياب بدون إذن: status=absent أو is_connected_penalty=true (خصم
 *     يوم الإجازة الأسبوعية/الرسمية المتصل بالغياب - راجع
 *     AttendanceCalculator::syncConnectedOffDayPenalty).
 *   - تأخير: status=present (discount_amount هنا خصم التأخير بس).
 *   - إجازة بدون راتب: status=leave (discount_amount بيتحط بس لو النوع
 *     unpaid - راجع LeaveRequestController@approve).
 *
 * خصم السلف (loan_deduction) منفصل تمامًا عن خصومات الحضور دي - بيتحسب
 * من EmployeeLoan::plannedDeduction() لكل سلفة/عهدة "نشطة" (active) على
 * الموظف: القسط الشهري المتفق عليه، أو كل المتبقي دفعة واحدة لو مفيش
 * قسط محدد. القيمة هنا "مبلغ مخطط" بس للعرض - الخصم الفعلي (تحديث
 * paid_amount وتسوية حساب الموظف) بيحصل بس وقت ترحيل الشهر
 * (PayrollController@postMonth)، عشان المعاينة قبل الترحيل متغيّرش
 * حاجة فعلية في الحسابات.
 */
class PayrollCalculator
{
    /**
     * @return array{employee: Employee, basic_salary: float, allowances: float, bonus: float, overtime_amount: float, absence_deduction: float, late_deduction: float, unpaid_leave_deduction: float, attendance_deductions: float, loan_deduction: float, total_deductions: float, gross_pay: float, net_pay: float}
     */
    public function calculateForEmployee(Employee $employee, Carbon $monthStart, Carbon $monthEnd): array
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $absenceDeduction = round((float) $attendances
            ->filter(fn (Attendance $a) => $a->status === Attendance::STATUS_ABSENT || $a->is_connected_penalty)
            ->sum('discount_amount'), 2);

        $lateDeduction = round((float) $attendances
            ->filter(fn (Attendance $a) => $a->status === Attendance::STATUS_PRESENT)
            ->sum('discount_amount'), 2);

        $unpaidLeaveDeduction = round((float) $attendances
            ->filter(fn (Attendance $a) => $a->status === Attendance::STATUS_LEAVE)
            ->sum('discount_amount'), 2);

        $overtimeAmount = round((float) $attendances->sum('overtime_amount'), 2);

        $bonus = (float) (EmployeeBonus::where('employee_id', $employee->id)
            ->whereDate('month', $monthStart->toDateString())
            ->value('amount') ?? 0);

        $loanDeduction = round((float) EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', EmployeeLoan::STATUS_ACTIVE)
            ->get()
            ->sum(fn (EmployeeLoan $loan) => $loan->plannedDeduction()), 2);

        $basicSalary = (float) $employee->basic_salary;
        $allowances = (float) $employee->allowances;

        $attendanceDeductions = round($absenceDeduction + $lateDeduction + $unpaidLeaveDeduction, 2);
        $totalDeductions = round($attendanceDeductions + $loanDeduction, 2);
        $grossPay = round($basicSalary + $allowances + $bonus + $overtimeAmount, 2);
        $netPay = round($grossPay - $totalDeductions, 2);

        return [
            'employee' => $employee,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'bonus' => $bonus,
            'overtime_amount' => $overtimeAmount,
            'absence_deduction' => $absenceDeduction,
            'late_deduction' => $lateDeduction,
            'unpaid_leave_deduction' => $unpaidLeaveDeduction,
            'attendance_deductions' => $attendanceDeductions,
            'loan_deduction' => $loanDeduction,
            'total_deductions' => $totalDeductions,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
        ];
    }

    /**
     * @return Collection<int, array>
     */
    public function calculateForMonth(Carbon $monthStart, Carbon $monthEnd, ?int $branchId = null, ?int $employeeId = null): Collection
    {
        $query = Employee::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        return $query->orderBy('name')->get()
            ->map(fn (Employee $employee) => $this->calculateForEmployee($employee, $monthStart, $monthEnd));
    }
}
