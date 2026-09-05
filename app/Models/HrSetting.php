<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model
{
    protected $table = 'hr_settings';

    protected $fillable = [
        'branchs_id',
        'work_start_time',
        'work_end_time',
        'late_grace_minutes',
        'overtime_multiplier',
        'weekly_off_days',
        'absence_deduction_multiplier',
        'extend_deduction_to_weekly_off',
    ];

    protected $casts = [
        'weekly_off_days' => 'array',
        'overtime_multiplier' => 'float',
        'absence_deduction_multiplier' => 'float',
        'extend_deduction_to_weekly_off' => 'boolean',
    ];

    /**
     * أيام الإجازة الأسبوعية الافتراضية لو الفرع لسه ما ظبطش الإعداد ده -
     * الجمعة بس (الأكثر شيوعًا)، قابلة للتعديل من شاشة إعدادات الموارد
     * البشرية.
     */
    public function offDays(): array
    {
        return $this->weekly_off_days ?: ['friday'];
    }

    /**
     * مضاعف قيمة الغياب - افتراضي 1 (يوم كامل) لو الفرع لسه ما ظبطش
     * الإعداد (سجل جديد بـ firstOrNew لسه ما اتحفظش).
     */
    public function absenceDeductionMultiplier(): float
    {
        return (float) ($this->absence_deduction_multiplier ?: 1.0);
    }

    /**
     * هل نمد خصم الغياب غير المصرح به لأيام الإجازة الأسبوعية المتصلة
     * بيه؟ افتراضي مفعّل (true) لو الإعداد لسه ما اتسجلش (سجل جديد).
     */
    public function shouldExtendDeductionToWeeklyOff(): bool
    {
        return $this->exists ? (bool) $this->extend_deduction_to_weekly_off : true;
    }

    public static function forBranch(?int $branchId): self
    {
        return self::firstOrNew(['branchs_id' => $branchId ?: 1]);
    }
}
