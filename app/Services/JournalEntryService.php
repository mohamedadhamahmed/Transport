<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Support\OperationType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalEntryService
{
    public const DAILY_SEQUENCE = 'journal_entry_daily';
    public const OPENING_SEQUENCE = 'journal_entry_opening';

    /**
     * الحصول على رقم القيد التالي من التسلسل المحاسبي الموحد.
     * محمي بقفل قاعدة البيانات (lockForUpdate):
     * 1. لا يتكرر أبداً بين العمليات المتزامنة.
     * 2. في حال فشل العملية (Rollback)، يعود التسلسل كما كان دون ترك فجوة.
     * 3. لا يتم تقليص التسلسل عند الحذف، فلا يُعاد استخدام رقم محذوف أبداً.
     */
    public static function getNextEntryNumber(
        string $sequenceName = self::DAILY_SEQUENCE,
        string $prefix = 'JE-',
        int $padding = 6
    ): string {
        $sequence = DB::table('accounting_sequences')
            ->where('name', $sequenceName)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            // حساب أعلى رقم حالي في جدول القيود لتفادي أي تضارب مع القيود السابقة
            $currentMax = 0;
            $existingNumbers = DB::table('journal_entries')
                ->where('entry_number', 'LIKE', $prefix . '%')
                ->pluck('entry_number');

            foreach ($existingNumbers as $num) {
                $raw = substr($num, strlen($prefix));
                $clean = preg_replace('/[^0-9]/', '', $raw);
                if (is_numeric($clean) && (int) $clean > $currentMax) {
                    $currentMax = (int) $clean;
                }
            }

            DB::table('accounting_sequences')->insert([
                'name' => $sequenceName,
                'current_value' => $currentMax,
                'prefix' => $prefix,
                'padding' => $padding,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('accounting_sequences')
                ->where('name', $sequenceName)
                ->lockForUpdate()
                ->first();
        }

        $nextValue = (int) $sequence->current_value + 1;

        DB::table('accounting_sequences')
            ->where('name', $sequenceName)
            ->update([
                'current_value' => $nextValue,
                'updated_at' => now(),
            ]);

        return $prefix . str_pad((string) $nextValue, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * تسجيل أو تحديث قيد يومية آلي لمستند محدد بالاعتماد على مصفوفة/مجموعة حركات CreditTransaction.
     */
    public static function recordForTransactions(
        Model $source,
        string $description,
        $date = null,
        ?int $branchId = null,
        $transactions = [],
        ?int $userId = null,
        ?int $costCenterId = null
    ): ?JournalEntry {
        $txCollection = collect($transactions);

        // تصفية الأسطر الفعلية فقط (التي تحتوي مبالغ مدينة أو دائنة أكبر من صفر)
        $validTransactions = $txCollection->filter(function ($tx) {
            return (float) ($tx->debtor ?? 0) > 0 || (float) ($tx->creditor ?? 0) > 0;
        });

        if ($validTransactions->isEmpty()) {
            return null;
        }

        $totalDebit = round((float) $validTransactions->sum('debtor'), 2);
        $totalCredit = round((float) $validTransactions->sum('creditor'), 2);

        // التحقق من توازن القيد مع هامش خطأ بسيط جداً لتفادي الفواصل العشرية
        if (abs($totalDebit - $totalCredit) > 0.05) {
            Log::warning('Journal entry out of balance for ' . get_class($source) . ' #' . $source->getKey(), [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
            ]);
        }

        // هل يوجد قيد سابق لنفس هذا المستند؟
        $existingEntry = JournalEntry::where('source_type', get_class($source))
            ->where('source_id', $source->getKey())
            ->first();

        $entryNumber = $existingEntry
            ? $existingEntry->entry_number
            : self::getNextEntryNumber(self::DAILY_SEQUENCE, 'JE-', 6);

        $entryDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        $creatorId = $userId ?: (Auth::id() ?: 1);

        if ($existingEntry) {
            $existingEntry->update([
                'entry_date' => $entryDate,
                'description' => $description,
                'branch_id' => $branchId ?: $existingEntry->branch_id,
                'cost_center_id' => $costCenterId ?: $existingEntry->cost_center_id,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_auto' => true,
            ]);
            $entry = $existingEntry;
            $entry->lines()->delete();
        } else {
            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'entry_type' => JournalEntry::TYPE_DAILY,
                'description' => $description,
                'branch_id' => $branchId,
                'cost_center_id' => $costCenterId,
                'source_type' => get_class($source),
                'source_id' => $source->getKey(),
                'is_auto' => true,
                'created_by' => $creatorId,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);
        }

        // إنشاء سطور القيد في جدول journal_entry_lines
        foreach ($validTransactions as $tx) {
            $entry->lines()->create([
                'account_id' => $tx->customer_id,
                'debit' => round((float) $tx->debtor, 2),
                'credit' => round((float) $tx->creditor, 2),
                'note' => $tx->note ?: $description,
            ]);
        }

        // ربط جميع حركات هذا المستند برقم ومعرف القيد
        $txTable = (new CreditTransaction)->getTable();
        foreach ($txCollection as $tx) {
            if ($tx instanceof CreditTransaction || isset($tx->id)) {
                DB::table($txTable)
                    ->where('id', $tx->id)
                    ->update([
                        'journal_entry_id' => $entry->id,
                        'entry_number' => $entry->entry_number,
                    ]);
                $tx->journal_entry_id = $entry->id;
                $tx->entry_number = $entry->entry_number;
            }
        }

        return $entry;
    }

    /**
     * استعلام الحركات الخاصة بمستند معين ثم إنشاء/تحديث القيد اليومي وربط الحركات به.
     */
    public static function syncForSource(
        Model $source,
        string $description,
        $date = null,
        ?int $branchId = null,
        ?int $userId = null,
        ?int $costCenterId = null
    ): ?JournalEntry {
        $query = CreditTransaction::query();

        if ($source instanceof \App\Models\Invoice) {
            $query->where('invoice_number', $source->invoice_number)
                ->where('operation_type', OperationType::SALE_INVOICE);
            $date = $date ?: ($source->invoice_date ?? $source->created_at);
            $branchId = $branchId ?: $source->branch_id;
        } elseif (class_exists(\App\Models\TransportInvoice::class) && $source instanceof \App\Models\TransportInvoice) {
            $query->where('invoice_number', $source->invoice_number)
                ->where('operation_type', OperationType::TRANSPORT_INVOICE);
            $date = $date ?: ($source->invoice_date ?? $source->created_at);
            $branchId = $branchId ?: $source->branch_id;
        } elseif (class_exists(\App\Models\TransportCreditNote::class) && $source instanceof \App\Models\TransportCreditNote) {
            $query->where('invoice_number', $source->credit_note_number)
                ->where('operation_type', OperationType::TRANSPORT_CREDIT_NOTE);
            $date = $date ?: ($source->credit_note_date ?? $source->created_at);
            $branchId = $branchId ?: $source->branch_id;
        } elseif ($source instanceof \App\Models\Purchase) {
            $query->where('invoice_number', $source->purchase_number)
                ->where('operation_type', OperationType::PURCHASE_INVOICE);
            $date = $date ?: ($source->purchase_date ?? $source->created_at);
            $branchId = $branchId ?: $source->branch_id;
            $costCenterId = $costCenterId ?: $source->cost_center_id;
        } elseif ($source instanceof \App\Models\AccountVoucher) {
            $query->where('invoice_number', $source->voucher_number)
                ->whereIn('operation_type', [OperationType::RECEIPT_VOUCHER, OperationType::PAYMENT_VOUCHER]);
            $date = $date ?: ($source->voucher_date ?? $source->created_at);
            $branchId = $branchId ?: $source->branch_id;
        } elseif ($source instanceof \App\Models\InvoiceReturn) {
            $query->where('invoice_number', $source->invoice_number)
                ->where('operation_type', OperationType::SALE_RETURN);
            $date = $date ?: $source->created_at;
            $branchId = $branchId ?: $source->branch_id;
        } elseif ($source instanceof \App\Models\PurchaseReturn) {
            $query->where('invoice_number', $source->return_number)
                ->where('operation_type', OperationType::PURCHASE_RETURN);
            $date = $date ?: $source->created_at;
            $branchId = $branchId ?: $source->branch_id;
        } else {
            // مستندات أخرى بالاعتماد على orginal_type و orginal_id إن وجدت
            $query->where('orginal_type', class_basename($source))
                ->where('orginal_id', $source->getKey());
        }

        $transactions = $query->get();

        if ($transactions->isEmpty()) {
            return null;
        }

        return self::recordForTransactions(
            $source,
            $description,
            $date,
            $branchId,
            $transactions,
            $userId,
            $costCenterId
        );
    }

    /**
     * حذف القيد اليومي الخاص بمستند معين (عند حذف المستند أو إلغائه) دون إرجاع رقم القيد إلى التسلسل.
     */
    public static function deleteForSource(Model $source): void
    {
        $entry = JournalEntry::where('source_type', get_class($source))
            ->where('source_id', $source->getKey())
            ->first();

        if ($entry) {
            $entry->lines()->delete();
            $entry->delete();
        }
    }
}
