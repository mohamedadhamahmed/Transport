<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l9-5 9 5-9 5-9-5Z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8" />
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('stock_transfers.title') }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('stock-transfers.choose-branch', ['mode' => 'dispatch']) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        + {{ __('stock_transfers.new_dispatch') }}
                    </a>
                    <a href="{{ route('stock-transfers.choose-branch', ['mode' => 'receive']) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        + {{ __('stock_transfers.new_receive') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="flex gap-2">
                <a href="{{ route('stock-transfers.index', ['box' => 'sent']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $box === 'sent' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    {{ __('stock_transfers.box_sent') }}
                </a>
                <a href="{{ route('stock-transfers.index', ['box' => 'received']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $box === 'received' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    {{ __('stock_transfers.box_received') }}
                </a>
                <a href="{{ route('stock-transfers.index', ['box' => 'draft']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $box === 'draft' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    {{ __('stock_transfers.box_draft') }}
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.transfer_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.from_branch') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.to_branch') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.transfer_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.status') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.view') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transfers as $transfer)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $transfer->transfer_number }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $transfer->fromBranch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $transfer->toBranch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ optional($transfer->transfer_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            {{ $transfer->status === 'received' ? 'bg-emerald-50 text-emerald-700' : ($transfer->status === 'sent' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                                            {{ __('stock_transfers.status_' . $transfer->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('stock-transfers.show', $transfer) }}" title="{{ __('stock_transfers.view') }}"
                                           class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('stock_transfers.no_transfers_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($transfers->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($transfers->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('stock_transfers.previous') }}</span>
                            @else
                                <a href="{{ $transfers->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('stock_transfers.previous') }}</a>
                            @endif

                            @foreach (range(1, $transfers->lastPage()) as $page)
                                @if ($page == $transfers->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $transfers->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($transfers->hasMorePages())
                                <a href="{{ $transfers->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('stock_transfers.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('stock_transfers.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
