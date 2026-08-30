<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 7h6M9 11h6M9 15h3"/>
                            <path d="M5 4h14a1 1 0 0 1 1 1v15l-3-2-3 2-3-2-3 2-3-2-3 2V5a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('quotations.title') }}</h2>
                </div>
                <a href="{{ route('quotations.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + {{ __('quotations.new_quotation') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                {{-- الفلاتر --}}
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('quotations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('quotations.filter_by_customer') }}</label>
                            <select name="customer_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('quotations.all_customers') }}</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('quotations.filter_by_status') }}</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('quotations.all_statuses') }}</option>
                                <option value="pending" @selected(request('status') === 'pending')>{{ __('quotations.status_pending') }}</option>
                                <option value="approved" @selected(request('status') === 'approved')>{{ __('quotations.status_approved') }}</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>{{ __('quotations.status_rejected') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('quotations.filter_by_date') }}</label>
                            <input type="date" name="date" value="{{ request('date') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('quotations.filter') }}
                            </button>
                            @if (request()->hasAny(['customer_id', 'status', 'date']))
                                <a href="{{ route('quotations.index') }}"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    {{ __('quotations.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.quotation_no') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.customer') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.created_by') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.status') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($quotations as $quotation)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $quotation->id }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $quotation->customer?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $quotation->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $quotation->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($quotation->grand_total, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($quotation->status === 'approved')
                                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ __('quotations.status_approved') }}</span>
                                        @elseif ($quotation->status === 'rejected')
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-50 text-red-700">{{ __('quotations.status_rejected') }}</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700">{{ __('quotations.status_pending') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('quotations.show', $quotation) }}" title="{{ __('quotations.view') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <a href="{{ route('quotations.show', $quotation) }}" title="{{ __('quotations.download_pdf') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('quotations.no_quotations_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($quotations->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($quotations->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('quotations.previous') }}</span>
                            @else
                                <a href="{{ $quotations->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('quotations.previous') }}</a>
                            @endif

                            @foreach (range(1, $quotations->lastPage()) as $page)
                                @if ($page == $quotations->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $quotations->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($quotations->hasMorePages())
                                <a href="{{ $quotations->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('quotations.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('quotations.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
