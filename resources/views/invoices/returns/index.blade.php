<x-app-layout>

    <div class="py-6">
        <div class="max-w-[1280px] mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بنفس ستايل باقي صفحات المرتجعات/الفواتير --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-6.7L3 9"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('invoices.previous_returns') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('invoices.sales_return_subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('invoices.returns.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    {{ __('invoices.sales_return') }}
                </a>
            </div>

            {{-- صندوق البحث --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" action="{{ route('invoices.returns.index') }}" class="flex gap-2 max-w-md">
                    <div class="relative flex-1">
                        <input type="text" name="q" value="{{ $q }}"
                               placeholder="{{ __('invoices.search_invoice_placeholder') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        {{ __('invoices.search') }}
                    </button>
                    @if($q !== '')
                        <a href="{{ route('invoices.returns.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition whitespace-nowrap">
                            {{ __('invoices.clear') }}
                        </a>
                    @endif
                </form>
            </div>

            {{-- جدول المرتجعات --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.return_reference') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.invoice_number') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.customer') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.branch') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.date') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.items_count') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.net_refund_total') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.print') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($returns as $group)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 font-medium text-gray-800 whitespace-nowrap">{{ $group->reference_value }}</td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">
                                        {{ $group->invoice_number ? '#' . $group->invoice_number : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $group->customer_name }}</td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $group->branch_name }}</td>
                                    <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                                        {{ optional($group->created_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-500">{{ $group->items_count }}</td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C] whitespace-nowrap">
                                        {{ number_format($group->net_total, 2) }}
                                    </td>
                                    <td class="px-3 py-2">
                               <a href="{{ route('invoices.returns.print', $group->reference_value) }}"
   target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
    </svg>
    {{ __('invoices.print') }}
</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                        {{ __('invoices.no_previous_returns') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
