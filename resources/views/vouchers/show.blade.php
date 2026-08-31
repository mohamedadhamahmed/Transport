<x-app-layout>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10h18M6 15h4M3 6h18v12H3z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">
                            {{ $voucher->isReceipt() ? __('vouchers.receipt') : __('vouchers.payment') }} #{{ $voucher->voucher_number }}
                        </h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ $voucher->voucher_date->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('vouchers.edit', $voucher) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('vouchers.edit_voucher') }}
                    </a>
                    <a href="{{ route('vouchers.print', $voucher) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('vouchers.print') }}
                    </a>
                    <a href="{{ route('vouchers.index', ['type' => $voucher->type]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('vouchers.back_to_list') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.treasury_account') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->treasuryAccount?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">
                            {{ $voucher->isReceipt() ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment') }}
                        </div>
                        <div class="font-medium text-gray-800">{{ $voucher->counterpartAccount?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.branch') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->branch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.cost_center') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->costCenter?->cost_center_ar ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.created_by') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->creator?->name ?? '-' }}</div>
                    </div>
                    @if ($voucher->description)
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.description') }}</div>
                            <div class="text-gray-700">{{ $voucher->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-lg p-6 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                <div class="text-xs text-white/50 mb-1">{{ __('vouchers.amount') }}</div>
                <div class="font-bold text-2xl">{{ number_format($voucher->amount, 2) }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
