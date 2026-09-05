<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EndOfServiceSettlement extends Model
{
    public const TYPE_RESIGNATION = 'resignation';
    public const TYPE_TERMINATION = 'termination';
    public const TYPE_CONTRACT_END = 'contract_end';
    public const TYPE_TERMINATION_FOR_CAUSE = 'termination_for_cause';
    public const TYPE_DEATH = 'death';

    protected $fillable = [
        'document_number',
        'employee_id',
        'termination_type',
        'hire_date',
        'termination_date',
        'years_of_service',
        'wage_basis',
        'gross_amount',
        'applied_percentage',
        'net_amount',
        'payment_treasury_account_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'years_of_service' => 'decimal:2',
        'wage_basis' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'applied_percentage' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'payment_treasury_account_id');
    }

    public static function nextDocumentNumber(): string
    {
        $next = (int) (self::max('id') ?? 0) + 1;

        return 'EOS-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
