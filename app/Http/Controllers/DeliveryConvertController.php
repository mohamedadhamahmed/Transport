<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customers;          // عدّل اسم الموديل حسب موديل العملاء عندك (لو مختلف عن نظام الفواتير)
use App\Models\Customer;           // موديل العميل في نظام الفواتير الحقيقي (لاحظ الفرق عن Customers أعلاه)
use App\Models\sales_withoud_taxes;
use App\Models\DeliveryInvoiceLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * "اعتماد" - تحويل الكميات المعلّقة (اللي اتسلّمت للعميل ولسه ما
 * اتفوترتش ولا رجعت) إلى فاتورة ضريبية حقيقية في نظام الفواتير الأصلي
 * (Invoice/InvoiceItem)، وهنا فقط تُسجَّل القيود المحاسبية وضريبة القيمة
 * المضافة، عن طريق استدعاء InvoiceController::finalizeInvoice() نفسها.
 *
 * ⚠️ متطلب أساسي: لازم تغيّر توقيع الميثود finalizeInvoice() في
 * InvoiceController الحقيقي من protected إلى public، عشان نقدر نستدعيها
 * من هنا من غير ما نكرر مئات الأسطر بتاعة القيود المحاسبية والضريبة:
 *
 *     public function finalizeInvoice(array $validated): Invoice
 *
 * (بدل: protected function finalizeInvoice(array $validated): Invoice)
 */
class DeliveryConvertController extends Controller
{
    /**
     * الخطوة الأولى: اختيار العميل اللي عايز تعتمد له كميات معلّقة
     */
    public function index(Request $request)
    {
        // نعرض بس العملاء اللي فعلاً عندهم كمية معلّقة (متسلّمة، مش
        // مرتجعة، ومش مفوترة بعد) في أي سند تسليم.
        $customerIdsWithPending = sales_withoud_taxes::where('save', 1)
            ->whereColumn('quantity', '>', DB::raw('quantityreturn + invoiced_quantity'))
            ->join('delivery_to_customer_withoud_tax_invoices', 'sales_withoud_taxes.invoice_id', '=', 'delivery_to_customer_withoud_tax_invoices.id')
            ->pluck('delivery_to_customer_withoud_tax_invoices.customer_id')
            ->unique();

        $customers = Customers::whereIn('id', $customerIdsWithPending)->orderBy('name')->get();

        return view('delivery.convert_index', compact('customers'));
    }

    /**
     * الخطوة الثانية: عرض كل الكميات المعلّقة بتاعة العميل ده (من كل
     * سندات التسليم بتاعته)، جاهزة لتحديد اللي عايز تفوترها دلوقتي.
     */
    public function create($customerId)
    {
        $customer = Customers::findOrFail($customerId);

        $items = sales_withoud_taxes::where('save', 1)
            ->whereColumn('quantity', '>', DB::raw('quantityreturn + invoiced_quantity'))
            ->whereHas('invoice', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId)->where('save', 1);
            })
            ->with(['product', 'invoice'])
            ->get();

        return view('delivery.convert_create', compact('customer', 'items'));
    }

    /**
     * تنفيذ التحويل: إنشاء فاتورة ضريبية حقيقية بالكميات المحددة، وتحديث
     * invoiced_quantity على كل بند تسليم متأثر + تسجيل رابط تتبع.
     */
    public function store(Request $request, $customerId)
    {
        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'payment_method' => ['required', 'in:cash,bank_transfer,card,credit,split'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_item_id' => ['required', 'exists:sales_withoud_taxes,id'],
            'items.*.invoice_qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        $customer = Customer::findOrFail($customerId); // موديل نظام الفواتير الحقيقي
        $branchId = auth()->user()->branch_id ?? null;

        $invoice = DB::transaction(function () use ($validated, $customer, $branchId) {
            $invoiceItems = [];

            foreach ($validated['items'] as $row) {
                $salesItem = sales_withoud_taxes::findOrFail($row['sales_item_id']);
                $available = $salesItem->quantity - $salesItem->quantityreturn - $salesItem->invoiced_quantity;

                if ($row['invoice_qty'] > $available) {
                    throw new \Exception(__('delivery.convert_error_exceed') . " ({$available})");
                }

                $invoiceItems[] = [
                    'product_id' => $salesItem->product_id,
                    'quantity' => $row['invoice_qty'],
                    'unit_price' => (float) $salesItem->Unit_Price,
                    'discount_amount' => 0,
                    'tax_rate' => $row['tax_rate'] ?? 0.15,
                    '_sales_item_id' => $salesItem->id, // مش هتتبعت للفاتورة، مستخدمة بس داخليًا تحت
                ];
            }

            $itemsForInvoice = array_map(function ($i) {
                unset($i['_sales_item_id']);
                return $i;
            }, $invoiceItems);

            $payload = [
                'customer_id' => $customer->id,
                'branch_id' => $branchId,
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $validated['cash_amount'] ?? 0,
                'bank_amount' => $validated['bank_amount'] ?? 0,
                'note' => $validated['note'] ?? __('delivery.converted_from_delivery_note'),
                'purchase_order_number' => null,
                'invoice_level_discount' => 0,
                'is_finalized' => true,
                'items' => $itemsForInvoice,
            ];

            // ⚠️ استدعاء finalizeInvoice() الحقيقية - راجع تعليق أعلى
            // الكلاس بخصوص تغيير توقيعها لـ public في InvoiceController.
            $invoiceController = app(\App\Http\Controllers\InvoiceController::class);
            $invoice = $invoiceController->finalizeInvoice($payload);

            // تحديث invoiced_quantity وربط كل بند بالفاتورة الناتجة (Audit Trail)
            $createdInvoiceItems = $invoice->items()->get();

            foreach ($invoiceItems as $index => $original) {
                $salesItem = sales_withoud_taxes::find($original['_sales_item_id']);
                $salesItem->invoiced_quantity += $original['quantity'];
                $salesItem->save();

                $matchingInvoiceItem = $createdInvoiceItems->get($index);

                DeliveryInvoiceLink::create([
                    'sales_item_id' => $salesItem->id,
                    'invoice_id' => $invoice->id,
                    'invoice_item_id' => $matchingInvoiceItem->id ?? null,
                    'quantity' => $original['quantity'],
                    'unit_price' => $original['unit_price'],
                ]);
            }

            // تحديث حالة كل سند تسليم متأثر لو كل كمياته اتحولت/رجعت بالكامل
            $affectedInvoiceIds = sales_withoud_taxes::whereIn('id', collect($invoiceItems)->pluck('_sales_item_id'))
                ->pluck('invoice_id')
                ->unique();

            foreach ($affectedInvoiceIds as $deliveryInvoiceId) {
                $allItems = sales_withoud_taxes::where('invoice_id', $deliveryInvoiceId)->get();
                $fullyResolved = $allItems->every(fn($i) => ($i->quantityreturn + $i->invoiced_quantity) >= $i->quantity);

                if ($fullyResolved) {
                    \App\Models\delivery_to_customer_withoud_tax_invoices::where('id', $deliveryInvoiceId)
                        ->update(['status' => 3]); // 3 = تم تحويلها بالكامل لفاتورة
                }
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('delivery.convert_success'));
    }
}
