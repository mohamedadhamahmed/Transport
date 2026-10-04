<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $table = 'credittransaction';

    // السماح لكل الحقول بالإدخال والتعديل دفعة واحدة
    protected $guarded = [];

    protected $casts = [
        'operation_type' => 'integer',
    ];

    /**
     * الحساب المالي اللي الحركة دي مسجلة عليه (customer_id بيشاور على
     * financialaccount.id - نفس التسمية القديمة من النظام الأصلي).
     */
    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * حماية الفترات المقفولة (إقفال السنة المالية): مفيش حركة تتسجل أو
     * تتعدل أو تتحذف بتاريخ جوه سنة اتقفلت - غير قيد الإقفال نفسه.
     */
    protected static function booted(): void
    {
        static::saving(function (self $tx) {
            if ((int) $tx->operation_type === \App\Support\ClosedPeriod::CLOSING_OPERATION_TYPE) {
                return;
            }
            \App\Support\ClosedPeriod::ensureOpen($tx->date_export ?: ($tx->created_at ?: now()));
            if ($tx->exists) {
                \App\Support\ClosedPeriod::ensureOpen($tx->getOriginal('date_export') ?: $tx->getOriginal('created_at'));
            }
        });

        static::deleting(function (self $tx) {
            if ((int) $tx->operation_type === \App\Support\ClosedPeriod::CLOSING_OPERATION_TYPE) {
                return;
            }
            \App\Support\ClosedPeriod::ensureOpen($tx->date_export ?: $tx->created_at);
        });
    }
}
