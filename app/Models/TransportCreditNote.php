<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** إشعار دائن على فاتورة نقليات */
class TransportCreditNote extends Model
{
    protected $fillable = [
        'credit_note_number', 'transport_invoice_id', 'customer_id', 'branch_id', 'created_by',
        'issue_date', 'issue_time', 'reason', 'release_loads',
        'subtotal', 'tax_rate', 'tax_type', 'tax_amount', 'total',
        'is_sent_to_zatca', 'zatca_status', 'zatca_invoice_uuid', 'zatca_document_type', 'zatca_signed_at',
        'zatca_hash', 'zatca_message', 'zatca_invoice_xml', 'zatca_cleared_invoice_xml',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'release_loads' => 'boolean',
        'is_sent_to_zatca' => 'boolean',
        'zatca_signed_at' => 'datetime',
        'tax_rate' => 'float',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(TransportInvoice::class, 'transport_invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(TransportCreditNoteItem::class);
    }

    public function isSimplified(): bool
    {
        return !preg_match('/^\d{15}$/', (string) $this->customer?->tax_number);
    }

    /** QR المرحلة التانية من الـ XML الموقّع (بعد الإرسال) */
    public function zatcaQrFromXml(): ?string
    {
        $xml = $this->zatca_cleared_invoice_xml ? base64_decode($this->zatca_cleared_invoice_xml, true) ?: $this->zatca_cleared_invoice_xml : $this->zatca_invoice_xml;
        if ($xml && preg_match('#<cbc:ID>QR</cbc:ID>.*?<cbc:EmbeddedDocumentBinaryObject[^>]*>([^<]+)</cbc:EmbeddedDocumentBinaryObject>#s', $xml, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
