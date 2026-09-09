<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function index()
    {
        $boms = BillOfMaterial::with('product')->orderByDesc('id')->paginate(20);

        return view('manufacturing.bom.index', compact('boms'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('manufacturing.bom.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateBom($request);

        $bom = BillOfMaterial::create([
            'branch_id' => $request->user()->branch_id ?? null,
            'code' => BillOfMaterial::generateCode(),
            'name' => $validated['name'],
            'product_id' => $validated['product_id'],
            'production_quantity' => $validated['production_quantity'],
            'status' => 'active',
            'is_default' => $request->boolean('is_default'),
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $bom->syncItems($validated['items']);

        return redirect()->route('manufacturing.bom.index')->with('success', __('manufacturing.bom_added'));
    }

    public function edit(BillOfMaterial $bom)
    {
        $bom->load('items');
        $products = Product::orderBy('name')->get();

        return view('manufacturing.bom.edit', compact('bom', 'products'));
    }

    public function update(Request $request, BillOfMaterial $bom)
    {
        $validated = $this->validateBom($request);

        $bom->update([
            'name' => $validated['name'],
            'product_id' => $validated['product_id'],
            'production_quantity' => $validated['production_quantity'],
            'status' => $request->input('status', 'active'),
            'is_default' => $request->boolean('is_default'),
            'notes' => $validated['notes'] ?? null,
        ]);

        $bom->syncItems($validated['items']);

        return redirect()->route('manufacturing.bom.index')->with('success', __('manufacturing.bom_updated'));
    }

    public function destroy(BillOfMaterial $bom)
    {
        $bom->delete();

        return back()->with('success', __('manufacturing.bom_deleted'));
    }

    private function validateBom(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'production_quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);
    }
}
