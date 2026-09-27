<?php

/**
 * القائمة الرئيسية (المصدر الوحيد) لكل صلاحية في النظام، مقسّمة حسب كل
 * قسم زي ما هو ظاهر في القائمة الجانبية بالظبط. أي صلاحية جديدة تتضاف
 * هنا بس، وبعدين تشغّلي: php artisan db:seed --class=RolesAndPermissionsSeeder
 * عشان تتزرع في جدول permissions تلقائيًا (السيدر بيستخدم updateOrCreate
 * فمتقلقيش من تكرار أو فقدان بيانات موجودة).
 *
 * الشكل: 'module_key' => ['label' => '...', 'label_en' => '...', 'permissions' => [
 *     'module_key.action' => ['label' => '...', 'label_en' => '...'],
 * ]]
 */
return [

    'transport' => [
        'label' => 'النقليات',
        'label_en' => 'Transportation',
        'permissions' => [
            'transport_invoices.view' => ['label' => 'عرض فواتير النقليات', 'label_en' => 'View transport invoices'],
            'transport_invoices.create' => ['label' => 'إنشاء فاتورة نقليات', 'label_en' => 'Create transport invoice'],
            'transport_invoices.edit' => ['label' => 'تعديل فاتورة نقليات', 'label_en' => 'Edit transport invoice'],
            'transport_invoices.delete' => ['label' => 'حذف فاتورة نقليات', 'label_en' => 'Delete transport invoice'],
            'trucks.view' => ['label' => 'عرض الشاحنات', 'label_en' => 'View trucks'],
            'trucks.create' => ['label' => 'إضافة شاحنة', 'label_en' => 'Create truck'],
            'trucks.edit' => ['label' => 'تعديل شاحنة', 'label_en' => 'Edit truck'],
            'trucks.delete' => ['label' => 'حذف شاحنة', 'label_en' => 'Delete truck'],
            'drivers.view' => ['label' => 'عرض السائقين', 'label_en' => 'View drivers'],
            'drivers.create' => ['label' => 'إضافة سائق', 'label_en' => 'Create driver'],
            'drivers.edit' => ['label' => 'تعديل سائق', 'label_en' => 'Edit driver'],
            'drivers.delete' => ['label' => 'حذف سائق', 'label_en' => 'Delete driver'],
            'truck_loads.view' => ['label' => 'عرض لوحة حركة الشاحنات', 'label_en' => 'View truck movement board'],
            'truck_loads.manage' => ['label' => 'تحميل / تفريغ / تحديد مكان الشاحنات', 'label_en' => 'Load / unload / locate trucks'],
            'truck_loads.report' => ['label' => 'تقرير الأحمال', 'label_en' => 'Loads report'],
            'waybills.view' => ['label' => 'عرض بوالص الشحن', 'label_en' => 'View waybills'],
            'waybills.create' => ['label' => 'إنشاء بوليصة شحن', 'label_en' => 'Create waybill'],
            'waybills.edit' => ['label' => 'تعديل / تسليم / إلغاء بوليصة', 'label_en' => 'Edit / deliver / cancel waybill'],
            'waybills.delete' => ['label' => 'حذف بوليصة شحن', 'label_en' => 'Delete waybill'],
            'transport_quotations.view' => ['label' => 'عرض عروض أسعار النقليات', 'label_en' => 'View transport quotations'],
            'transport_quotations.create' => ['label' => 'إنشاء عرض سعر نقليات', 'label_en' => 'Create transport quotation'],
            'transport_quotations.edit' => ['label' => 'تعديل عرض سعر نقليات', 'label_en' => 'Edit transport quotation'],
            'transport_quotations.delete' => ['label' => 'حذف عرض سعر نقليات', 'label_en' => 'Delete transport quotation'],
            'maintenance.view' => ['label' => 'عرض سندات صيانة ومصروفات الشاحنات', 'label_en' => 'View truck maintenance vouchers'],
            'maintenance.create' => ['label' => 'إنشاء سند صيانة شاحنة', 'label_en' => 'Create truck maintenance voucher'],
            'maintenance.edit' => ['label' => 'تعديل سند صيانة شاحنة', 'label_en' => 'Edit truck maintenance voucher'],
            'transport_reports.fleet' => ['label' => 'تقرير الشاحنات (الصيانة والوجهات والأحمال)', 'label_en' => 'Fleet report'],
        ],
    ],

    'customers' => [
        'label' => 'العملاء',
        'label_en' => 'Customers',
        'permissions' => [
            'customers.view' => ['label' => 'عرض العملاء', 'label_en' => 'View customers'],
            'customers.create' => ['label' => 'إضافة عميل جديد', 'label_en' => 'Create customer'],
            'customers.edit' => ['label' => 'تعديل بيانات عميل', 'label_en' => 'Edit customer'],
            'customers.delete' => ['label' => 'حذف عميل', 'label_en' => 'Delete customer'],
        ],
    ],

    'suppliers' => [
        'label' => 'الموردين',
        'label_en' => 'Suppliers',
        'permissions' => [
            'suppliers.view' => ['label' => 'عرض الموردين', 'label_en' => 'View suppliers'],
            'suppliers.create' => ['label' => 'إضافة مورد جديد', 'label_en' => 'Create supplier'],
            'suppliers.edit' => ['label' => 'تعديل بيانات مورد', 'label_en' => 'Edit supplier'],
            'suppliers.delete' => ['label' => 'حذف مورد', 'label_en' => 'Delete supplier'],
        ],
    ],


    'purchases' => [
        'label' => 'المشتريات',
        'label_en' => 'Purchases',
        'permissions' => [
            'purchases.view' => ['label' => 'عرض المشتريات', 'label_en' => 'View purchases'],
            'purchases.create' => ['label' => 'إنشاء عملية شراء', 'label_en' => 'Create purchase'],
            'purchases.edit' => ['label' => 'تعديل عملية شراء', 'label_en' => 'Edit purchase'],
            'purchases.delete' => ['label' => 'حذف عملية شراء', 'label_en' => 'Delete purchase'],
            'purchases.orders' => ['label' => 'أوامر الشراء', 'label_en' => 'Purchase orders'],
            'purchases.returns' => ['label' => 'مرتجعات المشتريات', 'label_en' => 'Purchase returns'],
        ],
    ],

    'products' => [
        'label' => 'المنتجات والمخزون',
        'label_en' => 'Products & Inventory',
        'permissions' => [
            'products.view' => ['label' => 'عرض المنتجات', 'label_en' => 'View products'],
            'products.create' => ['label' => 'إضافة منتج جديد', 'label_en' => 'Create product'],
            'products.edit' => ['label' => 'تعديل منتج', 'label_en' => 'Edit product'],
            'products.delete' => ['label' => 'حذف منتج', 'label_en' => 'Delete product'],
            'products.groups' => ['label' => 'إدارة مجموعات المنتجات', 'label_en' => 'Manage product groups'],
        ],
    ],

    'accounting' => [
        'label' => 'المحاسبة والفواتير',
        'label_en' => 'Accounting',
        'permissions' => [
            'accounts.view' => ['label' => 'عرض شجرة الحسابات', 'label_en' => 'View accounts'],
            'accounts.create' => ['label' => 'إضافة حساب جديد', 'label_en' => 'Create account'],
            'accounts.edit' => ['label' => 'تعديل حساب', 'label_en' => 'Edit account'],
            'journal_entries.view' => ['label' => 'عرض القيود اليومية', 'label_en' => 'View journal entries'],
            'journal_entries.create' => ['label' => 'إنشاء قيد يومية', 'label_en' => 'Create journal entry'],
            'journal_entries.edit' => ['label' => 'تعديل قيد يومية', 'label_en' => 'Edit journal entry'],
            'vouchers.view' => ['label' => 'عرض سندات القبض والصرف', 'label_en' => 'View vouchers'],
            'vouchers.create' => ['label' => 'إنشاء سند قبض/صرف', 'label_en' => 'Create voucher'],
            'vouchers.edit' => ['label' => 'تعديل سند قبض/صرف', 'label_en' => 'Edit voucher'],
        ],
    ],

    'hr' => [
        'label' => 'الموارد البشرية',
        'label_en' => 'Human Resources',
        'permissions' => [
            'employees.view' => ['label' => 'عرض الموظفين', 'label_en' => 'View employees'],
            'employees.create' => ['label' => 'إضافة موظف جديد', 'label_en' => 'Create employee'],
            'employees.edit' => ['label' => 'تعديل بيانات موظف', 'label_en' => 'Edit employee'],
            'departments.view' => ['label' => 'عرض أقسام الموظفين', 'label_en' => 'View departments'],
            'departments.create' => ['label' => 'إضافة قسم', 'label_en' => 'Create department'],
            'departments.edit' => ['label' => 'تعديل قسم', 'label_en' => 'Edit department'],
            'departments.delete' => ['label' => 'حذف قسم', 'label_en' => 'Delete department'],
            'attendance.view' => ['label' => 'عرض الحضور والانصراف', 'label_en' => 'View attendance'],
            'attendance.create' => ['label' => 'تسجيل حضور/انصراف', 'label_en' => 'Create attendance'],
            'employee_loans.view' => ['label' => 'عرض سلف الموظفين', 'label_en' => 'View employee loans'],
            'employee_loans.create' => ['label' => 'إضافة سلفة', 'label_en' => 'Create employee loan'],
            'asset_custodies.view' => ['label' => 'عرض عهد الموظفين', 'label_en' => 'View asset custodies'],
            'employee_custody.view' => ['label' => 'مخطط عُهد الموظفين', 'label_en' => 'Employee custody chart'],
            'leave_requests.view' => ['label' => 'عرض طلبات الإجازات', 'label_en' => 'View leave requests'],
            'end_of_service.view' => ['label' => 'عرض مكافآت نهاية الخدمة', 'label_en' => 'View end of service'],
            'payroll.view' => ['label' => 'عرض الرواتب', 'label_en' => 'View payroll'],
            'hr_settings.manage' => ['label' => 'إعدادات الموارد البشرية', 'label_en' => 'HR settings'],
        ],
    ],

    // قسم التقارير مقسّم لـ 6 موديولات (واحد لكل قسم من مركز التقارير)
    // وكل تقرير جوه ليه صلاحية مستقلة بنفسه (مش صلاحية واحدة شاملة زي
    // الأول)، عشان صاحب الحساب يقدر يدي كل موظف بالظبط التقارير اللي
    // محتاجها بس - مثلاً محاسب يشوف الميزانية بس من غير تقارير الرواتب.
    // راجعي ReportController::authorizeAnyReport لصفحات "قسم" التقارير
    // (اللي بتفتح لو عند المستخدم صلاحية تقرير واحد ع الأقل جواها).
    'reports_accounting' => [
        'label' => 'تقارير المحاسبة',
        'label_en' => 'Accounting Reports',
        'permissions' => [
            'reports_accounting.trial_balance' => ['label' => 'ميزان المراجعة', 'label_en' => 'Trial balance'],
            'reports_accounting.balance_sheet' => ['label' => 'الميزانية العمومية', 'label_en' => 'Balance sheet'],
            'reports_accounting.income_statement' => ['label' => 'قائمة الدخل', 'label_en' => 'Income statement'],
            'reports_accounting.equity_changes' => ['label' => 'قائمة التغير في حقوق الملكية', 'label_en' => 'Equity changes'],
            'reports_accounting.cash_flow' => ['label' => 'قائمة التدفقات النقدية', 'label_en' => 'Cash flow'],
            'reports_accounting.vouchers' => ['label' => 'تقرير السندات والقيود', 'label_en' => 'Vouchers & journal entries report'],
            'reports_accounting.cost_centers' => ['label' => 'تقرير مراكز التكلفة', 'label_en' => 'Cost centers report'],
            'reports_accounting.aging' => ['label' => 'أعمار ديون العملاء والموردين', 'label_en' => 'Receivables & payables aging'],
            'reports_accounting.expenses' => ['label' => 'تقرير المصروفات', 'label_en' => 'Expenses report'],
            'reports_accounting.customer_supplier_accounts' => ['label' => 'قائمة حسابات العملاء والموردين', 'label_en' => 'Customer & supplier accounts list'],
            'reports_accounting.daily_closing' => ['label' => 'التقرير الختامي اليومي', 'label_en' => 'Daily closing report'],
            'reports_accounting.tax_report' => ['label' => 'تقرير الإقرار الضريبي', 'label_en' => 'Tax declaration report'],
        ],
    ],


    'reports_purchases' => [
        'label' => 'تقارير المشتريات',
        'label_en' => 'Purchases Reports',
        'permissions' => [
            'reports_purchases.summary' => ['label' => 'ملخص المشتريات', 'label_en' => 'Purchases summary'],
            'reports_purchases.by_supplier' => ['label' => 'المشتريات حسب المورد', 'label_en' => 'Purchases by supplier'],
            'reports_purchases.by_employee' => ['label' => 'المشتريات حسب الموظف', 'label_en' => 'Purchases by employee'],
            'reports_purchases.by_product' => ['label' => 'المشتريات حسب المنتج', 'label_en' => 'Purchases by product'],
            'reports_purchases.returns' => ['label' => 'مرتجعات المشتريات', 'label_en' => 'Purchases returns'],
        ],
    ],

    'reports_products' => [
        'label' => 'تقارير المنتجات والمخزون',
        'label_en' => 'Products & Inventory Reports',
        'permissions' => [
            'reports_products.stock' => ['label' => 'المخزون الحالي', 'label_en' => 'Current stock'],
            'reports_products.low_stock' => ['label' => 'المنتجات الموشكة على النفاد', 'label_en' => 'Low stock'],
        ],
    ],

    'reports_hr' => [
        'label' => 'تقارير الموارد البشرية',
        'label_en' => 'HR Reports',
        'permissions' => [
            'reports_hr.payroll' => ['label' => 'تقرير الرواتب', 'label_en' => 'Payroll report'],
            'reports_hr.attendance' => ['label' => 'تقرير الحضور والانصراف', 'label_en' => 'Attendance report'],
            'reports_hr.loans' => ['label' => 'تقرير سلف الموظفين', 'label_en' => 'Employee loans report'],
            'reports_hr.employees' => ['label' => 'تقرير بيانات الموظفين', 'label_en' => 'Employees report'],
            'reports_hr.bonuses_deductions' => ['label' => 'تقرير المكافآت والخصومات', 'label_en' => 'Bonuses & deductions report'],
            'reports_hr.leaves' => ['label' => 'تقرير الإجازات', 'label_en' => 'Leaves report'],
        ],
    ],

    'settings' => [
        'label' => 'الإعدادات والضرائب',
        'label_en' => 'Settings & Taxes',
        'permissions' => [
            'settings.manage' => ['label' => 'إدارة إعدادات النظام', 'label_en' => 'Manage system settings'],
            'settings.taxes' => ['label' => 'إدارة الضرائب', 'label_en' => 'Manage taxes'],
            'settings.employee_discounts' => ['label' => 'إعدادات خصومات الموظفين', 'label_en' => 'Employee discount settings'],
            'zatca.view' => ['label' => 'فوترة إلكترونية (زاتكا)', 'label_en' => 'ZATCA e-invoicing'],
            'zatca.send' => ['label' => 'إرسال الفواتير للزكاة والضريبة (زاتكا)', 'label_en' => 'Send invoices to ZATCA'],
        ],
    ],

    'sensitive_data' => [
        'label' => 'بيانات حساسة',
        'label_en' => 'Sensitive Data',
        'permissions' => [
            'sensitive_data.view_profit' => ['label' => 'عرض الربح (المبيعات، التسليم، عروض الأسعار)', 'label_en' => 'View profit (sales, delivery, quotations)'],
        ],
    ],

    'administration' => [
        'label' => 'الإدارة (مستخدمين / فروع / صلاحيات)',
        'label_en' => 'Administration (Users / Branches / Roles)',
        'permissions' => [
            'users.view' => ['label' => 'عرض المستخدمين', 'label_en' => 'View users'],
            'users.create' => ['label' => 'إضافة مستخدم جديد', 'label_en' => 'Create user'],
            'users.edit' => ['label' => 'تعديل مستخدم', 'label_en' => 'Edit user'],
            'users.delete' => ['label' => 'تعطيل/حذف مستخدم', 'label_en' => 'Deactivate/delete user'],
            'branches.view' => ['label' => 'عرض الفروع', 'label_en' => 'View branches'],
            'branches.create' => ['label' => 'إضافة فرع جديد', 'label_en' => 'Create branch'],
            'branches.edit' => ['label' => 'تعديل فرع', 'label_en' => 'Edit branch'],
            'branches.delete' => ['label' => 'حذف فرع', 'label_en' => 'Delete branch'],
            'roles.manage' => ['label' => 'إدارة الأدوار والصلاحيات', 'label_en' => 'Manage roles & permissions'],
        ],
    ],

];
