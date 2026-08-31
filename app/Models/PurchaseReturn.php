<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'branch_id',
        'created_by',
        'refund_account_id',
        'return_number',
        'cost_center_id',
        'subtotal',
        'discount_amount',
        'invoice_level_discount',
        'tax_amount',
        'grand_total',
        'total_quantity',
        'reason',
        'return_date',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function refundAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'refund_account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * true = مرتجع آجل (تخفيض في رصيد المورد)، false = فيه استرداد فعلي
     * من حساب مالي (refund_account_id) - نفس فكرة Purchase::isCredit().
     */
    public function isCredit(): bool
    {
        return is_null($this->refund_account_id);
    }
}
