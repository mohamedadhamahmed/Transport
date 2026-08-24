<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * جدول حركات القيد (دائن/مدين) - نفس أعمدة الموديل القديم
 * App\Models\credittransactions بالظبط، بس بإسم موديل صحيح متوافق
 * مع تسمية Laravel (PascalCase مفرد).
 *
 * اسم الجدول الفعلي في قاعدة البيانات "credittransactions" (من غير
 * underscore) - ده بيخالف تخمين Eloquent الافتراضي لموديل اسمه
 * CreditTransaction (اللي كان هيبقى credit_transactions)، فلازم
 * نحدد $table صريح تحت عشان يفضل يشاور على نفس الجدول.
 *
 * ملاحظة: علاقة costCenter() بتشاور على موديل/جدول CostCenter اللي
 * لسه مش متبني في المشروع ده - هتحتاجي تعمليه لو هتستخدمي مراكز
 * التكلفة فعليًا.
 */
class CreditTransaction extends Model
{
    use HasFactory;

    protected $table = 'credittransactions';

    // أنواع العملية (عمود operation_type)
    public const TYPE_SALES = 1;

    public const TYPE_PURCHASES = 2;

    public const TYPE_RECEIPT_VOUCHER = 3;

    public const TYPE_PAYMENT_VOUCHER = 4;

    public const TYPE_DAILY_ENTRY = 5;

    public const TYPE_OPENING_ENTRY = 6;

    public const OPERATION_TYPE_LABELS = [
        self::TYPE_SALES => 'مبيعات',
        self::TYPE_PURCHASES => 'مشتريات',
        self::TYPE_RECEIPT_VOUCHER => 'سند قبض',
        self::TYPE_PAYMENT_VOUCHER => 'صرف',
        self::TYPE_DAILY_ENTRY => 'قيد يومية',
        self::TYPE_OPENING_ENTRY => 'قيد افتتاحي',
    ];

    protected $fillable = [
        'customer_id',
        'user_id',
        'recive_amount',
        'note',
        'currentblance',
        'pay_method',
        'branchs_id',
        'Pay_Method_Name',
        'created_at',
        'updated_at',
        'attachments',
        'orginal_type',
        'orginal_id',
        'dely_record',
        'parent_dely_record',
        'debtor',
        'creditor',
        'vat',
        'name',
        'tax',
        'decument_id',
        'invoice_number',
        'type_decument',
        'save',
        'Opening_entry',
        'parent_Opening_entry',
        'date_export',
        'sent_abd_count',
        'sent_serf_count',
        'type',
        'operation_type',
        'cost_center',
    ];

    protected $casts = [
        'recive_amount' => 'decimal:2',
        'currentblance' => 'decimal:2',
        'debtor' => 'decimal:2',
        'creditor' => 'decimal:2',
        'vat' => 'decimal:2',
        'tax' => 'decimal:2',
        'dely_record' => 'boolean',
        'save' => 'boolean',
        'Opening_entry' => 'boolean',
        'date_export' => 'date',
        'operation_type' => 'integer',
    ];

    /**
     * اسم نوع العملية بالعربي (مبيعات / مشتريات / سند قبض ... إلخ)
     * بناءً على قيمة operation_type. لو القيمة مش من الـ 6 المعروفين
     * بترجع null.
     */
    public function getOperationTypeLabelAttribute(): ?string
    {
        return self::OPERATION_TYPE_LABELS[$this->operation_type] ?? null;
    }

    // public function costCenter()
    // {
    //     return $this->belongsTo(CostCenter::class, 'cost_center');
    // }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'orginal_id');
    }

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchs_id');
    }
}
