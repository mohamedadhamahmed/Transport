<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    public const TYPE_DAILY = 'daily';

    public const TYPE_OPENING = 'opening';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'entry_type',
        'description',
        'branch_id',
        'cost_center_id',
        'source_type',
        'source_id',
        'is_auto',
        'created_by',
        'total_debit',
        'total_credit',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_auto' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class, 'journal_entry_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function isAuto(): bool
    {
        return (bool) $this->is_auto;
    }

    public function getSourceUrl(): ?string
    {
        if (! $this->source_type || ! $this->source_id) {
            return null;
        }

        try {
            return match ($this->source_type) {
                \App\Models\Invoice::class => route('invoices.show', $this->source_id),
                \App\Models\Purchase::class => route('purchases.show', $this->source_id),
                \App\Models\AccountVoucher::class => route('vouchers.show', $this->source_id),
                \App\Models\PurchaseReturn::class => route('purchases.returns.show', $this->source_id),
                \App\Models\InvoiceReturn::class => route('invoices.returns.index'),
                default => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getSourceLabel(): ?string
    {
        if (! $this->source_type) {
            return null;
        }

        return match ($this->source_type) {
            \App\Models\Invoice::class => 'فاتورة مبيعات',
            \App\Models\Purchase::class => 'فاتورة مشتريات',
            \App\Models\AccountVoucher::class => 'سند مالي',
            \App\Models\InvoiceReturn::class => 'مرتجع مبيعات',
            \App\Models\PurchaseReturn::class => 'مرتجع مشتريات',
            default => class_basename($this->source_type),
        };
    }

    public function isOpening(): bool
    {
        return $this->entry_type === self::TYPE_OPENING;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
