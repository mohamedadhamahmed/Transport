<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'branch', 'creator'])->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('issue_date', $request->date('date'));
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        return view('purchase-orders.create', compact('suppliers', 'branches', 'costCenters'));
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'branch', 'creator', 'items.product', 'convertedPurchase', 'convertedByUser', 'costCenter']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function store(Request $request)
    {
        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'invoice_level_discount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
        ])->validate();

        $purchaseOrder = DB::transaction(function () use ($validated) {
            return $this->createPurchaseOrder($validated);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', __('purchase_orders.created_successfully'));
    }

    /**
     * إنشاء أمر الشراء وبنوده فقط - بدون أي تأثير على المخزون أو أي
     * قيد محاسبي إطلاقًا (بعكس finalizePurchase في PurchaseController).
     * أمر الشراء مجرد طلب/نية شراء، مش التزام فعلي.
     */
    protected function createPurchaseOrder(array $validated): PurchaseOrder
    {
        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($validated['items'] as $item) {
            $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $subtotal += $lineSubtotal;
            $taxTotal += $lineSubtotal * $item['tax_rate'];
            $discountTotal += $item['discount_amount'] ?? 0;
        }

        $invoiceLevelDiscount = min($validated['invoice_level_discount'] ?? 0, $subtotal + $taxTotal);
        $shippingFee = $validated['shipping_fee'] ?? 0;
        $grandTotal = $subtotal + $taxTotal - $invoiceLevelDiscount + $shippingFee;
        $totalQuantity = array_sum(array_column($validated['items'], 'quantity'));

        $purchaseOrder = PurchaseOrder::create([
            'supplier_id' => $validated['supplier_id'],
            'branch_id' => $validated['branch_id'],
            'created_by' => Auth::id(),
            'warehouse_name' => $validated['warehouse_name'] ?? null,
            'cost_center_id' => $validated['cost_center_id'] ?? null,
            'shipping_fee' => $shippingFee,
            'subtotal' => $subtotal,
            'discount_amount' => $discountTotal,
            'invoice_level_discount' => $invoiceLevelDiscount,
            'tax_amount' => $taxTotal,
            'grand_total' => $grandTotal,
            'total_quantity' => $totalQuantity,
            'note' => $validated['note'] ?? null,
            'issue_date' => $validated['issue_date'] ?? now()->toDateString(),
            'status' => 'pending',
        ]);
        $purchaseOrder->update(['order_number' => (string) $purchaseOrder->id]);

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $lineTax = $lineSubtotal * $item['tax_rate'];

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $item['product_id'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'discount_amount' => $item['discount_amount'] ?? 0,
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $lineTax,
                'product_name_snapshot' => $product?->name,
                'product_code_snapshot' => $product?->code,
                'created_by' => Auth::id(),
            ]);
        }

        return $purchaseOrder;
    }

    /**
     * إلغاء أمر الشراء - بس لو لسه "قيد الانتظار" (لم يتحول لفاتورة
     * ولم يتم إلغاؤه من قبل).
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isPending()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', __('purchase_orders.already_processed'));
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', __('purchase_orders.cancelled_successfully'));
    }

    /**
     * طباعة/تحميل PDF احترافي لأمر الشراء - بنفس تقنية RTL/الخط
     * المستخدمة في التسعيرات والفواتير بالظبط (dir="rtl" صريح + خط
     * DejaVu Sans !important) عشان العربي يطلع سليم في dompdf.
     */
    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        $pdf = $this->buildPurchaseOrderPdf($purchaseOrder);

        return $pdf->download('purchase-order-' . $purchaseOrder->id . '.pdf');
    }

    public function pdf(PurchaseOrder $purchaseOrder)
    {
        return $this->downloadPdf($purchaseOrder);
    }

    protected function buildPurchaseOrderPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'branch', 'items.product']);

        return Pdf::loadView('purchase-orders.pdf', compact('purchaseOrder'))->setPaper('a4');
    }
}
