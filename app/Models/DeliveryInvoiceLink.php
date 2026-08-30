<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سجل تتبع (Audit Trail): يربط كل بند تسليم بالفاتورة الضريبية الحقيقية
 * التي حُوِّل إليها (كليًا أو جزئيًا)، ويحفظ الكمية والسعر وقت التحويل.
 */
class DeliveryInvoiceLink extends Model
{
    protected $table = 'delivery_invoice_links';

    protected $fillable = [
        'sales_item_id',
        'invoice_id',
        'invoice_item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'double',
        'unit_price' => 'decimal:2',
    ];

    public function salesItem()
    {
        return $this->belongsTo(\App\Models\DeliveryNoteItem::class, 'sales_item_id');
    }

    /**
     * الفاتورة الضريبية الحقيقية الناتجة (موديل Invoice في نظام الفواتير الأصلي)
     */
    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id');
    }
}
