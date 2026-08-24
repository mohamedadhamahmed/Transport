<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4v5h5M20 20v-5h-5"/>
                        <path d="M4.6 15a8 8 0 0 0 14.9 2M19.4 9A8 8 0 0 0 4.5 7"/>
                    </svg>
                </span>
                <h2 class="text-white font-bold text-lg">{{ __('returns.title') }}</h2>
            </div>

            @include('partials.sweet-alert-flash')

            {{-- البحث عن الفاتورة --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" action="{{ route('returns.index') }}" class="flex items-end gap-3">
                    <div class="flex-1 max-w-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('returns.enter_invoice_no') }} #</label>
                        <input type="text" name="invoice_number" value="{{ request('invoice_number') }}"
                               placeholder="{{ __('returns.invoice_no_placeholder') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-white font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                        {{ __('returns.search') }}
                    </button>
                </form>

                @if ($notFound)
                    <p class="mt-4 text-sm text-red-600">{{ __('returns.invoice_not_found') }}</p>
                @endif
            </div>

            @if ($invoice)
                <form method="POST" action="{{ route('returns.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                    {{-- بيانات الفاتورة --}}
                    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('returns.invoice_no') }}</div>
                            <div class="font-semibold text-[#0F1B4C]">#{{ $invoice->invoice_number ?? $invoice->id }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('returns.customer') }}</div>
                            <div class="font-medium text-gray-800">{{ $invoice->customer?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('returns.branch') }}</div>
                            <div class="font-medium text-gray-800">{{ $invoice->branch?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">{{ __('returns.date') }}</div>
                            <div class="font-medium text-gray-800">{{ $invoice->issue_date?->format('Y-m-d') ?? $invoice->created_at->format('Y-m-d') }}</div>
                        </div>
                    </div>

                    {{-- الأصناف --}}
                    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                        <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-[#0F1B4C] text-white/80">
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.product') }}</th>
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.unit_price') }}</th>
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.original_qty') }}</th>
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.already_returned') }}</th>
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.remaining_qty') }}</th>
                                        <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.return_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($invoice->items as $item)
                                        @php
                                            // بنحسب المتاح من quantity - returned_quantity مباشرة (مش من
                                            // remaining_quantity المخزّن) عشان يبقى صح حتى لو الفاتورة
                                            // اتعملت قبل ما نظبط تسجيل العمود ده.
                                            $returned = (float) $item->returned_quantity;
                                            $remaining = max(0, $item->quantity - $returned);
                                        @endphp
                                        <tr class="hover:bg-[#1456E8]/5 transition">
                                            <td class="px-3 py-2 font-medium text-gray-800">
                                                {{ $item->product_name_snapshot ?? $item->product?->name }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-500">{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ rtrim(rtrim(number_format($returned, 2), '0'), '.') }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }}</td>
                                            <td class="px-3 py-2">
                                                <input type="hidden" name="items[{{ $loop->index }}][invoice_item_id]" value="{{ $item->id }}">
                                                @if ($remaining > 0)
                                                    <input type="number" step="0.01" min="0" max="{{ $remaining }}"
                                                           name="items[{{ $loop->index }}][quantity]" value="0"
                                                           class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                                @else
                                                    <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="0">
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-400">
                                                        {{ __('returns.fully_returned') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg text-white font-medium bg-[#F5811E] hover:brightness-95 transition shadow-sm">
                                {{ __('returns.save_return') }}
                            </button>
                        </div>
                    </div>
                </form>
            @endif

            {{-- آخر المرتجعات --}}
            @if ($previousReturns->isNotEmpty())
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">
                        {{ __('returns.previous_returns') }}
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.invoice_no') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.product') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.return_qty') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.date') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('returns.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($previousReturns as $return)
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-4 py-3 text-gray-500">
                                            <a href="{{ route('returns.index', ['invoice_number' => $return->invoice?->invoice_number]) }}" class="text-[#1456E8] hover:underline">
                                                #{{ $return->invoice?->invoice_number ?? '-' }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $return->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ rtrim(rtrim(number_format($return->quantity, 2), '0'), '.') }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ $return->created_at->format('Y-m-d') }}</td>
                                        <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format(($return->unit_price * $return->quantity) + $return->tax_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
