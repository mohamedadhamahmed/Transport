<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.name') }} *</label>
        <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.name_en') }}</label>
        <input type="text" name="name_en" value="{{ old('name_en', $supplier->name_en ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.phone') }} *</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.company_name') }}</label>
        <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.tax_number') }}</label>
        <input type="text" name="tax_no" value="{{ old('tax_no', $supplier->tax_no ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.crn') }}</label>
        <input type="text" name="crn" value="{{ old('crn', $supplier->crn ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.credit_limit') }}</label>
        <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', $supplier->credit_limit ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.notes') }}</label>
        <input type="text" name="notes" value="{{ old('notes', $supplier->notes ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('suppliers.address') }}</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.city') }}</label>
        <input type="text" name="city" value="{{ old('city', $supplier->city ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.district') }}</label>
        <input type="text" name="sub_city" value="{{ old('sub_city', $supplier->sub_city ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.street_name') }}</label>
        <input type="text" name="street_name" value="{{ old('street_name', $supplier->street_name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.building_number') }}</label>
        <input type="text" name="building_number" value="{{ old('building_number', $supplier->building_number ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.plot_identification') }}</label>
        <input type="text" name="plot_identification" value="{{ old('plot_identification', $supplier->plot_identification ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('suppliers.postal_code') }}</label>
        <input type="text" name="postcode" value="{{ old('postcode', $supplier->postcode ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
        {{ __('suppliers.save') }}
    </button>
    <a href="{{ route('suppliers.index') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        {{ __('suppliers.cancel') }}
    </a>
</div>
