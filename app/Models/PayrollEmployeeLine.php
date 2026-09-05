<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صف واحد لبند شخصي (راتب/مكافأة/خصم حضور) اتنفذ لموظف معيّن وقت ترحيل
 * رواتب شهر - راجع PayrollController@postJournal (الإنشاء) و
 * @destroyPosting (الإرجاع عند إلغاء الترحيل).
 */
class PayrollEmployeeLine extends Model
{
    public const TYPE_SALARY = 'salary';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_DEDUCTION = 'deduction';

    protected $fillable = [
        'payroll_posting_id',
        'employee_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollPosting(): BelongsTo
    {
        return $this->belongsTo(PayrollPosting::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
