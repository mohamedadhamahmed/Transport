<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * عهدة أصل (لابتوب، عربية، موبايل...) مع موظف - راجع تعليق الميجريشن
 * create_asset_custodies_table لسبب فصلها عن EmployeeLoan (السلف المالية).
 */
class AssetCustody extends Model
{
    public const CONDITION_NEW = 'new';
    public const CONDITION_GOOD = 'good';
    public const CONDITION_USED = 'used';
    public const CONDITION_DAMAGED = 'damaged';

    public const STATUS_WITH_EMPLOYEE = 'with_employee';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'employee_id',
        'item_name',
        'category',
        'serial_number',
        'value',
        'condition_on_issue',
        'condition_on_return',
        'issued_date',
        'expected_return_date',
        'returned_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'issued_date' => 'date',
        'expected_return_date' => 'date',
        'returned_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isWithEmployee(): bool
    {
        return $this->status === self::STATUS_WITH_EMPLOYEE;
    }
}
