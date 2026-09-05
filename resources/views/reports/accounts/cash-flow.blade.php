<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.cash_flow_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.cash_flow_title') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports.accounts._filters', ['hasDateRange' => true, 'hasSearch' => true])

                <div class="px-4 py-3 dc-note-blue text-xs border-b">
                    {{ __('reports.cash_flow_simplified_note') }}
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.cash_account') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.opening_balance') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_change') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.closing_balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cashAccounts as $account)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $account->name }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($account->opening_balance, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end {{ $account->net_change >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ number_format($account->net_change, 2) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format($account->closing_balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_cash_accounts_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($cashAccounts->isNotEmpty())
                    <div class="flex justify-between items-center px-4 py-4 border-t border-gray-100 font-bold text-[#0F1B4C]">
                        <span>{{ __('reports.total_net_change') }}</span>
                        <span class="{{ $totalNetChange >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($totalNetChange, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
