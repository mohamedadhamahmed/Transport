<x-app-layout>

    <div class="py-6">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 8H6"/>
                            <circle cx="9" cy="21" r="1"/><circle cx="17" cy="21" r="1"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('purchases.title') }}</h2>
                </div>
                <a href="{{ route('purchases.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + {{ __('purchases.new_purchase') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                {{-- الفلاتر --}}
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('purchases.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('purchases.filter_by_supplier') }}</label>
                            <select name="supplier_id" data-ajax-select data-ajax-url="{{ route('suppliers.search') }}"
                                    data-ajax-placeholder="{{ __('purchases.all_suppliers') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('purchases.all_suppliers') }}</option>
                                @foreach ($suppliers as $id => $name)
                                    @if ($name)
                                        <option value="{{ $id }}" selected>{{ $name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('purchases.filter_by_date') }}</label>
                            <input type="date" name="date" value="{{ request('date') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('purchases.filter') }}
                            </button>
                            @if (request()->hasAny(['supplier_id', 'date']))
                                <a href="{{ route('purchases.index') }}"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    {{ __('purchases.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.purchase_no') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.supplier') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.created_by') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.payment_method') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchases.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($purchases as $purchase)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $purchase->purchase_number ?? $purchase->id }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $purchase->supplier?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ optional($purchase->issue_date)->format('Y-m-d') ?? $purchase->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $purchase->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($purchase->isCredit())
                                            <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700">{{ __('purchases.credit') }}</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ $purchase->paymentAccount?->name ?? __('purchases.payment_immediate') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($purchase->grand_total, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('purchases.show', $purchase) }}" title="{{ __('purchases.view') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <a href="{{ route('purchases.pdf', $purchase) }}" title="{{ __('purchases.download_pdf') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/>
                                                </svg>
                                            </a>
                                            {{-- تعديل: بيظهر بس لو المستخدم عنده صلاحية purchases.edit
                                                 والفاتورة نفسها قابلة للتعديل (isEditable() في موديل Purchase) --}}
                                            @can('purchases.edit')
                                                @if ($purchase->isEditable())
                                                    <a href="{{ route('purchases.edit', $purchase) }}" title="{{ __('purchases.edit') }}"
                                                       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span title="{{ __('purchases.not_editable') }}"
                                                          class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('purchases.no_purchases_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($purchases->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($purchases->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('purchases.previous') }}</span>
                            @else
                                <a href="{{ $purchases->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('purchases.previous') }}</a>
                            @endif

                            @foreach (range(1, $purchases->lastPage()) as $page)
                                @if ($page == $purchases->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $purchases->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($purchases->hasMorePages())
                                <a href="{{ $purchases->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('purchases.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('purchases.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('partials.ajax-select-assets')
</x-app-layout>
