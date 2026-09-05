<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * طلب إجازة - سجل طلب/موافقة/رفض بسيط (بدون رصيد) لكل الأنواع ما عدا
 * "سنوية" اللي ليها رصيد فعلي في EmployeeLeaveBalance (راجع تعليق
 * الميجريشن الخاص بيه). الموافقة (LeaveRequestController@approve) هي
 * اللي بتعكس أثر الطلب على شاشة الحضور (Attendance) - بتحول أي يوم في
 * مدى الطلب لحالة "إجازة" بدل غياب، وتشيل أي خصم غياب متصل كان
 * مسجل عليه قبل كده.
 */
class LeaveRequest extends Model
{
    public const TYPE_ANNUAL = 'annual';
    public const TYPE_SICK = 'sick';
    public const TYPE_UNPAID = 'unpaid';
    public const TYPE_EMERGENCY = 'emergency';
    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * هل النوع ده بيتخصم من راتب الموظف لو اتوافق عليه؟ بس "بدون راتب"
     * - باقي الأنواع (سنوية/مرضي/طارئ/أخرى) مدفوعة بالكامل في النسخة دي.
     */
    public function isUnpaid(): bool
    {
        return $this->type === self::TYPE_UNPAID;
    }

    public function consumesAnnualBalance(): bool
    {
        return $this->type === self::TYPE_ANNUAL;
    }
}
