<x-app-layout>

    <div class="py-6">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="3" width="12" height="18" rx="1"/><path d="M9 8h6M9 12h6M9 16h4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('purchase_orders.title') }}</h2>
                </div>
                <a href="{{ route('purchase-orders.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + {{ __('purchase_orders.new_purchase_order') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                {{-- الفلاتر --}}
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('purchase-orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('purchase_orders.filter_by_supplier') }}</label>
                            <select name="supplier_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('purchase_orders.all_suppliers') }}</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('purchase_orders.filter_by_status') }}</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('purchase_orders.all_statuses') }}</option>
                                <option value="pending" @selected(request('status') === 'pending')>{{ __('purchase_orders.status_pending') }}</option>
                                <option value="converted" @selected(request('status') === 'converted')>{{ __('purchase_orders.status_converted') }}</option>
                                <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('purchase_orders.status_cancelled') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('purchase_orders.filter_by_date') }}</label>
                            <input type="date" name="date" value="{{ request('date') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('purchase_orders.filter') }}
                            </button>
                            @if (request()->hasAny(['supplier_id', 'status', 'date']))
                                <a href="{{ route('purchase-orders.index') }}"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    {{ __('purchase_orders.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.order_no') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.supplier') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.created_by') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.status') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($purchaseOrders as $purchaseOrder)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $purchaseOrder->order_number ?? $purchaseOrder->id }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $purchaseOrder->supplier?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ optional($purchaseOrder->issue_date)->format('Y-m-d') ?? $purchaseOrder->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $purchaseOrder->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($purchaseOrder->grand_total, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($purchaseOrder->status === 'converted')
                                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ __('purchase_orders.status_converted') }}</span>
                                        @elseif ($purchaseOrder->status === 'cancelled')
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-50 text-red-700">{{ __('purchase_orders.status_cancelled') }}</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700">{{ __('purchase_orders.status_pending') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" title="{{ __('purchase_orders.view') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" title="{{ __('purchase_orders.download_pdf') }}"
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
                                        {{ __('purchase_orders.no_purchase_orders_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($purchaseOrders->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($purchaseOrders->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('purchase_orders.previous') }}</span>
                            @else
                                <a href="{{ $purchaseOrders->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('purchase_orders.previous') }}</a>
                            @endif

                            @foreach (range(1, $purchaseOrders->lastPage()) as $page)
                                @if ($page == $purchaseOrders->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $purchaseOrders->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($purchaseOrders->hasMorePages())
                                <a href="{{ $purchaseOrders->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('purchase_orders.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('purchase_orders.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
