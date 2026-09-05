<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Support\AccountEffect;
use App\Support\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * القيد اليومي اليدوي (Daily Journal Entry) + القيد الافتتاحي
 * (Opening Entry) - نفس الجدول (journal_entries) والكونترولر ده، وبس
 * بيتفرقوا بعمود entry_type ('daily' أو 'opening')، بالظبط زي
 * AccountVoucher اللي بيفرّق بين receipt/payment بعمود type واحد.
 * بتسمح بعمل قيد بعدد أسطر حر (2 على الأقل) - كل سطر حساب + مبلغ
 * مدين أو دائن - وبيتم التأكد إن إجمالي المدين = إجمالي الدائن قبل
 * الحفظ. لكل نوع سلسلة ترقيم منفصلة (JE-... للقيد اليومي، OE-... للقيد
 * الافتتاحي) وoperation_type مختلف في credittransaction (راجع
 * App\Support\OperationType).
 *
 * *** اتجاه current_balance حسب طبيعة الحساب ***
 * current_balance بيتحسب حسب FinancialAccount::balanceAfter() -
 * دائن (creditor_current - debtor_current) لو الحساب مورد
 * (orginal_type=2)، ومدين (debtor_current - creditor_current) لأي
 * حساب تاني (عميل أو حساب عام زي خزينة/بنك/مخزون). راجع تعليق
 * isCreditNormal() في موديل FinancialAccount لتفاصيل السبب.
 */
class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('journal_entries.view');

        $type = $request->input('type', JournalEntry::TYPE_DAILY);
        if (! in_array($type, [JournalEntry::TYPE_DAILY, JournalEntry::TYPE_OPENING], true)) {
            $type = JournalEntry::TYPE_DAILY;
        }

        $query = JournalEntry::with(['creator', 'branch'])->where('entry_type', $type);

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->input('date_to'));
        }

        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('journal-entries.index', compact('entries', 'type'));
    }

    public function create(Request $request)
    {
        $this->authorize('journal_entries.create');

        $type = $request->input('type', JournalEntry::TYPE_DAILY);
        if (! in_array($type, [JournalEntry::TYPE_DAILY, JournalEntry::TYPE_OPENING], true)) {
            $type = JournalEntry::TYPE_DAILY;
        }

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        return view('journal-entries.create', compact('type', 'branches', 'costCenters'));
    }

    public function store(Request $request)
    {
        $this->authorize('journal_entries.create');

        $validated = $request->validate([
            'entry_type' => ['required', 'in:daily,opening'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $lines = collect($validated['lines'])->map(function ($line) {
            $line['debit'] = round((float) ($line['debit'] ?? 0), 2);
            $line['credit'] = round((float) ($line['credit'] ?? 0), 2);

            return $line;
        });

        foreach ($lines as $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                abort(422, __('journal_entries.line_cannot_have_both'));
            }
            if ($line['debit'] <= 0 && $line['credit'] <= 0) {
                abort(422, __('journal_entries.line_amount_required'));
            }
        }

        $totalDebit = round($lines->sum('debit'), 2);
        $totalCredit = round($lines->sum('credit'), 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            abort(422, __('journal_entries.not_balanced'));
        }

        if ($totalDebit <= 0) {
            abort(422, __('journal_entries.zero_total'));
        }

        // القيد الافتتاحي (opening) له سلسلة ترقيم (OE-...) منفصلة عن
        // القيد اليومي العادي (JE-...) - بالظبط زي RV-/PV- في السندات.
        $isOpening = $validated['entry_type'] === JournalEntry::TYPE_OPENING;
        $prefix = $isOpening ? 'OE' : 'JE';
        $operationType = $isOpening ? OperationType::OPENING_ENTRY : OperationType::JOURNAL_ENTRY;

        $nextNumber = (int) (JournalEntry::where('entry_type', $validated['entry_type'])->max('id') ?? 0) + 1;
        $entryNumber = $prefix . '-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);

        $entry = DB::transaction(function () use ($validated, $lines, $totalDebit, $totalCredit, $entryNumber, $operationType) {
            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $validated['entry_date'],
                'entry_type' => $validated['entry_type'],
                'description' => $validated['description'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'created_by' => Auth::id(),
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            foreach ($lines as $line) {
                $account = FinancialAccount::lockForUpdate()->findOrFail($line['account_id']);
                $newBalance = AccountEffect::apply($account, $line['debit'], $line['credit']);

                $entry->lines()->create([
                    'account_id' => $account->id,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'note' => $line['note'] ?? null,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $account->id,
                    'recive_amount' => max($line['debit'], $line['credit']),
                    'note' => $line['note'] ?? $validated['description'] ?? OperationType::label($operationType),
                    'currentblance' => $newBalance,
                    'branchs_id' => $account->branchs_id ?? $validated['branch_id'] ?? null,
                    'debtor' => $line['debit'],
                    'creditor' => $line['credit'],
                    'invoice_number' => $entry->entry_number,
                    'operation_type' => $operationType,
                    'date_export' => $validated['entry_date'],
                ]);
            }

            return $entry;
        });

        return redirect()->route('journal-entries.show', $entry)->with('success', __('journal_entries.created_successfully'));
    }

    public function show(JournalEntry $journalEntry)
    {
        $this->authorize('journal_entries.view');

        $journalEntry->load(['lines.account', 'creator', 'branch', 'costCenter']);

        return view('journal-entries.show', ['entry' => $journalEntry]);
    }

    public function edit(JournalEntry $journalEntry)
    {
        $this->authorize('journal_entries.edit');

        $journalEntry->load(['lines.account']);

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        return view('journal-entries.edit', ['entry' => $journalEntry, 'branches' => $branches, 'costCenters' => $costCenters]);
    }

    /**
     * تعديل قيد موجود (يومي أو افتتاحي - نفس المنطق للاتنين):
     * بيرجع أثر كل سطر قديم من حسابه الأصلي، يمسح الأسطر وحركات
     * credittransaction القديمة، يحفظ بيانات القيد الجديدة، وبعدين
     * يطبّق أثر الأسطر الجديدة من الأول (ممكن تكون بعدد مختلف عن
     * الأسطر القديمة - إضافة/حذف سطور أثناء التعديل). عدد الأسطر
     * الحر ده هو اللي بيمنعنا نحدّث الأسطر في مكانها زي ما بنعمل في
     * VoucherController::update() (فيه بس حسابين ثابتين)، فهنا لازم
     * حذف وإعادة إنشاء. النوع (entry_type) ثابت مش قابل للتغيير - لو
     * غلط في النوع لازم يعمل قيد جديد بدل ما يعدّل في القديم.
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        $this->authorize('journal_entries.edit');

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $lines = collect($validated['lines'])->map(function ($line) {
            $line['debit'] = round((float) ($line['debit'] ?? 0), 2);
            $line['credit'] = round((float) ($line['credit'] ?? 0), 2);

            return $line;
        });

        foreach ($lines as $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                abort(422, __('journal_entries.line_cannot_have_both'));
            }
            if ($line['debit'] <= 0 && $line['credit'] <= 0) {
                abort(422, __('journal_entries.line_amount_required'));
            }
        }

        $totalDebit = round($lines->sum('debit'), 2);
        $totalCredit = round($lines->sum('credit'), 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            abort(422, __('journal_entries.not_balanced'));
        }

        if ($totalDebit <= 0) {
            abort(422, __('journal_entries.zero_total'));
        }

        $operationType = $journalEntry->isOpening() ? OperationType::OPENING_ENTRY : OperationType::JOURNAL_ENTRY;

        DB::transaction(function () use ($journalEntry, $validated, $lines, $totalDebit, $totalCredit, $operationType) {
            $oldLines = $journalEntry->lines()->get();

            // نحافظ على created_at الأصلي لحركات القيد القديمة عشان
            // ترتيبها في كشف الحساب متتأثرش بتوقيت التعديل نفسه.
            $originalCreatedAt = optional(
                CreditTransaction::where('invoice_number', $journalEntry->entry_number)
                    ->where('operation_type', $operationType)
                    ->oldest('created_at')
                    ->first()
            )->created_at ?? $journalEntry->created_at;

            // 1) نرجّع أثر كل سطر قديم من حسابه.
            foreach ($oldLines as $oldLine) {
                $account = FinancialAccount::lockForUpdate()->findOrFail($oldLine->account_id);
                AccountEffect::reverse($account, (float) $oldLine->debit, (float) $oldLine->credit);
            }

            // 2) نمسح الأسطر وحركات credittransaction القديمة.
            CreditTransaction::where('invoice_number', $journalEntry->entry_number)
                ->where('operation_type', $operationType)
                ->delete();
            $journalEntry->lines()->delete();

            // 3) تحديث هيدر القيد نفسه (رقمه ونوعه ثابتين).
            $journalEntry->update([
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // 4) نطبّق أثر الأسطر الجديدة من الأول ونعيد إنشاء الأسطر
            // وحركات القيد.
            foreach ($lines as $line) {
                $account = FinancialAccount::lockForUpdate()->findOrFail($line['account_id']);
                $newBalance = AccountEffect::apply($account, $line['debit'], $line['credit']);

                $journalEntry->lines()->create([
                    'account_id' => $account->id,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'note' => $line['note'] ?? null,
                ]);

                $transaction = new CreditTransaction([
                    'user_id' => Auth::id(),
                    'customer_id' => $account->id,
                    'recive_amount' => max($line['debit'], $line['credit']),
                    'note' => $line['note'] ?? $validated['description'] ?? OperationType::label($operationType),
                    'currentblance' => $newBalance,
                    'branchs_id' => $account->branchs_id ?? $validated['branch_id'] ?? null,
                    'debtor' => $line['debit'],
                    'creditor' => $line['credit'],
                    'invoice_number' => $journalEntry->entry_number,
                    'operation_type' => $operationType,
                    'date_export' => $validated['entry_date'],
                ]);
                $transaction->timestamps = false;
                $transaction->created_at = $originalCreatedAt;
                $transaction->updated_at = now();
                $transaction->save();
            }
        });

        return redirect()->route('journal-entries.show', $journalEntry)->with('success', __('journal_entries.updated_successfully'));
    }

    /**
     * صفحة طباعة احترافية للقيد (يومي/افتتاحي) - مستقلة عن
     * x-app-layout (زي resources/views/invoices/returns/print.blade.php
     * بالظبط)، وبتستخدم نفس هوية الشركة (Namear/camplogo/...) المعرّفة
     * في AppServiceProvider عشان تبقى متسقة مع باقي مطبوعات النظام.
     */
    public function print(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'creator', 'branch', 'costCenter']);

        return view('journal-entries.print', ['entry' => $journalEntry]);
    }
}
