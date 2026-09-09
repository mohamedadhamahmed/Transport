<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg">{{ __('products.edit') }} - {{ $product->name }}</h2>
            </div>
@if ($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            <form method="POST" action="{{ route('products.update', $product->id) }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
    @csrf
    @method('PUT')
    
    <!-- أضف هذا السطر هنا لتجنب خطأ فقدان الفرع -->
    <input type="hidden" name="branch_id" value="{{ $product->branch_id }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.name') }} *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.name_en') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $product->name_en) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.code') }}</label>
                        <input type="text" name="code" value="{{ old('code', $product->code) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.location') }}</label>
                        <input type="text" name="location" value="{{ old('location', $product->location) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.unit') }}</label>
                        <input type="text" name="unit" value="{{ old('unit', $product->unit) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.purchase_price') }}</label>
                        <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.sale_price') }}</label>
                        <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.wholesale_price') }}</label>
                        <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.low_stock_alert_quantity') }}</label>
                        <input type="number" step="1" min="0" name="low_stock_alert_quantity" value="{{ old('low_stock_alert_quantity', $product->low_stock_alert_quantity) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.tax_value') }}</label>
                        <input type="number" step="0.01" min="0" name="tax_value" value="{{ old('tax_value', $product->tax_value) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.group') }} *</label>
                        <select name="product_group_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">{{ __('products.choose_group') }}</option>
                            @foreach($productGroups as $group)
                                <option value="{{ $group->id }}" @selected(old('product_group_id', $product->product_group_id) == $group->id)>{{ $group->group_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- حقل الحالة المضاف حديثاً -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.status') ?? 'حالة المنتج' }} *</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="1" @selected(old('status', $product->status) == 1)>{{ __('products.active') ?? 'نشط' }}</option>
                            <option value="0" @selected(old('status', $product->status) == 0)>{{ __('products.inactive') ?? 'غير نشط' }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.notes') }}</label>
                        <input type="text" name="notes" value="{{ old('notes', $product->notes) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>

                @php
                    // المنتجات الأساسية اللي المنتج ده بديل ليها بالفعل (لو
                    // موجودة) - بتتحط جاهزة في مكوّن الاختيار Alpine بنفس
                    // الشكل اللي بيرجعه بحث products.search-alternates.
                    $selectedPrimaries = $product->primaryProducts()->get(['products.id', 'products.name', 'products.code'])
                        ->map(fn ($p) => ['id' => $p->id, 'text' => $p->name . ($p->code ? " ({$p->code})" : '')])
                        ->values();
                @endphp
                @include('products._alternates-field', ['selectedPrimaries' => $selectedPrimaries, 'excludeId' => $product->id])

                <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 mt-6 text-sm text-gray-600">
                    {{ __('products.stock_edit_note') }}
                    <span class="font-semibold text-[#0F1B4C]">{{ $product->stock_quantity }}</span> —
                    <a href="{{ route('products.index', $product->branch_id) }}" class="text-[#1456E8] underline">{{ __('products.go_edit_stock_from_list') }}</a>
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('products.save') }}
                    </button>
                    <a href="{{ route('products.index', $product->branch_id) }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('products.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>