<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\ManufacturingOrderStatus;
use App\Models\Product;
use App\Models\ProductionPlan;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    public function index()
    {
        $plans = ProductionPlan::with(['product', 'status'])->orderByDesc('id')->paginate(20);

        return view('manufacturing.production_plans.index', compact('plans'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $boms = BillOfMaterial::where('status', 'active')->get();
        $statuses = ManufacturingOrderStatus::orderBy('sort_order')->get();

        return view('manufacturing.production_plans.create', compact('products', 'boms', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        ProductionPlan::create([
            ...$validated,
            'branch_id' => $request->user()->branch_id ?? null,
            'code' => ProductionPlan::generateCode(),
            'source' => 'manual',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('manufacturing.production-plans.index')->with('success', __('manufacturing.plan_added'));
    }

    public function edit(ProductionPlan $productionPlan)
    {
        $products = Product::orderBy('name')->get();
        $boms = BillOfMaterial::where('status', 'active')->get();
        $statuses = ManufacturingOrderStatus::orderBy('sort_order')->get();

        return view('manufacturing.production_plans.edit', [
            'plan' => $productionPlan,
            'products' => $products,
            'boms' => $boms,
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, ProductionPlan $productionPlan)
    {
        $validated = $this->validatePlan($request);

        $productionPlan->update($validated);

        return redirect()->route('manufacturing.production-plans.index')->with('success', __('manufacturing.plan_updated'));
    }

    public function destroy(ProductionPlan $productionPlan)
    {
        $productionPlan->delete();

        return back()->with('success', __('manufacturing.plan_deleted'));
    }

    /**
     * بتحول خطة الإنتاج لأمر تصنيع فعلي (بتاخد نفس المنتج، القائمة،
     * والكمية) - زي زرار "تحويل" اللي بيظهر عادة جنب خطط الإنتاج.
     */
    public function convertToOrder(ProductionPlan $productionPlan)
    {
        $order = app(ManufacturingOrderController::class)->createFromPlan($productionPlan);

        return redirect()
            ->route('manufacturing.orders.edit', $order)
            ->with('success', __('manufacturing.plan_converted'));
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'customer_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.001',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'status_id' => 'nullable|exists:manufacturing_order_statuses,id',
        ]);
    }
}
