<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v1"/>
                            <path d="M18 8h4a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-4"/>
                            <circle cx="8" cy="18" r="2"/>
                            <circle cx="18" cy="18" r="2"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.purchases.purchases_vs_sales') }}</h2>
                        <p class="text-white/60 text-xs mt-0.5">{{ __('reports.purchases.purchases_vs_sales_desc') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('reports.purchases.by-product', request()->query()) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white/10 text-white hover:bg-white/20 transition whitespace-nowrap">
                        {{ __('reports.purchases.by_product') }}
                    </a>
                    <a href="{{ route('reports.purchases.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('reports.purchases.title') }}
                    </a>
                </div>
            </div>

            @if($productFilter ?? null)
                <div class="dc-print-hide rounded-xl border border-[#1456E8]/20 bg-[#1456E8]/5 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sm text-[#0F1B4C]">
                        {{ __('reports.filtered_by_product', ['name' => $productFilter->name, 'code' => $productFilter->code ?? '-']) }}
                    </span>
                    <a href="{{ route('reports.purchases.purchases-vs-sales', request()->except('product_id')) }}"
                       class="text-xs font-medium text-[#1456E8] hover:underline">
                        {{ __('reports.clear_product_filter') }}
                    </a>
                </div>
            @endif

            {{-- كروت KPI العلوية --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 dc-print-hide">
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.purchased_cost') }}</p>
                    <p class="text-lg sm:text-xl font-bold text-[#0F1B4C] mt-1">{{ number_format($totalPurchasedCost, 2) }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ __('reports.purchased_qty') }}: <span class="font-semibold text-gray-700">{{ number_format($totalPurchasedQty, 2) }}</span></p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.sales_revenue') }}</p>
                    <p class="text-lg sm:text-xl font-bold text-[#1456E8] mt-1">{{ number_format($totalSalesRevenue, 2) }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ __('reports.sold_qty') }}: <span class="font-semibold text-gray-700">{{ number_format($totalSoldQty, 2) }}</span></p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.overall_sell_through') }}</p>
                    <p class="text-lg sm:text-xl font-bold {{ $overallSellThrough >= 50 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                        {{ number_format($overallSellThrough, 1) }}%
                    </p>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $overallSellThrough >= 50 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min(100, $overallSellThrough) }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.total_profit') }}</p>
                    <p class="text-lg sm:text-xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                        {{ number_format($totalProfit, 2) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ __('reports.net_profit') }}</p>
                </div>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.purchases.purchases_vs_sales') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', ['hasDateRange' => true, 'searchPlaceholder' => __('reports.product')])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100 bg-gray-50/50">
                                <th class="text-start px-3 py-3">#</th>
                                <th class="text-start px-3 py-3">{{ __('reports.product_code') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.product') }}</th>
                                <th class="text-end px-3 py-3 bg-amber-50/30 text-amber-900">{{ __('reports.purchased_qty') }}</th>
                                <th class="text-end px-3 py-3 bg-amber-50/30 text-amber-900">{{ __('reports.purchased_cost') }}</th>
                                <th class="text-end px-3 py-3 bg-blue-50/30 text-blue-900">{{ __('reports.sold_qty') }}</th>
                                <th class="text-end px-3 py-3 bg-blue-50/30 text-blue-900">{{ __('reports.sales_revenue') }}</th>
                                <th class="text-end px-3 py-3">{{ __('reports.current_stock') }}</th>
                                <th class="text-end px-3 py-3">{{ __('reports.sell_through_percent') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.profit') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($rows as $idx => $row)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-3 py-2.5 text-gray-400 text-xs">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2.5 font-mono text-xs text-gray-500">{{ $row->product_code ?? '-' }}</td>
                                    <td class="px-4 py-2.5 font-medium text-[#0F1B4C]">{{ $row->product_name }}</td>
                                    <td class="px-3 py-2.5 text-end font-semibold text-amber-800 bg-amber-50/20">{{ number_format($row->purchased_qty, 2) }}</td>
                                    <td class="px-3 py-2.5 text-end text-amber-900 bg-amber-50/20">{{ number_format($row->purchased_cost, 2) }}</td>
                                    <td class="px-3 py-2.5 text-end font-semibold text-blue-800 bg-blue-50/20">{{ number_format($row->sold_qty, 2) }}</td>
                                    <td class="px-3 py-2.5 text-end text-blue-900 bg-blue-50/20">{{ number_format($row->sales_revenue, 2) }}</td>
                                    <td class="px-3 py-2.5 text-end font-medium {{ $row->current_stock <= 0 ? 'text-rose-600' : 'text-gray-700' }}">
                                        {{ number_format($row->current_stock, 2) }}
                                    </td>
                                    <td class="px-3 py-2.5 text-end">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->sell_through_percent >= 70 ? 'bg-emerald-50 text-emerald-700' : ($row->sell_through_percent >= 30 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                            {{ number_format($row->sell_through_percent, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-bold {{ $row->profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format($row->profit, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                        {{ __('reports.no_purchases_sales_data_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C] bg-gray-50/70">
                                    <td class="px-3 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-3 py-3 text-end text-amber-900 bg-amber-50/40">{{ number_format($totalPurchasedQty, 2) }}</td>
                                    <td class="px-3 py-3 text-end text-amber-900 bg-amber-50/40">{{ number_format($totalPurchasedCost, 2) }}</td>
                                    <td class="px-3 py-3 text-end text-blue-900 bg-blue-50/40">{{ number_format($totalSoldQty, 2) }}</td>
                                    <td class="px-3 py-3 text-end text-blue-900 bg-blue-50/40">{{ number_format($totalSalesRevenue, 2) }}</td>
                                    <td class="px-3 py-3 text-end">-</td>
                                    <td class="px-3 py-3 text-end">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-200 text-gray-800">
                                            {{ number_format($overallSellThrough, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end {{ $totalProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format($totalProfit, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
