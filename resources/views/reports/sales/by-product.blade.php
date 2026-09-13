<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8 12 3 3 8l9 5 9-5ZM3 8v8l9 5m0-13v13m9-13v8l-9 5"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.sales.by_product') }}</h2>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @can('reports_sales.top_products')
                        <a href="{{ route('reports.sales.top-products', request()->query()) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-200 hover:bg-amber-500/30 transition whitespace-nowrap">
                            {{ __('reports.sales.top_products') }} &larr;
                        </a>
                    @endcan
                    <a href="{{ route('reports.sales.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('reports.sales.title') }}
                    </a>
                </div>
            </div>

            @if($productFilter ?? null)
                <div class="dc-print-hide rounded-xl border border-[#1456E8]/20 bg-[#1456E8]/5 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sm text-[#0F1B4C]">
                        {{ __('reports.filtered_by_product', ['name' => $productFilter->name, 'code' => $productFilter->code ?? '-']) }}
                    </span>
                    <a href="{{ route('reports.sales.by-product', request()->except('product_id')) }}"
                       class="text-xs font-medium text-[#1456E8] hover:underline">
                        {{ __('reports.clear_product_filter') }}
                    </a>
                </div>
            @endif

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.sales.by_product') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', ['hasDateRange' => true, 'searchPlaceholder' => __('reports.product')])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.product') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.quantity') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $row->product_name }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $row->total_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format((float) $row->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_products_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalRevenue, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
