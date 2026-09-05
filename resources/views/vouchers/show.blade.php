<x-app-layout>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                    @can('vouchers.edit')
                    <a href="{{ route('vouchers.edit', $voucher) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('vouchers.edit_voucher') }}
                    </a>
                    @endcan
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
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.branch') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->branch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.created_by') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->creator?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.items_count') }}</div>
                        <div class="font-medium text-gray-800">{{ $voucher->lines->count() }}</div>
                    </div>
                    @if ($voucher->description)
                        <div class="md:col-span-2">
                            <div class="text-xs text-gray-400 mb-1">{{ __('vouchers.description') }}</div>
                            <div class="text-gray-700">{{ $voucher->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">
                                    {{ $voucher->isReceipt() ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment') }}
                                </th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.line_cost_center') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.line_description') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.net_amount') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.tax_amount') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($voucher->lines as $line)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $line->counterpartAccount?->name ?? '#' . $line->counterpart_account_id }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $line->costCenter?->cost_center_ar ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $line->description ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $line->is_taxable ? number_format($line->net_amount, 2) : '-' }}</td>
                                    <td class="px-3 py-2 text-[#F5811E]">{{ $line->is_taxable ? number_format($line->tax_amount, 2) : '-' }}</td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]">{{ number_format($line->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="5" class="px-3 py-2.5 text-gray-600">{{ __('vouchers.grand_total') }}</td>
                                <td class="px-3 py-2.5 text-[#0F1B4C]">{{ number_format($voucher->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
