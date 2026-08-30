<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * موديل رأس فاتورة التسليم (بدون ضريبة)
 * الجدول: delivery_to_customer_withoud_tax_invoices
 */
class delivery_to_customer_withoud_tax_invoices extends Model
{
    protected $table = 'delivery_to_customer_withoud_tax_invoices';

    protected $fillable = [
        'customer_id',
        'user_id',
        'Price',
        'Added_Value',
        'Number_of_Quantity',
        'Pay',
        'branchs_id',
        'discount',
        'discountOnInvoice',
        'discountOnProduct',
        'note',
        'status',
        'save',
        'cashamount',
        'bankamount',
        'creaditamount',
        'morepayment_way',
        'Bank_transfer',
        'signing_time',
        'xml',
        'document_type',
        'invoice_type',
        'sent_to_zatca',
        'sent_to_zatca_status',
        'invoiceUUid',
        'invoice_number',
        'invoice_counter',
        'issue_time',
        'issue_date',
        'hash',
        'qr_zatca',
        'xmltags',
        'currentblance',
        'NOTICE_Number',
        'xmltags_return',
        'xml_return',
        'hash_return',
        'sent_to_zatca_status_return',
        'qr_zatca_return',
        'issue_date_return',
        'issue_time_return',
        'display_number',
        'payment_return',
        'clearedInvoice',
        'uuid',
    ];

    protected $casts = [
        'Price' => 'double',
        'Added_Value' => 'double',
        'Number_of_Quantity' => 'double',
        'discount' => 'decimal:2',
        'discountOnInvoice' => 'double',
        'discountOnProduct' => 'double',
        'status' => 'integer',
        'save' => 'integer',
        'cashamount' => 'double',
        'bankamount' => 'double',
        'creaditamount' => 'double',
        'currentblance' => 'double',
        'signing_time' => 'datetime',
        'issue_time' => 'datetime:H:i:s',
        'issue_date' => 'date',
        'issue_time_return' => 'datetime:H:i:s',
        'issue_date_return' => 'date',
    ];

    /**
     * علاقة العميل
     * عدّل اسم الموديل \App\Models\Customers::class حسب اسم موديل العملاء الفعلي عندك
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    /**
     * علاقة الموظف الذي أنشأ السند
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * علاقة الفرع
     * عدّل اسم الموديل \App\Models\Branchs::class حسب اسم موديل الفروع الفعلي عندك
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branchs_id');
    }

    /**
     * علاقة بنود الفاتورة (المنتجات)
     */
    public function items()
    {
        return $this->hasMany(\App\Models\sales_withoud_taxes::class, 'invoice_id');
    }
}
