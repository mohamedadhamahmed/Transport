<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h11v8H3zM14 10h4l3 3v2h-7zM6.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM17.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.delivery.summary') }}</h2>
                </div>
                <a href="{{ route('reports.delivery.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.delivery.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.delivery.summary') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

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
                    'hasPrintDetails' => true,
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.delivery_note_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.customer') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.quantity') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.delivery_price') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.delivery_status') }}</th>
                                <th class="dc-print-hide text-start px-4 py-3">{{ __('reports.details') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notes as $note)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">#{{ $note->id }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ optional($note->customer)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($note->created_at)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $note->Number_of_Quantity, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format((float) $note->Price, 2) }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ((int) $note->status === 3)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">{{ __('reports.delivery_status_converted') }}</span>
                                        @elseif ((int) $note->status === 0)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full dc-note-blue">{{ __('reports.delivery_status_pending') }}</span>
                                        @else
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">{{ __('reports.delivery_status_partial') }}</span>
                                        @endif
                                    </td>
                                    <td class="dc-print-hide px-4 py-2.5">
                                        <button type="button" onclick="toggleRowDetails({{ $note->id }})" class="text-[#1456E8] text-xs font-medium hover:underline">
                                            {{ __('reports.view_details') }}
                                        </button>
                                    </td>
                                </tr>
                                <tr id="details-row-{{ $note->id }}" class="details-row hidden">
                                    <td colspan="7" class="px-4 py-3 bg-gray-50/60">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="text-gray-400 uppercase tracking-wide border-b border-gray-200">
                                                    <th class="text-start py-1.5">{{ __('reports.product') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.quantity') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.unit_price') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.discount') }}</th>
                                                    <th class="text-end py-1.5">{{ __('reports.line_total') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($note->items as $item)
                                                    <tr class="border-b border-gray-100">
                                                        <td class="py-1.5 text-[#0F1B4C]">{{ optional($item->product)->name ?? '-' }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->Unit_Price, 2) }}</td>
                                                        <td class="py-1.5 text-end text-gray-600">{{ number_format((float) $item->Discount_Value, 2) }}</td>
                                                        <td class="py-1.5 text-end font-medium text-[#0F1B4C]">{{ number_format((float) $item->line_total, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="py-3 text-center text-gray-400">{{ __('reports.no_products_found') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_delivery_notes_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($notes->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalQuantity, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalPrice, 2) }}</td>
                                    <td class="px-4 py-3"></td>
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
