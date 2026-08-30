<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'created_by',
        'payment_account_id',
        'supplier_invoice_number',
        'purchase_number',
        'warehouse_name',
        'cost_center',
        'cost_center_id',
        'shipping_fee',
        'subtotal',
        'discount_amount',
        'invoice_level_discount',
        'tax_amount',
        'grand_total',
        'total_quantity',
        'note',
        'issue_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

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
        return $this->hasMany(PurchaseItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'payment_account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function isCredit(): bool
    {
        return is_null($this->payment_account_id);
    }
}
