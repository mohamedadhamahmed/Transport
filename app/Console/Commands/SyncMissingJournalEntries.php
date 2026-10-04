<?php

namespace App\Console\Commands;

use App\Models\AccountVoucher;
use App\Models\CreditTransaction;
use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\JournalEntry;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Services\JournalEntryService;
use App\Support\OperationType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMissingJournalEntries extends Command
{
    protected $signature = 'accounts:sync-journal-entries';

    protected $description = 'مزامنة وتوليد قيود اليومية المفقودة للمبيعات والمشتريات والسندات والمرتجعات وربطها بالتسلسل الموحد';

    public function handle(): int
    {
        $this->info('بدء مزامنة قيود اليومية لجميع المستندات...');

        // 1. مزامنة فواتير المبيعات
        if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
            $invoices = Invoice::all();
            $this->info("فحص {$invoices->count()} فاتورة مبيعات...");
            $invoiceSynced = 0;
            foreach ($invoices as $invoice) {
                $hasEntry = JournalEntry::where('source_type', Invoice::class)
                    ->where('source_id', $invoice->id)
                    ->exists();

                if (! $hasEntry) {
                    $hasTx = CreditTransaction::where('invoice_number', $invoice->invoice_number)
                        ->where('operation_type', OperationType::SALE_INVOICE)
                        ->exists();

                    if ($hasTx) {
                        DB::transaction(function () use ($invoice, &$invoiceSynced) {
                            $desc = 'قيد فاتورة مبيعات رقم ' . ($invoice->invoice_number ?: $invoice->id);
                            $entry = JournalEntryService::syncForSource(
                                $invoice,
                                $desc,
                                $invoice->invoice_date ?: $invoice->created_at,
                                $invoice->branch_id,
                                $invoice->created_by ?? 1
                            );
                            if ($entry) {
                                $invoiceSynced++;
                            }
                        });
                    }
                }
            }
            $this->info("تم إنشاء قيود لـ {$invoiceSynced} فاتورة مبيعات.");
        }

        // 1.ب مزامنة فواتير النقليات
        if (\Illuminate\Support\Facades\Schema::hasTable('transport_invoices') && class_exists(\App\Models\TransportInvoice::class)) {
            $tInvoices = \App\Models\TransportInvoice::all();
            $this->info("فحص {$tInvoices->count()} فاتورة نقليات...");
            $tSynced = 0;
            foreach ($tInvoices as $tInv) {
                $hasEntry = JournalEntry::where('source_type', \App\Models\TransportInvoice::class)
                    ->where('source_id', $tInv->id)
                    ->exists();

                if (! $hasEntry) {
                    $hasTx = CreditTransaction::where('invoice_number', $tInv->invoice_number)
                        ->where('operation_type', OperationType::TRANSPORT_INVOICE)
                        ->exists();

                    if ($hasTx) {
                        DB::transaction(function () use ($tInv, &$tSynced) {
                            $desc = 'قيد فاتورة نقليات رقم ' . ($tInv->invoice_number ?: $tInv->id);
                            $entry = JournalEntryService::syncForSource(
                                $tInv,
                                $desc,
                                $tInv->invoice_date ?: $tInv->created_at,
                                $tInv->branch_id,
                                $tInv->created_by ?? 1
                            );
                            if ($entry) {
                                $tSynced++;
                            }
                        });
                    }
                }
            }
            $this->info("تم إنشاء قيود لـ {$tSynced} فاتورة نقليات.");
        }

        // 2. مزامنة فواتير المشتريات
        $purchases = Purchase::all();
        $this->info("فحص {$purchases->count()} فاتورة مشتريات...");
        $purchaseSynced = 0;
        foreach ($purchases as $purchase) {
            $hasEntry = JournalEntry::where('source_type', Purchase::class)
                ->where('source_id', $purchase->id)
                ->exists();

            if (! $hasEntry) {
                $hasTx = CreditTransaction::where('invoice_number', $purchase->purchase_number)
                    ->where('operation_type', OperationType::PURCHASE_INVOICE)
                    ->exists();

                if ($hasTx) {
                    DB::transaction(function () use ($purchase, &$purchaseSynced) {
                        $desc = 'قيد فاتورة مشتريات رقم ' . ($purchase->purchase_number ?: $purchase->id);
                        $entry = JournalEntryService::syncForSource(
                            $purchase,
                            $desc,
                            $purchase->purchase_date ?: $purchase->created_at,
                            $purchase->branch_id,
                            $purchase->created_by ?? 1,
                            $purchase->cost_center_id
                        );
                        if ($entry) {
                            $purchaseSynced++;
                        }
                    });
                }
            }
        }
        $this->info("تم إنشاء قيود لـ {$purchaseSynced} فاتورة مشتريات.");

        // 3. مزامنة السندات المالية (قبض وصرف)
        $vouchers = AccountVoucher::all();
        $this->info("فحص {$vouchers->count()} سند مالي...");
        $voucherSynced = 0;
        foreach ($vouchers as $voucher) {
            $hasEntry = JournalEntry::where('source_type', AccountVoucher::class)
                ->where('source_id', $voucher->id)
                ->exists();

            if (! $hasEntry) {
                $hasTx = CreditTransaction::where('invoice_number', $voucher->voucher_number)
                    ->whereIn('operation_type', [OperationType::RECEIPT_VOUCHER, OperationType::PAYMENT_VOUCHER])
                    ->exists();

                if ($hasTx) {
                    DB::transaction(function () use ($voucher, &$voucherSynced) {
                        $label = $voucher->isReceipt() ? 'سند قبض' : 'سند صرف';
                        $desc = "قيد {$label} رقم " . $voucher->voucher_number;
                        $entry = JournalEntryService::syncForSource(
                            $voucher,
                            $desc,
                            $voucher->voucher_date ?: $voucher->created_at,
                            $voucher->branch_id,
                            $voucher->created_by ?? 1
                        );
                        if ($entry) {
                            $voucherSynced++;
                        }
                    });
                }
            }
        }
        $this->info("تم إنشاء قيود لـ {$voucherSynced} سند مالي.");

        // 4. مزامنة مرتجعات المشتريات
        $purchaseReturns = PurchaseReturn::all();
        $this->info("فحص {$purchaseReturns->count()} مرتجع مشتريات...");
        $prSynced = 0;
        foreach ($purchaseReturns as $pr) {
            $hasEntry = JournalEntry::where('source_type', PurchaseReturn::class)
                ->where('source_id', $pr->id)
                ->exists();

            if (! $hasEntry) {
                $hasTx = CreditTransaction::where('invoice_number', $pr->return_number)
                    ->where('operation_type', OperationType::PURCHASE_RETURN)
                    ->exists();

                if ($hasTx) {
                    DB::transaction(function () use ($pr, &$prSynced) {
                        $desc = 'قيد مرتجع مشتريات رقم ' . ($pr->return_number ?: $pr->id);
                        $entry = JournalEntryService::syncForSource(
                            $pr,
                            $desc,
                            $pr->created_at,
                            $pr->branch_id,
                            $pr->created_by ?? 1
                        );
                        if ($entry) {
                            $prSynced++;
                        }
                    });
                }
            }
        }
        $this->info("تم إنشاء قيود لـ {$prSynced} مرتجع مشتريات.");

        // 5. مزامنة مرتجعات المبيعات
        if (\Illuminate\Support\Facades\Schema::hasTable('invoice_returns')) {
            $distinctReturnRefs = InvoiceReturn::distinct()->pluck('reference_value');
            $this->info("فحص " . count($distinctReturnRefs) . " عملية إرجاع مبيعات...");
            $irSynced = 0;
            foreach ($distinctReturnRefs as $ref) {
                $firstReturn = InvoiceReturn::where('reference_value', $ref)->first();
                if (! $firstReturn) {
                    continue;
                }

                $hasEntry = JournalEntry::where('source_type', InvoiceReturn::class)
                    ->where('source_id', $firstReturn->id)
                    ->exists();

                if (! $hasEntry) {
                    $invoice = Invoice::find($firstReturn->invoice_id);
                    if ($invoice) {
                        $transactions = CreditTransaction::where('invoice_number', $invoice->invoice_number)
                            ->where('operation_type', OperationType::SALE_RETURN)
                            ->where('created_at', '>=', $firstReturn->created_at->subSeconds(5))
                            ->where('created_at', '<=', $firstReturn->created_at->addSeconds(5))
                            ->get();

                        if ($transactions->isNotEmpty()) {
                            DB::transaction(function () use ($firstReturn, $invoice, $transactions, &$irSynced) {
                                $desc = 'قيد مرتجع مبيعات للفاتورة رقم ' . ($invoice->invoice_number ?: $invoice->id);
                                $entry = JournalEntryService::recordForTransactions(
                                    $firstReturn,
                                    $desc,
                                    $firstReturn->created_at,
                                    $invoice->branch_id,
                                    $transactions,
                                    $firstReturn->user_id ?? 1
                                );
                                if ($entry) {
                                    $irSynced++;
                                }
                            });
                        }
                    }
                }
            }
            $this->info("تم إنشاء قيود لـ {$irSynced} عملية إرجاع مبيعات.");
        }

        // 5.ب مزامنة إشعارات الدائن للنقليات
        if (\Illuminate\Support\Facades\Schema::hasTable('transport_credit_notes') && class_exists(\App\Models\TransportCreditNote::class)) {
            $tNotes = \App\Models\TransportCreditNote::all();
            $this->info("فحص {$tNotes->count()} إشعار دائن نقليات...");
            $nSynced = 0;
            foreach ($tNotes as $tNote) {
                $hasEntry = JournalEntry::where('source_type', \App\Models\TransportCreditNote::class)
                    ->where('source_id', $tNote->id)
                    ->exists();

                if (! $hasEntry) {
                    $hasTx = CreditTransaction::where('invoice_number', $tNote->credit_note_number)
                        ->where('operation_type', OperationType::TRANSPORT_CREDIT_NOTE)
                        ->exists();

                    if ($hasTx) {
                        DB::transaction(function () use ($tNote, &$nSynced) {
                            $desc = 'قيد إشعار دائن نقليات رقم ' . ($tNote->credit_note_number ?: $tNote->id);
                            $entry = JournalEntryService::syncForSource(
                                $tNote,
                                $desc,
                                $tNote->credit_note_date ?: $tNote->created_at,
                                $tNote->branch_id,
                                $tNote->created_by ?? 1
                            );
                            if ($entry) {
                                $nSynced++;
                            }
                        });
                    }
                }
            }
            $this->info("تم إنشاء قيود لـ {$nSynced} إشعار دائن نقليات.");
        }

        // 6. ربط أي قيود يومية يدوية قديمة بحركاتها في جدول credittransactions
        $txTable = (new CreditTransaction)->getTable();
        $manualEntries = JournalEntry::whereNull('source_type')->get();
        $linkedManual = 0;
        foreach ($manualEntries as $mEntry) {
            $updated = DB::table($txTable)
                ->where('invoice_number', $mEntry->entry_number)
                ->whereNull('journal_entry_id')
                ->update([
                    'journal_entry_id' => $mEntry->id,
                    'entry_number' => $mEntry->entry_number,
                ]);
            if ($updated > 0) {
                $linkedManual += $updated;
            }
        }
        $this->info("تم ربط {$linkedManual} حركة بحركات القيود اليدوية القديمة.");

        $this->info('اكتملت المزامنة بنجاح!');

        return Command::SUCCESS;
    }
}
