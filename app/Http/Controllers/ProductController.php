<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
public function index(Request $request, Branch $branch)
{
    $query = Product::where('branch_id', $branch->id);

    if ($request->filled('product_group')) {
        $query->where('product_group_id', $request->product_group);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        });
    }

    $products = $query->with('productGroup')->orderBy('name')->paginate(20)->withQueryString();
    if ($request->boolean('partial')) {
        return view('products._table', compact('products'));
    }

    $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

    return view('products.index', compact('products', 'branch', 'productGroups'));
}

    public function create()
    {
        $this->authorize('products.create');

        $branches = Branch::orderBy('name')->get();
        $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

        return view('products.create', compact('branches', 'productGroups'));
    }

    public function store(Request $request)
    {
        $this->authorize('products.create');

        $validated = $this->validated($request);
        $validated['created_by'] = Auth::id();

        $product = Product::create($validated);

        return redirect()->route('products.index', $product->branch_id)
            ->with('success', __('products.created_successfully'));
    }

    public function edit(Product $product)
    {
        $this->authorize('products.edit');

        $branches = Branch::orderBy('name')->get();
    $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

        return view('products.edit', compact('product', 'branches', 'productGroups'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('products.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'product_group_id' => ['required', 'exists:productgroup,id'],
            'status' => ['required'], // <-- إضافة قاعدة التحقق لحالة المنتج
        ]);

        // الحفاظ على الفرع الحالي للمنتج
        $validated['branch_id'] = $product->branch_id;

        $product->update($validated);

        return redirect()->route('products.index', $product->branch_id)
            ->with('success', __('products.updated_success'));
    
            }

    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $branchId = $product->branch_id;
        $product->delete();

        return redirect()->route('products.index', $branchId)
            ->with('success', __('products.deleted_successfully'));
    }

    private function validated(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}