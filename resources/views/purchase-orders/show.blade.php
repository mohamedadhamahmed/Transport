<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="3" width="12" height="18" rx="1"/><path d="M9 8h6M9 12h6M9 16h4"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('purchase_orders.order_no') }} #{{ $purchaseOrder->order_number ?? $purchaseOrder->id }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ optional($purchaseOrder->issue_date)->format('Y-m-d') ?? $purchaseOrder->created_at->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('purchase_orders.download_pdf') }}
                    </a>
                    <a href="{{ route('purchase-orders.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('purchase_orders.back_to_list') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            {{-- الحالة + أزرار الإجراءات --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500">{{ __('purchase_orders.status') }}:</span>
                        @if ($purchaseOrder->status === 'converted')
                            <span class="px-3 py-1 rounded-full text-sm bg-emerald-50 text-emerald-700 font-medium">{{ __('purchase_orders.status_converted') }}</span>
                        @elseif ($purchaseOrder->status === 'cancelled')
                            <span class="px-3 py-1 rounded-full text-sm bg-red-50 text-red-700 font-medium">{{ __('purchase_orders.status_cancelled') }}</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm bg-amber-50 text-amber-700 font-medium">{{ __('purchase_orders.status_pending') }}</span>
                        @endif
                    </div>

                    @if ($purchaseOrder->isPending())
                        <div class="flex items-center gap-2" x-data="{}">
                            <a href="{{ route('purchases.create', ['from_po' => $purchaseOrder->id]) }}"
                               onclick="return confirm(@js(__('purchase_orders.convert_notice')))"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                                {{ __('purchase_orders.convert_to_purchase') }}
                            </a>
                            <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}"
                                  onsubmit="return confirm(@js(__('purchase_orders.cancel_confirm')))">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 rounded-lg text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 transition">
                                    {{ __('purchase_orders.cancel_order') }}
                                </button>
                            </form>
                        </div>
                    @elseif ($purchaseOrder->isConverted() && $purchaseOrder->convertedPurchase)
                        <a href="{{ route('purchases.show', $purchaseOrder->convertedPurchase) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-[#0F1B4C] bg-[#0F1B4C]/5 hover:bg-[#0F1B4C]/10 transition">
                            {{ __('purchase_orders.view_purchase') }}
                        </a>
                    @endif
                </div>

                @if ($purchaseOrder->isConverted() && $purchaseOrder->convertedPurchase)
                    <div class="mt-4 text-xs text-gray-400">
                        {{ __('purchase_orders.converted_by') }}: {{ $purchaseOrder->convertedByUser?->name ?? '-' }}
                        &middot;
                        {{ __('purchase_orders.converted_at') }}: {{ optional($purchaseOrder->converted_at)->format('Y-m-d H:i') }}
                    </div>
                @endif
            </div>

            {{-- بيانات الأمر الأساسية --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('purchase_orders.supplier') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchaseOrder->supplier?->name ?? '-' }}</div>
                        @if ($purchaseOrder->supplier?->phone)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $purchaseOrder->supplier->phone }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('purchase_orders.branch') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchaseOrder->branch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('purchase_orders.created_by') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchaseOrder->creator?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('purchase_orders.warehouse_name') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchaseOrder->warehouse_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('purchase_orders.cost_center') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchaseOrder->costCenter?->cost_center_ar ?? '-' }}</div>
                    </div>
                    @if ($purchaseOrder->note)
                        <div class="md:col-span-3">
                            <div class="text-xs text-gray-400 mb-1">{{ __('purchase_orders.note') }}</div>
                            <div class="text-gray-700">{{ $purchaseOrder->note }}</div>
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
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.code') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.quantity') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.unit_price') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.discount') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.tax') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('purchase_orders.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($purchaseOrder->items as $item)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-400 text-xs">{{ $item->product_code_snapshot ?? $item->product?->code ?? '-' }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $item->product_name_snapshot ?? $item->product?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ number_format($item->discount_amount, 2) }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]">
                                        {{ number_format(($item->unit_price * $item->quantity) - $item->discount_amount + $item->tax_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.subtotal') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($purchaseOrder->subtotal, 2) }}</div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.discount_total') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($purchaseOrder->discount_amount + $purchaseOrder->invoice_level_discount, 2) }}</div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.tax_total') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($purchaseOrder->tax_amount, 2) }}</div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ __('purchase_orders.shipping') }}</div>
                        <div class="font-semibold text-[#0F1B4C]">{{ number_format($purchaseOrder->shipping_fee, 2) }}</div>
                    </div>
                    <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                        <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                        <div class="text-xs text-white/50 mb-1">{{ __('purchase_orders.grand_total') }}</div>
                        <div class="font-bold text-lg">{{ number_format($purchaseOrder->grand_total, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
