<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.accounts.title') }}</h2>
                        <p class="text-white/60 text-sm">{{ __('reports.accounts.subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hub_title') }}
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                @foreach (collect([
                    ['route' => 'reports.accounts.statement', 'label' => __('accounts.statement'), 'desc' => 'كشف حساب تفصيلي لأي حساب في شجرة الحسابات مع إمكانية البحث الفوري واختيار أي حساب وتصدير إكسيل', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'perm' => 'accounts.view'],
                    ['route' => 'reports.accounts.trial-balance', 'label' => __('reports.accounts.trial_balance'), 'desc' => __('reports.accounts.trial_balance_desc'), 'icon' => 'M4 6h16M4 12h16M4 18h7', 'perm' => 'reports_accounting.trial_balance'],
                    ['route' => 'reports.accounts.income-statement', 'label' => __('reports.accounts.income_statement'), 'desc' => __('reports.accounts.income_statement_desc'), 'icon' => 'M3 3v18h18M7 15l4-6 4 3 5-8', 'perm' => 'reports_accounting.income_statement'],
                    ['route' => 'reports.accounts.balance-sheet', 'label' => __('reports.accounts.balance_sheet'), 'desc' => __('reports.accounts.balance_sheet_desc'), 'icon' => 'M12 3 3 8v8l9 5 9-5V8l-9-5ZM3 8l9 5 9-5M12 13v8', 'perm' => 'reports_accounting.balance_sheet'],
                    ['route' => 'reports.accounts.equity-changes', 'label' => __('reports.accounts.equity_changes'), 'desc' => __('reports.accounts.equity_changes_desc'), 'icon' => 'M4 19h16M4 19V9l4-3 4 3 4-5 4 4v11', 'perm' => 'reports_accounting.equity_changes'],
                    ['route' => 'reports.accounts.cash-flow', 'label' => __('reports.accounts.cash_flow'), 'desc' => __('reports.accounts.cash_flow_desc'), 'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'perm' => 'reports_accounting.cash_flow'],
                    ['route' => 'reports.accounts.vouchers', 'label' => __('reports.accounts.vouchers'), 'desc' => __('reports.accounts.vouchers_desc'), 'icon' => 'M4 4h16v16H4V4Zm4 4h8M8 12h8M8 16h4', 'perm' => 'reports_accounting.vouchers'],
                    ['route' => 'reports.accounts.cost-centers', 'label' => __('reports.accounts.cost_centers'), 'desc' => __('reports.accounts.cost_centers_desc'), 'icon' => 'M12 2v20M2 12h20M12 2a10 10 0 0 1 0 20 10 10 0 0 1 0-20Z', 'perm' => 'reports_accounting.cost_centers'],
                    ['route' => 'reports.accounts.aging', 'label' => __('reports.accounts.aging'), 'desc' => __('reports.accounts.aging_desc'), 'icon' => 'M12 8v4l3 3M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z', 'perm' => 'reports_accounting.aging'],
                    ['route' => 'reports.accounts.expenses', 'label' => __('reports.accounts.expenses'), 'desc' => __('reports.accounts.expenses_desc'), 'icon' => 'M6 6h15l-1.5 9h-12ZM6 6 5 3H2m6 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z', 'perm' => 'reports_accounting.expenses'],
                    ['route' => 'reports.accounts.customer-supplier-accounts', 'label' => __('reports.accounts.customer_supplier_accounts'), 'desc' => __('reports.accounts.customer_supplier_accounts_desc'), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'perm' => 'reports_accounting.customer_supplier_accounts'],
                    ['route' => 'reports.accounts.daily-closing', 'label' => __('reports.accounts.daily_closing'), 'desc' => __('reports.accounts.daily_closing_desc'), 'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 3v4M15 3v4', 'perm' => 'reports_accounting.daily_closing'],
                    ['route' => 'reports.accounts.tax', 'label' => __('reports.accounts.tax_report'), 'desc' => __('reports.accounts.tax_report_desc'), 'icon' => 'M9 14l6-6m-5.5.5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm6 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3 6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z', 'perm' => 'reports_accounting.tax_report'],
                ])->filter(fn ($card) => auth()->user()?->hasPermission($card['perm'])) as $card)
                    <a href="{{ route($card['route']) }}"
                       class="dc-card-link bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex items-start gap-4">
                        <span class="w-10 h-10 shrink-0 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $card['icon'] }}"/></svg>
                        </span>
                        <div>
                            <h3 class="dc-card-link-title font-bold text-[#0F1B4C] transition">{{ $card['label'] }}</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ $card['desc'] }}</p>
                        </div>
                    </a>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>
