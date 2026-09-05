<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19h16M4 19V9l4-3 4 3 4-5 4 4v11"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.equity_changes_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.equity_changes_title') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports.accounts._filters', ['hasDateRange' => true])

                @unless ($hasEquityAccounts)
                    <div class="px-4 py-3 bg-amber-50 text-amber-700 text-xs border-b dc-border-amber-soft">
                        {{ __('reports.no_equity_accounts_note') }}
                    </div>
                @endunless

                <div class="p-4">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-600">{{ __('reports.opening_equity') }}</td>
                                <td class="py-3 text-end text-[#0F1B4C] font-medium">{{ number_format($openingEquity, 2) }}</td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-600">{{ __('reports.net_income_for_period') }}</td>
                                <td class="py-3 text-end {{ $netIncomeForPeriod >= 0 ? 'text-emerald-600' : 'text-red-600' }} font-medium">
                                    {{ number_format($netIncomeForPeriod, 2) }}
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 text-gray-600">{{ __('reports.other_movements') }}</td>
                                <td class="py-3 text-end text-[#0F1B4C] font-medium">{{ number_format($otherMovements, 2) }}</td>
                            </tr>
                            <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                <td class="py-3">{{ __('reports.closing_equity') }}</td>
                                <td class="py-3 text-end">{{ number_format($closingEquity, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
