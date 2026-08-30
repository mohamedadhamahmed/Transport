<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * موديل بنود سند التسليم (المنتجات - بدون أثر مالي حتى الاعتماد)
 * الجدول: delivery_note_item (بعد إعادة التسمية من sales_withoud_taxes)
 */
class DeliveryNoteItem extends Model
{
    protected $table = 'delivery_note_item';

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
        'invoiced_quantity',
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
        'invoiced_quantity' => 'double',
        'save' => 'integer',
        'reamingQuantity' => 'double',
    ];

    /**
     * علاقة الفاتورة (السند) الأصلية
     */
    public function invoice()
    {
        return $this->belongsTo(\App\Models\DeliveryNote::class, 'invoice_id');
    }

    /**
     * علاقة المنتج (الموديل الصحيح Product مفرد، مطابق لنظام الفواتير الحقيقي)
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
        return $this->belongsTo(\App\Models\Branchs::class, 'branch_id');
    }

    /**
     * الكمية المتاحة للإرجاع (خاصية محسوبة)
     */
    public function getAvailableToReturnAttribute()
    {
        return $this->quantity - $this->quantityreturn;
    }

    /**
     * الكمية المتاحة للتحويل لفاتورة (بعد استبعاد المرتجع والمُفوتَر بالفعل)
     */
    public function getAvailableToInvoiceAttribute()
    {
        return $this->quantity - $this->quantityreturn - $this->invoiced_quantity;
    }

    /**
     * سجل التحويلات لفواتير حقيقية (Audit Trail)
     */
    public function invoiceLinks()
    {
        return $this->hasMany(\App\Models\DeliveryInvoiceLink::class, 'sales_item_id');
    }

    /**
     * إجمالي البند بعد الخصم (خاصية محسوبة)
     */
    public function getLineTotalAttribute()
    {
        return ($this->quantity * $this->Unit_Price) - $this->Discount_Value;
    }
}
