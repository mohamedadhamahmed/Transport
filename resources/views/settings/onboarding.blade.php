<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('settings.onboarding_title') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('partials.sweet-alert-flash')

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

            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <div class="font-semibold text-gray-700 mb-1">{{ __('settings.onboarding_title') }}</div>
                <p class="text-xs text-gray-500 mb-4">{{ __('settings.onboarding_subtitle') }}</p>

                <div class="rounded-lg bg-gray-50 p-4 mb-4 text-sm text-gray-600 space-y-1">
                    <div class="font-medium text-gray-700 mb-1">{{ __('settings.onboarding_branch_info') }}</div>
                    <div>{{ __('settings.facility_name') }}: {{ $zakatSetting->name ?? '—' }}</div>
                    <div>{{ __('settings.common_name') }}: {{ $zakatSetting->common_name ?? '—' }}</div>
                    <div>{{ __('settings.trn') }}: {{ $zakatSetting->trn ?? '—' }}</div>
                </div>

                <div class="rounded-lg bg-blue-50 p-4 mb-4 text-sm text-gray-700 flex items-center justify-between gap-3">
                    <span>{{ __('settings.otp_hint') }}</span>
                    <a href="https://fatoora.zatca.gov.sa/" target="_blank" rel="noopener"
                       class="whitespace-nowrap rounded-lg py-2 px-4 text-white text-xs font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                        {{ __('settings.get_otp_from_fatoora') }}
                    </a>
                </div>

                <form method="POST" action="{{ route('settings.onboarding.store') }}"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    <input type="hidden" name="branchs_id" value="{{ $selectedBranchId }}">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.invoice_type') }}</label>
                        <select name="invoice_type" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            <option value="1100" @selected(old('invoice_type', $zakatSetting->invoice_type) == '1100')>{{ __('settings.invoice_type_both') }}</option>
                            <option value="1000" @selected(old('invoice_type', $zakatSetting->invoice_type) == '1000')>{{ __('settings.invoice_type_standard') }}</option>
                            <option value="0100" @selected(old('invoice_type', $zakatSetting->invoice_type) == '0100')>{{ __('settings.invoice_type_simplified') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.connection_type') }}</label>
                        <select name="is_production" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            <option value="0" @selected(!old('is_production', $zakatSetting->is_production))>{{ __('settings.connection_type_simulation') }}</option>
                            <option value="1" @selected(old('is_production', $zakatSetting->is_production))>{{ __('settings.connection_type_production') }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.otp_label') }}</label>
                        <input type="text" name="otp" value="{{ old('otp') }}" required
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div class="md:col-span-2 flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            {{ __('settings.certificate_status') }}:
                            @if ($zakatSetting->production_certificate)
                                <span class="text-green-600 font-medium">{{ __('settings.certificate_issued') }}</span>
                            @else
                                <span class="text-gray-500">{{ __('settings.certificate_not_issued') }}</span>
                            @endif
                        </div>
                        <button type="submit"
                            class="rounded-lg py-2 px-6 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            {{ __('settings.connect_now') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
