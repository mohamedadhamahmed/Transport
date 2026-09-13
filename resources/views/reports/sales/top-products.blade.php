<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.sales.top_products') }}</h2>
                        <p class="text-white/60 text-xs mt-0.5">{{ __('reports.sales.top_products_desc') }}</p>
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
                    <p class="text-lg sm:text-xl font-bold text-[#0F1B4C] mt-1">{{ number_format($totalRevenue, 2) }}</p>
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
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.quantity') }}</p>
                    <p class="text-lg sm:text-xl font-bold text-[#1456E8] mt-1">{{ number_format($totalQuantity, 2) }}</p>
                </div>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.sales.top_products') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                <form method="GET" action="{{ url()->current() }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.branch') }}</label>
                        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="">{{ __('reports.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) $branchId === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-36">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>

                    <div class="w-full sm:w-36">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>

                    <div class="w-full sm:w-44">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.sort_by') }}</label>
                        <select name="sort_by" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="qty" @selected($sortBy === 'qty')>{{ __('reports.sort_by_qty') }}</option>
                            <option value="revenue" @selected($sortBy === 'revenue')>{{ __('reports.sort_by_revenue') }}</option>
                            <option value="profit" @selected($sortBy === 'profit')>{{ __('reports.sort_by_profit') }}</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-28">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.limit_count') }}</label>
                        <select name="limit" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="10" @selected($limit === '10' || $limit === 10)>{{ __('reports.top_10') }}</option>
                            <option value="25" @selected($limit === '25' || $limit === 25)>{{ __('reports.top_25') }}</option>
                            <option value="50" @selected($limit === '50' || $limit === 50)>{{ __('reports.top_50') }}</option>
                            <option value="100" @selected($limit === '100' || $limit === 100)>{{ __('reports.top_100') }}</option>
                            <option value="all" @selected($limit === 'all')>{{ __('reports.all_records') }}</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.search_placeholder') }}</label>
                        <input type="text" name="q" value="{{ $q }}" placeholder="{{ __('reports.product') }}..." class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#1456E8] text-white rounded-lg text-sm font-medium hover:bg-[#0F3FBE] transition">
                            {{ __('reports.apply_filters') }}
                        </button>
                        <button type="button" onclick="window.print()" class="px-3 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition" title="{{ __('reports.print') }}">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        </button>
                        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="px-3 py-2 border border-emerald-200 text-emerald-700 bg-emerald-50 rounded-lg text-sm hover:bg-emerald-100 transition inline-flex items-center gap-1.5" title="{{ __('reports.export_excel') }}">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
                            <span class="hidden sm:inline text-xs">{{ __('reports.export_excel') }}</span>
                        </a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-center w-12 px-3 py-3">{{ __('reports.rank') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.product_code') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.product') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.invoices_count') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.quantity') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.total_sales') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.total_cost') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.profit') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.profit_margin') }}</th>
                                <th class="text-start px-4 py-3 w-36">{{ __('reports.sales_contribution') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $index => $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-3 py-2.5 text-center font-bold">
                                        @if ($index === 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-xs shadow-sm" title="المركز الأول">🥇</span>
                                        @elseif ($index === 1)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs shadow-sm" title="المركز الثاني">🥈</span>
                                        @elseif ($index === 2)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-50 text-amber-700 text-xs border border-amber-200 shadow-sm" title="المركز الثالث">🥉</span>
                                        @else
                                            <span class="text-xs text-gray-400 font-medium">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-500 font-mono text-xs">{{ $row->product_code ?? '-' }}</td>
                                    <td class="px-4 py-2.5 font-medium text-[#0F1B4C]">{{ $row->product_name }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ (int) $row->invoices_count }}</td>
                                    <td class="px-4 py-2.5 text-end font-semibold text-[#0F1B4C]">{{ number_format((float) $row->total_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-[#0F1B4C] font-semibold">{{ number_format((float) $row->total_revenue, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-500">{{ number_format((float) $row->total_cost, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold {{ $row->net_profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format((float) $row->net_profit, 2) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-medium {{ $row->profit_margin >= 0 ? 'text-[#1456E8]' : 'text-rose-600' }}">
                                        {{ number_format((float) $row->profit_margin, 1) }}%
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r from-[#1456E8] to-indigo-500" style="width: {{ min(100, max(4, $row->contribution_percent)) }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-gray-600 w-10 text-end">{{ number_format($row->contribution_percent, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_sales_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C] bg-gray-50/50">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">-</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalRevenue, 2) }}</td>
                                    <td class="px-4 py-3 text-end text-amber-700">{{ number_format($totalCost, 2) }}</td>
                                    <td class="px-4 py-3 text-end {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ number_format($totalProfit, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end {{ $avgMargin >= 0 ? 'text-[#1456E8]' : 'text-rose-700' }}">
                                        {{ number_format($avgMargin, 1) }}%
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
