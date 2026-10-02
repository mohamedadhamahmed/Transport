<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckStoreIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_number',
        'issue_date',
        'truck_id',
        'driver_id',
        'cost_center_id',
        'branch_id',
        'expense_category',
        'current_odometer',
        'oil_change_interval_km',
        'next_oil_change_odometer',
        'inventory_account_id',
        'expense_account_id',
        'journal_entry_id',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total_amount' => 'decimal:2',
        'current_odometer' => 'integer',
        'oil_change_interval_km' => 'integer',
        'next_oil_change_odometer' => 'integer',
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'inventory_account_id');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'expense_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(TruckStoreIssueItem::class);
    }

    public function categoryLabel(): string
    {
        return AccountVoucher::EXPENSE_CATEGORIES[$this->expense_category] ?? ($this->expense_category ?: 'صيانة');
    }

    public static function nextNumber(): string
    {
        $max = (int) (static::max('id') ?? 0) + 1;
        $num = 'TSI-' . str_pad((string) $max, 6, '0', STR_PAD_LEFT);
        while (static::where('issue_number', $num)->exists()) {
            $max++;
            $num = 'TSI-' . str_pad((string) $max, 6, '0', STR_PAD_LEFT);
        }
        return $num;
    }
}
