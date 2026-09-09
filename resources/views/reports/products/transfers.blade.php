<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3v4a1 1 0 0 1-1 1H4M7 21v-4a1 1 0 0 1 1-1h12M7 7 3 3M20 21l-4-4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.products.transfers') }}</h2>
                </div>
                <a href="{{ route('reports.products.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.products.title') }}
                </a>
            </div>

            @if($productFilter ?? null)
                <div class="dc-print-hide rounded-xl border border-[#1456E8]/20 bg-[#1456E8]/5 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sm text-[#0F1B4C]">
                        {{ __('reports.filtered_by_product', ['name' => $productFilter->name, 'code' => $productFilter->code ?? '-']) }}
                    </span>
                    <a href="{{ route('reports.products.transfers', request()->except('product_id')) }}"
                       class="text-xs font-medium text-[#1456E8] hover:underline">
                        {{ __('reports.clear_product_filter') }}
                    </a>
                </div>
            @endif

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.products.transfers') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', ['hasDateRange' => true, 'searchPlaceholder' => __('reports.transfer_number')])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.transfer_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.from_branch') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.to_branch') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.transfer_status') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transfers as $transfer)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $transfer->transfer_number }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ optional($transfer->fromBranch)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ optional($transfer->toBranch)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($transfer->transfer_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($transfer->status === \App\Models\StockTransfer::STATUS_RECEIVED)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">{{ __('reports.transfer_status_received') }}</span>
                                        @elseif ($transfer->status === \App\Models\StockTransfer::STATUS_SENT)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full dc-note-blue">{{ __('reports.transfer_status_sent') }}</span>
                                        @else
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">{{ __('reports.transfer_status_draft') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format((float) $transfer->items_sum_quantity, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_transfers_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($transfers->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="5">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
