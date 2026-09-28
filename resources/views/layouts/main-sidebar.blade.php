{{--
    الألوان مستخرجة فعليًا من شعار دفتركوم:
    #0F1B4C كحلي غامق (خلفية القائمة)
    #1456E8 أزرق (بداية التدرج)
    #6B2FD6 بنفسجي (نهاية التدرج)
    #F5811E برتقالي (لون التمييز / العنصر النشط)
--}}
<aside
    x-data="sidebarSearchComponent()"
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
    class="fixed lg:static inset-y-0 right-0 z-40 w-64 shrink-0 bg-[#0F1B4C] text-white flex flex-col transition-transform duration-200 ease-in-out">
    <!-- الشعار -->
    <div class="flex items-center justify-center gap-2 py-5 border-b border-white/10">
        <img src="{{ asset('images/sidebar-icon.png') }}" alt="{{ config('app.name', 'NEW VISION') }}" class="h-9 w-9 object-contain">
        <span class="text-white font-bold text-lg">{{ config('app.name', 'NEW VISION') }}</span>
    </div>

    <!-- بطاقة المستخدم -->
    <div class="px-4 py-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="relative shrink-0">
                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#1456E8] to-[#6B2FD6] flex items-center justify-center text-base font-bold ring-2 ring-white/10">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="absolute -bottom-0.5 -end-0.5 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#0F1B4C]"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate">{{ auth()->user()->name ?? '' }}</p>
                <p class="text-xs text-white/50 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>

        <!-- زرار تبديل اللغة (Pill Toggle) -->
        <div class="mt-4 grid grid-cols-2 gap-1 bg-white/5 rounded-full p-1">
            <a href="{{ route('lang.switch', 'ar') }}"
                class="text-center text-xs font-medium py-1.5 rounded-full transition
                      {{ app()->getLocale() === 'ar' ? 'bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white shadow' : 'text-white/60 hover:text-white' }}">
                العربية
            </a>
            <a href="{{ route('lang.switch', 'en') }}"
                class="text-center text-xs font-medium py-1.5 rounded-full transition
                      {{ app()->getLocale() === 'en' ? 'bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white shadow' : 'text-white/60 hover:text-white' }}">
                English
            </a>
        </div>
    </div>

    <!-- صندوق البحث في القائمة الجانبية -->
    <div class="px-3 pt-3 pb-1 border-b border-white/5">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-white/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
            <input type="text"
                x-model="searchQuery"
                @keydown.escape="searchQuery = ''"
                placeholder="{{ __('messages.search_menu') }}"
                class="w-full ps-9 pe-8 py-2 text-xs bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:outline-none focus:ring-1 focus:ring-[#1456E8] focus:border-[#1456E8] transition duration-150">
            <button type="button"
                x-show="searchQuery.trim().length > 0"
                x-cloak
                @click="searchQuery = ''"
                title="مسح البحث"
                class="absolute inset-y-0 end-0 flex items-center pe-2.5 text-white/40 hover:text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- عناصر القائمة -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">

        <!-- الرئيسية -->
        <div x-show="!isSearching() || matches(@js(__('messages.dashboard') . ' dashboard الرئيسية لوحة التحكم'))">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                      {{ request()->routeIs('dashboard')
                            ? 'bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white shadow-md shadow-black/20'
                            : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />
                </svg>
                <span class="text-sm font-medium">{{ __('messages.dashboard') }}</span>
            </a>
        </div>

        @php
        $u = auth()->user();

        $sections = [
        [
        // قسم النقليات: فواتير النقليات + الشاحنات + السائقين
        'label' => __('transport.section'),
        'groups' => [
        [
        'key' => 'transport-loads',
        'label' => __('transport.movement'),
        'icon' => 'clock',
        'items' => array_values(array_filter([
            $u?->hasPermission('truck_loads.view') ? ['label' => __('transport.board_title'), 'url' => route('transport.loads.board')] : null,
            $u?->hasPermission('waybills.create') ? ['label' => __('transport.new_waybill'), 'url' => route('transport.waybills.create')] : null,
            $u?->hasPermission('waybills.view') ? ['label' => __('transport.waybills'), 'url' => route('transport.waybills.index')] : null,
            $u?->hasPermission('truck_loads.report') ? ['label' => __('transport.loads_report'), 'url' => route('transport.loads.report')] : null,
            $u?->hasPermission('truck_loads.view') ? ['label' => __('transport.regions'), 'url' => route('transport.regions.index')] : null,
            $u?->hasPermission('drivers.view') ? ['label' => __('transport.drivers'), 'url' => route('transport.drivers.index')] : null,
        ])),
        ],
        [
        'key' => 'transport-invoices',
        'label' => __('transport.invoices'),
        'icon' => 'cart',
        'items' => array_values(array_filter([
            $u?->hasPermission('transport_invoices.create') ? ['label' => __('transport.new_invoice'), 'url' => route('transport.invoices.create')] : null,
            $u?->hasPermission('transport_invoices.view') ? ['label' => __('transport.invoices'), 'url' => route('transport.invoices.index')] : null,
            $u?->hasPermission('transport_invoices.view') ? ['label' => __('transport.unbilled_loads'), 'url' => route('transport.reports.unbilled')] : null,
            $u?->hasPermission('transport_invoices.view') ? ['label' => __('transport.cn_list'), 'url' => route('transport.credit-notes.index')] : null,
            $u?->hasPermission('transport_invoices.view') ? ['label' => __('transport.drafts'), 'url' => route('transport.invoices.index', ['status' => 'draft'])] : null,
            $u?->hasPermission('zatca.view') ? ['label' => __('transport.zatca_title'), 'url' => route('transport.zatca.index')] : null,
        ])),
        ],
        ],
        ],
        [
        'label' => __('purchases.title'),
        'groups' => [
        [
        'key' => 'purchases',
        'label' => __('purchases.title'),
        'icon' => 'cart',
        'items' => array_values(array_filter([
            $u?->hasPermission('purchases.view') ? ['label' => __('purchases.title'), 'url' => route('purchases.index')] : null,
            $u?->hasPermission('purchases.create') ? ['label' => __('purchases.new_purchase'), 'url' => route('purchases.create')] : null,
            $u?->hasPermission('purchases.orders') ? ['label' => __('purchase_orders.title'), 'url' => route('purchase-orders.index')] : null,
            $u?->hasPermission('purchases.orders') ? ['label' => __('purchase_orders.new_purchase_order'), 'url' => route('purchase-orders.create')] : null,
            $u?->hasPermission('purchases.returns') ? ['label' => __('purchase_returns.new_return'), 'url' => route('purchases.returns.create')] : null,
            $u?->hasPermission('purchases.returns') ? ['label' => __('purchase_returns.previous_returns'), 'url' => route('purchases.returns.index')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم: "الإضافات" - إدارة العملاء والموردين
        'label' => __('messages.additions'),
        'groups' => [
        [
        'key' => 'customers',
        'label' => __('customers.title'),
        'icon' => 'store',
        'items' => array_values(array_filter([
            $u?->hasPermission('customers.view') ? ['label' => __('customers.title'), 'url' => route('customers.index')] : null,
            $u?->hasPermission('customers.create') ? ['label' => __('customers.new_customer'), 'url' => route('customers.create')] : null,
        ])),
        ],
        [
        'key' => 'suppliers',
        'label' => __('suppliers.title'),
        'icon' => 'store',
        'items' => array_values(array_filter([
            $u?->hasPermission('suppliers.view') ? ['label' => __('suppliers.title'), 'url' => route('suppliers.index')] : null,
            $u?->hasPermission('suppliers.create') ? ['label' => __('suppliers.new_supplier'), 'url' => route('suppliers.create')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم "الموارد البشرية"
        'label' => __('employees.hr_section_title'),
        'groups' => [
        [
        'key' => 'hr',
        'match' => ['employees', 'attendance', 'employee-loans', 'asset-custodies', 'leave-requests', 'end-of-service', 'payroll', 'hr-settings'],
        'label' => __('employees.hr_section_title'),
        'icon' => 'user',
        'items' => array_values(array_filter([
            $u?->hasPermission('employees.view') ? ['label' => __('contracts.title'), 'url' => route('contracts.index')] : null,
            $u?->hasPermission('employees.view') ? ['label' => __('notifications.title'), 'url' => route('notifications.index')] : null,
            $u?->hasPermission('employees.view') ? ['label' => __('employees.title'), 'url' => route('employees.index')] : null,
            $u?->hasPermission('employees.create') ? ['label' => __('employees.new_employee'), 'url' => route('employees.create')] : null,
            $u?->hasPermission('employees.create') ? ['label' => __('employees.import_title'), 'url' => route('employees.import.form')] : null,
            $u?->hasPermission('departments.view') ? ['label' => __('employees.departments_title'), 'url' => route('employees.departments.index')] : null,
            $u?->hasPermission('attendance.view') ? ['label' => __('attendance.title'), 'url' => route('attendance.index')] : null,
            $u?->hasPermission('attendance.create') ? ['label' => __('attendance.new_entry'), 'url' => route('attendance.create')] : null,
            $u?->hasPermission('attendance.create') ? ['label' => __('attendance.import_from_biometric'), 'url' => route('attendance.import.form')] : null,
            $u?->hasPermission('employee_loans.view') ? ['label' => __('employee_loans.title'), 'url' => route('employee-loans.index')] : null,
            $u?->hasPermission('asset_custodies.view') ? ['label' => __('asset_custodies.title'), 'url' => route('asset-custodies.index')] : null,
            $u?->hasPermission('employee_custody.view') ? ['label' => __('transport.custody_chart'), 'url' => route('transport.reports.custody')] : null,
            $u?->hasPermission('drivers.view') ? ['label' => __('transport.drivers'), 'url' => route('transport.drivers.index')] : null,
            $u?->hasPermission('leave_requests.view') ? ['label' => __('leave_requests.title'), 'url' => route('leave-requests.index')] : null,
            $u?->hasPermission('end_of_service.view') ? ['label' => __('end_of_service.title'), 'url' => route('end-of-service.index')] : null,
            $u?->hasPermission('end_of_service.view') ? ['label' => __('end_of_service.new_settlement'), 'url' => route('end-of-service.create')] : null,
            $u?->hasPermission('payroll.view') ? ['label' => __('payroll.title'), 'url' => route('payroll.index')] : null,
            $u?->hasPermission('hr_settings.manage') ? ['label' => __('hr_settings.title'), 'url' => route('hr-settings.index')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم: "الشاحنات" (كان قسم المنتجات) - الشاحنات + سندات الصيانة + تقرير الشاحنات
        'label' => __('transport.trucks'),
        'groups' => [
        [
        'key' => 'trucks',
        'label' => __('transport.trucks'),
        'icon' => 'box',
        'items' => array_values(array_filter([
            $u?->hasPermission('trucks.view') ? ['label' => __('transport.trucks'), 'url' => route('transport.trucks.index')] : null,
            $u?->hasPermission('trucks.create') ? ['label' => __('transport.new_truck'), 'url' => route('transport.trucks.create')] : null,
            $u?->hasPermission('maintenance.create') ? ['label' => __('transport.new_maintenance'), 'url' => route('vouchers.create', ['type' => 'payment', 'maintenance' => 1])] : null,
            $u?->hasPermission('maintenance.view') ? ['label' => __('transport.maintenance_vouchers'), 'url' => route('vouchers.index', ['type' => 'payment', 'maintenance' => 1])] : null,
            $u?->hasPermission('maintenance.view') ? ['label' => __('transport.maintenance_report'), 'url' => route('transport.reports.maintenance')] : null,
            $u?->hasPermission('transport_reports.fleet') ? ['label' => __('transport.fleet_report'), 'url' => route('transport.reports.fleet')] : null,
            $u?->hasPermission('transport_reports.fleet') ? ['label' => __('transport.truck_statement'), 'url' => route('transport.reports.truck')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم: "المحاسبة والفواتير"
        'label' => __('messages.accounting_invoices'),
        'groups' => [
        [
        'key' => 'quotations',
        'label' => __('quotations.title'),
        'icon' => 'tag',
        'items' => array_values(array_filter([
            // عروض الأسعار بقت عروض أسعار نقليات (مسار/نوع شاحنة/سعر النقلة/تحويلة)
            $u?->hasPermission('transport_quotations.view') ? ['label' => __('transport.quotations'), 'url' => route('transport.quotations.index')] : null,
            $u?->hasPermission('transport_quotations.create') ? ['label' => __('transport.new_quotation'), 'url' => route('transport.quotations.create')] : null,
        ])),
        ],
        [
        'key' => 'zatca',
        'label' => __('zatca.title'),
        'icon' => 'doc',
        'items' => array_values(array_filter([
            $u?->hasPermission('zatca.view') ? ['label' => __('zatca.not_sent'), 'url' => route('transport.zatca.index', ['sent' => 0])] : null,
            $u?->hasPermission('zatca.view') ? ['label' => __('zatca.sent'), 'url' => route('transport.zatca.index', ['sent' => 1])] : null,
        ])),
        ],
        [
        'key' => 'accounts',
        'label' => __('accounts.title'),
        'icon' => 'ledger',
        'items' => array_values(array_filter([
            $u?->hasPermission('accounts.view') ? ['label' => __('accounts.list_title'), 'url' => route('accounts.index')] : null,
            $u?->hasPermission('accounts.view') ? ['label' => __('accounts.tree_title'), 'url' => route('accounts.tree')] : null,
            $u?->hasPermission('accounts.create') ? ['label' => __('accounts.new_account'), 'url' => route('accounts.create')] : null,
            $u?->hasPermission('accounts.view') ? ['label' => __('account_types.title'), 'url' => route('account-types.index')] : null,
        ])),
        ],
        [
        'key' => 'journal-entries',
        'label' => __('journal_entries.group_title'),
        'icon' => 'doc',
        'items' => array_values(array_filter([
            $u?->hasPermission('journal_entries.view') ? ['label' => __('journal_entries.daily_title'), 'url' => route('journal-entries.index', ['type' => 'daily'])] : null,
            $u?->hasPermission('journal_entries.create') ? ['label' => __('journal_entries.new_daily_entry'), 'url' => route('journal-entries.create', ['type' => 'daily'])] : null,
            $u?->hasPermission('journal_entries.view') ? ['label' => __('journal_entries.opening_title'), 'url' => route('journal-entries.index', ['type' => 'opening'])] : null,
            $u?->hasPermission('journal_entries.create') ? ['label' => __('journal_entries.new_opening_entry'), 'url' => route('journal-entries.create', ['type' => 'opening'])] : null,
            $u?->hasPermission('year_closing.manage') ? ['label' => app()->getLocale() === 'ar' ? 'إقفال السنة المالية' : 'Fiscal year closing', 'url' => route('year-closing.index')] : null,
        ])),
        ],
        [
        'key' => 'vouchers',
        'label' => __('vouchers.title'),
        'icon' => 'tag',
        'items' => array_values(array_filter([
            $u?->hasPermission('vouchers.view') ? ['label' => __('vouchers.receipt_title'), 'url' => route('vouchers.index', ['type' => 'receipt'])] : null,
            $u?->hasPermission('vouchers.create') ? ['label' => __('vouchers.new_receipt'), 'url' => route('vouchers.create', ['type' => 'receipt'])] : null,
            $u?->hasPermission('vouchers.view') ? ['label' => __('vouchers.payment_title'), 'url' => route('vouchers.index', ['type' => 'payment'])] : null,
            $u?->hasPermission('vouchers.create') ? ['label' => __('vouchers.new_payment'), 'url' => route('vouchers.create', ['type' => 'payment'])] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم: "التقارير"
        'label' => __('reports.section_title'),
        'groups' => [
        [
        'key' => 'reports',
        'label' => __('reports.section_title'),
        'icon' => 'doc',
        'items' => array_values(array_filter([
            ($u?->hasPermission('truck_loads.report')
                || $u?->hasPermission('transport_reports.fleet')
                || $u?->hasPermission('transport_invoices.view')
                || $u?->hasPermission('reports_accounting.trial_balance')
                || $u?->hasPermission('reports_accounting.balance_sheet')
                || $u?->hasPermission('reports_accounting.income_statement')
                || $u?->hasPermission('reports_accounting.equity_changes')
                || $u?->hasPermission('reports_accounting.cash_flow')
                || $u?->hasPermission('reports_purchases.summary')
                || $u?->hasPermission('reports_purchases.by_supplier')
                || $u?->hasPermission('reports_purchases.by_employee')
                || $u?->hasPermission('reports_purchases.by_product')
                || $u?->hasPermission('reports_purchases.returns')
                || $u?->hasPermission('reports_products.stock')
                || $u?->hasPermission('reports_products.low_stock')
                || $u?->hasPermission('reports_hr.payroll')
                || $u?->hasPermission('reports_hr.attendance')
                || $u?->hasPermission('reports_hr.loans')
                || $u?->hasPermission('reports_hr.employees')
                || $u?->hasPermission('reports_hr.bonuses_deductions')
                || $u?->hasPermission('reports_hr.leaves')
)
                ? ['label' => __('reports.hub_title'), 'url' => route('reports.index')] : null,
            ($u?->hasPermission('truck_loads.report') || $u?->hasPermission('transport_reports.fleet') || $u?->hasPermission('transport_invoices.view'))
                ? ['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')] : null,
            $u?->hasPermission('reports_accounting.trial_balance') ? ['label' => __('reports.accounts.trial_balance'), 'url' => route('reports.accounts.trial-balance')] : null,
            $u?->hasPermission('reports_accounting.income_statement') ? ['label' => __('reports.accounts.income_statement'), 'url' => route('reports.accounts.income-statement')] : null,
            $u?->hasPermission('reports_accounting.balance_sheet') ? ['label' => __('reports.accounts.balance_sheet'), 'url' => route('reports.accounts.balance-sheet')] : null,
            $u?->hasPermission('reports_accounting.equity_changes') ? ['label' => __('reports.accounts.equity_changes'), 'url' => route('reports.accounts.equity-changes')] : null,
            $u?->hasPermission('reports_accounting.cash_flow') ? ['label' => __('reports.accounts.cash_flow'), 'url' => route('reports.accounts.cash-flow')] : null,
            ($u?->hasPermission('reports_purchases.summary') || $u?->hasPermission('reports_purchases.by_supplier')
                || $u?->hasPermission('reports_purchases.by_employee') || $u?->hasPermission('reports_purchases.by_product')
                || $u?->hasPermission('reports_purchases.returns'))
                ? ['label' => __('reports.sections.purchases'), 'url' => route('reports.purchases.index')] : null,
            ($u?->hasPermission('reports_products.stock') || $u?->hasPermission('reports_products.low_stock'))
                ? ['label' => __('reports.sections.products'), 'url' => route('reports.products.index')] : null,
            ($u?->hasPermission('reports_hr.payroll') || $u?->hasPermission('reports_hr.attendance')
                || $u?->hasPermission('reports_hr.loans') || $u?->hasPermission('reports_hr.employees')
                || $u?->hasPermission('reports_hr.bonuses_deductions') || $u?->hasPermission('reports_hr.leaves'))
                ? ['label' => __('reports.sections.hr'), 'url' => route('reports.hr.index')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم: "الإعدادات والضرائب"
        'label' => __('settings.title'),
        'groups' => [
        [
        'key' => 'settings',
        'label' => __('settings.title'),
        'icon' => 'gear',
        'items' => array_values(array_filter([
            $u?->hasPermission('settings.manage') ? ['label' => __('settings.title'), 'url' => route('settings.index')] : null,
            $u?->hasPermission('settings.employee_discounts') ? ['label' => __('settings.employee_discounts_title'), 'url' => route('employee-discounts.index')] : null,
            $u?->hasPermission('settings.taxes') ? ['label' => __('taxes.title'), 'url' => route('taxes.index')] : null,
        ])),
        ],
        ],
        ],
        ];

        // قسم "الإدارة" (مستخدمين/فروع/أدوار وصلاحيات)
        $adminItems = [];
        if ($u?->hasPermission('users.view')) {
            $adminItems[] = ['label' => __('users.title'), 'url' => route('users.index')];
        }
        if ($u?->hasPermission('branches.view')) {
            $adminItems[] = ['label' => __('branches.title'), 'url' => route('branches.index')];
        }
        if ($u?->hasPermission('roles.manage')) {
            $adminItems[] = ['label' => __('roles.title'), 'url' => route('roles.index')];
        }
        if (! empty($adminItems)) {
            $sections[] = [
                'label' => __('messages.administration'),
                'groups' => [
                    [
                        'key' => 'administration',
                        'label' => __('messages.administration'),
                        'icon' => 'shield',
                        'items' => $adminItems,
                    ],
                ],
            ];
        }

        // تنظيف عام: حذف المجموعات الفارغة والأقسام الفارغة تلقائياً وفقاً للصلاحيات
        foreach ($sections as $sIndex => $section) {
            $sections[$sIndex]['groups'] = array_values(array_filter($section['groups'], fn ($g) => ! empty($g['items'])));
        }
        $sections = array_values(array_filter($sections, fn ($s) => ! empty($s['groups'])));

        // تجميع مصفوفة الكلمات المفتاحية للبحث السريع وتحديد حالة عدم وجود نتائج
        $allSearchableItems = [
            __('messages.dashboard') . ' dashboard الرئيسية لوحة التحكم',
            __('Profile') . ' profile الملف الشخصي',
        ];
        foreach ($sections as $sec) {
            $allSearchableItems[] = $sec['label'];
            foreach ($sec['groups'] as $grp) {
                $allSearchableItems[] = $sec['label'] . ' ' . $grp['label'];
                foreach ($grp['items'] as $itm) {
                    $allSearchableItems[] = $sec['label'] . ' ' . $grp['label'] . ' ' . $itm['label'];
                }
            }
        }

        $icons = [
        'bag' => '
        <path d="M6 8h12l-1 12H7L6 8Z" />
        <path d="M9 8V6a3 3 0 0 1 6 0v2" />',
        'cart' => '
        <circle cx="9" cy="20" r="1" />
        <circle cx="17" cy="20" r="1" />
        <path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .8h8.4a1 1 0 0 0 1-.8L20 8H6" />',
        'doc' => '
        <rect x="6" y="3" width="12" height="18" rx="1" />
        <path d="M9 8h6M9 12h6M9 16h4" />',
        'box' => '
        <path d="M3 8l9-5 9 5-9 5-9-5Z" />
        <path d="M3 8v8l9 5 9-5V8" />
        <path d="M12 13v8" />',
        'store' => '
        <path d="M4 21V10l8-6 8 6v11" />
        <path d="M9 21v-6h6v6" />',
        'gear' => '
        <circle cx="12" cy="12" r="3" />
        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />',
        'return' => '
        <path d="M3 7v6h6" />
        <path d="M3 13a9 9 0 1 0 3-6.7L3 9" />',
        'tag' => '
        <path d="M20.6 12.6 12.6 20.6a2 2 0 0 1-2.83 0l-6.37-6.37a2 2 0 0 1 0-2.83L11.4 3.4A2 2 0 0 1 12.8 2.8H19a2 2 0 0 1 2 2v6.2a2 2 0 0 1-.4 1.2Z" />
        <circle cx="16.5" cy="7.5" r="1.5" />',
        'ledger' => '
        <path d="M4 21V6a2 2 0 0 1 2-2h9l5 5v12a0 0 0 0 1 0 0H6a2 2 0 0 1-2-2Z" />
        <path d="M15 4v4a1 1 0 0 0 1 1h4" />
        <path d="M8 12h8M8 16h5" />',
        'user' => '
        <circle cx="12" cy="8" r="3.5" />
        <path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />',
        'clock' => '
        <circle cx="12" cy="12" r="9" />
        <path d="M12 7v5l3 3" />',
        'wallet' => '
        <path d="M3 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" />
        <path d="M16 12h3" />',
        'shield' => '
        <path d="M12 2 4 5v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V5l-8-3Z" />
        <path d="M9 12l2 2 4-4" />',
        ];
        @endphp

        @foreach ($sections as $section)
        @php
            $sectionCombinedText = $section['label'] . ' ' . collect($section['groups'])->map(function($g) {
                return $g['label'] . ' ' . collect($g['items'])->pluck('label')->implode(' ');
            })->implode(' ');
        @endphp
        <div x-show="!isSearching() || matches(@js($sectionCombinedText))">
            <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wider text-white/35">
                {{ $section['label'] }}
            </p>
            <div class="space-y-1">
                @foreach ($section['groups'] as $group)
                @php
                    // 'match' اختياري - لو المجموعة مدموجة من أكتر من
                    // شاشة (زي الموارد البشرية) بتحمل كل الـ prefixes
                    // القديمة، وإلا بيترجع لسلوك $group['key'] المفرد.
                    $groupIsActive = collect($group['match'] ?? [$group['key']])
                        ->contains(fn ($prefix) => request()->is($prefix . '*'));
                    $groupLabelsText = $section['label'] . ' ' . $group['label'];
                    $allItemsText = collect($group['items'])->pluck('label')->implode(' ');
                    $groupCombinedText = $groupLabelsText . ' ' . $allItemsText;
                @endphp
                <div x-data="{
                        activeOpen: {{ $groupIsActive ? 'true' : 'false' }},
                        userToggled: null,
                        get isOpen() {
                            if (isSearching()) {
                                return matches(@js($groupCombinedText));
                            }
                            return this.userToggled !== null ? this.userToggled : this.activeOpen;
                        },
                        toggle() {
                            this.userToggled = !this.isOpen;
                        }
                    }"
                    x-show="!isSearching() || matches(@js($groupCombinedText))">
                    <button type="button" @click="toggle()"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-gray-300 hover:bg-white/5 hover:text-white transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                {!! $icons[$group['icon']] !!}
                            </svg>
                            <span class="text-sm font-medium">{{ $group['label'] }}</span>
                        </span>
                        <svg :class="isOpen ? '-rotate-180' : ''" class="w-4 h-4 text-white/40 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="isOpen" x-cloak class="mt-1 me-4 pe-3 border-e-2 border-white/10 space-y-1">
                        @foreach ($group['items'] as $item)
                        @php
                            $itemCombinedText = $section['label'] . ' ' . $group['label'] . ' ' . $item['label'];
                        @endphp
                        <a href="{{ $item['url'] }}"
                            x-show="!isSearching() || matches(@js($itemCombinedText)) || matches(@js($groupLabelsText))"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-white/50 hover:bg-white/5 hover:text-white transition">
                            <span class="w-1 h-1 rounded-full bg-[#F5811E]"></span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- رسالة عدم وجود نتائج مطابقة للبحث -->
        <div x-show="isSearching() && !hasAnyResults()" x-cloak class="px-4 py-8 text-center text-white/40">
            <svg class="w-8 h-8 mx-auto mb-2 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <p class="text-sm font-medium text-white/70">{{ __('messages.no_search_results') }}</p>
            <p class="text-xs text-white/40 mt-1.5 dir-ltr truncate" x-text="'« ' + searchQuery + ' »'"></p>
        </div>
    </nav>

    <!-- تسجيل الخروج -->
    <div class="border-t border-white/10 p-3">
        <a href="{{ route('profile.edit') }}"
            x-show="!isSearching() || matches(@js(__('Profile') . ' profile الملف الشخصي'))"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="3.5" />
                <path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />
            </svg>
            {{ __('Profile') }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="M16 17l5-5-5-5" />
                    <path d="M21 12H9" />
                </svg>
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</aside>

<!-- كود جافاسكريبت للبحث الذكي في القائمة الجانبية مع معالجة الحروف العربية -->
<script>
    function sidebarSearchComponent() {
        return {
            searchQuery: '',
            allSearchItems: @js($allSearchableItems),
            normalize(str) {
                if (!str) return '';
                return str
                    .toString()
                    .toLowerCase()
                    .trim()
                    .replace(/[\u064B-\u065F\u0670]/g, '') // إزالة الحركات والتشكيل
                    .replace(/[أإآٱ]/g, 'ا')              // توحيد الألفات
                    .replace(/ة/g, 'ه')                  // توحيد التاء المربوطة
                    .replace(/ى/g, 'ي')                  // توحيد الياء
                    .replace(/[\s\-_]+/g, ' ');
            },
            matches(haystack) {
                if (!this.searchQuery || !this.searchQuery.trim()) return true;
                const q = this.normalize(this.searchQuery);
                const h = this.normalize(haystack);
                const tokens = q.split(' ').filter(t => t.length > 0);
                return tokens.every(token => h.includes(token));
            },
            isSearching() {
                return Boolean(this.searchQuery && this.searchQuery.trim().length > 0);
            },
            hasAnyResults() {
                if (!this.isSearching()) return true;
                return this.allSearchItems.some(item => this.matches(item));
            }
        };
    }
</script>

<!-- طبقة تظليل لإغلاق القائمة على الموبايل -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div>