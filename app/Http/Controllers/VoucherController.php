<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Support\AccountEffect;
use App\Support\ArabicNumberWords;
use App\Support\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * سندات القبض والصرف (Receipt / Payment Vouchers).
 *
 * سند قبض (receipt): فلوس داخلة لحساب الخزينة/البنك (treasury_account)
 * جاية من حساب تاني (counterpart_account - عميل/حساب عام). سند صرف
 * (payment): عكسه بالظبط - فلوس خارجة من الخزينة لحساب تاني.
 *
 * نفس قاعدة current_balance المستخدمة في JournalEntryController -
 * بتتحسب حسب FinancialAccount::balanceAfter() (طبيعة الحساب مدين
 * أو دائن بناءً على orginal_type). راجعي تعليق isCreditNormal() في
 * موديل FinancialAccount لتفاصيل السبب.
 */
class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', AccountVoucher::TYPE_RECEIPT);

        $query = AccountVoucher::with(['treasuryAccount', 'counterpartAccount', 'creator'])
            ->where('type', $type);

        if ($request->filled('date_from')) {
            $query->whereDate('voucher_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('voucher_date', '<=', $request->input('date_to'));
        }

        $vouchers = $query->orderByDesc('voucher_date')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('vouchers.index', compact('vouchers', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->input('type', AccountVoucher::TYPE_RECEIPT);
        if (! in_array($type, [AccountVoucher::TYPE_RECEIPT, AccountVoucher::TYPE_PAYMENT], true)) {
            $type = AccountVoucher::TYPE_RECEIPT;
        }

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        // حساب الخزينة في السند (قبض/صرف) بيتم اختياره ببحث Ajax مقصور
        // على الخزينة/البنوك بس (accounts.search?scope=treasury) - راجعي
        // AccountController::search(). 4 و5 هما رقمي الحساب الأب لمجموعتي
        // "الخزينة" و"البنوك"، ونفس القيد اتأكد تاني في store() تحت.
        return view('vouchers.create', compact('type', 'branches', 'costCenters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:receipt,payment'],
            'voucher_date' => ['required', 'date'],
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'counterpart_account_id' => ['required', 'integer', 'exists:financialaccount,id', 'different:treasury_account_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
        ]);

        // تأكيد إضافي (مش بس شكلي في الفورم) إن حساب الخزينة فعلاً
        // خزينة/بنك تابع لفرع - عشان محدش يقدر يبعت أي account id تاني
        // مباشرة في الـ request ويتحايل على الـ select.
        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (! $isValidTreasuryAccount) {
            abort(422, __('vouchers.invalid_treasury_account'));
        }

        $isReceipt = $validated['type'] === AccountVoucher::TYPE_RECEIPT;
        $amount = round((float) $validated['amount'], 2);
        $prefix = $isReceipt ? 'RV' : 'PV';
        $operationType = $isReceipt ? OperationType::RECEIPT_VOUCHER : OperationType::PAYMENT_VOUCHER;

        $nextNumber = (int) (AccountVoucher::where('type', $validated['type'])->max('id') ?? 0) + 1;
        $voucherNumber = $prefix . '-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);

        $voucher = DB::transaction(function () use ($validated, $isReceipt, $amount, $voucherNumber, $operationType) {
            $treasury = FinancialAccount::lockForUpdate()->findOrFail($validated['treasury_account_id']);
            $counterpart = FinancialAccount::lockForUpdate()->findOrFail($validated['counterpart_account_id']);

            $voucher = AccountVoucher::create([
                'voucher_number' => $voucherNumber,
                'type' => $validated['type'],
                'voucher_date' => $validated['voucher_date'],
                'treasury_account_id' => $treasury->id,
                'counterpart_account_id' => $counterpart->id,
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // سند قبض: الخزينة مدين (بتزيد) / الطرف التاني دائن (بينقص).
            // سند صرف: عكسه بالظبط.
            $treasuryDebit = $isReceipt ? $amount : 0;
            $treasuryCredit = $isReceipt ? 0 : $amount;
            $counterpartDebit = $isReceipt ? 0 : $amount;
            $counterpartCredit = $isReceipt ? $amount : 0;

            $treasuryNewBalance = AccountEffect::apply($treasury, $treasuryDebit, $treasuryCredit);
            $counterpartNewBalance = AccountEffect::apply($counterpart, $counterpartDebit, $counterpartCredit);

            $note = $validated['description'] ?? ($isReceipt ? __('vouchers.receipt_note_default') : __('vouchers.payment_note_default'));

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $treasury->id,
                'recive_amount' => $amount,
                'note' => $note,
                'currentblance' => $treasuryNewBalance,
                'pay_method' => $validated['type'],
                'branchs_id' => $treasury->branchs_id ?? $validated['branch_id'] ?? null,
                'debtor' => $treasuryDebit,
                'creditor' => $treasuryCredit,
                'invoice_number' => $voucherNumber,
                'operation_type' => $operationType,
                'date_export' => $validated['voucher_date'],
            ]);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $counterpart->id,
                'recive_amount' => $amount,
                'note' => $note,
                'currentblance' => $counterpartNewBalance,
                'pay_method' => $validated['type'],
                'branchs_id' => $counterpart->branchs_id ?? $validated['branch_id'] ?? null,
                'debtor' => $counterpartDebit,
                'creditor' => $counterpartCredit,
                'invoice_number' => $voucherNumber,
                'operation_type' => $operationType,
                'date_export' => $validated['voucher_date'],
            ]);

            return $voucher;
        });

        return redirect()->route('vouchers.show', $voucher)->with('success', __('vouchers.created_successfully'));
    }

    public function show(AccountVoucher $voucher)
    {
        $voucher->load(['treasuryAccount', 'counterpartAccount', 'creator', 'branch', 'costCenter']);

        return view('vouchers.show', compact('voucher'));
    }

    public function edit(AccountVoucher $voucher)
    {
        $voucher->load(['treasuryAccount', 'counterpartAccount']);

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        return view('vouchers.edit', compact('voucher', 'branches', 'costCenters'));
    }

    /**
     * تعديل سند موجود: بيرجع أثر السند القديم بالكامل من الحسابين
     * القدامى (زي ما كانوا لو السند اتلغى)، بعدين يطبّق أثر البيانات
     * الجديدة على الحسابين الجداد (ممكن يكونوا نفس الحسابين القدامى أو
     * غيرهم) - وده اللي بيخلي التعديل مأمون حتى لو المستخدم غيّر
     * المبلغ أو الحساب نفسه. نوع السند (قبض/صرف) مش قابل للتغيير هنا
     * عشان ده بيغيّر معنى مدين/دائن بالكامل - لو غلط في النوع لازم
     * يعمل سند جديد بدل ما يعدّل في القديم.
     */
    public function update(Request $request, AccountVoucher $voucher)
    {
        $validated = $request->validate([
            'voucher_date' => ['required', 'date'],
            'treasury_account_id' => ['required', 'integer', 'exists:financialaccount,id'],
            'counterpart_account_id' => ['required', 'integer', 'exists:financialaccount,id', 'different:treasury_account_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
        ]);

        $isValidTreasuryAccount = FinancialAccount::where('id', $validated['treasury_account_id'])
            ->whereIn('parent_account_number', [4, 5])
            ->exists();
        if (! $isValidTreasuryAccount) {
            abort(422, __('vouchers.invalid_treasury_account'));
        }

        $isReceipt = $voucher->isReceipt();
        $amount = round((float) $validated['amount'], 2);

        DB::transaction(function () use ($voucher, $validated, $isReceipt, $amount) {
            $oldTreasuryId = (int) $voucher->treasury_account_id;
            $oldCounterpartId = (int) $voucher->counterpart_account_id;
            $oldAmount = (float) $voucher->amount;

            $oldTreasuryDebit = $isReceipt ? $oldAmount : 0;
            $oldTreasuryCredit = $isReceipt ? 0 : $oldAmount;
            $oldCounterpartDebit = $isReceipt ? 0 : $oldAmount;
            $oldCounterpartCredit = $isReceipt ? $oldAmount : 0;

            // 1) نرجّع أثر السند القديم بالكامل من الحسابين القدامى.
            $oldTreasuryAccount = FinancialAccount::lockForUpdate()->findOrFail($oldTreasuryId);
            AccountEffect::reverse($oldTreasuryAccount, $oldTreasuryDebit, $oldTreasuryCredit);

            $oldCounterpartAccount = FinancialAccount::lockForUpdate()->findOrFail($oldCounterpartId);
            AccountEffect::reverse($oldCounterpartAccount, $oldCounterpartDebit, $oldCounterpartCredit);

            // 2) نطبّق أثر البيانات الجديدة على الحسابين الجداد (ممكن
            // يكونوا نفس القدامى - lockForUpdate هنا بترجع نفس الصف بعد
            // التحديث اللي حصل فوق في نفس الـ transaction، فمفيش تعارض).
            $newTreasuryDebit = $isReceipt ? $amount : 0;
            $newTreasuryCredit = $isReceipt ? 0 : $amount;
            $newCounterpartDebit = $isReceipt ? 0 : $amount;
            $newCounterpartCredit = $isReceipt ? $amount : 0;

            $treasury = FinancialAccount::lockForUpdate()->findOrFail($validated['treasury_account_id']);
            $treasuryNewBalance = AccountEffect::apply($treasury, $newTreasuryDebit, $newTreasuryCredit);

            $counterpart = FinancialAccount::lockForUpdate()->findOrFail($validated['counterpart_account_id']);
            $counterpartNewBalance = AccountEffect::apply($counterpart, $newCounterpartDebit, $newCounterpartCredit);

            // 3) تحديث صف السند نفسه (رقم السند ونوعه ثابتين مش بيتغيروا).
            $voucher->update([
                'voucher_date' => $validated['voucher_date'],
                'treasury_account_id' => $treasury->id,
                'counterpart_account_id' => $counterpart->id,
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
            ]);

            // 4) تحديث صفوف credittransaction المرتبطة في مكانها (بدل
            // حذف وإعادة إنشاء) عشان نحافظ على ترتيبها الأصلي (created_at)
            // في كشف الحساب - بنلاقيهم بـ invoice_number = رقم السند
            // و customer_id = id الحساب القديم (قبل التحديث فوق).
            $note = $validated['description'] ?? ($isReceipt ? __('vouchers.receipt_note_default') : __('vouchers.payment_note_default'));

            $oldTreasuryTransaction = CreditTransaction::where('invoice_number', $voucher->voucher_number)
                ->where('customer_id', $oldTreasuryId)
                ->first();
            if ($oldTreasuryTransaction) {
                $oldTreasuryTransaction->update([
                    'customer_id' => $treasury->id,
                    'recive_amount' => $amount,
                    'note' => $note,
                    'currentblance' => $treasuryNewBalance,
                    'branchs_id' => $treasury->branchs_id ?? $validated['branch_id'] ?? null,
                    'debtor' => $newTreasuryDebit,
                    'creditor' => $newTreasuryCredit,
                    'date_export' => $validated['voucher_date'],
                ]);
            }

            $oldCounterpartTransaction = CreditTransaction::where('invoice_number', $voucher->voucher_number)
                ->where('customer_id', $oldCounterpartId)
                ->where('id', '!=', optional($oldTreasuryTransaction)->id)
                ->first();
            if ($oldCounterpartTransaction) {
                $oldCounterpartTransaction->update([
                    'customer_id' => $counterpart->id,
                    'recive_amount' => $amount,
                    'note' => $note,
                    'currentblance' => $counterpartNewBalance,
                    'branchs_id' => $counterpart->branchs_id ?? $validated['branch_id'] ?? null,
                    'debtor' => $newCounterpartDebit,
                    'creditor' => $newCounterpartCredit,
                    'date_export' => $validated['voucher_date'],
                ]);
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
        $voucher->load(['treasuryAccount', 'counterpartAccount', 'creator', 'branch', 'costCenter']);

        $amountInWords = ArabicNumberWords::amountToWords((float) $voucher->amount);

        return view('vouchers.print', compact('voucher', 'amountInWords'));
    }
}
