<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_price',
        'sale_price',
        'quantity',
        'returned_quantity',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'product_name_snapshot',
        'product_code_snapshot',
        'created_by',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * أصناف مرتجع المشتريات المسجلة على السطر ده (سطر واحد ممكن يترجع
     * على أكتر من دفعة/عملية إرجاع مختلفة).
     */
    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * الكمية المتاح إرجاعها من السطر ده = الكمية الأصلية ناقص اللي
     * اترجع منها قبل كده. محسوبة دايمًا لحظيًا (مش عمود مخزّن) عشان
     * تفضل متزامنة مع quantity/returned_quantity الفعليين.
     */
    public function getRemainingQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->returned_quantity;
    }
}
