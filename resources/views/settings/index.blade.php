<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('settings.general_settings') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('partials.sweet-alert-flash')

            {{-- اختيار الفرع --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <form method="GET" class="flex items-center gap-3">
                    <label class="text-sm text-gray-600">{{ __('settings.branch') }}:</label>
                    <select name="branch_id" onchange="this.form.submit()"
                        class="rounded-lg border-gray-300 text-sm focus:ring-[#1456E8] focus:border-[#1456E8]">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branch->id == $selectedBranchId)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- بيانات الشركة العامة --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <div class="font-semibold text-gray-700 mb-4">{{ __('settings.company_general_info') }}</div>

                <form method="POST" action="{{ route('settings.system.update') }}" enctype="multipart/form-data"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="branchs_id" value="{{ $selectedBranchId }}">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.name_ar') }}</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $systemSetting->name_ar) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.name_en') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $systemSetting->name_en) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.commercial_record') }} (SR)</label>
                        <input type="text" name="SR" value="{{ old('SR', $systemSetting->SR) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.tax_number') }} (Tax)</label>
                        <input type="text" name="Tax" value="{{ old('Tax', $systemSetting->Tax) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.address_ar') }}</label>
                        <input type="text" name="address_ar" value="{{ old('address_ar', $systemSetting->address_ar) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.address_en') }}</label>
                        <input type="text" name="address_en" value="{{ old('address_en', $systemSetting->address_en) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.service_cost') }}</label>
                        <input type="number" step="0.01" name="serviceCost" value="{{ old('serviceCost', $systemSetting->serviceCost) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.delivery_cost') }}</label>
                        <input type="number" step="0.01" name="deliveryCost" value="{{ old('deliveryCost', $systemSetting->deliveryCost) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.bank_name') }}</label>
                        <input type="text" name="bankname" value="{{ old('bankname', $systemSetting->bankname) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.bank_account_number') }}</label>
                        <input type="text" name="bank_acount_number" value="{{ old('bank_acount_number', $systemSetting->bank_acount_number) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.iban') }}</label>
                        <input type="text" name="bank_acount_iban" value="{{ old('bank_acount_iban', $systemSetting->bank_acount_iban) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.company_logo') }}</label>
                        <input type="file" name="logo" class="w-full text-sm">
                        @if ($systemSetting->logo && $systemSetting->logo !== 'empty')
                            <img src="{{ asset('assets/img/brand/' . $systemSetting->logo) }}" class="h-16 mt-2 rounded">
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="rounded-lg py-2 px-6 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            {{ __('settings.save_company_info') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- بيانات الزكاة والفوترة الإلكترونية --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <div class="font-semibold text-gray-700 mb-4">{{ __('settings.zakat_zatca_settings') }}</div>

                <form method="POST" action="{{ route('settings.zakat.update') }}"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="branchs_id" value="{{ $selectedBranchId }}">
                    <input type="hidden" name="company_id" value="{{ old('company_id', $zakatSetting->company_id ?? 1) }}">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.facility_name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $zakatSetting->name) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.mobile') }}</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $zakatSetting->mobile) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.trn') }}</label>
                        <input type="number" name="trn" value="{{ old('trn', $zakatSetting->trn) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.crn') }}</label>
                        <input type="number" name="crn" value="{{ old('crn', $zakatSetting->crn) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.street_name') }}</label>
                        <input type="text" name="street_name" value="{{ old('street_name', $zakatSetting->street_name) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.building_number') }}</label>
                        <input type="number" name="building_number" value="{{ old('building_number', $zakatSetting->building_number) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.plot_identification') }}</label>
                        <input type="number" name="plot_identification" value="{{ old('plot_identification', $zakatSetting->plot_identification) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.postal_number') }}</label>
                        <input type="number" name="postal_number" value="{{ old('postal_number', $zakatSetting->postal_number) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.region') }}</label>
                        <input type="text" name="region" value="{{ old('region', $zakatSetting->region) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.city') }}</label>
                        <input type="text" name="city" value="{{ old('city', $zakatSetting->city) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.business_category') }}</label>
                        <select name="business_category" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            @foreach (['IT', 'Food', 'Film Festivals'] as $cat)
                                <option value="{{ $cat }}" @selected(old('business_category', $zakatSetting->business_category) == $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.invoice_type') }}</label>
                        <select name="invoice_type" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            @foreach (['1100', '0100', '1000'] as $type)
                                <option value="{{ $type }}" @selected(old('invoice_type', $zakatSetting->invoice_type) == $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.email_address') }}</label>
                        <input type="email" name="email_address" value="{{ old('email_address', $zakatSetting->email_address) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="is_production" value="1"
                               @checked(old('is_production', $zakatSetting->is_production))
                               class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                        <label class="text-sm text-gray-600">{{ __('settings.production_mode') }}</label>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="rounded-lg py-2 px-6 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            {{ __('settings.save_zakat_info') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>