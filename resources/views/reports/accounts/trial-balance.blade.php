<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.trial_balance_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.trial_balance_title') }}</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports.accounts._filters', ['hasDateRange' => false, 'hasSearch' => true])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.account_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.account_name') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.debtor') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.creditor') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $account)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $account->account_number ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">
                                        {{ $account->name }}
                                        @if ($account->is_branch_specific)
                                            <span class="ms-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#1456E8]/10 text-[#1456E8]">{{ __('reports.branch_specific_badge') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $account->debtor_current, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $account->creditor_current, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_accounts_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($accounts->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="2">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalDebtor, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalCreditor, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if ($accounts->isNotEmpty())
                    <div class="px-4 py-3 border-t border-gray-100 text-sm font-medium {{ $isBalanced ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $isBalanced ? __('reports.balanced') : __('reports.not_balanced') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
