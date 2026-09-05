<?php

namespace App\Services\Hr;

use App\Models\EndOfServiceSettlement;
use App\Models\Employee;
use Carbon\Carbon;

/**
 * حاسبة مكافأة نهاية الخدمة بنظام العمل السعودي الرسمي (المادة 84/85):
 *
 *  - أجر نصف شهر عن كل سنة من أول 5 سنين خدمة.
 *  - أجر شهر كامل عن كل سنة زيادة عن الـ 5 سنين.
 *  - سنوات الخدمة الكسرية بتتحسب بالتناسب (نسبة من السنة).
 *  - "الأجر" المعتمد هنا = الراتب الأساسي + البدلات الثابتة (نفس أساس
 *    Employee::dailySalary() × 30 عشان الاتساق مع باقي حسابات الراتب
 *    في المشروع).
 *
 * نسبة الاستحقاق بتختلف حسب سبب انتهاء الخدمة (المادة 85):
 *  - استقالة (resignation): تدرّج حسب سنوات الخدمة -
 *      أقل من سنتين: صفر، من 2 لأقل من 5: الثلث، من 5 لأقل من 10: الثلثين،
 *      10 سنين فأكثر: المكافأة كاملة.
 *  - فصل من صاحب العمل (termination) / انتهاء العقد (contract_end) /
 *    وفاة (death): المكافأة كاملة 100%.
 *  - فصل تأديبي بسبب يرجع للعامل (termination_for_cause - المادة 80):
 *    بدون مكافأة (صفر) - محفوظة هنا للتوثيق فقط، القرار النهائي بيرجع
 *    للإدارة/المستشار القانوني في كل حالة.
 */
class EndOfServiceCalculator
{
    /**
     * @return array{years_of_service:float, wage_basis:float, gross_amount:float, applied_percentage:float, net_amount:float}
     */
    public function calculate(Employee $employee, Carbon $terminationDate, string $terminationType, ?float $wageOverride = null): array
    {
        $hireDate = $employee->hire_date ? Carbon::parse($employee->hire_date) : $terminationDate->copy();

        $totalDays = max(0, $hireDate->diffInDays($terminationDate));
        $yearsOfService = round($totalDays / 365.25, 4);

        $wageBasis = $wageOverride !== null ? $wageOverride : round($employee->dailySalary() * 30, 2);

        $grossAmount = $this->grossAmount($yearsOfService, $wageBasis);
        $appliedPercentage = $this->appliedPercentage($terminationType, $yearsOfService);
        $netAmount = round($grossAmount * ($appliedPercentage / 100), 2);

        return [
            'years_of_service' => round($yearsOfService, 2),
            'wage_basis' => $wageBasis,
            'gross_amount' => round($grossAmount, 2),
            'applied_percentage' => $appliedPercentage,
            'net_amount' => $netAmount,
        ];
    }

    private function grossAmount(float $years, float $monthlyWage): float
    {
        $halfMonth = $monthlyWage / 2;

        if ($years <= 5) {
            return $years * $halfMonth;
        }

        return (5 * $halfMonth) + (($years - 5) * $monthlyWage);
    }

    private function appliedPercentage(string $terminationType, float $years): float
    {
        if ($terminationType === EndOfServiceSettlement::TYPE_TERMINATION_FOR_CAUSE) {
            return 0;
        }

        if ($terminationType !== EndOfServiceSettlement::TYPE_RESIGNATION) {
            // فصل من صاحب العمل / انتهاء عقد / وفاة - المكافأة كاملة.
            return 100;
        }

        // استقالة - تدرّج المادة 85.
        if ($years < 2) {
            return 0;
        }
        if ($years < 5) {
            return round(100 / 3, 2);
        }
        if ($years < 10) {
            return round(200 / 3, 2);
        }

        return 100;
    }
}
