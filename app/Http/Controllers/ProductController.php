<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('branch')->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(15)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();

        return view('products.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['created_by'] = Auth::id();

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', __('products.created_successfully'));
    }

    public function edit(Product $product)
    {
        $branches = Branch::orderBy('name')->get();

        return view('products.edit', compact('product', 'branches'));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product->id));

        return redirect()->route('products.index')
            ->with('success', __('products.updated_successfully'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
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