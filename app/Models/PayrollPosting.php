<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPosting extends Model
{
    // اتصرف فورًا وقت الترحيل من حساب خزينة مختار (السلوك الافتراضي/القديم).
    public const FUNDING_SOURCE_TREASURY = 'treasury';

    // اتقيّد كمستحق على حساب "مستحقات رواتب الموظفين" - لسه ما اتصرفش
    // فعليًا، راجع HrAccountService::payrollPayableAccountId().
    public const FUNDING_SOURCE_PAYABLE = 'payable';

    protected $fillable = [
        'document_number',
        'month',
        'treasury_account_id',
        'funding_source',
        'total_gross',
        'total_bonus',
        'total_deductions',
        'total_loan_deductions',
        'total_net',
        'paid_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'month' => 'date',
        'total_gross' => 'decimal:2',
        'total_bonus' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_loan_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'treasury_account_id');
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * ترحيل مستحق (استحقاق) لسه ما اتصرفش - يعني net_pay مقيّد على
     * "مستحقات رواتب الموظفين" مش خزينة فعلية، وبيحتاج تسديد لاحق
     * (PayrollController@payAccruedPosting).
     */
    public function isAwaitingPayment(): bool
    {
        return $this->funding_source === self::FUNDING_SOURCE_PAYABLE && !$this->isPaid();
    }

    /**
     * تفاصيل خصومات السلف اللي اتنفذت في الترحيل ده - سلفة سلفة، عشان
     * إلغاء الترحيل (PayrollController@destroyPosting) يقدر يرجع كل واحدة
     * لصاحبها بالظبط.
     */
    public function loanDeductions(): HasMany
    {
        return $this->hasMany(PayrollLoanDeduction::class);
    }

    /**
     * تفاصيل بنود الرواتب/المكافآت/خصومات الحضور الشخصية اللي اتنفذت في
     * الترحيل ده - موظف موظف وبند بند، عشان إلغاء الترحيل
     * (PayrollController@destroyPosting) يقدر يرجع كل بند لصاحبه بالظبط
     * على حسابه الشخصي (بدل حساب مجموعة مشترك زي قبل).
     */
    public function employeeLines(): HasMany
    {
        return $this->hasMany(PayrollEmployeeLine::class);
    }

    public static function nextDocumentNumber(): string
    {
        $next = (int) (self::max('id') ?? 0) + 1;

        return 'PAY-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
