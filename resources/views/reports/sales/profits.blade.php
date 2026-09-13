<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.sales.profits') }}</h2>
                        <p class="text-white/60 text-xs mt-0.5">{{ __('reports.sales.profits_desc') }}</p>
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
                    <p class="text-[11px] font-medium text-gray-500">{{ __('reports.profit_margin_percent') }}</p>
                    <p class="text-lg sm:text-xl font-bold {{ $avgMargin >= 0 ? 'text-[#1456E8]' : 'text-rose-600' }} mt-1">
                        {{ number_format($avgMargin, 1) }}%
                    </p>
                </div>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.sales.profits') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasDateRange' => true,
                    'hasEntitySelect' => true,
                    'entityParam' => 'customer_id',
                    'entityId' => $customerId,
                    'entityLabel' => __('reports.customer'),
                    'entityAllLabel' => __('reports.all_customers'),
                    'entityOptions' => $customers,
                    'entityAjaxUrl' => route('customers.search'),
                    'searchPlaceholder' => __('reports.invoice_number'),
                    'hasPrintDetails' => true,
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.invoice_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.customer') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.quantity') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.total_sales') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.total_cost') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.profit') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.profit_margin') }}</th>
                                <th class="dc-print-hide text-start px-4 py-3">{{ __('reports.details') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 font-medium text-[#0F1B4C]">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ optional($invoice->customer)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $invoice->total_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-[#0F1B4C] font-semibold">{{ number_format($invoice->sales_revenue, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-500">{{ number_format($invoice->total_cost, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold {{ $invoice->profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ number_format($invoice->profit, 2) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-medium {{ $invoice->profit_margin >= 0 ? 'text-[#1456E8]' : 'text-rose-600' }}">
                                        {{ number_format($invoice->profit_margin, 1) }}%
                                    </td>
                                    <td class="dc-print-hide px-4 py-2.5">
                                        <button type="button" onclick="toggleRowDetails({{ $invoice->id }})" class="text-[#1456E8] text-xs font-medium hover:underline">
                                            {{ __('reports.view_details') }}
                                        </button>
                                    </td>
                                </tr>
                                <tr id="details-row-{{ $invoice->id }}" class="details-row hidden">
                                    <td colspan="9" class="px-4 py-3 bg-gray-50/70 border-b border-gray-100">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="text-gray-400 uppercase tracking-wide border-b border-gray-200">
                                                    <th class="text-start py-1.5">{{ __('reports.product') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.quantity') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.unit_price') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.unit_cost') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.total_sales') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.total_cost') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.profit') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.profit_margin') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($invoice->items as $item)
                                                    <tr class="border-b border-gray-100 last:border-0">
                                                        <td class="py-1.5 text-[#0F1B4C] font-medium">{{ optional($item->product)->name ?? ($item->product_name_snapshot ?? '-') }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->unit_price, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-500">{{ number_format((float) $item->unit_cost, 2) }}</td>
                                                        <td class="py-1.5 text-end text-[#0F1B4C]">{{ number_format((float) $item->revenue, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-500">{{ number_format((float) $item->total_cost, 2) }}</td>
                                                        <td class="py-1.5 text-end font-semibold {{ $item->profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                            {{ number_format((float) $item->profit, 2) }}
                                                        </td>
                                                        <td class="py-1.5 text-end font-medium {{ $item->margin >= 0 ? 'text-[#1456E8]' : 'text-rose-600' }}">
                                                            {{ number_format((float) $item->margin, 1) }}%
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="py-3 text-center text-gray-400">{{ __('reports.no_products_found') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_sales_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($invoices->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C] bg-gray-50/50">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalSales, 2) }}</td>
                                    <td class="px-4 py-3 text-end text-amber-700">{{ number_format($totalCost, 2) }}</td>
                                    <td class="px-4 py-3 text-end {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ number_format($totalProfit, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end {{ $avgMargin >= 0 ? 'text-[#1456E8]' : 'text-rose-700' }}">
                                        {{ number_format($avgMargin, 1) }}%
                                    </td>
                                    <td class="dc-print-hide"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
