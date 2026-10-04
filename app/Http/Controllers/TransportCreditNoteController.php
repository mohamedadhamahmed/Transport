<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\FinancialAccount;
use App\Models\Setting;
use App\Models\TransportCreditNote;
use App\Models\TransportInvoice;
use App\Models\TruckLoad;
use App\Models\Waybill;
use App\Support\OperationType;
use App\Services\JournalEntryService;
use App\Support\ZatcaQr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * الإشعارات الدائنة على فواتير النقليات: لو فيه غلط في فاتورة (سعر زيادة،
 * نقلة مش المفروض تتفوتر، خصم بعد الإصدار، إلغاء الفاتورة كلها...) بنعمل
 * إشعار دائن بدل تعديل/حذف الفاتورة (خصوصًا لو اتبعتت للزكاة).
 *
 * - كل سطر في الإشعار = مبلغ بيتخصم من سطر في الفاتورة الأصلية (قبل الضريبة)
 *   ومينفعش يزيد عن الباقي من السطر بعد الإشعارات اللي قبله.
 * - الضريبة بنفس نسبة الفاتورة الأصلية.
 * - القيد: الإيرادات مدين بالصافي + الضريبة مدين بالضريبة / العميل دائن بالإجمالي
 *   (عكس قيد الفاتورة) - نوع العملية TRANSPORT_CREDIT_NOTE.
 * - لو الإشعار بيلغي الفاتورة بالكامل ممكن ترجع الأحمال "غير مفوترة" عشان
 *   تتفوتر من جديد صح.
 * - الإرسال للزكاة: مستند 381 بمرجع الفاتورة الأصلية (ZatcaController::
 *   sendTransportCreditNote) - لازم الفاتورة الأصلية تكون اتبعتت الأول.
 */
class TransportCreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('transport_invoices.view');

        $notes = TransportCreditNote::with(['invoice:id,invoice_number', 'customer:id,name,tax_number', 'creator:id,name'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%' . trim($request->input('q')) . '%';
                $q->where(fn ($w) => $w->where('credit_note_number', 'like', $t)
                    ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', $t))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $t)));
            })
            ->when($request->filled('zatca'), fn ($q) => $q->where('is_sent_to_zatca', $request->input('zatca') === 'sent'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totals = TransportCreditNote::selectRaw('COUNT(*) c, COALESCE(SUM(subtotal),0) s, COALESCE(SUM(tax_amount),0) t, COALESCE(SUM(total),0) g, SUM(CASE WHEN is_sent_to_zatca = 0 THEN 1 ELSE 0 END) p')->first();

        return view('transport.credit-notes.index', compact('notes', 'totals'));
    }

    public function create(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.edit');

        if ($error = $this->cannotCredit($invoice)) {
            return redirect()->route('transport.invoices.show', $invoice)->with('error', $error);
        }

        $invoice->load(['items', 'customer', 'creditNotes']);

        return view('transport.credit-notes.create', [
            'invoice' => $invoice,
            'lines' => $this->creditableLines($invoice),
            'remaining' => $this->remaining($invoice),
        ]);
    }

    public function store(Request $request, TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.edit');

        if ($error = $this->cannotCredit($invoice)) {
            return redirect()->route('transport.invoices.show', $invoice)->with('error', $error);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'issue_date' => ['required', 'date'],
            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
            'release_loads' => ['nullable', 'boolean'],
        ], [], ['reason' => __('transport.cn_reason')]);

        $note = DB::transaction(function () use ($invoice, $data) {
            $invoice = TransportInvoice::whereKey($invoice->id)->lockForUpdate()->first();
            $invoice->load(['items', 'creditNotes.items']);
            $lines = collect($this->creditableLines($invoice))->keyBy('id');

            $rows = [];
            foreach ($data['amounts'] as $itemId => $amount) {
                $amount = round((float) $amount, 2);
                if ($amount <= 0) {
                    continue;
                }
                $line = $lines[(int) $itemId] ?? null;
                if (!$line) {
                    continue;
                }
                if ($amount > $line['remaining'] + 0.001) {
                    throw ValidationException::withMessages(['amounts' => __('transport.cn_line_exceeds', [
                        'line' => $line['label'], 'max' => number_format($line['remaining'], 2),
                    ])]);
                }
                $rows[] = [
                    'transport_invoice_item_id' => $line['id'],
                    'description' => mb_substr('إشعار دائن: ' . $line['label'], 0, 255),
                    'amount' => $amount,
                ];
            }

            if (!$rows) {
                throw ValidationException::withMessages(['amounts' => __('transport.cn_nothing')]);
            }

            $subtotal = round(array_sum(array_column($rows, 'amount')), 2);
            $rate = (float) $invoice->tax_rate;
            $tax = round($subtotal * $rate, 2);
            $remainingBefore = $this->remaining($invoice);
            if ($subtotal > $remainingBefore['subtotal'] + 0.01) {
                throw ValidationException::withMessages(['amounts' => __('transport.cn_total_exceeds', ['max' => number_format($remainingBefore['subtotal'], 2)])]);
            }
            $isFull = abs($remainingBefore['subtotal'] - $subtotal) < 0.01;

            $now = Carbon::now('Asia/Riyadh');
            $note = TransportCreditNote::create([
                'transport_invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'branch_id' => $invoice->branch_id,
                'created_by' => Auth::id(),
                'issue_date' => $data['issue_date'],
                'issue_time' => $now->toTimeString(),
                'reason' => $data['reason'],
                'release_loads' => $isFull && !empty($data['release_loads']),
                'subtotal' => $subtotal,
                'tax_rate' => $rate,
                'tax_type' => $invoice->tax_type,
                'tax_amount' => $tax,
                'total' => round($subtotal + $tax, 2),
            ]);
            $note->update(['credit_note_number' => 'CN-' . str_pad((string) $note->id, 6, '0', STR_PAD_LEFT)]);
            $note->items()->createMany($rows);

            $this->recordAccounting($note->fresh());

            // إلغاء الفاتورة بالكامل: الأحمال ترجع "غير مفوترة" عشان تتفوتر من جديد
            if ($note->release_loads) {
                TruckLoad::where('transport_invoice_id', $invoice->id)->update(['transport_invoice_id' => null]);
                Waybill::where('transport_invoice_id', $invoice->id)->update(['transport_invoice_id' => null]);
            }

            return $note;
        });

        return redirect()->route('transport.credit-notes.show', ['creditNote' => $note, 'saved' => 1])
            ->with('success', __('transport.cn_created'));
    }

    public function show(TransportCreditNote $creditNote)
    {
        $this->authorize('transport_invoices.view');

        $creditNote->load(['invoice', 'customer', 'branch', 'creator', 'items']);

        return view('transport.credit-notes.show', [
            'note' => $creditNote,
            'qrData' => $creditNote->zatcaQrFromXml() ?? $this->qrData($creditNote),
        ]);
    }

    /** حذف إشعار لسه متبعتش للزكاة (بيرجّع القيد والأحمال) */
    public function destroy(TransportCreditNote $creditNote)
    {
        $this->authorize('transport_invoices.delete');

        if ($creditNote->is_sent_to_zatca) {
            return back()->with('error', __('transport.cn_zatca_locked'));
        }

        DB::transaction(function () use ($creditNote) {
            $this->reverseAccounting($creditNote);

            if ($creditNote->release_loads) {
                // الأحمال اللي كانت على الفاتورة ترجع مربوطة بيها (لو محدش فوترها تاني)
                $loadIds = $creditNote->invoice?->items()->whereNotNull('truck_load_id')->pluck('truck_load_id') ?? collect();
                TruckLoad::whereIn('id', $loadIds)->whereNull('transport_invoice_id')
                    ->update(['transport_invoice_id' => $creditNote->transport_invoice_id]);
                Waybill::whereIn('truck_load_id', $loadIds)->whereNull('transport_invoice_id')
                    ->update(['transport_invoice_id' => $creditNote->transport_invoice_id]);
            }

            $creditNote->items()->delete();
            $creditNote->delete();
        });

        return redirect()->route('transport.credit-notes.index')->with('success', __('transport.cn_deleted'));
    }

    /** إرسال الإشعار للزكاة (381) */
    public function sendZatca(TransportCreditNote $creditNote)
    {
        $this->authorize('zatca.send');

        $setting = Setting::where('branchs_id', $creditNote->branch_id)->first() ?? Setting::query()->first();
        if (!$setting) {
            $result = ['success' => false, 'message' => __('zatca.settings_missing')];
        } else {
            try {
                $result = app(ZatcaController::class)->sendTransportCreditNote($creditNote, $setting);
            } catch (Throwable $e) {
                $result = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        if (request()->expectsJson()) {
            return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
        }

        return back()->with(($result['success'] ?? false) ? 'success' : 'error',
            ($result['success'] ?? false) ? __('transport.cn_zatca_sent') : ($result['message'] ?? __('zatca.send_failed')));
    }

    public function downloadXml(TransportCreditNote $creditNote)
    {
        $this->authorize('zatca.view');

        $xml = $creditNote->zatca_cleared_invoice_xml
            ? (base64_decode($creditNote->zatca_cleared_invoice_xml, true) ?: $creditNote->zatca_cleared_invoice_xml)
            : $creditNote->zatca_invoice_xml;
        abort_unless($xml, 404);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $creditNote->credit_note_number . '.xml"',
        ]);
    }

    // ------------------------------------------------------------------

    private function cannotCredit(TransportInvoice $invoice): ?string
    {
        if ($invoice->is_draft) {
            return __('transport.cn_draft_invoice');
        }
        if ($this->remaining($invoice)['subtotal'] <= 0.009) {
            return __('transport.cn_fully_credited');
        }

        return null;
    }

    /** سطور الفاتورة + اللي اتخصم منها قبل كده + الباقي */
    private function creditableLines(TransportInvoice $invoice): array
    {
        $invoice->loadMissing(['items', 'creditNotes.items']);
        $credited = $invoice->creditNotes->flatMap->items->groupBy('transport_invoice_item_id')
            ->map(fn ($g) => (float) $g->sum('amount'));

        return $invoice->items->map(function ($item) use ($credited) {
            $label = $item->description
                ?: trim(($item->truck_snapshot ? $item->truck_snapshot . ' · ' : '') . ($item->from_location ?? '') . ' ← ' . ($item->to_location ?? ''), ' ←·')
                . ($item->trip_date ? ' (' . $item->trip_date->format('Y-m-d') . ')' : '');
            $total = round((float) $item->line_total, 2);
            $done = round((float) ($credited[$item->id] ?? 0), 2);

            return [
                'id' => $item->id,
                'label' => $label ?: ('#' . $item->id),
                'waybill' => $item->waybill_number,
                'total' => $total,
                'credited' => $done,
                'remaining' => max(0, round($total - $done, 2)),
            ];
        })->values()->all();
    }

    /**
     * الباقي من الفاتورة (قبل الضريبة) = صافي الفاتورة - كل الإشعارات.
     * (صافي الفاتورة بعد خصم الفاتورة نفسه - فالسطور ممكن مجموعها يزيد عنه
     * لو كان فيه خصم، فبنقصّ الحد الأقصى على الصافي.)
     */
    private function remaining(TransportInvoice $invoice): array
    {
        $invoice->loadMissing('creditNotes');
        $credited = (float) $invoice->creditNotes->sum('subtotal');
        $sub = max(0, round((float) $invoice->subtotal - $credited, 2));

        return ['subtotal' => $sub, 'credited' => round($credited, 2), 'total' => round($sub * (1 + (float) $invoice->tax_rate), 2)];
    }

    /** قيد الإشعار: عكس قيد الفاتورة (إيرادات + ضريبة مدين / العميل دائن) */
    private function recordAccounting(TransportCreditNote $note): void
    {
        if ((float) $note->total <= 0) {
            return;
        }

        $branchId = $note->branch_id;
        $customer = Customer::find($note->customer_id);
        $now = Carbon::now('Asia/Riyadh');
        $base = [
            'user_id' => Auth::id(),
            'branchs_id' => $branchId,
            'pay_method' => 'credit',
            'Pay_Method_Name' => 'Credit',
            'note' => 'إشعار دائن رقم : ' . $note->credit_note_number . ' على فاتورة نقليات ' . ($note->invoice?->invoice_number ?? ''),
            'operation_type' => OperationType::TRANSPORT_CREDIT_NOTE,
            'invoice_number' => $note->credit_note_number,
            'balance_posted' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // الإيرادات (112) مدين بالصافي
        $revenue = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', $branchId)->first();
        if ($revenue) {
            CreditTransaction::create($base + [
                'customer_id' => $revenue->id,
                'recive_amount' => $note->subtotal,
                'currentblance' => $revenue->current_balance - $note->subtotal,
                'debtor' => $note->subtotal,
            ]);
            // الإيرادات مدين بالصافي (كانت بتتسجل حركة بس من غير تحديث الرصيد).
            $revenue->postAmounts((float) $note->subtotal, 0);
        }

        // الضريبة (102) مدين بالضريبة
        if ((float) $note->tax_amount > 0) {
            $vat = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', $branchId)->first();
            if ($vat) {
                $vat->update([
                    'current_balance' => $vat->current_balance - $note->tax_amount,
                    'debtor_current' => $vat->debtor_current + $note->tax_amount,
                ]);
                CreditTransaction::create($base + [
                    'customer_id' => $vat->id,
                    'recive_amount' => $note->tax_amount,
                    'currentblance' => $vat->current_balance,
                    'debtor' => $note->tax_amount,
                    'vat' => 1,
                    'name' => $customer->name ?? '',
                ]);
            }
        }

        // العميل دائن بالإجمالي (بيقلّل اللي عليه)
        if ($customer) {
            $customer->decrement('balance', $note->total);
            $acc = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customer->id)->first();
            if ($acc) {
                $acc->update([
                    'current_balance' => $acc->current_balance - $note->total,
                    'creditor_current' => $acc->creditor_current + $note->total,
                ]);
                CreditTransaction::create($base + [
                    'customer_id' => $acc->id,
                    'recive_amount' => $note->total,
                    'currentblance' => $acc->current_balance,
                    'creditor' => $note->total,
                ]);
            }
        }

        // إنشاء أو تحديث القيد المحاسبي الموحد لإشعار دائن النقليات
        $entryDescription = 'قيد إشعار دائن نقليات رقم ' . ($note->credit_note_number ?: $note->id);
        JournalEntryService::syncForSource(
            $note,
            $entryDescription,
            $note->credit_note_date ?: now(),
            $branchId,
            Auth::id()
        );
    }

    private function reverseAccounting(TransportCreditNote $note): void
    {
        JournalEntryService::deleteForSource($note);

        $branchId = $note->branch_id;

        // فواتير اتسجلت بعد الإصلاح: كل حركة عليها balance_posted=1 اتضافت
        // فعلاً على رصيد حسابها - فبنعكسها هي نفسها بالظبط.
        $posted = CreditTransaction::where('invoice_number', $note->credit_note_number)
            ->where('operation_type', OperationType::TRANSPORT_CREDIT_NOTE)
            ->where('balance_posted', 1)
            ->get();

        if ($posted->isNotEmpty()) {
            foreach ($posted as $transaction) {
                $account = FinancialAccount::lockForUpdate()->find($transaction->customer_id);
                $account?->postAmounts(-(float) $transaction->debtor, -(float) $transaction->creditor);
            }

            Customer::find($note->customer_id)?->increment('balance', $note->total);

            CreditTransaction::where('invoice_number', $note->credit_note_number)
                ->where('operation_type', OperationType::TRANSPORT_CREDIT_NOTE)
                ->delete();

            return;
        }

        // فواتير قديمة (قبل الإصلاح): بس الضريبة والعميل كانوا بيتحدّثوا.

        if ((float) $note->tax_amount > 0) {
            $vat = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', $branchId)->first();
            $vat?->update([
                'current_balance' => $vat->current_balance + $note->tax_amount,
                'debtor_current' => $vat->debtor_current - $note->tax_amount,
            ]);
        }

        $customer = Customer::find($note->customer_id);
        if ($customer) {
            $customer->increment('balance', $note->total);
            $acc = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customer->id)->first();
            $acc?->update([
                'current_balance' => $acc->current_balance + $note->total,
                'creditor_current' => $acc->creditor_current - $note->total,
            ]);
        }

        CreditTransaction::where('invoice_number', $note->credit_note_number)
            ->where('operation_type', OperationType::TRANSPORT_CREDIT_NOTE)
            ->delete();
    }

    /** QR المرحلة الأولى للإشعار */
    private function qrData(TransportCreditNote $note): string
    {
        $co = \App\Support\CompanyInfo::get($note->branch_id);
        $issuedAt = Carbon::parse($note->issue_date->format('Y-m-d') . ' ' . ($note->issue_time ?: '00:00:00'), 'Asia/Riyadh')
            ->utc()->format('Y-m-d\TH:i:s\Z');

        return ZatcaQr::phaseOne(
            $co['name_ar'],
            $co['vat_digits'],
            $issuedAt,
            (float) $note->total,
            (float) $note->tax_amount
        );
    }
}
