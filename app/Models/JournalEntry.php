<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    public const TYPE_DAILY = 'daily';

    public const TYPE_OPENING = 'opening';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'entry_type',
        'description',
        'branch_id',
        'cost_center_id',
        'created_by',
        'total_debit',
        'total_credit',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function isOpening(): bool
    {
        return $this->entry_type === self::TYPE_OPENING;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
