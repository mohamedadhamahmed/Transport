<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * جدول الحسابات المالية (شجرة الحسابات) - نفس أعمدة الموديل القديم
 * App\Models\financial_accounts بالظبط، بس بإسم موديل صحيح متوافق
 * مع تسمية Laravel (PascalCase مفرد). اسم الجدول نفسه في قاعدة
 * البيانات فضل زي ما هو (financial_accounts) لإنه بيتطابق أصلاً مع
 * تخمين Eloquent الافتراضي لاسم الجدول.
 *
 * ملاحظة: علاقة accountType() بتشاور على موديل/جدول acounts_type
 * اللي لسه مش متبني في المشروع ده - هتحتاجي تعمليه لو هتستخدمي
 * الحقل ده فعليًا.
 */
class FinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'account_type', 'parent_account_number', 'account_number',
        'start_balance', 'current_balance', 'other_table_FK', 'notes',
        'created_at', 'updated_at', 'added_by', 'updated_by', 'com_code', 'date',
        'active', 'is_parent', 'start_balance_status', 'orginal_id',
        'orginal_type',
        'orginal_supplier',
        'debtor_end',
        'creditor_end',
        'debtor_current',
        'creditor_current',
        'debtor_opening',
        'creditor_opening',
        'branchs_id',
        'tax_no',
    ];

    protected $casts = [
        'date' => 'date',
        'active' => 'boolean',
        'is_parent' => 'boolean',
        'start_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'debtor_end' => 'decimal:2',
        'creditor_end' => 'decimal:2',
        'debtor_current' => 'decimal:2',
        'creditor_current' => 'decimal:2',
        'debtor_opening' => 'decimal:2',
        'creditor_opening' => 'decimal:2',
    ];

    public function creditTransactions()
    {
        // customer_id هو العمود اللي بيربط الحركات بالحساب المالي
        return $this->hasMany(CreditTransaction::class, 'customer_id');
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type');
    }

    public function parentAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'parent_account_number');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchs_id');
    }
}
