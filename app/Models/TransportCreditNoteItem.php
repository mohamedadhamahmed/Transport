<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportCreditNoteItem extends Model
{
    protected $fillable = ['transport_credit_note_id', 'transport_invoice_item_id', 'description', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function creditNote()
    {
        return $this->belongsTo(TransportCreditNote::class, 'transport_credit_note_id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(TransportInvoiceItem::class, 'transport_invoice_item_id');
    }
}
