<?php

namespace App\Services\Hr;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HrHoliday;
use App\Models\HrSetting;
use Carbon\Carbon;

/**
 * حاسبة الحضور والانصراف - مستخدمة من AttendanceController (إدخال يدوي)
 * ومن AttendanceImporter (استيراد ملف بصمة إكسيل) سوا، عشان نتيجة
 * الحساب تفضل واحدة أيًا كان مصدر البيانات (نفس فكرة ComputesAttendance
 * trait اللي اتعمل في ALHYAKIL_UPDATE-main، بس هنا كـ service منفصل
 * مطابق لأسلوب my-erp - كل حسابات القسم ده جوه app/Services/Hr).
 *
 * الترتيب اللي بيتفحص بيه اليوم:
 *   1) إجازة رسمية (hr_holidays) -> status=holiday، صفر في كل حاجة.
 *   2) يوم إجازة أسبوعية (hr_settings.weekly_off_days) -> status=weekend.
 *   3) مفيش بصمة دخول ولا خروج خالص -> status=absent، وخصم يوم كامل
 *      × absence_deduction_multiplier (إعداد قابل للتعديل).
 *   4) غير كده -> status=present، وبيتحسب:
 *      - late_minutes: التأخير عن work_start_time بعد خصم late_grace_minutes.
 *      - overtime_hours/overtime_amount: الوقت بعد work_end_time، بقيمة
 *        الساعة المحسوبة من راتب الموظف نفسه (dailySalary/ساعات الدوام)
 *        × overtime_multiplier.
 *      - discount_amount: خصم التأخير بنفس قيمة الساعة (من غير مضاعف).
 *
 * بعد ما يوم الغياب يتسجل (منAttendanceController أو AttendanceImporter)،
 * لازم ننادي syncConnectedOffDayPenalty() لتطبيق/إلغاء خصم أيام الإجازة
 * الأسبوعية/الرسمية المتصلة بيه لو الإعداد extend_deduction_to_weekly_off
 * مفعّل (راجع تعليق الميجريشن الخاص بيه لتفاصيل القاعدة).
 */
class AttendanceCalculator
{
    /**
     * @return array{status:string, late_minutes:int, overtime_hours:float, overtime_amount:float, discount_amount:float}
     */
    public function calculate(Employee $employee, Carbon $date, ?string $checkIn, ?string $checkOut, HrSetting $setting): array
    {
        $zero = [
            'late_minutes' => 0,
            'overtime_hours' => 0.0,
            'overtime_amount' => 0.0,
            'discount_amount' => 0.0,
        ];

        if ($this->isHoliday($date, $employee->branch_id)) {
            return array_merge($zero, ['status' => Attendance::STATUS_HOLIDAY]);
        }

        if ($this->isWeeklyOff($date, $setting)) {
            return array_merge($zero, ['status' => Attendance::STATUS_WEEKEND]);
        }

        if (!$checkIn && !$checkOut) {
            return array_merge($zero, [
                'status' => Attendance::STATUS_ABSENT,
                'discount_amount' => round($employee->dailySalary() * $setting->absenceDeductionMultiplier(), 2),
            ]);
        }

        $dateString = $date->toDateString();
        $workStart = Carbon::parse($dateString . ' ' . $setting->work_start_time);
        $workEnd = Carbon::parse($dateString . ' ' . $setting->work_end_time);

        // ساعات الدوام الرسمي - أساس حساب "قيمة الساعة" لكل موظف. لو
        // الإعداد غلط (نهاية قبل بداية أو نفس الوقت) بنرجع لـ 8 ساعات
        // افتراضيًا بدل قسمة على صفر.
        $workHours = $workEnd->greaterThan($workStart) ? $workStart->diffInMinutes($workEnd) / 60 : 8;
        $hourlyRate = $workHours > 0 ? ($employee->dailySalary() / $workHours) : 0;

        $lateMinutes = 0;
        if ($checkIn) {
            $actualIn = Carbon::parse($dateString . ' ' . $checkIn);
            if ($actualIn->greaterThan($workStart)) {
                $diff = $workStart->diffInMinutes($actualIn);
                $lateMinutes = max(0, $diff - (int) $setting->late_grace_minutes);
            }
        }

        $overtimeHours = 0.0;
        if ($checkOut) {
            $actualOut = Carbon::parse($dateString . ' ' . $checkOut);
            if ($actualOut->greaterThan($workEnd)) {
                $overtimeHours = round($workEnd->diffInMinutes($actualOut) / 60, 2);
            }
        }

        $overtimeAmount = round($overtimeHours * $hourlyRate * (float) $setting->overtime_multiplier, 2);
        $discountAmount = round(($lateMinutes / 60) * $hourlyRate, 2);

        return [
            'status' => Attendance::STATUS_PRESENT,
            'late_minutes' => $lateMinutes,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'discount_amount' => $discountAmount,
        ];
    }

    /**
     * بتتنادى بعد ما يوم معين يتسجل غياب (أو يبطل غيابه - مثلاً بعد
     * موافقة على طلب إجازة) - بتمشي لقدام ولوراء من نفس اليوم وتوقف
     * أول ما تلاقي يوم شغل عادي، وعلى الطريق بتحط/تشيل خصم يوم كامل
     * على أي يوم إجازة أسبوعية/رسمية لقيتها متصلة بالغياب.
     *
     * ما بتلمسش أي يوم متسجل أصلاً بحالة تانية (حضور/إجازة معتمدة) عشان
     * ميتلخبطش مع بيانات حقيقية غير الإجازة الأسبوعية/الرسمية.
     */
    public function syncConnectedOffDayPenalty(Employee $employee, Carbon $date, HrSetting $setting): void
    {
        if (!$setting->shouldExtendDeductionToWeeklyOff()) {
            return;
        }

        $isUnauthorizedAbsence = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $date->toDateString())
            ->where('status', Attendance::STATUS_ABSENT)
            ->exists();

        $penaltyAmount = $isUnauthorizedAbsence
            ? round($employee->dailySalary() * $setting->absenceDeductionMultiplier(), 2)
            : 0.0;

        $this->walkAndPenalize($employee, $date->copy()->subDay(), -1, $setting, $penaltyAmount, $isUnauthorizedAbsence);
        $this->walkAndPenalize($employee, $date->copy()->addDay(), 1, $setting, $penaltyAmount, $isUnauthorizedAbsence);
    }

    private function walkAndPenalize(Employee $employee, Carbon $cursor, int $step, HrSetting $setting, float $penaltyAmount, bool $isUnauthorizedAbsence): void
    {
        while ($this->isHoliday($cursor, $employee->branch_id) || $this->isWeeklyOff($cursor, $setting)) {
            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'date' => $cursor->toDateString(),
            ]);

            // لو اليوم ده متسجل أصلاً بحالة حقيقية غير الإجازة الأسبوعية/
            // الرسمية (حضور، أو إجازة معتمدة) - نوقف السلسلة هنا خالص.
            if ($attendance->exists && !in_array($attendance->status, [Attendance::STATUS_WEEKEND, Attendance::STATUS_HOLIDAY], true)) {
                break;
            }

            if ($isUnauthorizedAbsence) {
                if (!$attendance->exists) {
                    $attendance->status = $this->isHoliday($cursor, $employee->branch_id) ? Attendance::STATUS_HOLIDAY : Attendance::STATUS_WEEKEND;
                    $attendance->source = Attendance::SOURCE_MANUAL;
                }
                $attendance->discount_amount = $penaltyAmount;
                $attendance->is_connected_penalty = true;
                $attendance->notes = __('attendance.connected_penalty_note', ['date' => $date->toDateString()]);
                $attendance->save();
            } elseif ($attendance->exists && $attendance->is_connected_penalty) {
                // كان فيه خصم متصل قبل كده وسببه بطل (اتوافق على إجازة
                // مثلاً) - نشيل الخصم من غير ما نمسح الصف.
                $attendance->discount_amount = 0;
                $attendance->is_connected_penalty = false;
                $attendance->notes = null;
                $attendance->save();
            }

            $cursor->addDays($step);
        }
    }

    private function isHoliday(Carbon $date, ?int $branchId): bool
    {
        return HrHoliday::whereDate('date', $date->toDateString())
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branchs_id')->orWhere('branchs_id', $branchId);
            })
            ->exists();
    }

    private function isWeeklyOff(Carbon $date, HrSetting $setting): bool
    {
        return in_array(strtolower($date->format('l')), $setting->offDays(), true);
    }
}
