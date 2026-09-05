<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 6h15l-1.5 9h-12ZM6 6 5 3H2m6 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.accounts.expenses') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.accounts.expenses') }}</h2>

            {{-- فلاتر: فرع + حساب المصروف (بحث Ajax حي بمقترحات) + فترة --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasDateRange' => true,
                    'hasEntitySelect' => true,
                    'entityParam' => 'account_id',
                    'entityId' => $accountId,
                    'entityLabel' => __('reports.account_name'),
                    'entityAllLabel' => __('reports.all_expense_accounts'),
                    'entityOptions' => $accountOptions,
                    'entityAjaxUrl' => route('reports.accounts.expenses.search'),
                ])

                @if ($branchId)
                    <div class="dc-print-hide px-4 py-2 bg-amber-50 text-amber-700 text-xs border-b dc-border-amber-soft">
                        {{ __('reports.branch_filter_note') }}
                    </div>
                @endif

                <div class="px-4 py-2 text-xs text-gray-400">{{ __('reports.period_note') }}</div>
            </div>

            {{-- بطاقة الإجمالي --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.accounts.expenses') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ $accounts->count() }}</p>
                </div>
                <div class="bg-[#0F1B4C] rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-white/50">{{ __('reports.net_total') }}</p>
                    <p class="text-lg font-bold text-white mt-0.5">{{ number_format($totalExpenses, 2) }}</p>
                </div>
            </div>

            {{-- جدول حسابات المصروفات --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.account_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.account_name') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_total') }}</th>
                                <th class="text-end px-4 py-3">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $account)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $account->account_number }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C] font-medium">
                                        {{ $account->name }}
                                        @if ($account->is_branch_specific)
                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#1456E8]/10 text-[#1456E8]">{{ __('reports.branch') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-end font-semibold text-[#0F1B4C]">{{ number_format($account->period_amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-400 text-xs">{{ $totalExpenses != 0 ? number_format($account->period_amount / $totalExpenses * 100, 1) : '0.0' }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_expenses_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($accounts->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="2">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalExpenses, 2) }}</td>
                                    <td class="px-4 py-3 text-end">100%</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
