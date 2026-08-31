<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountVoucher extends Model
{
    use HasFactory;

    public const TYPE_RECEIPT = 'receipt';

    public const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'voucher_number',
        'type',
        'voucher_date',
        'treasury_account_id',
        'counterpart_account_id',
        'amount',
        'description',
        'branch_id',
        'cost_center_id',
        'created_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function isReceipt(): bool
    {
        return $this->type === self::TYPE_RECEIPT;
    }

    public function treasuryAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'treasury_account_id');
    }

    public function counterpartAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'counterpart_account_id');
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
