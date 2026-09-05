<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReturn;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * كنترولر مرتجع المبيعات.
 *
 * المنطق المحاسبي هنا هو عكس تمامًا لـ InvoiceController@store الحالي عندك:
 * - بدل ما نقلل المخزون بنزوده تاني.
 * - بدل ما نزود حساب الكاش/البنك (5 / 4) بنقلله (بنرجع فلوس للعميل).
 * - بدل ما نزود ضريبة القيمة المضافة المستحقة (102) بنقللها.
 * - بدل ما نسجل في حساب الإيراد (112) بنسجل في حساب "مرتجعات المبيعات" (184)
 *   عشانميتلخبطش مع الإيراد الأصلي.
 * - بدل ما نقلل تكلفة البضاعة المباعة (183) ونزود المخزون المحاسبي (181)
 *   بنعمل العكس.
 * - لو جزء من الفاتورة كان آجل (credit)، الجزء ده بيترد أوتوماتيك كتخفيض في
 *   رصيد العميل (Balance) من غير ما يحتاج اختيار طريقة استرداد - أما الباقي
 *   (اللي كان كاش/بنك/كارت) فالموظف بيختار طريقة استرداده دلوقتي.
 *
 * ملحوظة: نفس الكود الحالي عندك بيسيب current_balance من غير تحديث فعلي في
 * حسابات الكاش/البنك/الإيراد/التكلفة/المخزون (بيسجل حركة في CreditTransaction
 * بس من غير ما يعمل ->update() على الحساب نفسه) - وهنا خليت نفس السلوك بالظبط
 * في القيود اللي بتقابلها (الكاش/البنك) عشان نفضل متوافقين مع باقي النظام،
 * أما حسابات الضريبة والعميل الآجل والمرتجعات والتكلفة/المخزون فكانت بتتحدث
 * فعليًا هناك، فحدّثتها هنا بالمثل.
 */
class InvoiceReturnController extends Controller
{
    /**
     * صفحة إنشاء مرتجع مبيعات جديد.
     */
    public function create()
    {
        $this->authorize('invoices.returns');

        return view('invoices.returns.create');
    }

    /**
     * صفحة عرض المرتجعات السابقة - كل صفوف invoice_returns اللي ليها نفس
     * reference_value بتتجمع في صف واحد (لأنها في الأصل عملية إرجاع واحدة
     * ممكن تشمل أكتر من صنف)، مع دعم بحث برقم الفاتورة الأصلية أو اسم العميل.
     */
    public function index(Request $request)
    {
        $this->authorize('invoices.returns');

        $q = trim((string) $request->query('q', ''));

        $groups = InvoiceReturn::query()
            ->select('reference_value')
            ->selectRaw('MIN(invoice_id) as invoice_id')
            ->selectRaw('MIN(branch_id) as branch_id')
            ->selectRaw('MIN(created_at) as created_at')
            ->selectRaw('SUM(unit_price * quantity) as subtotal')
            ->selectRaw('SUM(discount_amount + invoice_discount_amount) as total_discount')
            ->selectRaw('SUM(tax_amount) as tax_total')
            ->selectRaw('COUNT(*) as items_count')
            ->when($q !== '', function ($query) use ($q) {
                $query->whereIn('invoice_id', function ($sub) use ($q) {
                    $sub->select('id')
                        ->from('invoices')
                        ->where('invoice_number', 'like', "%{$q}%")
                        ->orWhereIn('customer_id', function ($c) use ($q) {
                            $c->select('id')
                                ->from('customers')
                                ->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->groupBy('reference_value')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // نحمل بيانات الفواتير الأصلية + العملاء دفعة واحدة عشان نتجنب N+1
        $invoiceIds = $groups->getCollection()->pluck('invoice_id')->unique()->filter()->values();
        $invoices = Invoice::with('customer:id,name')
            ->whereIn('id', $invoiceIds)
            ->get(['id', 'invoice_number', 'customer_id', 'branch_id'])
            ->keyBy('id');

        $branchIds = $groups->getCollection()->pluck('branch_id')->unique()->filter()->values();
        $branches = Branch::whereIn('id', $branchIds)->get(['id', 'name'])->keyBy('id');

        $groups->getCollection()->transform(function ($group) use ($invoices, $branches) {
            $invoice = $invoices->get($group->invoice_id);

            $group->invoice_number = optional($invoice)->invoice_number;
            $group->customer_name = optional(optional($invoice)->customer)->name ?? '-';
            $group->branch_name = optional($branches->get($group->branch_id))->name ?? '-';

            $group->net_total = round(
                (float) $group->subtotal - (float) $group->total_discount + (float) $group->tax_total,
                2
            );

            return $group;
        });

        return view('invoices.returns.index', [
            'returns' => $groups,
            'q' => $q,
        ]);
    }

    /**
     * بحث AJAX عن فاتورة برقمها أو باسم العميل - بيرجع بس الفواتير المعتمدة
     * (is_finalized) واللي لسه فيها أصناف قابلة للإرجاع (remaining_quantity > 0).
     */
    public function searchInvoice(Request $request)
    {
        $this->authorize('invoices.returns');

        $q = trim((string) $request->query('q', ''));

        $invoices = Invoice::query()
            ->where('is_finalized', 1)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('invoice_number', 'like', "%{$q}%")
                        ->orWhereHas('customer', function ($c) use ($q) {
                            $c->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->whereHas('items', function ($items) {
                $items->where('remaining_quantity', '>', 0);
            })
            ->with('customer:id,name')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'invoice_number', 'customer_id', 'payment_method', 'subtotal', 'tax_amount', 'invoice_level_discount', 'created_at']);

        return response()->json($invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => optional($invoice->customer)->name ?? '-',
                'payment_method' => $invoice->payment_method,
                'grand_total' => round($invoice->subtotal + $invoice->tax_amount - $invoice->invoice_level_discount, 2),
                'created_at' => optional($invoice->created_at)->format('Y-m-d H:i'),
            ];
        }));
    }

    /**
     * بيرجع أصناف فاتورة معينة القابلة للإرجاع (remaining_quantity > 0)
     * + بيانات الفاتورة اللازمة لحساب نسبة الآجل والخصم.
     */
    public function invoiceItems(Invoice $invoice)
    {
        $this->authorize('invoices.returns');

        $items = InvoiceItem::where('invoice_id', $invoice->id)
            ->where('remaining_quantity', '>', 0)
            ->get();

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'customer_name' => optional($invoice->customer)->name,
                'branch_id' => $invoice->branch_id,
                'payment_method' => $invoice->payment_method,
                'subtotal' => (float) $invoice->subtotal,
                'tax_amount' => (float) $invoice->tax_amount,
                'invoice_level_discount' => (float) $invoice->invoice_level_discount,
                'cash_amount' => (float) $invoice->cash_amount,
                'bank_amount' => (float) $invoice->bank_amount,
                'credit_amount' => (float) $invoice->credit_amount,
            ],
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product_name_snapshot,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => (float) $item->quantity,
                    'returned_quantity' => (float) $item->returned_quantity,
                    'remaining_quantity' => (float) $item->remaining_quantity,
                    'discount_amount' => (float) $item->discount_amount,
                    'tax_rate' => (float) $item->tax_rate,
                ];
            }),
        ]);
    }

    /**
     * حفظ مرتجع المبيعات.
     */
    public function store(Request $request)
    {
        $this->authorize('invoices.returns');

        // نفس الباترن المستخدم في InvoiceController@store: الأصناف بتوصل
        // كـ JSON string جوه حقل مخفي items_json (زي ما الـ blade بيبعتها)،
        // فبنفكها هنا لمصفوفة عادية قبل الـ validation.
        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'invoice_id' => ['required', 'exists:invoices,id'],
            'refund_method' => ['nullable', 'in:cash,bank_transfer,card'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_item_id' => ['required', 'exists:invoice_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ])->validate();

        $referenceValue = DB::transaction(function () use ($validated) {
            $invoice = Invoice::findOrFail($validated['invoice_id']);

            // مرجع واحد لكل أصناف المرتجع ده عشان تقدر تجمعهم مع بعض بعدين
            $referenceValue = 'RET-' . $invoice->id . '-' . now()->timestamp;

            $invoiceGrossTotal = (float) $invoice->subtotal + (float) $invoice->tax_amount; // قبل خصم الفاتورة
            $invoiceGrandTotal = $invoiceGrossTotal - (float) $invoice->invoice_level_discount; // المدفوع فعليًا

            $netTotalAmount = 0.0;   // صافي المبلغ اللي هيترد للعميل (بعد نصيبه من خصم الفاتورة)
            $totalTax = 0.0;         // إجمالي الضريبة المرتجعة
            $totalWithoutTax = 0.0;  // إجمالي قيمة المرتجع قبل الضريبة (بعد خصم الصنف، قبل خصم الفاتورة)
            $totalCostValue = 0.0;   // تكلفة البضاعة المرتجعة (لقيد 183 / 181)

            $returnRows = [];

            // === تمريرة أولى: نتأكد من صحة كل صنف ونحسب قيمه ===
            foreach ($validated['items'] as $row) {
                $invoiceItem = InvoiceItem::where('invoice_id', $invoice->id)
                    ->where('id', $row['invoice_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $returnQty = (float) $row['quantity'];

                if ($returnQty > (float) $invoiceItem->remaining_quantity + 0.0001) {
                    abort(422, __('invoices.return_quantity_exceeds_remaining'));
                }

                $itemQty = (float) $invoiceItem->quantity;
                $taxRate = (float) $invoiceItem->tax_rate;

                // متوسط صافي سعر الوحدة بعد خصم الصنف نفسه (مش خصم الفاتورة)
                $unitNet = $itemQty > 0
                    ? (((float) $invoiceItem->unit_price * $itemQty) - (float) $invoiceItem->discount_amount) / $itemQty
                    : 0;

                $lineNet = $unitNet * $returnQty;
                $lineTax = $lineNet * $taxRate;
                $lineGross = $lineNet + $lineTax;
                $lineDiscount = $itemQty > 0
                    ? ((float) $invoiceItem->discount_amount / $itemQty) * $returnQty
                    : 0;

                // نصيب الصنف ده من خصم الفاتورة الإجمالي، بالتناسب مع نصيبه من إجمالي الفاتورة
                $lineInvoiceDiscountShare = $invoiceGrossTotal > 0
                    ? (float) $invoice->invoice_level_discount * ($lineGross / $invoiceGrossTotal)
                    : 0;

                $lineNetRefund = $lineGross - $lineInvoiceDiscountShare;

                $product = Product::find($invoiceItem->product_id);

                $returnRows[] = [
                    'invoiceItem' => $invoiceItem,
                    'product' => $product,
                    'returnQty' => $returnQty,
                    'unit_price' => $invoiceItem->unit_price,
                    'tax_rate' => $taxRate,
                    'lineTax' => round($lineTax, 2),
                    'lineDiscount' => round($lineDiscount, 2),
                    'lineInvoiceDiscountShare' => round($lineInvoiceDiscountShare, 2),
                    'lineNetRefund' => round($lineNetRefund, 2),
                ];

                $netTotalAmount += $lineNetRefund;
                $totalTax += $lineTax;
                $totalWithoutTax += $lineNet;

                if ($product) {
                    $totalCostValue += ($product->purchase_price ?? 0) * $returnQty;
                }
            }

            $netTotalAmount = round($netTotalAmount, 2);
            $totalTax = round($totalTax, 2);
            $totalWithoutTax = round($totalWithoutTax, 2);
            $totalCostValue = round($totalCostValue, 2);

            // نسبة الجزء "الآجل" من الفاتورة الأصلية - ده بيترد أوتوماتيك كتخفيض
            // في رصيد العميل، مش فلوس فعلية بترجعله.
            $creditRatio = $invoiceGrandTotal > 0 ? ((float) $invoice->credit_amount / $invoiceGrandTotal) : 0;
            $creditRefundAmount = round($netTotalAmount * $creditRatio, 2);
            $cashRefundAmount = round($netTotalAmount - $creditRefundAmount, 2);

            $refundMethod = $validated['refund_method'] ?? null;
            if ($cashRefundAmount > 0.009 && !$refundMethod) {
                abort(422, __('invoices.refund_method_required'));
            }

            // === تمريرة تانية: نحفظ فعليًا صفوف المرتجع + نحدث الفاتورة الأصلية والمخزون ===
            foreach ($returnRows as $row) {
                $lineCashPortion = $netTotalAmount > 0
                    ? round($row['lineNetRefund'] * ($cashRefundAmount / $netTotalAmount), 2)
                    : 0;
                $cardRefundAmount = ($refundMethod === 'card') ? $lineCashPortion : 0;

                InvoiceReturn::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $row['invoiceItem']->product_id,
                    'branch_id' => $invoice->branch_id,
                    'reference_value' => $referenceValue,
                    'unit_price' => $row['unit_price'],
                    'quantity' => $row['returnQty'],
                    'tax_amount' => $row['lineTax'],
                    'tax_rate' => $row['tax_rate'],
                    'discount_amount' => $row['lineDiscount'],
                    'invoice_discount_amount' => $row['lineInvoiceDiscountShare'],
                    'card_refund_amount' => $cardRefundAmount,
                    'is_sent_to_zatca' => 0,
                    'created_by' => Auth::id(),
                ]);

                $row['invoiceItem']->increment('returned_quantity', $row['returnQty']);
                $row['invoiceItem']->decrement('remaining_quantity', $row['returnQty']);
                $row['invoiceItem']->increment('returned_discount_amount', $row['lineDiscount']);

                if ($row['product']) {
                    $row['product']->increment('stock_quantity', $row['returnQty']);
                    $row['product']->decrement('total_sold', $row['returnQty']);
                }
            }

            $now = Carbon::now('Asia/Riyadh');
            $noteText = 'مرتجع فاتورة مبيعات رقم :' . $invoice->id;
            // نفس الباترن الموجود في invoices.store() الحالي عندك (branchs_id مع
            // fallback لـ branch_id بتاع الفاتورة لو العمود مش موجود على المستخدم)
            $branchId = Auth::user()->branchs_id ?? $invoice->branch_id;
            $customer = Customer::find($invoice->customer_id);

            // 1) عكس قيد الدفع (كاش/بنك/كارت) بقد الجزء اللي هيترد فعليًا فلوس
            if ($cashRefundAmount > 0 && $refundMethod) {
                // نفس أرقام الحسابات المستخدمة في invoices.store(): 5 = كاش، 4 = بنك/شبكة
                $accountNumber = $refundMethod === 'cash' ? 5 : 4;
                $financialAccount = FinancialAccount::where('parent_account_number', $accountNumber)
                    ->where('branchs_id', $branchId)
                    ->first();

                if ($financialAccount) {
                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $financialAccount->id,
                        'recive_amount' => $cashRefundAmount,
                        'branchs_id' => $branchId,
                        'pay_method' => $refundMethod,
                        'note' => $noteText,
                        'currentblance' => $financialAccount->current_balance - $cashRefundAmount,
                        'Pay_Method_Name' => ucfirst($refundMethod),
                        'created_at' => $now,
                        'updated_at' => $now,
                        'creditor' => $cashRefundAmount,
                        'operation_type' => 2, // 1 = بيع (زي الأصلي) / 2 = مرتجع
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }
            }

            // 2) تقليل رصيد العميل الآجل تلقائي (لو جزء من الفاتورة كان آجل)
            if ($creditRefundAmount > 0 && $customer) {
                $customer->decrement('Balance', $creditRefundAmount);

                $customerFinancialAccount = FinancialAccount::where('orginal_type', 1)
                    ->where('orginal_id', $invoice->customer_id)
                    ->first();

                if ($customerFinancialAccount) {
                    $customerFinancialAccount->update([
                        'current_balance' => $customerFinancialAccount->current_balance - $creditRefundAmount,
                        'creditor_current' => $customerFinancialAccount->creditor_current + $creditRefundAmount,
                    ]);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $customerFinancialAccount->id,
                        'recive_amount' => $creditRefundAmount,
                        'branchs_id' => $branchId,
                        'pay_method' => 'credit',
                        'note' => $noteText,
                        'currentblance' => $customerFinancialAccount->current_balance,
                        'Pay_Method_Name' => 'Credit',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'creditor' => $creditRefundAmount,
                        'operation_type' => 2,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }
            }

            // 3) عكس ضريبة القيمة المضافة (102) - تقليل الضريبة المستحقة على المتجر
            if ($totalTax > 0) {
                $vatAccount = FinancialAccount::where('parent_account_number', 102)
                    ->where('branchs_id', $branchId)
                    ->first();

                if ($vatAccount) {
                    $vatAccount->update([
                        'current_balance' => $vatAccount->current_balance - $totalTax,
                        'debtor_current' => $vatAccount->debtor_current + $totalTax,
                    ]);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $vatAccount->id,
                        'recive_amount' => $totalTax,
                        'branchs_id' => $branchId,
                        'pay_method' => $refundMethod ?? 'credit',
                        'note' => $noteText,
                        'currentblance' => $vatAccount->current_balance,
                        'Pay_Method_Name' => $refundMethod ? ucfirst($refundMethod) : 'Credit',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'debtor' => $totalTax,
                        'vat' => 1,
                        'name' => $customer->name ?? '',
                        'tax' => $customer->tax_no ?? '',
                        'operation_type' => 2,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }
            }

            // 4) قيد حساب "مرتجعات المبيعات" (184) - منفصل عن حساب الإيراد (112)
            if ($totalWithoutTax > 0) {
                $returnsAccount = FinancialAccount::where('parent_account_number', 184)
                    ->where('branchs_id', $branchId)
                    ->first();

                if ($returnsAccount) {
                    $returnsAccount->update([
                        'current_balance' => $returnsAccount->current_balance - $totalWithoutTax,
                        'debtor_current' => $returnsAccount->debtor_current + $totalWithoutTax,
                    ]);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $returnsAccount->id,
                        'recive_amount' => $totalWithoutTax,
                        'branchs_id' => $branchId,
                        'pay_method' => $refundMethod ?? 'credit',
                        'note' => $noteText,
                        'currentblance' => $returnsAccount->current_balance,
                        'Pay_Method_Name' => $refundMethod ? ucfirst($refundMethod) : 'Credit',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'debtor' => $totalWithoutTax,
                        'operation_type' => 2,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }
            }

            // 5) عكس تكلفة البضاعة المباعة (183) والمخزون المحاسبي (181)
            if ($totalCostValue > 0) {
                $costAccount = FinancialAccount::where('parent_account_number', 183)
                    ->where('branchs_id', $branchId)
                    ->first();

                if ($costAccount) {
                    $costAccount->update([
                        'current_balance' => $costAccount->current_balance - $totalCostValue,
                        'creditor_current' => $costAccount->creditor_current + $totalCostValue,
                    ]);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $costAccount->id,
                        'recive_amount' => $totalCostValue,
                        'branchs_id' => $branchId,
                        'pay_method' => $refundMethod ?? 'credit',
                        'note' => $noteText,
                        'currentblance' => $costAccount->current_balance,
                        'Pay_Method_Name' => $refundMethod ? ucfirst($refundMethod) : 'Credit',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'creditor' => $totalCostValue,
                        'operation_type' => 2,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }

                $inventoryAccount = FinancialAccount::where('parent_account_number', 181)
                    ->where('branchs_id', $branchId)
                    ->first();

                if ($inventoryAccount) {
                    $inventoryAccount->update([
                        'current_balance' => $inventoryAccount->current_balance + $totalCostValue,
                        'debtor_current' => $inventoryAccount->debtor_current + $totalCostValue,
                    ]);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $inventoryAccount->id,
                        'recive_amount' => $totalCostValue,
                        'branchs_id' => $branchId,
                        'pay_method' => $refundMethod ?? 'credit',
                        'note' => $noteText,
                        'currentblance' => $inventoryAccount->current_balance,
                        'Pay_Method_Name' => $refundMethod ? ucfirst($refundMethod) : 'Credit',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'debtor' => $totalCostValue,
                        'operation_type' => 2,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                }
            }

            return $referenceValue;
        });

        // بعد الحفظ نروح مباشرة لصفحة طباعة إشعار الدائن، عشان الموظف يقدر
        // يطبعه للعميل فورًا - بدل ما يرجع لصفحة إنشاء مرتجع فاضية.
        return redirect()->route('invoices.returns.print', $referenceValue)
            ->with('success', __('invoices.return_created_successfully'));
    }

    /**
     * صفحة طباعة إشعار دائن (Credit Note) لمرتجع مبيعات - بتجمع كل صفوف
     * invoice_returns اللي ليهم نفس reference_value (يعني نفس عملية الإرجاع)
     * وتعرضهم في صفحة واحدة قابلة للطباعة، بنفس شكل صفحة طباعة الفاتورة العادية.
     */
    public function print(string $referenceValue)
    {
        $this->authorize('invoices.returns');

        $returns = InvoiceReturn::with('product')
            ->where('reference_value', $referenceValue)
            ->orderBy('id')
            ->get();

        abort_if($returns->isEmpty(), 404);

        $invoice = Invoice::with(['customer', 'branch'])->findOrFail($returns->first()->invoice_id);

        $subtotal = 0;             // إجمالي قبل أي خصم (سعر الوحدة × الكمية المرتجعة)
        $itemDiscountTotal = 0;    // مجموع خصم الأصناف نفسها
        $invoiceDiscountTotal = 0; // مجموع نصيب الأصناف من خصم الفاتورة الإجمالي
        $taxTotal = 0;
        $cardRefundTotal = 0;

        foreach ($returns as $row) {
            $subtotal += $row->unit_price * $row->quantity;
            $itemDiscountTotal += $row->discount_amount;
            $invoiceDiscountTotal += $row->invoice_discount_amount;
            $taxTotal += $row->tax_amount;
            $cardRefundTotal += $row->card_refund_amount;
        }

        $totalDiscount = round($itemDiscountTotal + $invoiceDiscountTotal, 2);
        $netBeforeTax = round($subtotal - $totalDiscount, 2);
        $taxTotal = round($taxTotal, 2);
        $netTotal = round($netBeforeTax + $taxTotal, 2);
        $taxRate = (float) ($returns->first()->tax_rate ?: ($invoice->tax_rate ?? 0.15));

        // ملحوظة مهمة: جدول invoice_returns مفيهوش عمود صريح لطريقة الاسترداد
        // (كاش / بنك / كارت) - بس فيه card_refund_amount. اللي تحته ده استنتاج
        // تقريبي بس (كارت لو كان فيه مبلغ اتسجل بالكارت، وإلا نقدي/تحويل).
        // لو عايز الدقة الكاملة، ضيف عمود refund_method في الجدول واحفظه في
        // store() بدل الاستنتاج ده، وابدّل السطر ده بـ $returns->first()->refund_method.
        $refundMethodLabel = $cardRefundTotal > 0.009
            ? __('invoices.card')
            : (($netTotal - $cardRefundTotal) > 0.009 ? __('invoices.cash') . ' / ' . __('invoices.bank_transfer') : __('invoices.credit'));

        $qrCodeData = $this->buildZatcaQrData(
            (string) (defined('sallerQrCode') ? sallerQrCode : ($invoice->branch->name ?? '')),
            (string) (defined('TaxQrCode') ? TaxQrCode : ''),
            optional($returns->first()->created_at)->format('Y-m-d\TH:i:s') ?? now()->format('Y-m-d\TH:i:s'),
            $netTotal,
            $taxTotal
        );

        return view('invoices.returns.print', [
            'returns' => $returns,
            'invoice' => $invoice,
            'referenceValue' => $referenceValue,
            'subtotal' => $subtotal,
            'totalDiscount' => $totalDiscount,
            'netBeforeTax' => $netBeforeTax,
            'taxTotal' => $taxTotal,
            'netTotal' => $netTotal,
            'taxRate' => $taxRate,
            'refundMethodLabel' => $refundMethodLabel,
            'qrCodeData' => $qrCodeData,
        ]);
    }

    /**
     * بناء بيانات QR الزاتكا بنفس معيار TLV (Tag-Length-Value) المستخدم في
     * صفحة طباعة الفاتورة العادية عندك - بس بدالة أنضف بدل ConvertToHEX/pack.
     * chr($tag) و chr(strlen($value)) بيدوا بالظبط نفس نتيجة
     * pack("H*", sprintf("%02X", $value)) طالما القيمة أقل من 256 (وده دايمًا
     * صحيح هنا لأننا بنحسب طول نص، مش رقم كبير).
     */
    private function buildZatcaQrData(string $sellerName, string $vatNumber, string $timestamp, float $total, float $vatAmount): string
    {
        $tlv = function (int $tag, string $value) {
            return chr($tag) . chr(strlen($value)) . $value;
        };

        $totalStr = number_format($total, 2, '.', '');
        $vatStr = number_format($vatAmount, 2, '.', '');

        $data = $tlv(1, $sellerName)
            . $tlv(2, $vatNumber)
            . $tlv(3, $timestamp)
            . $tlv(4, $totalStr)
            . $tlv(5, $vatStr);

        return base64_encode($data);
    }
}
