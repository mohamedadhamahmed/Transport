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

    /**
     * هل الفاتورة مربوطة/مرسلة لهيئة الزكاة والضريبة والجمارك (ZATCA) بنجاح؟
     */
    public function isLinkedToZatca(): bool
    {
        return (bool) ($this->is_sent_to_zatca || $this->zatca_status === 'PASS');
    }

    /**
     * الفاتورة قابلة للتعديل بس لو:
     * 1) غير مربوطة بهيئة الزكاة (لو مافيش ربط) - الفواتير المرسلة والمربوطة
     *    مع هيئة الزكاة (ZATCA) يمنع تعديلها قانونياً وفنياً حسب متطلبات الهيئة.
     * 2) معتمدة فعلاً (is_finalized) - المسودات ليها شاشة تعديل منفصلة
     *    (DraftInvoiceController) مالهاش علاقة بالفاتورة دي أصلاً.
     * 3) عندها بنود.
     * 4) مفيش أي بند فيها اترجع منه أي كمية (مرتجع مبيعات) - عشان منطق
     *    التعديل والإرجاع مش متزامنين مع بعض، ومنطق الإرجاع بيعتمد على
     *    remaining_quantity اللي هيتمسح مع البند لو عدّلنا.
     */
    public function isEditable(): bool
    {
        if ($this->isLinkedToZatca()) {
            return false;
        }

        if (!$this->is_finalized) {
            return false;
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        if ($items->isEmpty()) {
            return false;
        }

        foreach ($items as $item) {
            if ((float) $item->returned_quantity > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * سبب عدم قابلية التعديل لتوضيحه للمستخدم في الواجهة
     */
    public function getNonEditableReason(): ?string
    {
        if ($this->isLinkedToZatca()) {
            return __('invoices.zatca_linked_not_editable');
        }

        if (!$this->is_finalized) {
            return __('invoices.draft_not_editable_here');
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        if ($items->isEmpty()) {
            return __('invoices.no_items_error');
        }

        foreach ($items as $item) {
            if ((float) $item->returned_quantity > 0) {
                return __('invoices.has_returns_not_editable');
            }
        }

        return null;
    }
}
