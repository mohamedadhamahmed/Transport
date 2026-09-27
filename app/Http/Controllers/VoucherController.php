<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\Tax;
use App\Support\AccountEffect;
use App\Support\ArabicNumberWords;
use App\Support\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * سندات القبض والصرف (Receipt / Payment Vouchers) - سند واحد بعدد
 * بنود حر (على الأقل بند واحد)، بالظبط زي فكرة أسطر القيد اليومي في
 * JournalEntryController (طلب: "عوز السندات تكون متعددة").
 *
 * سند قبض (receipt): فلوس داخلة لحساب الخزينة/البنك (treasury_account)
 * جاية من حساب/حسابات تانية (كل بند = طرف تاني مستقل - عميل/حساب
 * عام). سند صرف (payment): عكسه بالظبط - فلوس خارجة من الخزينة
 * لحساب/حسابات تانية.
 *
 * حساب الخزينة بياخد أثر إجمالي كل البنود مرة واحدة (إيداع/سحب واحد
 * فعلي)، بينما كل بند بياخد أثره المستقل على حسابه (الطرف التاني +
 * حساب الضريبة لو البند خاضع لضريبة). راجع تعليق ميجريشن
 * account_voucher_lines لتفاصيل نقل حقول الضريبة من مستوى السند لمستوى
 * البند.
 *
 * نفس قاعدة current_balance المستخدمة في JournalEntryController -
 * بتتحسب حسب FinancialAccount::balanceAfter() (طبيعة الحساب مدين
 * أو دائن بناءً على orginal_type). راجع تعليق isCreditNormal() في
 * موديل FinancialAccount لتفاصيل السبب.
 *
 * ملحوظة بخصوص الضريبة لكل بند: لو البند خاضع لضريبة، "amount" المُدخل
 * بيتعامل معاه كمبلغ شامل الضريبة، وبيتقسم لـ net_amount (بيروح لحساب
 * الطرف التاني) + tax_amount (بيروح لحساب ضريبة القيمة المضافة الخاص
 * بفرع حساب الخزينة/البنك المختار)، بينما حساب الخزينة نفسه (على
 * مستوى السند ككل) بياخد أثر إجمالي كل مبالغ البنود الكاملة (شاملة
 * الضريبة). اتجاه القيد على حساب الضريبة (مدين/دائن) نفس اتجاه سند
 * القبض/الصرف العادي (زي بالظبط PurchaseController وInvoiceController).
 */
class VoucherController extends Controller
{
    /**
     * سندات الصيانة (سند صرف مربوط بشاحنة) ليها صلاحيات مستقلة
     * maintenance.* - باقي السندات vouchers.*
     */
    private function authorizeVoucher(string $action, bool $maintenance): void
    {
        $this->authorize(($maintenance ? 'maintenance.' : 'vouchers.') . $action);
    }

    public function index(Request $request)
    {
        $this->authorizeVoucher('view', $request->boolean('maintenance'));

        $type = $request->input('type', AccountVoucher::TYPE_RECEIPT);

        $query = AccountVoucher::with(['treasuryAccount', 'creator'])
            ->withCount('lines')
            ->withSum('lines as lines_total', 'amount')
            ->where('type', $type);

        // سندات الصيانة = سندات صرف مربوطة بشاحنة (قسم الشاحنات)
        if ($request->boolean('maintenance')) {
            $query->whereNotNull('truck_id')->with('truck:id,plate_number,name');
        }
        if ($request->filled('truck_id')) {
            $query->where('truck_id', $request->input('truck_id'));
        }
        if ($request->filled('expense_category')) {
            $query->where('expense_category', $request->input('expense_category'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('voucher_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('voucher_date', '<=', $request->input('date_to'));
        }

        $vouchers = $query->orderByDesc('voucher_date')->orderByDesc('id')->paginate(20)->withQueryString();

        $isMaintenance = $request->boolean('maintenance');
        $trucks = $isMaintenance ? \App\Models\Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name']) : collect();

        return view('vouchers.index', compact('vouchers', 'type', 'isMaintenance', 'trucks'));
    }

    public function create(Request $request)
    {
        $this->authorizeVoucher('create', $request->boolean('maintenance'));

        $type = $request->input('type', AccountVoucher::TYPE_RECEIPT);
        if (! in_array($type, [AccountVoucher::TYPE_RECEIPT, AccountVoucher::TYPE_PAYMENT], true)) {
            $type = AccountVoucher::TYPE_RECEIPT;
        }

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        // الضرائب المفعّلة مرتبة بالأولوية (الأقل رقمًا يظهر أول واحد
        // ويبقى المختار الافتراضي في القائمة) - نفس ترتيب TaxController.
        $taxes = Tax::where('is_active', true)->orderBy('priority')->get();

        // حساب الخزينة في السند (قبض/صرف) بيتم اختياره ببحث Ajax مقصور
        // على الخزينة/البنوك بس (accounts.search?scope=treasury) - راجع
        // AccountController::search(). 4 و5 هما رقمي الحساب الأب لمجموعتي
        // "الخزينة" و"البنوك"، ونفس القيد اتأكد تاني في store() تحت.
        // سند صيانة: نفس سند الصرف بالظبط + اختيار الشاحنة ونوع المصروف
        $isMaintenance = $request->boolean('maintenance');
        if ($isMaintenance) {
            $type = AccountVoucher::TYPE_PAYMENT;
        }
        $trucks = \App\Models\Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name', 'status']);
        $selectedTruckId = $request->input('truck_id');

        return view('vouchers.create', compact('type', 'branches', 'costCenters', 'taxes', 'isMaintenance', 'trucks', 'selectedTruckId'));
    }

    /**
     * تجهيز بيانات كل بند مُدخل (تقسيم الضريبة لو خاضع لها + التأكد من
     * وجود حساب "ضريبة القيمة المضافة" المناسب) قبل أي تعديل فعلي على
     * قاعدة البيانات - نفس المنطق مستخدم من store() وupdate() الاتنين.
     */
    private function prepareLines(array $rawLines, ?FinancialAccount $treasuryForVat): array
    {
        $prepared = [];

        foreach ($rawLines as $rawLine) {
            $amount = round((float) $rawLine['amount'], 2);
            $isTaxable = ! empty($rawLine['is_taxable']) && ! empty($rawLine['tax_id']);
            $tax = $isTaxable ? Tax::find($rawLine['tax_id']) : null;
            $taxRate = $tax ? (float) $tax->rate : 0.0;
            $netAmount = $isTaxable ? round($amount * 100 / (100 + $taxRate), 2) : $amount;
            $taxAmount = $isTaxable ? round($amount - $netAmount, 2) : 0.0;

            $vatAccount = null;
            if ($isTaxable && $taxAmount > 0) {
                $vatAccount = FinancialAccount::where('parent_account_number', 102)
                    ->where('branchs_id', $treasuryForVat?->branchs_id)
                    ->first();
                if (! $vatAccount) {
                    abort(422, __('vouchers.vat_account_not_found'));
                }
            }

            $prepared[] = [
                'counterpart_account_id' => (int) $rawLine['counterpart_account_id'],
                'amount' => $amount,
                'description' => $rawLine['description'] ?? null,
                'cost_center_id' => $rawLine['cost_center_id'] ?? null,
                'is_taxable' => $isTaxable,
                'tax' => $tax,
                'tax_rate' => $taxRate,
                'net_amount' => $netAmount,
                'tax_amount' => $taxAmount,
                'vat_account' => $vatAccount,
            ];
        }

        return $prepared;
    }

    private function linesValidationRules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.counterpart_account_id' => ['required', 'integer', 'exists:financialaccount,id', 'different:treasury_account_id'],
            'lines.*.amount' => ['required', 'numeric', 'gt:0'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'lines.*.is_taxable' => ['nullable', 'boolean'],
            'lines.*.tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
        ];
    }

    public function store(Request $request)
    {
        $this->authorizeVoucher('create', $request->boolean('is_maintenance'));

        $validated = $request->validate(array_merge([
            'type' => ['required', 'in:receipt,payment'],
            'voucher_date' => ['required', 'date'],
            'truck_id' => ['nullable', 'required_if:is_maintenance,1', 'exists:trucks,id'],
            'expense_category' => ['nullable', 'in:' . implode(',', array_keys(AccountVoucher::EXPENSE_CATEGORIES))],
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ], $this->linesValidationRules()));

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (! $isValidTreasuryAccount) {
            abort(422, __('vouchers.invalid_treasury_account'));
        }

        $isReceipt = $validated['type'] === AccountVoucher::TYPE_RECEIPT;
        $prefix = $isReceipt ? 'RV' : 'PV';
        $operationType = $isReceipt ? OperationType::RECEIPT_VOUCHER : OperationType::PAYMENT_VOUCHER;

        $treasuryForVat = FinancialAccount::find($validated['treasury_account_id']);
        $preparedLines = $this->prepareLines($validated['lines'], $treasuryForVat);
        $totalAmount = round(array_sum(array_column($preparedLines, 'amount')), 2);

        $nextNumber = (int) (AccountVoucher::where('type', $validated['type'])->max('id') ?? 0) + 1;
        $voucherNumber = $prefix . '-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);

        $voucher = DB::transaction(function () use (
            $validated, $isReceipt, $totalAmount, $voucherNumber, $operationType, $preparedLines
        ) {
            $treasury = FinancialAccount::lockForUpdate()->findOrFail($validated['treasury_account_id']);

            $voucher = AccountVoucher::create([
                'voucher_number' => $voucherNumber,
                'type' => $validated['type'],
                'voucher_date' => $validated['voucher_date'],
                'treasury_account_id' => $treasury->id,
                'description' => $validated['description'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'truck_id' => $validated['truck_id'] ?? null,
                'expense_category' => !empty($validated['truck_id']) ? ($validated['expense_category'] ?? 'maintenance') : null,
                'created_by' => Auth::id(),
            ]);

            // سند قبض: الخزينة مدين (بتزيد) بإجمالي كل البنود / سند صرف:
            // عكسه بالظبط. حساب الخزينة بياخد المبلغ الكامل (شامل
            // الضريبة لو موجودة) - الفلوس اللي فعليًا دخلت/خرجت.
            $treasuryDebit = $isReceipt ? $totalAmount : 0;
            $treasuryCredit = $isReceipt ? 0 : $totalAmount;
            $treasuryNewBalance = AccountEffect::apply($treasury, $treasuryDebit, $treasuryCredit);

            $treasuryNote = $validated['description'] ?? ($isReceipt ? __('vouchers.receipt_note_default') : __('vouchers.payment_note_default'));

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $treasury->id,
                'recive_amount' => $totalAmount,
                'note' => $treasuryNote,
                'currentblance' => $treasuryNewBalance,
                'pay_method' => $validated['type'],
                'branchs_id' => $treasury->branchs_id ?? $validated['branch_id'] ?? null,
                'debtor' => $treasuryDebit,
                'creditor' => $treasuryCredit,
                'invoice_number' => $voucherNumber,
                'operation_type' => $operationType,
                'date_export' => $validated['voucher_date'],
            ]);

            foreach ($preparedLines as $line) {
                $counterpart = FinancialAccount::lockForUpdate()->findOrFail($line['counterpart_account_id']);

                // الطرف التاني بياخد "صافي" المبلغ بس (من غير الضريبة) لو
                // البند خاضع لضريبة - جزء الضريبة بيروح لحساب الضريبة تحت.
                $counterpartDebit = $isReceipt ? 0 : $line['net_amount'];
                $counterpartCredit = $isReceipt ? $line['net_amount'] : 0;
                $counterpartNewBalance = AccountEffect::apply($counterpart, $counterpartDebit, $counterpartCredit);

                $voucher->lines()->create([
                    'counterpart_account_id' => $counterpart->id,
                    'amount' => $line['amount'],
                    'description' => $line['description'],
                    'cost_center_id' => $line['cost_center_id'],
                    'is_taxable' => $line['is_taxable'],
                    'tax_id' => $line['tax']?->id,
                    'tax_rate' => $line['is_taxable'] ? $line['tax_rate'] : null,
                    'net_amount' => $line['is_taxable'] ? $line['net_amount'] : null,
                    'tax_amount' => $line['is_taxable'] ? $line['tax_amount'] : null,
                    'vat_account_id' => $line['vat_account']?->id,
                ]);

                $lineNote = $line['description'] ?? $treasuryNote;

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $counterpart->id,
                    'recive_amount' => $line['net_amount'],
                    'note' => $lineNote,
                    'currentblance' => $counterpartNewBalance,
                    'pay_method' => $validated['type'],
                    'branchs_id' => $counterpart->branchs_id ?? $validated['branch_id'] ?? null,
                    'debtor' => $counterpartDebit,
                    'creditor' => $counterpartCredit,
                    'invoice_number' => $voucherNumber,
                    'operation_type' => $operationType,
                    'date_export' => $validated['voucher_date'],
                ]);

                if ($line['vat_account'] && $line['tax_amount'] > 0) {
                    $vatLocked = FinancialAccount::lockForUpdate()->findOrFail($line['vat_account']->id);
                    $vatDebit = $isReceipt ? 0 : $line['tax_amount'];
                    $vatCredit = $isReceipt ? $line['tax_amount'] : 0;
                    $vatNewBalance = AccountEffect::apply($vatLocked, $vatDebit, $vatCredit);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $vatLocked->id,
                        'recive_amount' => $line['tax_amount'],
                        'note' => $lineNote,
                        'currentblance' => $vatNewBalance,
                        'pay_method' => $validated['type'],
                        'branchs_id' => $vatLocked->branchs_id,
                        'debtor' => $vatDebit,
                        'creditor' => $vatCredit,
                        'invoice_number' => $voucherNumber,
                        'operation_type' => $operationType,
                        'date_export' => $validated['voucher_date'],
                        'vat' => 1,
                    ]);
                }
            }

            return $voucher;
        });

        return redirect()->route('vouchers.show', $voucher)->with('success', __('vouchers.created_successfully'));
    }

    public function show(AccountVoucher $voucher)
    {
        $this->authorizeVoucher('view', (bool) $voucher->truck_id);

        $voucher->load(['treasuryAccount', 'creator', 'branch', 'lines.counterpartAccount', 'lines.costCenter', 'lines.vatAccount']);

        return view('vouchers.show', compact('voucher'));
    }

    public function edit(AccountVoucher $voucher)
    {
        $this->authorizeVoucher('edit', (bool) $voucher->truck_id);

        $voucher->load(['treasuryAccount', 'lines.counterpartAccount']);

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();
        $taxes = Tax::where('is_active', true)->orderBy('priority')->get();

        // بنبني مصفوفة الأسطر الحالية هنا في الكونترولر (بدل ما نعمل
        // ->map(fn ($l) => [...]) جوه @json() مباشرة في الـ blade) عشان
        // نبعت لـ @json() متغير بسيط (مصفوفة PHP عادية) - نفس بالظبط
        // الأسلوب المستخدم في QuotationController::edit() ودي طريقة
        // مضمونة ومختبرة في الأكشن ده.
        $existingLines = $voucher->lines->map(function ($l) {
            return [
                'counterpart_account_id' => $l->counterpart_account_id,
                'counterpart_account_name' => optional($l->counterpartAccount)->name,
                'amount' => (float) $l->amount,
                'description' => $l->description,
                'cost_center_id' => $l->cost_center_id,
                'is_taxable' => (bool) $l->is_taxable,
                'tax_id' => $l->tax_id,
            ];
        })->values();

        $isMaintenance = (bool) $voucher->truck_id;
        $trucks = \App\Models\Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name', 'status']);
        $selectedTruckId = $voucher->truck_id;

        return view('vouchers.edit', compact('voucher', 'branches', 'costCenters', 'taxes', 'existingLines', 'isMaintenance', 'trucks', 'selectedTruckId'));
    }

    /**
     * تعديل سند موجود: بيرجع أثر السند القديم بالكامل (الخزينة بإجمالي
     * بنودها القديمة + كل بند قديم من حسابه)، يمسح البنود وحركات
     * credittransaction القديمة، وبعدين يطبّق أثر البيانات الجديدة من
     * الأول - بالظبط زي JournalEntryController::update() (عدد البنود
     * الحر بيمنع تحديث الأسطر في مكانها زي ما كان بيحصل قبل كده لما كان
     * فيه حساب تاني ثابت واحد بس). نوع السند (قبض/صرف) مش قابل للتغيير
     * هنا عشان ده بيغيّر معنى مدين/دائن بالكامل.
     */
    public function update(Request $request, AccountVoucher $voucher)
    {
        $this->authorizeVoucher('edit', (bool) $voucher->truck_id);

        $validated = $request->validate(array_merge([
            'voucher_date' => ['required', 'date'],
            'truck_id' => ['nullable', 'required_if:is_maintenance,1', 'exists:trucks,id'],
            'expense_category' => ['nullable', 'in:' . implode(',', array_keys(AccountVoucher::EXPENSE_CATEGORIES))],
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ], $this->linesValidationRules()));

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (! $isValidTreasuryAccount) {
            abort(422, __('vouchers.invalid_treasury_account'));
        }

        $isReceipt = $voucher->isReceipt();
        $operationType = $isReceipt ? OperationType::RECEIPT_VOUCHER : OperationType::PAYMENT_VOUCHER;

        $treasuryForVat = FinancialAccount::find($validated['treasury_account_id']);
        $preparedLines = $this->prepareLines($validated['lines'], $treasuryForVat);
        $totalAmount = round(array_sum(array_column($preparedLines, 'amount')), 2);

        DB::transaction(function () use ($voucher, $validated, $isReceipt, $totalAmount, $operationType, $preparedLines) {
            $oldLines = $voucher->lines()->get();
            $oldTotalAmount = round((float) $oldLines->sum('amount'), 2);

            // نحافظ على created_at الأصلي لحركات السند القديمة عشان
            // ترتيبها في كشف الحساب متتأثرش بتوقيت التعديل نفسه.
            $originalCreatedAt = optional(
                CreditTransaction::where('invoice_number', $voucher->voucher_number)
                    ->where('operation_type', $operationType)
                    ->oldest('created_at')
                    ->first()
            )->created_at ?? $voucher->created_at;

            // 1) نرجّع أثر السند القديم بالكامل: الخزينة (بإجمالي البنود
            // القديمة) وكل بند قديم من حسابه (والضريبة لو كانت موجودة).
            $oldTreasury = FinancialAccount::lockForUpdate()->findOrFail($voucher->treasury_account_id);
            $oldTreasuryDebit = $isReceipt ? $oldTotalAmount : 0;
            $oldTreasuryCredit = $isReceipt ? 0 : $oldTotalAmount;
            AccountEffect::reverse($oldTreasury, $oldTreasuryDebit, $oldTreasuryCredit);

            foreach ($oldLines as $oldLine) {
                $oldCounterpart = FinancialAccount::lockForUpdate()->findOrFail($oldLine->counterpart_account_id);
                $oldNetAmount = $oldLine->is_taxable ? (float) $oldLine->net_amount : (float) $oldLine->amount;
                $oldCounterpartDebit = $isReceipt ? 0 : $oldNetAmount;
                $oldCounterpartCredit = $isReceipt ? $oldNetAmount : 0;
                AccountEffect::reverse($oldCounterpart, $oldCounterpartDebit, $oldCounterpartCredit);

                if ($oldLine->vat_account_id && $oldLine->is_taxable && (float) $oldLine->tax_amount > 0) {
                    $oldVat = FinancialAccount::lockForUpdate()->find($oldLine->vat_account_id);
                    if ($oldVat) {
                        $oldVatDebit = $isReceipt ? 0 : (float) $oldLine->tax_amount;
                        $oldVatCredit = $isReceipt ? (float) $oldLine->tax_amount : 0;
                        AccountEffect::reverse($oldVat, $oldVatDebit, $oldVatCredit);
                    }
                }
            }

            // 2) نمسح البنود وحركات credittransaction القديمة كلها.
            CreditTransaction::where('invoice_number', $voucher->voucher_number)
                ->where('operation_type', $operationType)
                ->delete();
            $voucher->lines()->delete();

            // 3) تحديث هيدر السند نفسه (رقمه ونوعه ثابتين).
            $treasury = FinancialAccount::lockForUpdate()->findOrFail($validated['treasury_account_id']);
            $voucher->update([
                'voucher_date' => $validated['voucher_date'],
                'treasury_account_id' => $treasury->id,
                'description' => $validated['description'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'truck_id' => $validated['truck_id'] ?? null,
                'expense_category' => !empty($validated['truck_id']) ? ($validated['expense_category'] ?? 'maintenance') : null,
            ]);

            // 4) نطبّق أثر البيانات الجديدة من الأول: الخزينة بإجماليها
            // الجديد، وكل بند جديد من حسابه.
            $treasuryDebit = $isReceipt ? $totalAmount : 0;
            $treasuryCredit = $isReceipt ? 0 : $totalAmount;
            $treasuryNewBalance = AccountEffect::apply($treasury, $treasuryDebit, $treasuryCredit);

            $treasuryNote = $validated['description'] ?? ($isReceipt ? __('vouchers.receipt_note_default') : __('vouchers.payment_note_default'));

            $treasuryTransaction = new CreditTransaction([
                'user_id' => Auth::id(),
                'customer_id' => $treasury->id,
                'recive_amount' => $totalAmount,
                'note' => $treasuryNote,
                'currentblance' => $treasuryNewBalance,
                'pay_method' => $voucher->type,
                'branchs_id' => $treasury->branchs_id ?? $validated['branch_id'] ?? null,
                'debtor' => $treasuryDebit,
                'creditor' => $treasuryCredit,
                'invoice_number' => $voucher->voucher_number,
                'operation_type' => $operationType,
                'date_export' => $validated['voucher_date'],
            ]);
            $treasuryTransaction->timestamps = false;
            $treasuryTransaction->created_at = $originalCreatedAt;
            $treasuryTransaction->updated_at = now();
            $treasuryTransaction->save();

            foreach ($preparedLines as $line) {
                $counterpart = FinancialAccount::lockForUpdate()->findOrFail($line['counterpart_account_id']);
                $counterpartDebit = $isReceipt ? 0 : $line['net_amount'];
                $counterpartCredit = $isReceipt ? $line['net_amount'] : 0;
                $counterpartNewBalance = AccountEffect::apply($counterpart, $counterpartDebit, $counterpartCredit);

                $voucher->lines()->create([
                    'counterpart_account_id' => $counterpart->id,
                    'amount' => $line['amount'],
                    'description' => $line['description'],
                    'cost_center_id' => $line['cost_center_id'],
                    'is_taxable' => $line['is_taxable'],
                    'tax_id' => $line['tax']?->id,
                    'tax_rate' => $line['is_taxable'] ? $line['tax_rate'] : null,
                    'net_amount' => $line['is_taxable'] ? $line['net_amount'] : null,
                    'tax_amount' => $line['is_taxable'] ? $line['tax_amount'] : null,
                    'vat_account_id' => $line['vat_account']?->id,
                ]);

                $lineNote = $line['description'] ?? $treasuryNote;

                $counterpartTransaction = new CreditTransaction([
                    'user_id' => Auth::id(),
                    'customer_id' => $counterpart->id,
                    'recive_amount' => $line['net_amount'],
                    'note' => $lineNote,
                    'currentblance' => $counterpartNewBalance,
                    'pay_method' => $voucher->type,
                    'branchs_id' => $counterpart->branchs_id ?? $validated['branch_id'] ?? null,
                    'debtor' => $counterpartDebit,
                    'creditor' => $counterpartCredit,
                    'invoice_number' => $voucher->voucher_number,
                    'operation_type' => $operationType,
                    'date_export' => $validated['voucher_date'],
                ]);
                $counterpartTransaction->timestamps = false;
                $counterpartTransaction->created_at = $originalCreatedAt;
                $counterpartTransaction->updated_at = now();
                $counterpartTransaction->save();

                if ($line['vat_account'] && $line['tax_amount'] > 0) {
                    $vatLocked = FinancialAccount::lockForUpdate()->findOrFail($line['vat_account']->id);
                    $vatDebit = $isReceipt ? 0 : $line['tax_amount'];
                    $vatCredit = $isReceipt ? $line['tax_amount'] : 0;
                    $vatNewBalance = AccountEffect::apply($vatLocked, $vatDebit, $vatCredit);

                    $vatTransaction = new CreditTransaction([
                        'user_id' => Auth::id(),
                        'customer_id' => $vatLocked->id,
                        'recive_amount' => $line['tax_amount'],
                        'note' => $lineNote,
                        'currentblance' => $vatNewBalance,
                        'pay_method' => $voucher->type,
                        'branchs_id' => $vatLocked->branchs_id,
                        'debtor' => $vatDebit,
                        'creditor' => $vatCredit,
                        'invoice_number' => $voucher->voucher_number,
                        'operation_type' => $operationType,
                        'date_export' => $validated['voucher_date'],
                        'vat' => 1,
                    ]);
                    $vatTransaction->timestamps = false;
                    $vatTransaction->created_at = $originalCreatedAt;
                    $vatTransaction->updated_at = now();
                    $vatTransaction->save();
                }
            }
        });

        return redirect()->route('vouchers.show', $voucher)->with('success', __('vouchers.updated_successfully'));
    }

    /**
     * صفحة طباعة احترافية للسند (قبض/صرف) - مستقلة عن x-app-layout
     * (زي resources/views/invoices/returns/print.blade.php بالظبط)،
     * وبتستخدم نفس هوية الشركة (Namear/camplogo/...) المعرّفة في
     * AppServiceProvider عشان تبقى متسقة مع باقي مطبوعات النظام.
     */
    public function print(AccountVoucher $voucher)
    {
        $voucher->load(['treasuryAccount', 'creator', 'branch', 'lines.counterpartAccount', 'lines.costCenter']);

        $amountInWords = ArabicNumberWords::amountToWords($voucher->total_amount);

        return view('vouchers.print', compact('voucher', 'amountInWords'));
    }
}
