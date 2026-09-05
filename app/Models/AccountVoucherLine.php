<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * بند من بنود سند القبض/الصرف (App\Models\AccountVoucher::lines) -
 * راجع تعليق ميجريشن account_voucher_lines لشرح الفكرة بالكامل.
 */
class AccountVoucherLine extends Model
{
    protected $fillable = [
        'account_voucher_id',
        'counterpart_account_id',
        'amount',
        'description',
        'cost_center_id',
        'is_taxable',
        'tax_id',
        'tax_rate',
        'net_amount',
        'tax_amount',
        'vat_account_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'tax_rate' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function voucher()
    {
        return $this->belongsTo(AccountVoucher::class, 'account_voucher_id');
    }

    public function counterpartAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'counterpart_account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function vatAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'vat_account_id');
    }
}
