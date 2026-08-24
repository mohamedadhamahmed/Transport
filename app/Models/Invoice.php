<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'created_by',
        'branch_id',
        'receiving_branch_id',

        'subtotal',
        'tax_amount',
        'total_quantity',
        'discount_amount',
        'invoice_level_discount',
        'product_level_discount',

        'payment_method',
        'has_multiple_payment_methods',
        'cash_amount',
        'bank_amount',
        'credit_amount',
        'wire_transfer_amount',
        'customer_balance_after',

        'status',
        'is_finalized',
        'note',

        'invoice_number',
        'display_sequence_number',
        'local_uuid',
        'purchase_order_number',
        'notice_number',
        'return_payment_method',

        'issue_time',
        'issue_date',

        'is_sent_to_zatca',
        'zatca_status',
        'zatca_invoice_uuid',
        'zatca_signed_at',
        'zatca_invoice_xml',
        'zatca_document_type',
        'zatca_invoice_type',
        'zatca_invoice_counter',
        'zatca_hash',
        'zatca_qr_code',
        'zatca_xml_tags',
        'zatca_cleared_invoice_xml',

        'credit_note_issue_date',
        'credit_note_issue_time',
        'credit_note_zatca_xml_tags',
        'credit_note_zatca_xml',
        'credit_note_zatca_hash',
        'credit_note_zatca_status',
        'credit_note_zatca_qr_code',
        'tax_rate'
    ];

    protected $casts = [
        'has_multiple_payment_methods' => 'boolean',
        'is_finalized' => 'boolean',
        'is_sent_to_zatca' => 'boolean',
        'issue_date' => 'date',
        'credit_note_issue_date' => 'date',
        'zatca_signed_at' => 'datetime',
        'tax_rate' => 'float', // <-- تحويل الحقل إلى رقم عشري تلقائياً
    ];
protected $attributes = [
        'tax_rate' => 0.15, // القيمة الافتراضية لنسبة الضريبة
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * الفرع المستلم - في فواتير التحويل بين الفروع
     */
    public function receivingBranch()
    {
        return $this->belongsTo(Branch::class, 'receiving_branch_id');
    }

    /**
     * أصناف الفاتورة
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * الأصناف المرتجعة من الفاتورة دي
     */
    public function returns()
    {
        return $this->hasMany(InvoiceReturn::class);
    }
}
