<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * موديل بنود فاتورة التسليم (المنتجات - بدون ضريبة)
 * الجدول: sales_withoud_taxes
 */
class sales_withoud_taxes extends Model
{
    protected $table = 'sales_withoud_taxes';

    protected $fillable = [
        'product_id',
        'invoice_id',
        'Discount_Value',
        'branch_id',
        'Added_Value',
        'Unit_Price',
        'quantity',
        'discountreturn',
        'quantityreturn',
        'save',
        'reamingQuantity',
        'unit',
    ];

    protected $casts = [
        'Discount_Value' => 'decimal:2',
        'Added_Value' => 'decimal:2',
        'Unit_Price' => 'decimal:2',
        'quantity' => 'double',
        'discountreturn' => 'double',
        'quantityreturn' => 'double',
        'save' => 'integer',
        'reamingQuantity' => 'double',
    ];

    /**
     * علاقة الفاتورة (السند) الأصلية
     */
    public function invoice()
    {
        return $this->belongsTo(\App\Models\delivery_to_customer_withoud_tax_invoices::class, 'invoice_id');
    }

    /**
     * علاقة المنتج
     * عدّل اسم الموديل \App\Models\Products::class حسب اسم موديل المنتجات الفعلي عندك
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    /**
     * علاقة الفرع
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    /**
     * الكمية المتاحة للإرجاع (خاصية محسوبة)
     */
    public function getAvailableToReturnAttribute()
    {
        return $this->quantity - $this->quantityreturn;
    }

    /**
     * إجمالي البند بعد الخصم (خاصية محسوبة)
     */
    public function getLineTotalAttribute()
    {
        return ($this->quantity * $this->Unit_Price) - $this->Discount_Value;
    }
}
