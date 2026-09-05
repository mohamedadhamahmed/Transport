<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg">{{ __('products.new_product') }}</h2>
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
            <form method="POST" action="{{ route('products.store') }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
    @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.branch') }} *</label>
                        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">{{ __('products.choose_branch_title') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.name_en') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.code') }}</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.location') }}</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.unit') }}</label>
                        <input type="text" name="unit" value="{{ old('unit') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.purchase_price') }}</label>
                        <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.sale_price') }}</label>
                        <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.wholesale_price') }}</label>
                        <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ old('wholesale_price') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.initial_stock_quantity') }}</label>
                        <input type="number" step="0.01" min="0" name="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.low_stock_alert_quantity') }}</label>
                        <input type="number" step="1" min="0" name="low_stock_alert_quantity" value="{{ old('low_stock_alert_quantity') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.tax_value') }}</label>
                        <input type="number" step="0.01" min="0" name="tax_value" value="{{ old('tax_value') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.group') }} *</label>
                        <select name="product_group_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">{{ __('products.choose_group') }}</option>
                            @foreach($productGroups as $group)
                                <option value="{{ $group->id }}" @selected(old('product_group_id') == $group->id)>{{ $group->group_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.status') }} *</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="active" @selected(old('status', 'active') == 'active')>{{ __('products.active') }}</option>
                            <option value="inactive" @selected(old('status') == 'inactive')>{{ __('products.inactive') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('products.notes') }}</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('products.save') }}
                    </button>
                    <a href="{{ route('products.choose_branch') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('products.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
