<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18M7 15l4-6 4 3 5-8"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.income_statement_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.income_statement_title') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports.accounts._filters', ['hasDateRange' => true, 'hasSearch' => true])

                <div class="p-4 space-y-6">

                    {{-- الإيرادات --}}
                    <div>
                        <h3 class="font-bold text-[#0F1B4C] text-sm mb-2 pb-2 border-b-2 dc-underline-emerald">{{ __('reports.revenue') }}</h3>
                        <table class="w-full text-sm">
                            <tbody>
                                @forelse ($revenueAccounts as $account)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-2 text-gray-600">{{ $account->name }}</td>
                                        <td class="py-2 text-end text-[#0F1B4C]">{{ number_format($account->period_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-6 text-center text-gray-400">{{ __('reports.no_movements_found') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="py-3">{{ __('reports.total_revenue') }}</td>
                                    <td class="py-3 text-end">{{ number_format($totalRevenue, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- المصروفات --}}
                    <div>
                        <h3 class="font-bold text-[#0F1B4C] text-sm mb-2 pb-2 border-b-2 dc-underline-amber">{{ __('reports.expenses') }}</h3>
                        <table class="w-full text-sm">
                            <tbody>
                                @forelse ($expenseAccounts as $account)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-2 text-gray-600">{{ $account->name }}</td>
                                        <td class="py-2 text-end text-[#0F1B4C]">{{ number_format($account->period_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-6 text-center text-gray-400">{{ __('reports.no_movements_found') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="py-3">{{ __('reports.total_expenses') }}</td>
                                    <td class="py-3 text-end">{{ number_format($totalExpenses, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex justify-between items-center rounded-xl px-4 py-4 {{ $netIncome >= 0 ? 'bg-emerald-50' : 'dc-bg-red-soft' }}">
                        <span class="font-bold {{ $netIncome >= 0 ? 'text-emerald-700' : 'dc-text-red-strong' }}">
                            {{ $netIncome >= 0 ? __('reports.net_income') : __('reports.net_loss') }}
                        </span>
                        <span class="font-bold text-lg {{ $netIncome >= 0 ? 'text-emerald-700' : 'dc-text-red-strong' }}">
                            {{ number_format(abs($netIncome), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
