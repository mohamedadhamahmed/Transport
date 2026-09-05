<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\delivery_to_customer_withoud_tax_invoices;
use App\Models\sales_withoud_taxes;
use App\Models\Product;           // عدّل اسم الموديل حسب موديل المنتجات عندك
use App\Models\Customer;          // عدّل اسم الموديل حسب موديل العملاء عندك
use App\Models\FinancialAccount;   // موديل الحسابات المالية (للقيود المحاسبية)
use App\Models\CreditTransaction;  // موديل حركات القيد المحاسبي
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DeliveryController extends Controller
{
        /**
     * قائمة منتجات مقسّمة صفحات (20 في كل صفحة) لمودال "اختيار منتج" في
     * شاشة تسليم منتج - نفس منطق InvoiceController::pickProducts() تمامًا.
     *
     * ملحوظة: كانت الدالة دي مبتفلترش على branch_id خالص (بعكس نفس الدالة
     * في InvoiceController وDeliveryNoteController)، يعني المودال كان بيورّي
     * منتجات كل الفروع مخلوطة مع بعض - ضفنا نفس فلتر الفرع المتبع في باقي
     * الشاشات.
     */
    public function pickProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $products->getCollection()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'location' => $p->location ?? null,
                    'stock_quantity' => $p->stock_quantity ?? null,
                    'purchase_price' => $p->purchase_price,
                    'sale_price' => $p->sale_price,
                ];
            })->values(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * عرض فورم إنشاء عملية تسليم منتج جديدة
     */
    public function create()
    {
        $this->authorize('delivery.create');

        // منحملش كل جدول العملاء هنا (بحث Ajax حي في الفورم نفسه).
        $customers = [];

        return view('delivery.create', compact('customers'));
    }

    /**
     * البحث اللحظي عن منتجات بالاسم/الكود (يُستخدم عبر Alpine.js من صفحة التسليم)
     *
     * ملحوظة: الدالة كانت اسمها "searchProduct" (مفرد) بينما الراوت في
     * routes/web.php بيستدعي "searchProducts" (جمع) - يعني صندوق البحث
     * السريع في شاشة "تسليم منتج" كان بيرمي خطأ 500 (Method does not
     * exist) من غير ما يشتغل خالص. كمان كانت بتفلتر على عمود
     * "product_number" مش موجود في جدول المنتجات (العمود الصح "code")
     * وعلى "branchs_id" (خطأ إملائي، الصح "branch_id") فمكانتش هتفلتر
     * بالفرع حتى لو الاسم كان صح.
     */
    public function searchProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'code', 'sale_price', 'purchase_price', 'stock_quantity']);

        return response()->json($products);
    }

    /**
     * إضافة منتج سريع من مودال "منتج جديد" في شاشة تسليم منتج.
     *
     * ملحوظة: الدالة دي مكانتش موجودة خالص في الكنترولر رغم إن الراوت
     * "delivery.products.quick" والفورم في resources/views/delivery/create.blade.php
     * (زرار "منتج جديد") بينادوها - يعني زرار "منتج جديد" في شاشة تسليم
     * منتج كان بيرمي خطأ 500 (Method does not exist) من غير ما يشتغل
     * خالص. الدالة هنا بنفس منطق DeliveryNoteController::quickStoreProduct().
     */
    public function quickStoreProduct(Request $request)
    {
        $branchId = Auth::user()?->branch_id;
        $request->merge(['branch_id' => $branchId, 'status' => 'active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = Auth::id();

        $product = Product::create($validated);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sale_price' => $product->sale_price,
            'purchase_price' => $product->purchase_price,
        ]);
    }

    /**
     * عرض قائمة التسليمات السابقة مع فلترة بالتاريخ والعميل
     */
    public function history(Request $request)
    {
        $this->authorize('delivery.view');

        $start_at = $request->start_at ?? date('Y-m-01');
        $end_at   = $request->end_at ?? date('Y-m-d');

        $query = delivery_to_customer_withoud_tax_invoices::with(['customer', 'user'])
            ->whereDate('created_at', '>=', $start_at)
            ->whereDate('created_at', '<=', $end_at)
            ->where('save', 1);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->orderByDesc('created_at')->paginate(20);

        // فلتر العميل بقى بحث Ajax حي (منحملش كل جدول العملاء).
        $selectedCustomerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $Customer = $selectedCustomerId
            ? [$selectedCustomerId => optional(Customer::find($selectedCustomerId))->name]
            : [];

        return view('delivery.history', compact('invoices', 'Customer'));
    }

    /**
     * عرض/طباعة سند تسليم واحد
     */
    public function show($id)
    {
        $this->authorize('delivery.view');

        $invoice = delivery_to_customer_withoud_tax_invoices::with(['customer', 'user'])->findOrFail($id);
        $items = sales_withoud_taxes::where('invoice_id', $id)
            ->where('save', 1)
            ->with('product')
            ->get();

        return view('delivery.show', compact('invoice', 'items'));
    }

    /**
     * التحقق من صحة البيانات + حفظ سند تسليم جديد (رأس السند + البنود + القيود المحاسبية).
     * الحماية من الضغط المزدوج بنفس أسلوب InvoiceController::store() بالظبط:
     * Cache::add() عملية ذرية (atomic) - أول طلب بس هو اللي بينجح يسجل
     * القفل، وأي طلب تاني بنفس التوكن بيترفض فورًا حتى لو الطلبين وصلوا
     * للسيرفر في نفس اللحظة تقريبًا (على عكس فحص عمود في قاعدة البيانات
     * اللي بيفضل عرضة لـ race condition بين الطلبين).
     */
    public function store(Request $request)
    {
        $this->authorize('delivery.create');

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'submission_token' => ['nullable', 'string', 'max:64'],
            'customer_id' => ['required'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,credit,split'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'discountOnInvoice' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.name' => ['nullable', 'string'],
            'items.*.code' => ['nullable', 'string'],
        ])->validate();

        // حماية ذرية من تكرار الإرسال (ضغط مزدوج / إعادة إرسال)
        $submissionToken = $validated['submission_token'] ?? null;
        if ($submissionToken) {
            $lockKey = 'delivery_submission_' . $submissionToken;
            if (!Cache::add($lockKey, true, now()->addMinutes(15))) {
                return redirect()->route('delivery.history')
                    ->with('error', __('delivery.duplicate_submission_prevented'));
            }
        }

        $invoice = $this->finalizeDelivery($validated);

        return redirect()->route('delivery.history')->with('success', __('delivery.save_success'));
    }

    /**
     * تحويل بيانات السند لسند تسليم فعلي: بيتسجل، بيتخصم من المخزون
     * (اختياري)، وبيتسجل كل القيود المحاسبية المرتبطة بيه - بدون أي
     * حساب ضريبة قيمة مضافة (بعكس finalizeInvoice() في نظام الفواتير).
     * نفس بنية finalizeInvoice() بالظبط، فصلناها في ميثود واحدة بدل ما
     * تتكرر جوه store() مباشرة.
     */
    protected function finalizeDelivery(array $validated): delivery_to_customer_withoud_tax_invoices
    {
        return DB::transaction(function () use ($validated) {
            $branchId = Auth::user()->branchs_id ?? 1;
            $now = Carbon::now('Asia/Riyadh');

            // ===== 1. إعادة حساب الإجماليات من السيرفر لضمان الدقة =====
            $productIds = collect($validated['items'])->pluck('product_id')->unique();
            $ProductData = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $totalPrice = 0;
            $totalQuantity = 0;
            $totalCost = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $totalPrice += $lineTotal;
                $totalQuantity += $item['quantity'];

                $product = $ProductData->get($item['product_id']);
                $totalCost += ($product->purchase_price ?? 0) * $item['quantity'];
            }

            $discountOnInvoice = min($validated['discountOnInvoice'] ?? 0, $totalPrice);
            $grandTotal = $totalPrice - $discountOnInvoice;

            $cashAmount = 0;
            $bankAmount = 0;
            $bankTransfer = 0;
            $creditAmount = 0;

            switch ($validated['payment_method']) {
                case 'cash':
                    $cashAmount = $grandTotal;
                    break;
                case 'bank_transfer':
                    $bankTransfer = $grandTotal;
                    break;
                case 'card':
                    $bankAmount = $grandTotal;
                    break;
                case 'credit':
                    $creditAmount = $grandTotal;
                    break;
                case 'split':
                    $cashAmount = $validated['cash_amount'] ?? 0;
                    $bankAmount = $validated['bank_amount'] ?? 0;
                    $creditAmount = max(0, $grandTotal - ($cashAmount + $bankAmount));
                    break;
            }

            // ===== 2. حفظ رأس السند =====
            $invoice = delivery_to_customer_withoud_tax_invoices::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => Auth::id(),
                'Price' => $grandTotal,
                'Number_of_Quantity' => $totalQuantity,
                'Pay' => ucfirst($validated['payment_method']),
                'branchs_id' => $branchId,
                'discountOnInvoice' => $discountOnInvoice,
                'note' => $validated['note'] ?? '-',
                'status' => 0,
                'save' => 1,
                'cashamount' => $cashAmount,
                'bankamount' => $bankAmount,
                'creaditamount' => $creditAmount,
                'Bank_transfer' => $bankTransfer,
            ]);

            // ===== 3. حفظ بنود السند وتحديث المخزون =====
            foreach ($validated['items'] as $item) {
                sales_withoud_taxes::create([
                    'product_id' => $item['product_id'],
                    'invoice_id' => $invoice->id,
                    'Discount_Value' => $item['discount'] ?? 0,
                    'branch_id' => $branchId,
                    'Unit_Price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'save' => 1,
                ]);

                // نقص المخزون (اختياري - فعّله لو محتاج تحديث الكمية المتبقية فعليًا)
                // Product::where('id', $item['product_id'])->decrement('stock_quantity', $item['quantity']);
            }

            // ===== 4. القيود المحاسبية (بدون ضريبة) =====
            $this->recordAccountingEntries($invoice, $branchId, $now, [
                'cashamount' => $cashAmount,
                'Bank_transfer' => $bankTransfer,
                'bankamount' => $bankAmount,
                'creaditamount' => $creditAmount,
                'grandTotal' => $grandTotal,
                'totalCost' => $totalCost,
                'payment_method' => $validated['payment_method'],
            ]);

            return $invoice;
        });
    }

    /**
     * القيود المحاسبية لسند التسليم - نفس منطق InvoiceController تمامًا
     * (خزينة/بنك مدين، إيراد دائن، تكلفة/مخزون، مبلغ آجل) لكن من غير أي
     * فصل أو حساب لضريبة القيمة المضافة (لا يوجد قيد لحساب الضريبة 102).
     */
    protected function recordAccountingEntries(delivery_to_customer_withoud_tax_invoices $invoice, int $branchId, Carbon $now, array $pData): void
    {
        $customerId = $invoice->customer_id;
        $customerData = Customer::find($customerId);
        $noteText = 'سند تسليم رقم :' . $invoice->id;
        $payMethod = $pData['payment_method'];

        // أ. قيد الدفع النقدي (Cash)
        if ($pData['cashamount']) {
            $cashAccount = FinancialAccount::where('parent_account_number', 5)->where('branchs_id', $branchId)->first();
            if ($cashAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $cashAccount->id,
                    'recive_amount' => $pData['cashamount'],
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $cashAccount->current_balance + $pData['cashamount'],
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'debtor' => $pData['cashamount'],
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
                $cashAccount->update([
                    'current_balance' => $cashAccount->current_balance + $pData['cashamount'],
                    'debtor_current' => $cashAccount->debtor_current + $pData['cashamount'],
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
            }
        }

        // ب. قيد الشبكة / التحويل البنكي
        $totalBank = $pData['Bank_transfer'] + $pData['bankamount'];
        if ($totalBank) {
            $bankAccount = FinancialAccount::where('parent_account_number', 4)->where('branchs_id', $branchId)->first();
            if ($bankAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $bankAccount->id,
                    'recive_amount' => $totalBank,
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $bankAccount->current_balance + $totalBank,
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'debtor' => $totalBank,
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
                $bankAccount->update([
                    'current_balance' => $bankAccount->current_balance + $totalBank,
                    'debtor_current' => $bankAccount->debtor_current + $totalBank,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
            }
        }

        // ج. حساب الإيرادات (بدون فصل ضريبة - المبلغ الكامل إيراد صافي)
        $revenueAccount = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', $branchId)->first();
        if ($revenueAccount && $pData['grandTotal'] > 0) {
            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $revenueAccount->id,
                'recive_amount' => $pData['grandTotal'],
                'branchs_id' => $branchId,
                'pay_method' => $payMethod,
                'note' => $noteText,
                'currentblance' => $revenueAccount->current_balance + $pData['grandTotal'],
                'Pay_Method_Name' => ucfirst($payMethod),
                'created_at' => $now,
                'updated_at' => $now,
                'creditor' => $pData['grandTotal'],
                'operation_type' => 5,
                'invoice_number' => $invoice->id,
            ]);
            $revenueAccount->update([
                'current_balance' => $revenueAccount->current_balance + $pData['grandTotal'],
                'creditor_current' => $revenueAccount->creditor_current + $pData['grandTotal'],
            ]);
        }

        // د. تكلفة البضاعة المباعة (183) والمخزن (181)
        if ($pData['totalCost'] > 0) {
            $costAccount = FinancialAccount::where('parent_account_number', 183)->where('branchs_id', $branchId)->first();
            if ($costAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $costAccount->id,
                    'recive_amount' => $pData['totalCost'],
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $costAccount->current_balance + $pData['totalCost'],
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'debtor' => $pData['totalCost'],
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
                $costAccount->update([
                    'current_balance' => $costAccount->current_balance + $pData['totalCost'],
                    'debtor_current' => $costAccount->debtor_current + $pData['totalCost'],
                ]);
            }

            $inventoryAccount = FinancialAccount::where('parent_account_number', 181)->where('branchs_id', $branchId)->first();
            if ($inventoryAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $inventoryAccount->id,
                    'recive_amount' => $pData['totalCost'],
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $inventoryAccount->current_balance - $pData['totalCost'],
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'creditor' => $pData['totalCost'],
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
                $inventoryAccount->update([
                    'current_balance' => $inventoryAccount->current_balance - $pData['totalCost'],
                    'creditor_current' => $inventoryAccount->creditor_current + $pData['totalCost'],
                ]);
            }
        }

        // هـ. المبلغ الآجل (لو موجود)
        if ($pData['creaditamount'] != 0 && $customerData) {
            $customerData->increment('Balance', $pData['creaditamount']);

            $customerFinancialAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerFinancialAccount) {
                $customerFinancialAccount->update([
                    'current_balance' => $customerFinancialAccount->current_balance + $pData['creaditamount'],
                    'debtor_current' => $customerFinancialAccount->debtor_current + $pData['creaditamount'],
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerFinancialAccount->id,
                    'recive_amount' => $pData['creaditamount'],
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethod,
                    'note' => $noteText,
                    'currentblance' => $customerFinancialAccount->current_balance,
                    'Pay_Method_Name' => ucfirst($payMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'debtor' => $pData['creaditamount'],
                    'operation_type' => 5,
                    'invoice_number' => $invoice->id,
                ]);
            }
        }
    }
}
