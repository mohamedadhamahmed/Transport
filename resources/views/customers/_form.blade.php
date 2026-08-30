<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.name') }} *</label>
        <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.phone') }} *</label>
        <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.company_name') }}</label>
        <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.tax_number') }}</label>
        <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.crn') }}</label>
        <input type="text" name="commercial_registration_number" value="{{ old('commercial_registration_number', $customer->commercial_registration_number ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.credit_limit') }}</label>
        <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit ?? 10000) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.grace_period_days') }}</label>
        <input type="number" step="1" min="0" name="grace_period_days" value="{{ old('grace_period_days', $customer->grace_period_days ?? 30) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    @if(!($customer->exists ?? false))
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.opening_balance') }}</label>
        <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    @endif
    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.notes') }}</label>
        <input type="text" name="notes" value="{{ old('notes', $customer->notes ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('customers.national_address') }}</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.city') }}</label>
        <input type="text" name="city" value="{{ old('city', $customer->city ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.district') }}</label>
        <input type="text" name="district" value="{{ old('district', $customer->district ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.street_name') }}</label>
        <input type="text" name="street_name" value="{{ old('street_name', $customer->street_name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.building_number') }}</label>
        <input type="text" name="building_number" value="{{ old('building_number', $customer->building_number ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.plot_identification') }}</label>
        <input type="text" name="plot_identification" value="{{ old('plot_identification', $customer->plot_identification ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('customers.postal_code') }}</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
        {{ __('customers.save') }}
    </button>
    <a href="{{ route('customers.index') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        {{ __('customers.cancel') }}
    </a>
</div>
