<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceReturnItem extends Model
{
    protected $fillable = [
        'invoice_return_id',
        'invoice_item_id',
        'product_id',
        'product_name_snapshot',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoiceReturn()
    {
        return $this->belongsTo(InvoiceReturn::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
