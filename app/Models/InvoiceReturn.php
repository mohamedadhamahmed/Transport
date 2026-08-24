<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * موديل جدول invoice_returns - صف واحد لكل "صنف مرتجع" من فاتورة معينة.
 * أكتر من صف ممكن يشتركوا في نفس reference_value لو رجعوا في نفس العملية.
 */
class InvoiceReturn extends Model
{
    protected $table = 'invoice_returns';

    protected $fillable = [
        'invoice_id',
        'product_id',
        'branch_id',
        'reference_value',
        'unit_price',
        'quantity',
        'tax_amount',
        'tax_rate',
        'discount_amount',
        'invoice_discount_amount',
        'card_refund_amount',
        'is_sent_to_zatca',
        'created_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'invoice_discount_amount' => 'decimal:2',
        'card_refund_amount' => 'decimal:2',
        'is_sent_to_zatca' => 'boolean',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
