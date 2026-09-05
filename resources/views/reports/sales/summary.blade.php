<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 13h6M9 17h6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.sales.summary') }}</h2>
                </div>
                <a href="{{ route('reports.sales.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.sales.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.sales.summary') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

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
                                <th class="text-end px-4 py-3">{{ __('reports.subtotal') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.tax') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.discount') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_total') }}</th>
                                <th class="dc-print-hide text-start px-4 py-3">{{ __('reports.details') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ optional($invoice->customer)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $invoice->total_quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $invoice->subtotal, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $invoice->tax_amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $invoice->discount_amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format($invoice->net_total, 2) }}</td>
                                    <td class="dc-print-hide px-4 py-2.5">
                                        <button type="button" onclick="toggleRowDetails({{ $invoice->id }})" class="text-[#1456E8] text-xs font-medium hover:underline">
                                            {{ __('reports.view_details') }}
                                        </button>
                                    </td>
                                </tr>
                                <tr id="details-row-{{ $invoice->id }}" class="details-row hidden">
                                    <td colspan="9" class="px-4 py-3 bg-gray-50/60">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="text-gray-400 uppercase tracking-wide border-b border-gray-200">
                                                    <th class="text-start py-1.5">{{ __('reports.product') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.quantity') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.unit_price') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.discount') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.tax') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.line_total') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($invoice->items as $item)
                                                    <tr class="border-b border-gray-100">
                                                        <td class="py-1.5 text-[#0F1B4C]">{{ optional($item->product)->name ?? '-' }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->unit_price, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->discount_amount, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->tax_amount, 2) }}</td>
                                                        <td class="py-1.5 text-end font-medium text-[#0F1B4C]">{{ number_format((float) $item->unit_price * (float) $item->quantity + (float) $item->tax_amount - (float) $item->discount_amount, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="py-3 text-center text-gray-400">{{ __('reports.no_products_found') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_invoices_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($invoices->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalSubtotal, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalTax, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalDiscount, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalNet, 2) }}</td>
                                    <td class="dc-print-hide px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
