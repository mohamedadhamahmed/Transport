<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'created_by',
        'order_number',
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
        'status',
        'converted_purchase_id',
        'converted_by',
        'converted_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'converted_at' => 'datetime',
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
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function convertedPurchase()
    {
        return $this->belongsTo(Purchase::class, 'converted_purchase_id');
    }

    public function convertedByUser()
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
