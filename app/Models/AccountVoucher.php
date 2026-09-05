<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountVoucher extends Model
{
    use HasFactory;

    public const TYPE_RECEIPT = 'receipt';

    public const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'voucher_number',
        'type',
        'voucher_date',
        'treasury_account_id',
        'description',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
    ];

    public function isReceipt(): bool
    {
        return $this->type === self::TYPE_RECEIPT;
    }

    public function treasuryAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'treasury_account_id');
    }

    /**
     * بنود السند (App\Models\AccountVoucherLine) - كل بند بيمثل طرف
     * تاني (counterpart_account) ومبلغ مستقل، مع بياناته الخاصة (مركز
     * تكلفة/بيان/ضريبة). راجع تعليق ميجريشن account_voucher_lines
     * لشرح الفكرة بالكامل (كانت الحقول دي على مستوى السند نفسه قبل
     * دعم "السندات المتعددة البنود").
     */
    public function lines()
    {
        return $this->hasMany(AccountVoucherLine::class, 'account_voucher_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * إجمالي مبلغ السند = مجموع مبالغ كل بنوده (المبلغ الفعلي اللي
     * اتحرك من/لحساب الخزينة). لو البنود متحمّلة مسبقًا (eager loaded)
     * بيستخدمها من غير أي استعلام إضافي، وإلا بيعمل lazy load عادي.
     */
    public function getTotalAmountAttribute(): float
    {
        return round((float) $this->lines->sum('amount'), 2);
    }
}
