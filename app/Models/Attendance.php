<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_HOLIDAY = 'holiday';
    public const STATUS_WEEKEND = 'weekend';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_IMPORT = 'import';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'overtime_hours',
        'overtime_amount',
        'discount_amount',
        'is_connected_penalty',
        'source',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'is_connected_penalty' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
