<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 8H6"/>
                            <path d="M9 21a9 9 0 1 1 9-9"/><path d="M9 8v5h5"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('purchase_returns.title') }}</h2>
                </div>
                <a href="{{ route('purchases.returns.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + {{ __('purchase_returns.new_return') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                {{-- الفلاتر --}}
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('purchases.returns.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('purchase_returns.filter_by_supplier') }}</label>
                            <select name="supplier_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('purchase_returns.all_suppliers') }}</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('purchase_returns.filter') }}
                            </button>
                            @if (request()->hasAny(['supplier_id']))
                                <a href="{{ route('purchases.returns.index') }}"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    {{ __('purchase_returns.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.return_no') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.linked_purchase') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.supplier') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.created_by') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_returns.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($returns as $return)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $return->return_number ?? $return->id }}</td>
                                    <td class="px-4 py-3 text-gray-500">
                                        @if ($return->purchase)
                                            <a href="{{ route('purchases.show', $return->purchase) }}" class="text-[#1456E8] hover:underline">
                                                #{{ $return->purchase->purchase_number ?? $return->purchase->id }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $return->supplier?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ optional($return->return_date)->format('Y-m-d') ?? $return->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $return->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($return->grand_total, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('purchases.returns.show', $return) }}" title="{{ __('purchase_returns.view') }}"
                                           class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('purchase_returns.no_returns_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($returns->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($returns->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('purchase_returns.previous') }}</span>
                            @else
                                <a href="{{ $returns->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('purchase_returns.previous') }}</a>
                            @endif

                            @foreach (range(1, $returns->lastPage()) as $page)
                                @if ($page == $returns->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $returns->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($returns->hasMorePages())
                                <a href="{{ $returns->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('purchase_returns.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('purchase_returns.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
