<x-app-layout>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l9-5 9 5-9 5-9-5Z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">#{{ $stockTransfer->transfer_number }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('stock_transfers.status_' . $stockTransfer->status) }}</p>
                    </div>
                </div>
                <a href="{{ route('stock-transfers.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('stock_transfers.back_to_list') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.from_branch') }}</div>
                        <div class="font-medium text-gray-800">{{ $stockTransfer->fromBranch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.to_branch') }}</div>
                        <div class="font-medium text-gray-800">{{ $stockTransfer->toBranch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.transfer_date') }}</div>
                        <div class="font-medium text-gray-800">{{ optional($stockTransfer->transfer_date)->format('Y-m-d') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.sender_employee') }}</div>
                        <div class="font-medium text-gray-800">{{ $stockTransfer->senderUser?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.receiver_employee') }}</div>
                        <div class="font-medium text-gray-800">{{ $stockTransfer->receiverUser?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.status') }}</div>
                        <div class="font-medium text-gray-800">{{ __('stock_transfers.status_' . $stockTransfer->status) }}</div>
                    </div>
                    @if ($stockTransfer->notes)
                        <div class="md:col-span-3">
                            <div class="text-xs text-gray-400 mb-1">{{ __('stock_transfers.notes') }}</div>
                            <div class="text-gray-700">{{ $stockTransfer->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.product') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($stockTransfer->items as $item)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 font-medium text-gray-800">
                                        {{ $item->product_name_snapshot }}
                                        @if ($item->product_code_snapshot)
                                            <span class="text-xs text-gray-400">(#{{ $item->product_code_snapshot }})</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">{{ $item->quantity }} {{ $item->unit_snapshot }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($stockTransfer->isSent())
                <form method="POST" action="{{ route('stock-transfers.confirm-receive', $stockTransfer) }}" class="flex justify-end">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                        {{ __('stock_transfers.confirm_receive') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
