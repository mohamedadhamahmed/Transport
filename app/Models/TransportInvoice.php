<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportInvoice extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'branch_id', 'created_by',
        'issue_date', 'issue_time', 'supply_from', 'supply_to', 'prices_include_tax',
        'trips_count', 'trips_total', 'transfers_total', 'discount_amount',
        'subtotal', 'tax_rate', 'tax_type', 'tax_amount', 'total',
        'payment_method', 'cash_amount', 'bank_amount', 'credit_amount',
        'po_number', 'transport_quotation_id', 'note',
        'is_draft', 'is_sent_to_zatca', 'zatca_status', 'zatca_invoice_uuid', 'zatca_document_type', 'zatca_signed_at',
        'zatca_hash', 'zatca_message', 'zatca_invoice_xml', 'zatca_cleared_invoice_xml',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'supply_from' => 'date',
        'supply_to' => 'date',
        'prices_include_tax' => 'boolean',
        'is_sent_to_zatca' => 'boolean',
        'is_draft' => 'boolean',
        'zatca_signed_at' => 'datetime',
        'tax_rate' => 'float',
        'trips_total' => 'decimal:2',
        'transfers_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'bank_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

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
        return $this->hasMany(TransportInvoiceItem::class);
    }

    /** الإشعارات الدائنة على الفاتورة */
    public function creditNotes()
    {
        return $this->hasMany(TransportCreditNote::class, 'transport_invoice_id');
    }

    /** الأحمال (حركة الشاحنات) اللي اتفوترت في الفاتورة دي */
    public function loads()
    {
        return $this->hasMany(TruckLoad::class, 'transport_invoice_id');
    }

    /** ضريبية (للعميل رقم ضريبي) / مبسطة */
    public function isSimplified(): bool
    {
        return !preg_match('/^\d{15}$/', (string) $this->customer?->tax_number);
    }

    /**
     * الفاتورة معمولة من شاشة "فاتورة نقل جديدة" (أحمال + بنود يدوية)؟
     * الفواتير القديمة (نقلات بشاحنات + تحويلات) بتتعدّل من الفورم القديم.
     */
    public function usesLoadsForm(): bool
    {
        if ($this->tax_type === 'custom' || $this->is_draft) {
            return false;
        }

        return $this->items->isEmpty()
            || $this->items->contains(fn ($i) => $i->truck_load_id || $i->description);
    }

    /**
     * QR زاتكا (المرحلة التانية) من الـ XML الموقّع بعد الإرسال - لو
     * الفاتورة لسه متبعتتش بيرجع null (والطباعة بتستخدم QR المرحلة الأولى).
     */
    public function zatcaQrFromXml(): ?string
    {
        $xml = $this->zatca_cleared_invoice_xml ? base64_decode($this->zatca_cleared_invoice_xml, true) ?: $this->zatca_cleared_invoice_xml : $this->zatca_invoice_xml;
        if (!$xml) {
            return null;
        }
        if (preg_match('#<cbc:ID>QR</cbc:ID>.*?<cbc:EmbeddedDocumentBinaryObject[^>]*>([^<]+)</cbc:EmbeddedDocumentBinaryObject>#s', $xml, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private static ?bool $zatcaLinked = null;

    /** النظام مربوط بالزكاة (فيه إعدادات فيها شهادة) */
    public static function zatcaLinked(): bool
    {
        if (self::$zatcaLinked === null) {
            try {
                self::$zatcaLinked = Setting::query()
                    ->where(fn ($q) => $q->whereNotNull('production_certificate')->where('production_certificate', '!=', ''))
                    ->exists();
            } catch (\Throwable $e) {
                self::$zatcaLinked = false;
            }
        }

        return self::$zatcaLinked;
    }

    /**
     * ينفع تتعدّل/تتحذف؟ المسودة دايمًا آه. الفاتورة المعتمدة: لأ لو النظام
     * مربوط بالزكاة أو اتبعتت أو عليها إشعار دائن - التصحيح بإشعار دائن.
     */
    public function isEditable(): bool
    {
        if ($this->is_draft) {
            return true;
        }

        return !self::zatcaLinked() && !$this->is_sent_to_zatca && !$this->creditNotes()->exists();
    }

    /** فاتورة اتبعتت لزاتكا بنجاح - مينفعش تتعدّل أو تتحذف */
    public function isLockedByZatca(): bool
    {
        return (bool) $this->is_sent_to_zatca;
    }
}
