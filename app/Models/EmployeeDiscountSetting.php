<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDiscountSetting extends Model
{
    protected $fillable = ['user_id', 'branchs_id', 'max_discount'];

    protected $casts = [
        'max_discount' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branchs_id');
    }

    /**
     * يجيب أقصى نسبة خصم مسموحة لموظف معين في فرع معين.
     * لو الموظف ماله نسبة خاصة، يرجع نسبة الفرع الافتراضية من system_settings.
     */
    public static function maxDiscountFor(int $userId, int $branchId): float
    {
        $override = self::where('user_id', $userId)
            ->where('branchs_id', $branchId)
            ->value('max_discount');

        if ($override !== null) {
            return (float) $override;
        }

        return (float) SystemSetting::where('branchs_id', $branchId)
            ->value('max_employee_discount') ?? 0;
    }
}