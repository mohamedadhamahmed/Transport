<x-app-layout>

    <div class="py-6">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10h18M6 15h4M3 6h18v12H3z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ !empty($isMaintenance) ? '🔧 ' . __('transport.maintenance_vouchers') : ($type === 'receipt' ? __('vouchers.receipt_title') : __('vouchers.payment_title')) }}</h2>
                </div>
                @can(!empty($isMaintenance) ? 'maintenance.create' : 'vouchers.create')
                <a href="{{ !empty($isMaintenance) ? route('vouchers.create', ['type' => 'payment', 'maintenance' => 1]) : route('vouchers.create', ['type' => $type]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + {{ !empty($isMaintenance) ? __('transport.new_maintenance') : ($type === 'receipt' ? __('vouchers.new_receipt') : __('vouchers.new_payment')) }}
                </a>
                @endcan
            </div>

            @include('partials.sweet-alert-flash')

            <div class="flex gap-2">
                <a href="{{ route('vouchers.index', ['type' => 'receipt']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $type === 'receipt' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    {{ __('vouchers.receipt_title') }}
                </a>
                <a href="{{ route('vouchers.index', ['type' => 'payment']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $type === 'payment' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    {{ __('vouchers.payment_title') }}
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                <div class="p-4 border-b border-gray-100">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <input type="hidden" name="type" value="{{ $type }}">
                        @if (!empty($isMaintenance))
                            <input type="hidden" name="maintenance" value="1">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('transport.truck') }}</label>
                                <select name="truck_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                    <option value="">{{ __('transport.all') }}</option>
                                    @foreach ($trucks as $t)<option value="{{ $t->id }}" @selected((string) request('truck_id') === (string) $t->id)>{{ $t->display_name }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('transport.expense_category') }}</label>
                                <select name="expense_category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                    <option value="">{{ __('transport.all') }}</option>
                                    @foreach (\App\Models\AccountVoucher::EXPENSE_CATEGORIES as $k => $label)<option value="{{ $k }}" @selected(request('expense_category') === $k)>{{ $label }}</option>@endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('vouchers.date_from') }}</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('vouchers.date_to') }}</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('vouchers.filter') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.voucher_no') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.voucher_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.treasury_account') }}</th>
                                @if (!empty($isMaintenance))
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('transport.truck') }}</th>
                                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('transport.expense_category') }}</th>
                                @endif
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.items_count') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('vouchers.view') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($vouchers as $voucher)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#{{ $voucher->voucher_number }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $voucher->treasuryAccount?->name ?? '-' }}</td>
                                    @if (!empty($isMaintenance))
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $voucher->truck?->plate_number ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ $voucher->expenseCategoryLabel() ?? '-' }}</td>
                                    @endif
                                    <td class="px-4 py-3 text-gray-500">{{ $voucher->lines_count }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($voucher->lines_total ?? 0, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('vouchers.show', $voucher) }}" title="{{ __('vouchers.view') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            @can($voucher->truck_id ? 'maintenance.edit' : 'vouchers.edit')
                                            <a href="{{ route('vouchers.edit', $voucher) }}" title="{{ __('vouchers.edit_voucher') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ !empty($isMaintenance) ? 8 : 6 }}" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('vouchers.no_vouchers_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($vouchers->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($vouchers->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('vouchers.previous') }}</span>
                            @else
                                <a href="{{ $vouchers->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('vouchers.previous') }}</a>
                            @endif

                            @foreach (range(1, $vouchers->lastPage()) as $page)
                                @if ($page == $vouchers->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $vouchers->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($vouchers->hasMorePages())
                                <a href="{{ $vouchers->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('vouchers.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('vouchers.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
