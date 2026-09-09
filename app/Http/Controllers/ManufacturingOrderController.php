<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderStatus;
use App\Models\Product;
use App\Models\ProductionPlan;
use App\Models\Workstation;
use Illuminate\Http\Request;

class ManufacturingOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = ManufacturingOrder::with(['product', 'status'])
            ->when($request->status_id, fn ($q) => $q->where('status_id', $request->status_id))
            ->orderByDesc('id')
            ->paginate(20);

        $statuses = ManufacturingOrderStatus::orderBy('sort_order')->get();

        return view('manufacturing.orders.index', compact('orders', 'statuses'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $boms = BillOfMaterial::where('status', 'active')->get();
        $workstations = Workstation::where('status', 'active')->get();
        $statuses = ManufacturingOrderStatus::orderBy('sort_order')->get();

        return view('manufacturing.orders.create', compact('products', 'boms', 'workstations', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateOrder($request);

        $order = ManufacturingOrder::create([
            ...$validated,
            'branch_id' => $request->user()->branch_id ?? null,
            'code' => ManufacturingOrder::generateCode(),
            'status_id' => $validated['status_id'] ?? ManufacturingOrderStatus::where('is_default', true)->value('id'),
            'created_by' => $request->user()->id,
        ]);

        $order->buildItemsFromBom();

        return redirect()->route('manufacturing.orders.edit', $order)->with('success', __('manufacturing.order_added'));
    }

    /**
     * بتنشئ أمر تصنيع مباشرة من خطة إنتاج (نفس المنتج/القائمة/الكمية).
     * مستخدمة من ProductionPlanController::convertToOrder.
     */
    public function createFromPlan(ProductionPlan $plan): ManufacturingOrder
    {
        $order = ManufacturingOrder::create([
            'branch_id' => $plan->branch_id,
            'code' => ManufacturingOrder::generateCode(),
            'name' => $plan->name,
            'product_id' => $plan->product_id,
            'bill_of_material_id' => $plan->bill_of_material_id,
            'production_plan_id' => $plan->id,
            'customer_id' => $plan->customer_id,
            'quantity' => $plan->quantity,
            'date_start' => $plan->date_start,
            'date_end' => $plan->date_end,
            'status_id' => ManufacturingOrderStatus::where('is_default', true)->value('id'),
            'created_by' => $plan->created_by,
        ]);

        $order->buildItemsFromBom();

        return $order;
    }

    public function edit(ManufacturingOrder $order)
    {
        $order->load(['items.product', 'indirectCosts']);
        $statuses = ManufacturingOrderStatus::orderBy('sort_order')->get();
        $workstations = Workstation::where('status', 'active')->get();

        return view('manufacturing.orders.edit', compact('order', 'statuses', 'workstations'));
    }

    public function update(Request $request, ManufacturingOrder $order)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'workstation_id' => 'nullable|exists:workstations,id',
            'status_id' => 'nullable|exists:manufacturing_order_statuses,id',
        ]);

        $order->update($validated);

        return back()->with('success', __('manufacturing.order_updated'));
    }

    public function destroy(ManufacturingOrder $order)
    {
        if ($order->completed_at) {
            return back()->with('error', __('manufacturing.cannot_delete_completed_order'));
        }

        $order->delete();

        return redirect()->route('manufacturing.orders.index')->with('success', __('manufacturing.order_deleted'));
    }

    /**
     * إضافة تكلفة غير مباشرة للأمر، وإعادة حساب الإجمالي فورًا.
     */
    public function addIndirectCost(Request $request, ManufacturingOrder $order)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $order->indirectCosts()->create($validated);
        $order->recalculateTotals();

        return back()->with('success', __('manufacturing.indirect_cost_added'));
    }

    public function removeIndirectCost(ManufacturingOrder $order, $indirectCostId)
    {
        $order->indirectCosts()->whereKey($indirectCostId)->delete();
        $order->recalculateTotals();

        return back()->with('success', __('manufacturing.indirect_cost_deleted'));
    }

    /**
     * تعديل الكمية المستهلكة فعليًا من مادة خام معينة قبل إتمام الأمر
     * (لو مختلفة عن الكمية المطلوبة نظريًا حسب الـ BOM).
     */
    public function updateItem(Request $request, ManufacturingOrder $order, $itemId)
    {
        $validated = $request->validate([
            'consumed_quantity' => 'required|numeric|min:0',
        ]);

        $item = $order->items()->whereKey($itemId)->firstOrFail();
        $item->update(['consumed_quantity' => $validated['consumed_quantity']]);

        return back()->with('success', __('manufacturing.item_updated'));
    }

    /**
     * إتمام أمر التصنيع: سحب المواد الخام من المخزون وإضافة الكمية
     * المنتجة لرصيد المنتج التام. راجع ManufacturingOrder::complete()
     * للمنطق الكامل والتحقق من كفاية المخزون.
     */
    public function complete(ManufacturingOrder $order)
    {
        $order->complete();

        return back()->with('success', __('manufacturing.order_completed'));
    }

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'production_plan_id' => 'nullable|exists:production_plans,id',
            'workstation_id' => 'nullable|exists:workstations,id',
            'customer_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.001',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'status_id' => 'nullable|exists:manufacturing_order_statuses,id',
        ]);
    }
}
