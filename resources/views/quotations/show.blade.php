<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 7h6M9 11h6M9 15h3"/>
                            <path d="M5 4h14a1 1 0 0 1 1 1v15l-3-2-3 2-3-2-3 2-3-2-3 2V5a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('quotations.quotation_no') }} #{{ $quotation->id }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('quotations.pdf', $quotation) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>
                        </svg>
                        {{ __('quotations.download_pdf') }}
                    </a>
                    <a href="{{ route('quotations.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('quotations.back_to_list') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            {{-- الحالة + أزرار الاعتماد/الرفض --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 flex items-center justify-between flex-wrap gap-4">
                <div>
                    @if ($quotation->status === 'approved')
                        <span class="px-3 py-1.5 rounded-full text-sm bg-emerald-50 text-emerald-700 font-medium">{{ __('quotations.status_approved') }}</span>
                        @if ($quotation->invoice)
                            <a href="{{ route('invoices.show', $quotation->invoice) }}" class="ms-2 text-sm text-[#1456E8] hover:underline">
                                {{ __('quotations.view_invoice') }} #{{ $quotation->invoice->invoice_number ?? $quotation->invoice->id }}
                            </a>
                        @endif
                        <div class="text-xs text-gray-400 mt-1">
                            {{ __('quotations.approved_by') }}: {{ $quotation->approver?->name ?? '-' }} — {{ $quotation->approved_at?->format('Y-m-d H:i') }}
                        </div>
                    @elseif ($quotation->status === 'rejected')
                        <span class="px-3 py-1.5 rounded-full text-sm bg-red-50 text-red-700 font-medium">{{ __('quotations.status_rejected') }}</span>
                    @else
                        <span class="px-3 py-1.5 rounded-full text-sm bg-amber-50 text-amber-700 font-medium">{{ __('quotations.status_pending') }}</span>
                    @endif
                </div>
                @if ($quotation->isPending())
                    <div class="flex items-center gap-2" x-data>
                        @can('quotations.edit')
                            <a href="{{ route('quotations.edit', $quotation) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium text-[#0F1B4C] bg-[#0F1B4C]/5 hover:bg-[#0F1B4C]/10 border border-[#0F1B4C]/10 transition">
                                {{ __('messages.edit') }}
                            </a>
                        @endcan
                        @can('quotations.edit')
                            <form method="POST" action="{{ route('quotations.approve', $quotation) }}"
                                  @submit="if (!confirm(@js(__('quotations.approve_confirm')))) $event.preventDefault();">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 rounded-lg text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm">
                                    {{ __('quotations.approve') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('quotations.reject', $quotation) }}"
                                  @submit="if (!confirm(@js(__('quotations.reject_confirm')))) $event.preventDefault();">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 rounded-lg text-red-600 text-sm font-medium bg-red-50 hover:bg-red-100 transition">
                                    {{ __('quotations.reject') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                @endif
            </div>

            {{-- بيانات التسعيرة --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('quotations.customer') }}</div>
                        <div class="font-medium text-gray-800">{{ $quotation->customer?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('quotations.date') }}</div>
                        <div class="font-medium text-gray-800">{{ $quotation->created_at->format('Y-m-d') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('quotations.payment_method') }}</div>
                        <div class="font-medium text-gray-800">{{ __('quotations.' . $quotation->payment_method) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('quotations.created_by') }}</div>
                        <div class="font-medium text-gray-800">{{ $quotation->creator?->name ?? '-' }}</div>
                    </div>
                    @if ($quotation->note)
                        <div class="md:col-span-4">
                            <div class="text-xs text-gray-400 mb-1">{{ __('quotations.note') }}</div>
                            <div class="text-gray-700">{{ $quotation->note }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- الأصناف --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.unit_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.discount') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.tax') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($quotation->items as $item)
                                @php
                                    $lineSubtotal = ($item->unit_price * $item->quantity) - ($item->discount_amount ?? 0);
                                    $lineTotal = $lineSubtotal + ($item->tax_amount ?? 0);
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 text-gray-400 text-xs">{{ $item->product_code_snapshot ?? $item->product?->code ?? '-' }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $item->product_name_snapshot ?? $item->product?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $item->quantity }}</td>
                                    <td class="px-3 py-2">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-3 py-2">{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                                    <td class="px-3 py-2">{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]">{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('quotations.subtotal') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($quotation->subtotal, 2) }}</div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('quotations.discount_total') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($quotation->discount_amount + $quotation->invoice_level_discount, 2) }}</div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('quotations.tax_total') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($quotation->tax_amount, 2) }}</div>
                    </div>
                    <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                        <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                        <div class="text-xs text-white/50 mb-1">{{ __('quotations.grand_total') }}</div>
                        <div class="font-bold text-lg">{{ number_format($quotation->grand_total, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- التسعيرات السابقة لنفس العميل --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="font-semibold text-gray-800 mb-3">{{ __('quotations.previous_quotations_for_customer') }}</h3>
                @if ($previousQuotations->isEmpty())
                    <div class="text-sm text-gray-400 text-center py-4">{{ __('quotations.no_previous_quotations') }}</div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500">
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.quotation_no') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.date') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.status') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.items_count') }}</th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide">{{ __('quotations.grand_total') }}</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($previousQuotations as $prev)
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 font-medium text-gray-800">#{{ $prev->id }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $prev->created_at->format('Y-m-d') }}</td>
                                        <td class="px-3 py-2">
                                            @if ($prev->status === 'approved')
                                                <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ __('quotations.status_approved') }}</span>
                                            @elseif ($prev->status === 'rejected')
                                                <span class="px-2 py-1 rounded-full text-xs bg-red-50 text-red-700">{{ __('quotations.status_rejected') }}</span>
                                            @else
                                                <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700">{{ __('quotations.status_pending') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-gray-500">{{ $prev->items->count() }}</td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]">{{ number_format($prev->grand_total, 2) }}</td>
                                        <td class="px-3 py-2">
                                            <a href="{{ route('quotations.show', $prev) }}" class="text-[#1456E8] text-xs font-medium hover:underline">{{ __('quotations.view') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
