<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <polyline points="16 11 18 13 22 9"></polyline>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.sales.employee_profits') }}</h2>
                        <p class="text-white/60 text-xs mt-0.5">{{ __('reports.sales.employee_profits_desc') }}</p>
                    </div>
                </div>
                <a href="{{ route('reports.sales.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.sales.title') }}
                </a>
            </div>

            {{-- كروت الملخص العلوية --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.total_sales') }}</p>
                    <p class="text-lg sm:text-xl font-bold text-[#0F1B4C] mt-1">{{ number_format($totalSales, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.total_cost') }}</p>
                    <p class="text-lg sm:text-xl font-bold text-amber-600 mt-1">{{ number_format($totalCost, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.total_profit') }}</p>
                    <p class="text-lg sm:text-xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                        {{ number_format($totalProfit, 2) }}
                    </p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.top_performing_employee') }}</p>
                    <p class="text-sm sm:text-base font-bold text-[#1456E8] mt-1 truncate" title="{{ optional($topEmployee)->user_name }}">
                        {{ optional($topEmployee)->user_name ?? '-' }}
                    </p>
                </div>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.sales.employee_profits') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasDateRange' => true,
                    'hasEntitySelect' => true,
                    'entityParam' => 'user_id',
                    'entityId' => $userId,
                    'entityLabel' => __('reports.employee'),
                    'entityAllLabel' => __('reports.all_employees'),
                    'entityOptions' => $users->pluck('name', 'id'),
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.employee') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.invoices_count') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.quantity') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.total_sales') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.total_cost') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_profit') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.profit_margin') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.avg_profit_per_invoice') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 font-medium text-[#0F1B4C] flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center text-xs font-bold">
                                            {{ mb_substr($row->user_name, 0, 1) }}
                                        </span>
                                        {{ $row->user_name }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ (int) $row->invoices_count }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $row->total_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-semibold text-[#0F1B4C]">{{ number_format((float) $row->total_sales, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-500">{{ number_format((float) $row->total_cost, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold {{ $row->net_profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format((float) $row->net_profit, 2) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-medium {{ $row->profit_margin >= 0 ? 'text-[#1456E8]' : 'text-rose-600' }}">
                                        {{ number_format((float) $row->profit_margin, 1) }}%
                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-700 font-medium">
                                        {{ number_format((float) $row->avg_profit_per_invoice, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_sales_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C] bg-gray-50/50">
                                    <td class="px-4 py-3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ $totalInvoices }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalSales, 2) }}</td>
                                    <td class="px-4 py-3 text-end text-amber-700">{{ number_format($totalCost, 2) }}</td>
                                    <td class="px-4 py-3 text-end {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ number_format($totalProfit, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end {{ $avgMargin >= 0 ? 'text-[#1456E8]' : 'text-rose-700' }}">
                                        {{ number_format($avgMargin, 1) }}%
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        {{ $totalInvoices > 0 ? number_format($totalProfit / $totalInvoices, 2) : '0.00' }}
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
