<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 3 8v8l9 5 9-5V8l-9-5ZM3 8l9 5 9-5M12 13v8"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.balance_sheet_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.balance_sheet_title') }} - {{ __('reports.as_of') }} {{ now()->format('Y-m-d') }}</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports.accounts._filters', ['hasDateRange' => false, 'hasSearch' => true])

                <p class="px-4 pt-3 text-xs text-gray-400">{{ __('reports.as_of') }} {{ now()->format('Y-m-d') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-0 md:gap-6 p-4">

                    {{-- الأصول --}}
                    <div>
                        <h3 class="font-bold text-[#0F1B4C] text-sm mb-2 pb-2 border-b-2 dc-border-blue-soft">{{ __('reports.assets') }}</h3>
                        <table class="w-full text-sm">
                            <tbody>
                                @forelse ($assets as $account)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-2 text-gray-600">
                                            {{ $account->name }}
                                            @if ($account->is_branch_specific)
                                                <span class="ms-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#1456E8]/10 text-[#1456E8]">{{ __('reports.branch_specific_badge') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-end text-[#0F1B4C]">{{ number_format($account->natural_balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-6 text-center text-gray-400">{{ __('reports.no_accounts_found') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="py-3">{{ __('reports.total_assets') }}</td>
                                    <td class="py-3 text-end">{{ number_format($totalAssets, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- الخصوم وحقوق الملكية --}}
                    <div class="mt-6 md:mt-0">
                        <h3 class="font-bold text-[#0F1B4C] text-sm mb-2 pb-2 border-b-2 dc-underline-amber">{{ __('reports.liabilities') }}</h3>
                        <table class="w-full text-sm">
                            <tbody>
                                @forelse ($liabilities as $account)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-2 text-gray-600">
                                            {{ $account->name }}
                                            @if ($account->is_branch_specific)
                                                <span class="ms-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#1456E8]/10 text-[#1456E8]">{{ __('reports.branch_specific_badge') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-end text-[#0F1B4C]">{{ number_format($account->natural_balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-4 text-center text-gray-400">{{ __('reports.no_accounts_found') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 font-semibold text-[#0F1B4C]">
                                    <td class="py-2.5">{{ __('reports.total_liabilities') }}</td>
                                    <td class="py-2.5 text-end">{{ number_format($totalLiabilities, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <h3 class="font-bold text-[#0F1B4C] text-sm mt-5 mb-2 pb-2 border-b-2 dc-underline-purple">{{ __('reports.equity') }}</h3>
                        <table class="w-full text-sm">
                            <tbody>
                                @forelse ($equity as $account)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-2 text-gray-600">
                                            {{ $account->name }}
                                            @if ($account->is_branch_specific)
                                                <span class="ms-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-[#1456E8]/10 text-[#1456E8]">{{ __('reports.branch_specific_badge') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-end text-[#0F1B4C]">{{ number_format($account->natural_balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-4 text-center text-gray-400">{{ __('reports.no_accounts_found') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 font-semibold text-[#0F1B4C]">
                                    <td class="py-2.5">{{ __('reports.total_equity') }}</td>
                                    <td class="py-2.5 text-end">{{ number_format($totalEquity, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="flex justify-between border-t-2 border-gray-200 font-bold text-[#0F1B4C] text-sm mt-3 pt-3">
                            <span>{{ __('reports.total_liabilities_and_equity') }}</span>
                            <span>{{ number_format($totalLiabilities + $totalEquity, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-gray-100 text-sm font-medium {{ $isBalanced ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $isBalanced ? __('reports.balance_sheet_balanced') : __('reports.balance_sheet_not_balanced') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
