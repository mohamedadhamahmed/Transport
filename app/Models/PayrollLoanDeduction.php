<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صف واحد لكل خصم سلفة اتنفذ فعليًا وقت ترحيل رواتب شهر معيّن - راجع
 * PayrollController@postLoanDeductionsForEmployee (الإنشاء) و
 * @destroyPosting (الإرجاع عند إلغاء الترحيل).
 */
class PayrollLoanDeduction extends Model
{
    protected $fillable = [
        'payroll_posting_id',
        'employee_loan_id',
        'employee_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollPosting(): BelongsTo
    {
        return $this->belongsTo(PayrollPosting::class);
    }

    public function employeeLoan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
