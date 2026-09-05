<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * رصيد الإجازة السنوية للموظف - راجع تعليق ميجريشن
 * create_employee_leave_balances_table لسبب اقتصار الرصيد على نوع
 * "سنوية" بس.
 */
class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'balance_days',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'balance_days' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * رصيد الموظف - لو أول مرة (سجل جديد) بيتحدد الرصيد الافتراضي حسب
     * نظام العمل السعودي: 30 يوم لو خدمته 5 سنين فأكثر، 21 يوم غير كده.
     */
    public static function forEmployee(Employee $employee): self
    {
        $balance = self::firstOrNew(['employee_id' => $employee->id]);

        if (!$balance->exists) {
            $yearsOfService = $employee->hire_date
                ? $employee->hire_date->diffInYears(now())
                : 0;

            $balance->balance_days = $yearsOfService >= 5 ? 30 : 21;
        }

        return $balance;
    }
}
