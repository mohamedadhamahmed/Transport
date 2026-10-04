<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * كنترولر مرتجع المشتريات.
 *
 * المنطق المحاسبي هنا هو عكس تمامًا لـ PurchaseController@finalizePurchase
 * (بالظبط زي ما InvoiceReturnController هو عكس InvoiceController@store):
 * - بدل ما نزود المخزون بنقلله.
 * - بدل ما نزود المخزون المحاسبي (181) بنقلله.
 * - بدل ما نزود ضريبة القيمة المضافة المدخلة (102) بنقللها.
 * - بدل ما نزود رصيد المورد (آجل) بنقلله، أو بدل ما نخصم من حساب الدفع
 *   الفوري بنزود فيه (استرداد).
 *
 * المرتجع هنا لازم يكون مرتبط بفاتورة شراء موجودة بالفعل (purchase_id) -
 * مفيش مرتجع حر من غير فاتورة أصلية، ومينفعش ترجع كمية أكتر من
 * "الكمية المتاحة" على كل سطر (quantity - returned_quantity).
 */
class PurchaseReturnController extends Controller
{
    /**
     * *** رقم operation_type لمرتجع المشتريات في جدول credittransactions ***
     * التعليق التوثيقي في ميجريشن
     * add_invoice_number_and_operation_type_to_credittransactions_table
     * بيقول: 1=مبيعات، 2=مشتريات، 3=سند قبض... لكن الكود الفعلي في
     * PurchaseController@finalizePurchase بيسجل فاتورة الشراء نفسها بـ
     * operation_type=3 (مش 2!)، ومرتجع المبيعات في
     * InvoiceReturnController@store بيسجل بـ operation_type=2 (مش 1!).
     * يعني الترقيم المستخدم فعليًا في الكود هو: 1=فاتورة بيع، 2=مرتجع
     * بيع، 3=فاتورة شراء - فكملت نفس التسلسل الفعلي ده بـ 4=مرتجع شراء.
     * *** تأكد إن الرقم ده مش متاستخدم لحاجة تانية عندك قبل ما تشغل
     * النظام على بيانات حقيقية ***.
     */
    const OPERATION_TYPE = 4;

    /**
     * صفحة عرض مرتجعات المشتريات السابقة.
     */
    public function index(Request $request)
    {
        $this->authorize('purchases.returns');

        $query = PurchaseReturn::with(['purchase', 'supplier', 'branch', 'creator'])->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        $returns = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.returns.index', compact('returns', 'suppliers'));
    }

    /**
     * صفحة إنشاء مرتجع مشتريات جديد - البحث عن فاتورة شراء ثم اختيار
     * الأصناف والكميات المرتجعة منها.
     */
    public function create()
    {
        $this->authorize('purchases.returns');

        return view('purchases.returns.create');
    }

    /**
     * بحث AJAX عن فاتورة شراء برقمها (رقم فاتورتنا أو رقم فاتورة المورد)
     * أو باسم المورد - بيرجع بس الفواتير اللي لسه فيها أصناف قابلة
     * للإرجاع (على الأقل سطر واحد quantity > returned_quantity).
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $purchases = Purchase::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('purchase_number', 'like', "%{$q}%")
                        ->orWhere('supplier_invoice_number', 'like', "%{$q}%")
                        ->orWhereHas('supplier', function ($s) use ($q) {
                            $s->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->whereHas('items', function ($items) {
                $items->whereColumn('quantity', '>', 'returned_quantity');
            })
            ->with('supplier:id,name')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'purchase_number', 'supplier_id', 'payment_account_id', 'grand_total', 'created_at']);

        return response()->json($purchases->map(function (Purchase $purchase) {
            return [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_name' => optional($purchase->supplier)->name ?? '-',
                'is_credit' => $purchase->isCredit(),
                'grand_total' => (float) $purchase->grand_total,
                'created_at' => optional($purchase->created_at)->format('Y-m-d H:i'),
            ];
        }));
    }

    /**
     * بيرجع أصناف فاتورة شراء معينة القابلة للإرجاع + بيانات الفاتورة
     * اللازمة لعرضها وتحديد طريقة الاسترداد الافتراضية.
     */
    public function items(Purchase $purchase)
    {
        $items = PurchaseItem::where('purchase_id', $purchase->id)
            ->whereColumn('quantity', '>', 'returned_quantity')
            ->get();

        return response()->json([
            'purchase' => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier_id' => $purchase->supplier_id,
                'supplier_name' => optional($purchase->supplier)->name,
                'branch_id' => $purchase->branch_id,
                'is_credit' => $purchase->isCredit(),
                'payment_account_id' => $purchase->payment_account_id,
                'payment_account_name' => optional($purchase->paymentAccount)->name,
            ],
            'items' => $items->map(function (PurchaseItem $item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product_name_snapshot,
                    'code' => $item->product_code_snapshot,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => (float) $item->quantity,
                    'returned_quantity' => (float) $item->returned_quantity,
                    'remaining_quantity' => (float) ($item->quantity - $item->returned_quantity),
                    'discount_amount' => (float) $item->discount_amount,
                    'tax_rate' => (float) $item->tax_rate,
                ];
            }),
        ]);
    }

    /**
     * حسابات الدفع الفورية (نقدي/بنك/شبكة) الخاصة بفرع معيّن - نفس
     * الاستعلام المستخدم في PurchaseController، بتتنادى بالـ ajax عشان
     * تختار منها "حساب الاسترداد" لو الفاتورة الأصلية كانت دفع فوري.
     */
    public function refundAccountsForBranch(Request $request, $branchId)
    {
        return response()->json(
            FinancialAccount::whereIn('parent_account_number', [4, 5])
                ->where('branchs_id', $branchId)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $this->authorize('purchases.returns');

        $purchaseReturn->load(['purchase', 'supplier', 'branch', 'creator', 'costCenter', 'items.product', 'refundAccount']);

        return view('purchases.returns.show', compact('purchaseReturn'));
    }

    /**
     * تحميل مرتجع المشتريات PDF - نفس فكرة PurchaseController::downloadPdf()
     * بالظبط، بس على قالب purchases.returns.pdf.
     */
    public function downloadPdf(PurchaseReturn $purchaseReturn)
    {
        $this->authorize('purchases.returns');

        $purchaseReturn->load(['purchase', 'supplier', 'branch', 'creator', 'costCenter', 'items.product', 'refundAccount']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchases.returns.pdf', compact('purchaseReturn'))->setPaper('a4');

        return $pdf->download('purchase-return-' . ($purchaseReturn->return_number ?? $purchaseReturn->id) . '.pdf');
    }

    public function store(Request $request)
    {
        $this->authorize('purchases.returns');

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'purchase_id' => ['required', 'exists:purchases,id'],
            // نفس ملحوظة PurchaseController@store بالظبط: اسم الجدول في
            // قاعدة بياناتك الفعلية "financialaccount" (موديل
            // FinancialAccount)، لكن قاعدة التحقق هنا بترجع لجدول
            // financialaccount زي ما هو مكتوب حرفيًا في
            // PurchaseController الأصلي - سايباها زيها بالظبط عشان تفضل
            // متوافقة مع باقي الشاشة، فلو فشل التحقق ده عندك اتأكدي من
            // نفس النقطة في شاشة فاتورة المشتريات كمان.
            'refund_account_id' => ['nullable', 'exists:financialaccount,id'],
            'reason' => ['nullable', 'string'],
            'return_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ])->validate();

        $purchaseReturn = DB::transaction(function () use ($validated) {
            return $this->finalizePurchaseReturn($validated);
        });

        return redirect()->route('purchases.returns.show', $purchaseReturn)
            ->with('success', __('purchase_returns.created_successfully'));
    }

    /**
     * القلب المحاسبي لمرتجع المشتريات - شوفي التعليق التفصيلي أعلى
     * الكلاس. أهم فرضيتين لازم تتأكد منهم:
     *
     * 1) متوسط التكلفة (average_cost) وسعر الشراء (purchase_price) بتاع
     *    المنتج مش بيترجعوا لقيمتهم قبل الفاتورة الأصلية - بس بننقص
     *    stock_quantity بكمية المرتجع. إرجاع average_cost لقيمته "الصح"
     *    رياضيًا مش مضمون لو حصلت مشتريات تانية للمنتج ده بعد الفاتورة
     *    الأصلية (نفس القرار المتبع في مرتجع المبيعات -
     *    InvoiceReturnController مبيعملش undo لأي average cost برضه).
     *
     * 2) رسوم الشحن (shipping_fee) على الفاتورة الأصلية مش بترتد هنا
     *    خالص حتى لو رجعتِ كل أصناف الفاتورة - افتراض إن الشحن خدمة
     *    اتنفذت فعلاً ومش قابلة للاسترجاع. لو عايز رد نسبي منها مع كل
     *    مرتجع، قوللي أظبطها.
     */
    protected function finalizePurchaseReturn(array $validated): PurchaseReturn
    {
        $purchase = Purchase::lockForUpdate()->findOrFail($validated['purchase_id']);

        $purchaseSubtotal = (float) $purchase->subtotal; // بعد خصم الأصناف، قبل خصم الفاتورة والضريبة
        $purchaseInvoiceDiscount = (float) $purchase->invoice_level_discount;

        $returnSubtotal = 0.0;      // قيمة الأصناف المرتجعة بعد خصم الصنف، قبل خصم الفاتورة والضريبة
        $returnItemDiscount = 0.0;  // نصيب الأصناف المرتجعة من خصم الأصناف نفسها
        $returnTax = 0.0;
        $returnQtyTotal = 0.0;
        $returnRows = [];

        // === تمريرة أولى: نتأكد من صحة كل صنف ونحسب قيمه ===
        foreach ($validated['items'] as $row) {
            $purchaseItem = PurchaseItem::where('purchase_id', $purchase->id)
                ->where('id', $row['purchase_item_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $returnQty = (float) $row['quantity'];
            $remaining = (float) $purchaseItem->quantity - (float) $purchaseItem->returned_quantity;

            if ($returnQty > $remaining + 0.0001) {
                abort(422, __('purchase_returns.return_quantity_exceeds_remaining'));
            }

            $itemQty = (float) $purchaseItem->quantity;
            $taxRate = (float) $purchaseItem->tax_rate;

            // متوسط قيمة الوحدة بعد خصم السطر الأصلي نفسه (مش خصم الفاتورة)
            $unitNet = $itemQty > 0
                ? (((float) $purchaseItem->unit_price * $itemQty) - (float) $purchaseItem->discount_amount) / $itemQty
                : 0;

            $lineSubtotal = $unitNet * $returnQty;
            $lineTax = $lineSubtotal * $taxRate;
            $lineDiscount = $itemQty > 0
                ? ((float) $purchaseItem->discount_amount / $itemQty) * $returnQty
                : 0;

            $returnRows[] = [
                'purchaseItem' => $purchaseItem,
                'product' => Product::find($purchaseItem->product_id),
                'returnQty' => $returnQty,
                'unit_price' => $purchaseItem->unit_price,
                'tax_rate' => $taxRate,
                'lineTax' => round($lineTax, 2),
                'lineDiscount' => round($lineDiscount, 2),
            ];

            $returnSubtotal += $lineSubtotal;
            $returnItemDiscount += $lineDiscount;
            $returnTax += $lineTax;
            $returnQtyTotal += $returnQty;
        }

        $returnSubtotal = round($returnSubtotal, 2);
        $returnItemDiscount = round($returnItemDiscount, 2);
        $returnTax = round($returnTax, 2);

        // نصيب المرتجع من خصم الفاتورة الإجمالي - بالتناسب مع نصيبه من
        // subtotal الفاتورة الأصلية، بنفس منطق توزيع الخصم في
        // finalizePurchase (كان بيتخصم بالكامل من جهة المخزون/subtotal،
        // مش من الضريبة).
        $returnInvoiceDiscountShare = $purchaseSubtotal > 0
            ? round($purchaseInvoiceDiscount * ($returnSubtotal / $purchaseSubtotal), 2)
            : 0;

        // قيمة البضاعة بدون ضريبة (لعكس قيد المخزون 181)
        $returnGoodsWithoutTax = round($returnSubtotal - $returnInvoiceDiscountShare, 2);
        // إجمالي المبلغ اللي هيترد فعليًا (للمورد أو لحساب الاسترداد) - شامل الضريبة
        $returnGrandTotal = round($returnGoodsWithoutTax + $returnTax, 2);

        $branchId = $purchase->branch_id;
        $refundAccountId = $validated['refund_account_id'] ?? null;

        // لو الفاتورة الأصلية كانت آجل، مفيش فلوس دُفعت أصلاً عشان
        // تترد فعليًا - فبنتجاهل أي حساب استرداد اتبعت بالغلط ونعتبره
        // آجل. لو كانت دفع فوري وملحددتيش حساب استرداد، بنستخدم افتراضيًا
        // نفس حساب الدفع الأصلي.
        if ($purchase->isCredit()) {
            $refundAccountId = null;
        } elseif (is_null($refundAccountId)) {
            $refundAccountId = $purchase->payment_account_id;
        }

        // 1. إنشاء رأس مرتجع المشتريات
        $purchaseReturn = PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'supplier_id' => $purchase->supplier_id,
            'branch_id' => $branchId,
            'created_by' => Auth::id(),
            'refund_account_id' => $refundAccountId,
            'cost_center_id' => $purchase->cost_center_id,
            'subtotal' => $returnSubtotal,
            'discount_amount' => $returnItemDiscount,
            'invoice_level_discount' => $returnInvoiceDiscountShare,
            'tax_amount' => $returnTax,
            'grand_total' => $returnGrandTotal,
            'total_quantity' => $returnQtyTotal,
            'reason' => $validated['reason'] ?? null,
            'return_date' => $validated['return_date'] ?? now()->toDateString(),
        ]);
        $purchaseReturn->update(['return_number' => (string) $purchaseReturn->id]);

        // 2. بنود المرتجع + تحديث الكمية المرتجعة على السطر الأصلي + إنقاص المخزون
        foreach ($returnRows as $row) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $purchaseReturn->id,
                'purchase_item_id' => $row['purchaseItem']->id,
                'product_id' => $row['purchaseItem']->product_id,
                'unit_price' => $row['unit_price'],
                'quantity' => $row['returnQty'],
                'discount_amount' => $row['lineDiscount'],
                'tax_rate' => $row['tax_rate'],
                'tax_amount' => $row['lineTax'],
                'product_name_snapshot' => $row['purchaseItem']->product_name_snapshot,
                'product_code_snapshot' => $row['purchaseItem']->product_code_snapshot,
                'created_by' => Auth::id(),
            ]);

            $row['purchaseItem']->increment('returned_quantity', $row['returnQty']);

            if ($row['product']) {
                $row['product']->decrement('stock_quantity', $row['returnQty']);
            }
        }

        $now = Carbon::now('Asia/Riyadh');
        $returnNote = 'مرتجع فاتورة مشتريات رقم :' . $purchase->id;
        $payMethodName = $refundAccountId ? 'Immediate' : 'Credit';

        // 3. عكس حساب المورد (لو كانت الفاتورة آجل) أو حساب الاسترداد الفوري
        if (is_null($refundAccountId)) {
            $supplier = Supplier::find($purchase->supplier_id);
            if ($supplier) {
                $supplier->decrement('balance', $returnGrandTotal);
            }

            $supplierAccount = FinancialAccount::where('orginal_type', 2)
                ->where('orginal_id', $purchase->supplier_id)
                ->first();
            if ($supplierAccount) {
                $supplierAccount->update([
                    'current_balance' => $supplierAccount->current_balance - $returnGrandTotal,
                    'debtor_current' => $supplierAccount->debtor_current + $returnGrandTotal,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $supplierAccount->id,
                    'recive_amount' => $returnGrandTotal,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $returnNote,
                    'currentblance' => $supplierAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'debtor' => $returnGrandTotal,
                    'creditor' => 0,
                    'invoice_number' => $purchaseReturn->return_number,
                    'operation_type' => self::OPERATION_TYPE,
                ]);
            }
        } else {
            $refundAccount = FinancialAccount::lockForUpdate()->find($refundAccountId);
            if ($refundAccount) {
                $refundAccount->update([
                    'current_balance' => $refundAccount->current_balance + $returnGrandTotal,
                    'creditor_current' => $refundAccount->creditor_current + $returnGrandTotal,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $refundAccount->id,
                    'recive_amount' => $returnGrandTotal,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $returnNote,
                    'currentblance' => $refundAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'creditor' => $returnGrandTotal,
                    'debtor' => 0,
                    'invoice_number' => $purchaseReturn->return_number,
                    'operation_type' => self::OPERATION_TYPE,
                ]);
            }
        }

        // 4. عكس قيد المخزون (181) - بينقص بقيمة البضاعة المرتجعة بدون الضريبة.
        if ($returnGoodsWithoutTax > 0) {
            $inventoryAccount = FinancialAccount::where('parent_account_number', 181)
                ->where('branchs_id', $branchId)
                ->first();
            if ($inventoryAccount) {
                $inventoryAccount->update([
                    'current_balance' => $inventoryAccount->current_balance - $returnGoodsWithoutTax,
                    'creditor_current' => $inventoryAccount->creditor_current + $returnGoodsWithoutTax,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $inventoryAccount->id,
                    'recive_amount' => $returnGoodsWithoutTax,
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $returnNote,
                    'currentblance' => $inventoryAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'creditor' => $returnGoodsWithoutTax,
                    'debtor' => 0,
                    'invoice_number' => $purchaseReturn->return_number,
                    'operation_type' => self::OPERATION_TYPE,
                ]);
            }
        }

        // 5. عكس ضريبة القيمة المضافة المدخلة (102) - بتنقص رصيدها المدين
        //    (بترجع جزء من الضريبة اللي كانت هترجع ليكي كضريبة مشتريات).
        if ($returnTax > 0) {
            $vatAccount = FinancialAccount::where('parent_account_number', 102)
                ->where('branchs_id', $branchId)
                ->first();
            if ($vatAccount) {
                $supplierData = Supplier::find($purchase->supplier_id);
                $vatAccount->update([
                    'current_balance' => $vatAccount->current_balance - $returnTax,
                    'creditor_current' => $vatAccount->creditor_current + $returnTax,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $vatAccount->id,
                    'recive_amount' => $returnTax,
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $returnNote,
                    'currentblance' => $vatAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'creditor' => $returnTax,
                    'debtor' => 0,
                    'vat' => 1,
                    'name' => $supplierData->name ?? '',
                    'tax' => $supplierData->tax_no ?? '',
                    'invoice_number' => $purchaseReturn->return_number,
                    'operation_type' => self::OPERATION_TYPE,
                ]);
            }
        }

        // إنشاء أو تحديث القيد المحاسبي الآلي الموحد لمرتجع المشتريات وربط حركاته
        $entryDescription = 'قيد مرتجع مشتريات رقم ' . ($purchaseReturn->return_number ?: $purchaseReturn->id);
        JournalEntryService::syncForSource(
            $purchaseReturn,
            $entryDescription,
            $purchaseReturn->created_at ?: now(),
            $branchId,
            Auth::id()
        );

        return $purchaseReturn;
    }
}
