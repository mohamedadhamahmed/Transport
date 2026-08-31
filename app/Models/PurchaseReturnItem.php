<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'purchase_item_id',
        'product_id',
        'unit_price',
        'quantity',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'product_name_snapshot',
        'product_code_snapshot',
        'created_by',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
