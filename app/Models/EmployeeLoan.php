<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    public const TYPE_LOAN = 'loan';
    public const TYPE_CUSTODY = 'custody';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SETTLED = 'settled';

    protected $fillable = [
        'document_number',
        'employee_id',
        'type',
        'treasury_account_id',
        'amount',
        'monthly_installment',
        'paid_amount',
        'date',
        'description',
        'status',
        'settled_at',
        'settled_treasury_account_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'settled_at' => 'date',
        'amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function treasuryAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'treasury_account_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public static function nextDocumentNumber(string $type): string
    {
        $prefix = $type === self::TYPE_CUSTODY ? 'CUS' : 'LOAN';
        $next = (int) (self::max('id') ?? 0) + 1;

        return $prefix . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * المتبقي فعليًا بعد خصم أي أقساط اتخصمت من الرواتب لحد دلوقتي
     * (راجع PayrollController@postMonth).
     */
    public function remainingAmount(): float
    {
        return max(0, round((float) $this->amount - (float) $this->paid_amount, 2));
    }

    /**
     * قيمة القسط اللي المفروض يتخصم في كشف الرواتب الجاي - القسط
     * الشهري المتفق عليه، أو المتبقي بالكامل لو مفيش قسط محدد (سلفة
     * بتتخصم دفعة واحدة في أقرب راتب).
     */
    public function plannedDeduction(): float
    {
        $remaining = $this->remainingAmount();
        if ($remaining <= 0) {
            return 0;
        }

        $installment = (float) $this->monthly_installment;

        return $installment > 0 ? min($installment, $remaining) : $remaining;
    }
}
