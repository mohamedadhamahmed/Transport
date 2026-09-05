<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.delivery.pending') }}</h2>
                </div>
                <a href="{{ route('reports.delivery.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.delivery.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.delivery.pending') }} - {{ __('reports.as_of') }} {{ now()->format('Y-m-d') }}</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasEntitySelect' => true,
                    'entityParam' => 'customer_id',
                    'entityId' => $customerId,
                    'entityLabel' => __('reports.customer'),
                    'entityAllLabel' => __('reports.all_customers'),
                    'entityOptions' => $customers,
                    'entityAjaxUrl' => route('customers.search'),
                ])

                @if ($notes->isEmpty())
                    <div class="px-4 py-3 dc-note-blue text-xs border-b">
                        {{ __('reports.no_pending_delivery_notes') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.delivery_note_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.customer') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.remaining_quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notes as $note)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">#{{ $note->id }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ optional($note->customer)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($note->created_at)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium dc-text-red-strong">{{ number_format($note->remaining_quantity, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_delivery_notes_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($notes->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalRemaining, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
