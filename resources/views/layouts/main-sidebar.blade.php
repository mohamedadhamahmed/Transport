{{--
    الألوان مستخرجة فعليًا من شعار دفتركوم:
    #0F1B4C كحلي غامق (خلفية القائمة)
    #1456E8 أزرق (بداية التدرج)
    #6B2FD6 بنفسجي (نهاية التدرج)
    #F5811E برتقالي (لون التمييز / العنصر النشط)
--}}
<aside
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

    <!-- عناصر القائمة -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">

        <!-- الرئيسية -->
        <div>
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
        // كل عنصر فيه 'url' حقيقي بيروح لصفحته الفعلية، وأي عنصر لسه
        // 'url' => '#' يبقى Placeholder لحد ما نبني صفحته.
        $sections = [
        [
        'label' => __('messages.sales'),
        'groups' => [
        [
        'key' => 'invoices',
        'label' => __('messages.sales'),
        'icon' => 'bag',
        'items' => [
        ['label' => __('invoices.title'), 'url' => route('invoices.index')],
        ['label' => __('invoices.new_invoice'), 'url' => route('invoices.create')],
        ['label' => __('invoices.previous_drafts'), 'url' => route('invoices.drafts.index')],
        ['label' => __('invoices.sales_return'), 'url' => route('invoices.returns.create')],
        ['label' => __('invoices.previous_returns'), 'url' => route('invoices.returns.index')],


        ],
        ],
        [
        'key' => 'quotations',
        'label' => __('quotations.title'),
        'icon' => 'tag',
        'items' => [
        ['label' => __('quotations.title'), 'url' => route('quotations.index')],
        ['label' => __('quotations.new_quotation'), 'url' => route('quotations.create')],
        ],
        ],
        [
        'key' => 'zatca',
        'label' => __('zatca.title'),
        'icon' => 'doc',
        'items' => [
        ['label' => __('zatca.not_sent'), 'url' => route('zatca.index', ['sent' => 0])],
        ['label' => __('zatca.sent'), 'url' => route('zatca.index', ['sent' => 1])],
        ],
        ],
        ],
        ],
        [
        'label' => __('delivery.title') ?? __('delivery.delivery_product'),
        'groups' => [
        [
        'key' => 'delivery',
        'label' => __('delivery.delivery_product'),
        'icon' => 'clock',
        'items' => [
        ['label' => __('delivery.delivery_product'), 'url' => route('delivery.create')],
        ['label' => __('delivery.delivery_history'), 'url' => route('delivery.history')],
        ],
        ],
        ],
        ],
         [
        // قائمة منفصلة ثانية: "سند تسليم" (Delivery Note) - النظام الجديد
        // (تسجيل معلّق، ثم اعتماد وتحويل لفاتورة ضريبية حقيقية).
        'label' => __('deliverynote.title'),
        'groups' => [
        [
        'key' => 'delivery-note',
        'label' => __('deliverynote.title'),
        'icon' => 'box',
        'items' => [
        ['label' => __('deliverynote.delivery_product'), 'url' => route('deliverynote.create')],
        ['label' => __('deliverynote.delivery_history'), 'url' => route('deliverynote.history')],
        ['label' => __('deliverynote.approve_and_invoice'), 'url' => route('deliverynote.convert.index')],
        ],
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
        'items' => [
        ['label' => __('purchases.title'), 'url' => route('purchases.index')],
        ['label' => __('purchases.new_purchase'), 'url' => route('purchases.create')],
        ['label' => __('purchase_orders.title'), 'url' => route('purchase-orders.index')],
        ['label' => __('purchase_orders.new_purchase_order'), 'url' => route('purchase-orders.create')],
        ['label' => __('purchase_returns.new_return'), 'url' => route('purchases.returns.create')],
        ['label' => __('purchase_returns.previous_returns'), 'url' => route('purchases.returns.index')],
        ],
        ],
        ],
        ],
        [
        // قسم جديد: "الإضافات" - إدارة العملاء والموردين (إضافة/تعديل).
        'label' => __('messages.additions') ?? 'الإضافات',
        'groups' => [
        [
        'key' => 'customers',
        'label' => __('customers.title'),
        'icon' => 'store',
        'items' => array_values(array_filter([
        auth()->user()?->hasPermission('customers.view') ? ['label' => __('customers.title'), 'url' => route('customers.index')] : null,
        auth()->user()?->hasPermission('customers.create') ? ['label' => __('customers.new_customer'), 'url' => route('customers.create')] : null,
        ])),
        ],
        [
        'key' => 'suppliers',
        'label' => __('suppliers.title'),
        'icon' => 'store',
        'items' => array_values(array_filter([
        auth()->user()?->hasPermission('suppliers.view') ? ['label' => __('suppliers.title'), 'url' => route('suppliers.index')] : null,
        auth()->user()?->hasPermission('suppliers.create') ? ['label' => __('suppliers.new_supplier'), 'url' => route('suppliers.create')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم "الموارد البشرية" - كان قبل كده 8 مجموعات (أكورديون) منفصلة
        // تحت بعض، دلوقتي مدموجين في مجموعة واحدة بس (زي "المشتريات"
        // بالظبط) - زرار واحد يفتح كل حاجة تحته. الـ 'match' بتحمل كل
        // الـ prefixes القديمة عشان الأكورديون يتفتح تلقائي لو المستخدم
        // داخل أي شاشة من شاشات الموارد البشرية (راجع استخدامها تحت في
        // نفس الملف بدل $group['key'] المفرد).
        'label' => __('employees.hr_section_title'),
        'groups' => [
        [
        'key' => 'hr',
        'match' => ['employees', 'attendance', 'employee-loans', 'asset-custodies', 'leave-requests', 'end-of-service', 'payroll', 'hr-settings'],
        'label' => __('employees.hr_section_title'),
        'icon' => 'user',
        'items' => [
        ['label' => __('contracts.title'), 'url' => route('contracts.index')],
        ['label' => __('notifications.title'), 'url' => route('notifications.index')],
        ['label' => __('employees.title'), 'url' => route('employees.index')],
        ['label' => __('employees.new_employee'), 'url' => route('employees.create')],
        ['label' => __('employees.import_title'), 'url' => route('employees.import.form')],
        ['label' => __('attendance.title'), 'url' => route('attendance.index')],
        ['label' => __('attendance.new_entry'), 'url' => route('attendance.create')],
        ['label' => __('attendance.import_from_biometric'), 'url' => route('attendance.import.form')],
        ['label' => __('employee_loans.title'), 'url' => route('employee-loans.index')],
        ['label' => __('asset_custodies.title'), 'url' => route('asset-custodies.index')],
        ['label' => __('leave_requests.title'), 'url' => route('leave-requests.index')],
        ['label' => __('end_of_service.title'), 'url' => route('end-of-service.index')],
        ['label' => __('end_of_service.new_settlement'), 'url' => route('end-of-service.create')],
        ['label' => __('payroll.title'), 'url' => route('payroll.index')],
        ['label' => __('hr_settings.title'), 'url' => route('hr-settings.index')],
        ],
        ],
        ],
        ],
        [
        // قسم جديد: "المنتجات والمخزون" - نقطة الدخول دايمًا "اختيار
        // الفرع" لأنه إجباري قبل أي عرض/تعديل للمنتجات.
        'label' => __('products.title') ?? 'المنتجات والمخزون',
        'groups' => [
        [
        'key' => 'products',
        'label' => __('products.all_products'),
        'icon' => 'box',
        'items' => [
        ['label' => __('products.all_products'), 'url' => route('products.choose_branch')],
        ['label' => __('products.add_group') ?? 'إضافة مجموعة منتجات', 'url' => route('product-groups.create')],
        ],
        ],
        ],
        ],
        [
        // قسم جديد: "المحاسبة والفواتير" - شجرة الحسابات، القيد اليومي،
        // سندات القبض والصرف. رابط accounts.search مش موجود هنا لإنه
        // endpoint بحث AJAX داخلي بس (مش صفحة).
        'label' => __('messages.accounting_invoices'),
        'groups' => [
        [
        'key' => 'accounts',
        'label' => __('accounts.title'),
        'icon' => 'ledger',
        'items' => [
        ['label' => __('accounts.list_title'), 'url' => route('accounts.index')],
        ['label' => __('accounts.tree_title'), 'url' => route('accounts.tree')],
        ['label' => __('accounts.new_account'), 'url' => route('accounts.create')],
        ['label' => __('account_types.title'), 'url' => route('account-types.index')],
        ],
        ],
        [
        'key' => 'journal-entries',
        'label' => __('journal_entries.group_title'),
        'icon' => 'doc',
        'items' => [
        ['label' => __('journal_entries.daily_title'), 'url' => route('journal-entries.index', ['type' => 'daily'])],
        ['label' => __('journal_entries.new_daily_entry'), 'url' => route('journal-entries.create', ['type' => 'daily'])],
        ['label' => __('journal_entries.opening_title'), 'url' => route('journal-entries.index', ['type' => 'opening'])],
        ['label' => __('journal_entries.new_opening_entry'), 'url' => route('journal-entries.create', ['type' => 'opening'])],
        ],
        ],
        [
        'key' => 'vouchers',
        'label' => __('vouchers.title'),
        'icon' => 'tag',
        'items' => [
        ['label' => __('vouchers.receipt_title'), 'url' => route('vouchers.index', ['type' => 'receipt'])],
        ['label' => __('vouchers.new_receipt'), 'url' => route('vouchers.create', ['type' => 'receipt'])],
        ['label' => __('vouchers.payment_title'), 'url' => route('vouchers.index', ['type' => 'payment'])],
        ['label' => __('vouchers.new_payment'), 'url' => route('vouchers.create', ['type' => 'payment'])],
        ],
        ],
        ],
        ],
        [
        // قسم جديد: "التقارير" - مركز تقارير موحّد لكل أقسام النظام
        // (حسابات/مبيعات/تسليم منتج/مشتريات/منتجات/موارد بشرية). كل
        // تقرير جوه دلوقتي ليه صلاحيته المستقلة بنفسه (reports_*.* في
        // config/permissions.php)، فالرابط بيظهر بس لو المستخدم عنده
        // صلاحية التقرير ده بالذات - راجع ReportController.
        'label' => __('reports.section_title'),
        'groups' => [
        [
        'key' => 'reports',
        'label' => __('reports.section_title'),
        'icon' => 'doc',
        'items' => array_values(array_filter([
        (auth()->user()?->hasPermission('reports_accounting.trial_balance')
            || auth()->user()?->hasPermission('reports_accounting.balance_sheet')
            || auth()->user()?->hasPermission('reports_accounting.income_statement')
            || auth()->user()?->hasPermission('reports_accounting.equity_changes')
            || auth()->user()?->hasPermission('reports_accounting.cash_flow')
            || auth()->user()?->hasPermission('reports_sales.summary')
            || auth()->user()?->hasPermission('reports_sales.by_customer')
            || auth()->user()?->hasPermission('reports_sales.by_employee')
            || auth()->user()?->hasPermission('reports_sales.by_product')
            || auth()->user()?->hasPermission('reports_sales.returns')
            || auth()->user()?->hasPermission('reports_purchases.summary')
            || auth()->user()?->hasPermission('reports_purchases.by_supplier')
            || auth()->user()?->hasPermission('reports_purchases.by_employee')
            || auth()->user()?->hasPermission('reports_purchases.by_product')
            || auth()->user()?->hasPermission('reports_purchases.returns')
            || auth()->user()?->hasPermission('reports_products.stock')
            || auth()->user()?->hasPermission('reports_products.low_stock')
            || auth()->user()?->hasPermission('reports_products.stock_transfers')
            || auth()->user()?->hasPermission('reports_hr.payroll')
            || auth()->user()?->hasPermission('reports_hr.attendance')
            || auth()->user()?->hasPermission('reports_hr.loans')
            || auth()->user()?->hasPermission('reports_hr.employees')
            || auth()->user()?->hasPermission('reports_hr.bonuses_deductions')
            || auth()->user()?->hasPermission('reports_hr.leaves')
            || auth()->user()?->hasPermission('reports_delivery.summary')
            || auth()->user()?->hasPermission('reports_delivery.pending')
            || auth()->user()?->hasPermission('reports_delivery.by_employee'))
            ? ['label' => __('reports.hub_title'), 'url' => route('reports.index')] : null,
        auth()->user()?->hasPermission('reports_accounting.trial_balance') ? ['label' => __('reports.accounts.trial_balance'), 'url' => route('reports.accounts.trial-balance')] : null,
        auth()->user()?->hasPermission('reports_accounting.income_statement') ? ['label' => __('reports.accounts.income_statement'), 'url' => route('reports.accounts.income-statement')] : null,
        auth()->user()?->hasPermission('reports_accounting.balance_sheet') ? ['label' => __('reports.accounts.balance_sheet'), 'url' => route('reports.accounts.balance-sheet')] : null,
        auth()->user()?->hasPermission('reports_accounting.equity_changes') ? ['label' => __('reports.accounts.equity_changes'), 'url' => route('reports.accounts.equity-changes')] : null,
        auth()->user()?->hasPermission('reports_accounting.cash_flow') ? ['label' => __('reports.accounts.cash_flow'), 'url' => route('reports.accounts.cash-flow')] : null,
        (auth()->user()?->hasPermission('reports_sales.summary') || auth()->user()?->hasPermission('reports_sales.by_customer')
            || auth()->user()?->hasPermission('reports_sales.by_employee') || auth()->user()?->hasPermission('reports_sales.by_product')
            || auth()->user()?->hasPermission('reports_sales.returns'))
            ? ['label' => __('reports.sections.sales'), 'url' => route('reports.sales.index')] : null,
        (auth()->user()?->hasPermission('reports_delivery.summary') || auth()->user()?->hasPermission('reports_delivery.pending')
            || auth()->user()?->hasPermission('reports_delivery.by_employee'))
            ? ['label' => __('reports.sections.delivery'), 'url' => route('reports.delivery.index')] : null,
        (auth()->user()?->hasPermission('reports_purchases.summary') || auth()->user()?->hasPermission('reports_purchases.by_supplier')
            || auth()->user()?->hasPermission('reports_purchases.by_employee') || auth()->user()?->hasPermission('reports_purchases.by_product')
            || auth()->user()?->hasPermission('reports_purchases.returns'))
            ? ['label' => __('reports.sections.purchases'), 'url' => route('reports.purchases.index')] : null,
        (auth()->user()?->hasPermission('reports_products.stock') || auth()->user()?->hasPermission('reports_products.low_stock')
            || auth()->user()?->hasPermission('reports_products.stock_transfers'))
            ? ['label' => __('reports.sections.products'), 'url' => route('reports.products.index')] : null,
        (auth()->user()?->hasPermission('reports_hr.payroll') || auth()->user()?->hasPermission('reports_hr.attendance')
            || auth()->user()?->hasPermission('reports_hr.loans') || auth()->user()?->hasPermission('reports_hr.employees')
            || auth()->user()?->hasPermission('reports_hr.bonuses_deductions') || auth()->user()?->hasPermission('reports_hr.leaves'))
            ? ['label' => __('reports.sections.hr'), 'url' => route('reports.hr.index')] : null,
        ])),
        ],
        ],
        ],
        [
        // قسم جديد: "المستودعات" - تحويل منتجات بين فروع الشركة (سند
        // صرف + سند استلام)، منفصل تمامًا عن سندات التسليم للعميل.
        'label' => __('stock_transfers.title'),
        'groups' => [
        [
        'key' => 'stock-transfers',
        'label' => __('stock_transfers.title'),
        'icon' => 'box',
        'items' => [
        ['label' => __('stock_transfers.new_dispatch'), 'url' => route('stock-transfers.choose-branch', ['mode' => 'dispatch'])],
        ['label' => __('stock_transfers.box_sent'), 'url' => route('stock-transfers.index', ['box' => 'sent'])],
        ['label' => __('stock_transfers.new_receive'), 'url' => route('stock-transfers.choose-branch', ['mode' => 'receive'])],
        ['label' => __('stock_transfers.box_received'), 'url' => route('stock-transfers.index', ['box' => 'received'])],
        ['label' => __('stock_transfers.box_draft'), 'url' => route('stock-transfers.index', ['box' => 'draft'])],
        ],
        ],
        ],
        ],
        // <-- حط قسم التصنيع هنا
        [
        'label' => __('manufacturing.manufacturing'),
        'groups' => [
            [
                'key' => 'manufacturing',
                'match' => ['manufacturing'],
                'label' => __('manufacturing.manufacturing'),
                'icon' => 'box',
                'items' => [
                    ['label' => __('manufacturing.workstations_title'), 'url' => route('manufacturing.workstations.index')],
                    ['label' => __('manufacturing.statuses_title'), 'url' => route('manufacturing.statuses.index')],
                    ['label' => __('manufacturing.bom_title'), 'url' => route('manufacturing.bom.index')],
                    ['label' => __('manufacturing.plans_title'), 'url' => route('manufacturing.production-plans.index')],
                    ['label' => __('manufacturing.orders_title'), 'url' => route('manufacturing.orders.index')],
                ],
            ],
        ],
        ],
        [
        'label' => __('settings.title'),
        'groups' => [
        [
        'key' => 'settings',
        'label' => __('settings.title'),
        'icon' => 'gear',
        'items' => [
        ['label' => __('settings.title'), 'url' => route('settings.index')],
        ['label' => __('settings.employee_discounts_title'), 'url' => route('employee-discounts.index')],
        ['label' => __('taxes.title'), 'url' => route('taxes.index')], // <-- تم إضافة رابط الضرائب هنا
        ],
        ],
        ],
        ],
        ];

        // قسم "الإدارة" (مستخدمين/فروع/أدوار وصلاحيات) - بيظهر بس لو
        // المستخدم عنده صلاحية وحدة على الأقل من التلاتة دي (عادةً
        // المدير العام بس، حسب نظام الصلاحيات الجديد). لو مالوش أي
        // صلاحية منهم، القسم كله مش بيتضاف لـ $sections أصلًا.
        $adminItems = [];
        if (auth()->user()?->hasPermission('users.view')) {
            $adminItems[] = ['label' => __('users.title'), 'url' => route('users.index')];
        }
        if (auth()->user()?->hasPermission('branches.view')) {
            $adminItems[] = ['label' => __('branches.title'), 'url' => route('branches.index')];
        }
        if (auth()->user()?->hasPermission('roles.manage')) {
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

        // تنضيف عام: أي مجموعة (group) صلاحيات المستخدم خلّت الـ items
        // بتاعتها فاضية (يعني مالوش صلاحية ولا حاجة فيها) بتتشال
        // تلقائيًا من القائمة، وأي قسم (section) خلصت كل مجموعاته فاضية
        // بيتشال هو كمان - عشان القائمة الجانبية تفضل نضيفة ومطابقة
        // لصلاحيات كل مستخدم (حاليًا بيطبّق ده على مجموعتي العملاء
        // والموردين بس - باقي الأقسام لسه بتظهر لأي مستخدم مسجّل دخول).
        foreach ($sections as $sIndex => $section) {
        $sections[$sIndex]['groups'] = array_values(array_filter($section['groups'], fn ($g) => ! empty($g['items'])));
        }
        $sections = array_values(array_filter($sections, fn ($s) => ! empty($s['groups'])));

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
        <div>
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
                @endphp
                <div x-data="{ open: {{ $groupIsActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-gray-300 hover:bg-white/5 hover:text-white transition">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                {!! $icons[$group['icon']] !!}
                            </svg>
                            <span class="text-sm font-medium">{{ $group['label'] }}</span>
                        </span>
                        <svg :class="open ? '-rotate-180' : ''" class="w-4 h-4 text-white/40 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 me-4 pe-3 border-e-2 border-white/10 space-y-1">
                        @foreach ($group['items'] as $item)
                        <a href="{{ $item['url'] }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-white/50 hover:bg-white/5 hover:text-white transition">
                            <span class="w-1 h-1 rounded-full bg-[#F5811E]"></span>
                            {{ $item['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </nav>

    <!-- تسجيل الخروج -->
    <div class="border-t border-white/10 p-3">
        <a href="{{ route('profile.edit') }}"
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

<!-- طبقة تظليل لإغلاق القائمة على الموبايل -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div>