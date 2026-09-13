<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18M7 15l4-6 4 3 5-8" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.hub_title') }}</h2>
                    <p class="text-white/60 text-sm">{{ __('reports.hub_subtitle') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                @php
                    // كل قسم بيظهر بس لو المستخدم عنده صلاحية تقرير واحد
                    // على الأقل جواه (نفس منطق ReportController::authorizeAnyReport
                    // ونفس منطق القائمة الجانبية - عشان محدش يشوف كارت
                    // لصفحة هيتمنع منها).
                    $reportSections = collect([
                        ['route' => 'reports.accounts.index', 'label' => __('reports.sections.accounts'), 'desc' => __('reports.accounts.subtitle'), 'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6', 'perms' => ['reports_accounting.trial_balance', 'reports_accounting.balance_sheet', 'reports_accounting.income_statement', 'reports_accounting.equity_changes', 'reports_accounting.cash_flow']],
                        ['route' => 'reports.sales.index', 'label' => __('reports.sections.sales'), 'desc' => __('reports.sales.subtitle'), 'icon' => 'M9 2h6l1 4h4v2h-2l-1.6 9.6A2 2 0 0 1 14.4 20H9.6a2 2 0 0 1-2-1.4L6 8H4V6h4l1-4Z', 'perms' => ['reports_sales.summary', 'reports_sales.profits', 'reports_sales.employee_profits', 'reports_sales.top_products', 'reports_sales.by_customer', 'reports_sales.by_employee', 'reports_sales.by_product', 'reports_sales.returns']],
                        ['route' => 'reports.delivery.index', 'label' => __('reports.sections.delivery'), 'desc' => __('reports.delivery.subtitle'), 'icon' => 'M3 7h11v8H3zM14 10h4l3 3v2h-7zM6.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM17.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z', 'perms' => ['reports_delivery.summary', 'reports_delivery.pending', 'reports_delivery.by_employee']],
                        ['route' => 'reports.purchases.index', 'label' => __('reports.sections.purchases'), 'desc' => __('reports.purchases.subtitle'), 'icon' => 'M6 6h15l-1.5 9h-12ZM6 6 5 3H2m6 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z', 'perms' => ['reports_purchases.summary', 'reports_purchases.by_supplier', 'reports_purchases.by_employee', 'reports_purchases.by_product', 'reports_purchases.purchases_vs_sales', 'reports_purchases.returns']],
                        ['route' => 'reports.products.index', 'label' => __('reports.sections.products'), 'desc' => __('reports.products.subtitle'), 'icon' => 'M21 8 12 3 3 8l9 5 9-5ZM3 8v8l9 5m0-13v13m9-13v8l-9 5', 'perms' => ['reports_products.stock', 'reports_products.low_stock', 'reports_products.stock_transfers']],
                        ['route' => 'reports.hr.index', 'label' => __('reports.sections.hr'), 'desc' => __('reports.hr.subtitle'), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'perms' => ['reports_hr.payroll', 'reports_hr.attendance', 'reports_hr.loans', 'reports_hr.employees', 'reports_hr.bonuses_deductions', 'reports_hr.leaves']],
                    ])->filter(fn ($section) => collect($section['perms'])->contains(fn ($p) => auth()->user()?->hasPermission($p)));
                @endphp

                @foreach ($reportSections as $section)
                    <a href="{{ route($section['route']) }}"
                       class="dc-card-link bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $section['icon'] }}"/></svg>
                            </span>
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">{{ __('reports.available') }}</span>
                        </div>
                        <div>
                            <h3 class="dc-card-link-title font-bold text-[#0F1B4C] transition">{{ $section['label'] }}</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ $section['desc'] }}</p>
                        </div>
                    </a>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>
