<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ZatcaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeDiscountSettingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\TaxController;
// تسليم منتج


use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HrSettingController;
use App\Http\Controllers\EmployeeLoanController;
use App\Http\Controllers\AssetCustodyController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\EndOfServiceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductInventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeContractController;
use App\Http\Controllers\NotificationController;
// النقليات
use App\Http\Controllers\TruckController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TransportInvoiceController;
use App\Http\Controllers\ProductLookupController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TruckLoadController;
use App\Http\Controllers\TransportQuotationController;
use App\Http\Controllers\WaybillController;
use App\Http\Controllers\TransportReportController;
use App\Http\Controllers\TransportZatcaController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TransportCreditNoteController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // بيانات كروت الشاشة الرئيسية بتتحمل بالـ Ajax من هنا بعد ما الصفحة
    // تظهر على طول، بدل ما المستخدم يستنى كل الاستعلامات قبل ما يشوف حاجة.
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
});

// الإدارة: مستخدمين / فروع / أدوار وصلاحيات - كل الحماية الفعلية بتتم
// جوه الكنترولرات نفسها بـ $this->authorize('...') (مش هنا في الراوتس)،
// عشان أي طلب مباشر للرابط برضه يترفض لو المستخدم مش معاه الصلاحية.
Route::middleware(['auth'])->group(function () {


// (قسم التصنيع اتشال - مش مستخدم في النقليات)



    Route::resource('users', UserController::class)->except(['show']);
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->except(['show']);
});


Route::middleware(['auth'])->group(function () {
Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

// ===== الموردين =====
Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');

// ===== الموارد البشرية: الموظفين (أساس قسم الموارد البشرية) =====

Route::prefix('contracts')->name('contracts.')->group(function () {
    Route::get('/', [EmployeeContractController::class, 'index'])->name('index');
    Route::get('create', [EmployeeContractController::class, 'create'])->name('create');
    Route::post('/', [EmployeeContractController::class, 'store'])->name('store');
    Route::get('{contract}/edit', [EmployeeContractController::class, 'edit'])->name('edit');
    Route::put('{contract}', [EmployeeContractController::class, 'update'])->name('update');
    Route::delete('{contract}', [EmployeeContractController::class, 'destroy'])->name('destroy');
});

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
});
// الروتس الثابتة (create/import/...) لازم تسبق أي روت فيه باراميتر
// ({employee}) بنفس القاعدة المتبعة في باقي المشروع.
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::get('/employees/import', [EmployeeController::class, 'importForm'])->name('employees.import.form');
Route::get('/employees/import/template', [EmployeeController::class, 'downloadTemplate'])->name('employees.import.template');
Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import.store');
Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
Route::patch('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
// أقسام الموظفين (بتتختار في إنشاء/تعديل الموظف)
Route::get('/hr/departments', [DepartmentController::class, 'index'])->name('employees.departments.index');
Route::post('/hr/departments', [DepartmentController::class, 'store'])->name('employees.departments.store');
Route::put('/hr/departments/{department}', [DepartmentController::class, 'update'])->name('employees.departments.update');
Route::delete('/hr/departments/{department}', [DepartmentController::class, 'destroy'])->name('employees.departments.destroy');

// ===== الحضور والانصراف (تقرير شهري + إدخال يدوي + استيراد بصمة إكسيل) =====
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
Route::get('/attendance/import', [AttendanceController::class, 'importForm'])->name('attendance.import.form');
Route::get('/attendance/import/template', [AttendanceController::class, 'downloadTemplate'])->name('attendance.import.template');
Route::post('/attendance/import', [AttendanceController::class, 'import'])->name('attendance.import.store');
Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

// ===== إعدادات الموارد البشرية (أوقات الدوام/سماحية التأخير/الأوفرتايم) + الإجازات الرسمية =====
Route::get('/hr-settings', [HrSettingController::class, 'index'])->name('hr-settings.index');
Route::put('/hr-settings', [HrSettingController::class, 'update'])->name('hr-settings.update');
Route::post('/hr-settings/holidays', [HrSettingController::class, 'storeHoliday'])->name('hr-settings.holidays.store');
Route::delete('/hr-settings/holidays/{holiday}', [HrSettingController::class, 'destroyHoliday'])->name('hr-settings.holidays.destroy');

// ===== السلف والعهد (loans/custodies) - صرف/تسوية/إلغاء بقيود محاسبية حقيقية =====
Route::get('/employee-loans', [EmployeeLoanController::class, 'index'])->name('employee-loans.index');
Route::post('/employee-loans', [EmployeeLoanController::class, 'store'])->name('employee-loans.store');
Route::patch('/employee-loans/{loan}/settle', [EmployeeLoanController::class, 'settle'])->name('employee-loans.settle');
Route::delete('/employee-loans/{loan}', [EmployeeLoanController::class, 'destroy'])->name('employee-loans.destroy');

// ===== عهدة الأصول (لابتوب/عربية/موبايل...) - تسجيل تشغيلي بدون أثر محاسبي =====
Route::get('/asset-custodies', [AssetCustodyController::class, 'index'])->name('asset-custodies.index');
Route::post('/asset-custodies', [AssetCustodyController::class, 'store'])->name('asset-custodies.store');
Route::patch('/asset-custodies/{assetCustody}/return', [AssetCustodyController::class, 'returnAsset'])->name('asset-custodies.return');
Route::delete('/asset-custodies/{assetCustody}', [AssetCustodyController::class, 'destroy'])->name('asset-custodies.destroy');

// ===== طلبات الإجازة + رصيد الإجازة السنوية =====
Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
Route::patch('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
Route::delete('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('leave-requests.destroy');
Route::put('/leave-requests/balance/{employee}', [LeaveRequestController::class, 'updateBalance'])->name('leave-requests.balance.update');

// ===== مكافأة نهاية الخدمة - حساب رسمي بنظام العمل السعودي + ترحيل قيد =====
Route::get('/end-of-service', [EndOfServiceController::class, 'index'])->name('end-of-service.index');
Route::get('/end-of-service/create', [EndOfServiceController::class, 'create'])->name('end-of-service.create');
Route::post('/end-of-service', [EndOfServiceController::class, 'store'])->name('end-of-service.store');
Route::delete('/end-of-service/{endOfServiceSettlement}', [EndOfServiceController::class, 'destroy'])->name('end-of-service.destroy');

// ===== كشف الرواتب الشهري + قسيمة راتب فردية + ترحيل رواتب الشهر =====
Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
Route::get('/payroll/slip/{employee}', [PayrollController::class, 'slip'])->name('payroll.slip');
Route::post('/payroll/bonus', [PayrollController::class, 'storeBonus'])->name('payroll.bonus.store');
Route::post('/payroll/post', [PayrollController::class, 'postMonth'])->name('payroll.post');
Route::post('/payroll/posting/{payrollPosting}/pay', [PayrollController::class, 'payAccruedPosting'])->name('payroll.posting.pay');
Route::delete('/payroll/posting/{payrollPosting}', [PayrollController::class, 'destroyPosting'])->name('payroll.posting.destroy');

// ===== المنتجات والمخزون =====
Route::get('product-groups/create', [ProductInventoryController::class, 'createGroup'])->name('product-groups.create');
Route::post('product-groups', [ProductInventoryController::class, 'storeGroup'])->name('product-groups.store');
// الخطوة الإجبارية الأولى: اختيار الفرع
Route::get('/products/choose-branch', [ProductInventoryController::class, 'chooseBranch'])->name('products.choose_branch');

// قائمة المنتجات لفرع معيّن (فلترة بالفئة/الرقم داخل نفس الصفحة)
Route::get('/products/branch/{branch}', [ProductInventoryController::class, 'index'])->name('products.index');

// تعديل بيانات منتج
Route::get('/products/{product}/edit', [ProductInventoryController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductInventoryController::class, 'update'])->name('products.update');

// تعديل كمية المخزون فقط
Route::patch('/products/{product}/stock', [ProductInventoryController::class, 'updateStock'])->name('products.stock.update');

// مودال "العمليات" الخاص بمنتج واحد (زرار العمليات في مودال اختيار منتج،
// الفواتير والمشتريات مع بعض) - بيانات JSON مجمّعة من مبيعات/مشتريات/
// تحويلات المخزون لنفس المنتج. الحماية الفعلية جوه ProductController@operationsData.
Route::get('/products/{product}/operations-data', [ProductController::class, 'operationsData'])->name('products.operations.data');

// مودال "البدائل" الخاص بمنتج واحد (زرار البدائل في مودال اختيار منتج).
Route::get('/products/{product}/alternates', [ProductController::class, 'alternatesData'])->name('products.alternates');

// بحث Ajax عام (كل الفروع) مستخدم في فورم إنشاء/تعديل منتج لاختيار
// المنتجات "الأساسية" اللي المنتج ده بديل ليها.
Route::get('/products/search-alternates', [ProductController::class, 'searchAlternates'])->name('products.search-alternates');

// رفع إكسيل (مخزون افتتاحي / تعديل بالجملة)
Route::get('/products/branch/{branch}/import', [ProductInventoryController::class, 'showImportForm'])->name('products.import.form');
Route::post('/products/branch/{branch}/import', [ProductInventoryController::class, 'importExcel'])->name('products.import.process');

// (سندات التسليم اتشالت)



    // جرس الإشعارات في الهيدر - endpoint خفيف بيرجع عدّادين (فواتير
    // زاتكا فشلت + منتجات وصلت لحد تنبيه المخزون)، كل واحد متفلتر على
    // صلاحية المستخدم. راجع NotificationController@summary.
    Route::get('/notifications/summary', [NotificationController::class, 'summary'])->name('notifications.summary');

    // صفحة تانية من "عمليات اليوم" (زرار "عرض المزيد" في القايمة) -
    // راجع NotificationController@recentOperationsPage.
    Route::get('/notifications/recent', [NotificationController::class, 'recentOperationsPage'])->name('notifications.recent');

    // فتح الجرس = "اتشافت" (الرقم الأحمر بيعدّ اللي جه بعد كده بس)
    Route::post('/notifications/seen', [NotificationController::class, 'markSeen'])->name('notifications.seen');
});
Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchase-orders.pdf');
Route::post('/purchases/cost-centers/quick', [PurchaseController::class, 'quickStoreCostCenter'])->name('purchases.cost-centers.quick');
Route::get('/purchases/payment-accounts/{branch}', [PurchaseController::class, 'paymentAccountsForBranch'])->name('purchases.payment-accounts');
Route::get('/purchases/items-template', [PurchaseController::class, 'downloadItemsTemplate'])->name('purchases.items-template');
Route::post('/purchases/items/import', [PurchaseController::class, 'importItems'])->name('purchases.items.import');

Route::prefix('settings')->group(function () {
    Route::get('/taxes', [TaxController::class, 'index'])->name('taxes.index');
    Route::post('/taxes', [TaxController::class, 'store'])->name('taxes.store');
    Route::delete('/taxes/{tax}', [TaxController::class, 'destroy'])->name('taxes.destroy');
});
Route::middleware(['auth'])->group(function () {
    // (تسليم المنتج القديم اتشال)


    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/system', [SettingsController::class, 'updateSystemSettings'])->name('settings.system.update');
    Route::put('/settings/zakat', [SettingsController::class, 'updateZakatSettings'])->name('settings.zakat.update');
    Route::get('/settings/onboarding', [SettingsController::class, 'onboarding'])->name('settings.onboarding');
    Route::post('/settings/onboarding', [SettingsController::class, 'storeOnboarding'])->name('settings.onboarding.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ->except(['index', 'edit', 'update']): الأسماء دي كانت متعرّفة فوق
    // بالفعل لـ ProductInventoryController (قايمة/تعديل منتج داخل فرع
    // معيّن)، ولو سبنا Route::resource يسجلها هنا كمان كانت بتاخد الأسبقية
    // (آخر تسجيل بيكسب) وتبوّظ كل روابط "قائمة المنتجات"/"تعديل منتج" في
    // النظام كله (بترجع لصفحة فاضية من غير فلترة فرع). فباقي من الـ
    // resource بس create/store (إضافة منتج جديد) وshow/destroy (حذف).
    Route::resource('products', ProductController::class)->except(['index', 'edit', 'update']);
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::post('/purchases/suppliers/quick', [PurchaseController::class, 'quickStoreSupplier'])->name('purchases.suppliers.quick');
    Route::get('/purchases/product-cost-history/{product}', [PurchaseController::class, 'productCostHistory'])->name('purchases.product-cost-history');
    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'downloadPdf'])->name('purchases.pdf');

    // مسارات مرتجع المشتريات - لازم تكون هنا، قبل /purchases/{purchase}،
    // عشان لارافيل ميفهمش "returns" على إنها ID فاتورة شراء (route model
    // binding). الترتيب هنا كمان مهم: الروتس الثابتة (create/search/index)
    // لازم تسبق أي روت فيه باراميتر (زي {purchase}/items أو
    // {purchaseReturn})، بنفس الترتيب المتبع في مسارات مرتجع المبيعات تحت.
    Route::get('/purchases/returns', [PurchaseReturnController::class, 'index'])->name('purchases.returns.index');
    Route::get('/purchases/returns/create', [PurchaseReturnController::class, 'create'])->name('purchases.returns.create');
    Route::get('/purchases/returns/search', [PurchaseReturnController::class, 'search'])->name('purchases.returns.search');
    Route::post('/purchases/returns', [PurchaseReturnController::class, 'store'])->name('purchases.returns.store');
    Route::get('/purchases/returns/refund-accounts/{branch}', [PurchaseReturnController::class, 'refundAccountsForBranch'])->name('purchases.returns.refund-accounts');
    Route::get('/purchases/returns/{purchase}/items', [PurchaseReturnController::class, 'items'])->name('purchases.returns.items');
    Route::get('/purchases/returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchases.returns.show');
    Route::get('/purchases/returns/{purchaseReturn}/pdf', [PurchaseReturnController::class, 'downloadPdf'])->name('purchases.returns.pdf');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

    // بحث/اختيار المنتجات (مستخدم في المشتريات وأوامر الشراء) - قسم المبيعات اتشال
    Route::get('/invoices/products/pick', [ProductLookupController::class, 'pickProducts'])->name('invoices.products.pick');
    Route::get('/invoices/products/search', [ProductLookupController::class, 'searchProducts'])->name('invoices.products.search');
});

// ===== قسم الحسابات والقيود (شجرة الحسابات، القيد اليومي، سندات
// القبض والصرف) - نفس ترتيب الروتس المتبع في باقي المشروع: الروتس
// الثابتة (create/search/index) لازم تسبق أي روت فيه باراميتر
// ({account}/{journalEntry}/{voucher}) عشان لارافيل ميحاولش يفهم
// "create" أو "search" على إنها ID (route model binding).
Route::middleware(['auth'])->group(function () {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/tree', [AccountController::class, 'tree'])->name('accounts.tree');
    Route::get('/accounts/tree/search', [AccountController::class, 'treeSearch'])->name('accounts.tree.search');
    Route::get('/accounts/tree/{account}/children', [AccountController::class, 'treeChildren'])->name('accounts.tree.children');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/search', [AccountController::class, 'search'])->name('accounts.search');
    Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::patch('/accounts/{account}/toggle', [AccountController::class, 'toggleActive'])->name('accounts.toggle');
    Route::get('/accounts/{account}/statement', [AccountController::class, 'statement'])->name('accounts.statement');

    // "أنواع الحسابات" - شاشة إدارية لتفعيل/تعطيل الفروع الخمسة
    // الرئيسية لشجرة الحسابات (راجع AccountTypeController).
    Route::get('/account-types', [AccountTypeController::class, 'index'])->name('account-types.index');
    Route::patch('/account-types/{accountType}/toggle', [AccountTypeController::class, 'toggleActive'])->name('account-types.toggle');

    Route::get('/journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('/journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::post('/journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.store');
    Route::get('/journal-entries/{journalEntry}/edit', [JournalEntryController::class, 'edit'])->name('journal-entries.edit');
    Route::put('/journal-entries/{journalEntry}', [JournalEntryController::class, 'update'])->name('journal-entries.update');
    Route::get('/journal-entries/{journalEntry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');
    Route::get('/journal-entries/{journalEntry}/print', [JournalEntryController::class, 'print'])->name('journal-entries.print');

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::get('/vouchers/{voucher}', [VoucherController::class, 'show'])->name('vouchers.show');
    Route::get('/vouchers/{voucher}/print', [VoucherController::class, 'print'])->name('vouchers.print');
});

// ===== مركز التقارير (قسم الحسابات مبني أول قسم حسب الترتيب المتفق
// عليه - باقي الأقسام الخمسة هتتضاف بعده بنفس البنية). راجع تعليق
// ReportController للتفاصيل الكاملة عن منطق كل تقرير وفلتر الفرع. =====
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/accounts', [ReportController::class, 'accountsIndex'])->name('reports.accounts.index');
    Route::get('/reports/accounts/statement', [AccountController::class, 'statementReport'])->name('reports.accounts.statement');
    Route::get('/reports/accounts/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.accounts.trial-balance');
    Route::get('/reports/accounts/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.accounts.income-statement');
    Route::get('/reports/accounts/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.accounts.balance-sheet');
    Route::get('/reports/accounts/equity-changes', [ReportController::class, 'equityChanges'])->name('reports.accounts.equity-changes');
    Route::get('/reports/accounts/cash-flow', [ReportController::class, 'cashFlow'])->name('reports.accounts.cash-flow');
    Route::get('/reports/accounts/vouchers', [ReportController::class, 'vouchersSummary'])->name('reports.accounts.vouchers');
    Route::get('/reports/accounts/cost-centers', [ReportController::class, 'costCenters'])->name('reports.accounts.cost-centers');
    Route::get('/reports/accounts/aging', [ReportController::class, 'receivablesPayablesAging'])->name('reports.accounts.aging');
    Route::get('/reports/accounts/expenses', [ReportController::class, 'expensesReport'])->name('reports.accounts.expenses');
    Route::get('/reports/accounts/expenses/search', [ReportController::class, 'expensesAccountsSearch'])->name('reports.accounts.expenses.search');
    Route::get('/reports/accounts/customer-supplier-accounts', [ReportController::class, 'customerSupplierAccounts'])->name('reports.accounts.customer-supplier-accounts');
    Route::get('/reports/accounts/customer-supplier-accounts/search', [ReportController::class, 'customerSupplierAccountsSearch'])->name('reports.accounts.customer-supplier-accounts.search');
    Route::get('/reports/accounts/daily-closing', [ReportController::class, 'dailyClosingReport'])->name('reports.accounts.daily-closing');
    Route::get('/reports/accounts/tax', [ReportController::class, 'taxReport'])->name('reports.accounts.tax');

    // ===== قسم المشتريات =====
    Route::get('/reports/purchases', [ReportController::class, 'purchasesIndex'])->name('reports.purchases.index');
    Route::get('/reports/purchases/summary', [ReportController::class, 'purchasesSummary'])->name('reports.purchases.summary');
    Route::get('/reports/purchases/by-supplier', [ReportController::class, 'purchasesBySupplier'])->name('reports.purchases.by-supplier');
    Route::get('/reports/purchases/by-product', [ReportController::class, 'purchasesByProduct'])->name('reports.purchases.by-product');
    Route::get('/reports/purchases/returns', [ReportController::class, 'purchasesReturns'])->name('reports.purchases.returns');
    Route::get('/reports/purchases/by-employee', [ReportController::class, 'purchasesByEmployee'])->name('reports.purchases.by-employee');

    // ===== قسم المنتجات =====
    Route::get('/reports/products', [ReportController::class, 'productsIndex'])->name('reports.products.index');
    Route::get('/reports/products/stock', [ReportController::class, 'productsStock'])->name('reports.products.stock');
    Route::get('/reports/products/low-stock', [ReportController::class, 'productsLowStock'])->name('reports.products.low-stock');
    Route::get('/reports/products/movement', [ReportController::class, 'productsMovement'])->name('reports.products.movement');

    // ===== قسم الموارد البشرية =====
    Route::get('/reports/hr', [ReportController::class, 'hrIndex'])->name('reports.hr.index');
    Route::get('/reports/hr/payroll', [ReportController::class, 'hrPayroll'])->name('reports.hr.payroll');
    Route::get('/reports/hr/attendance', [ReportController::class, 'hrAttendance'])->name('reports.hr.attendance');
    Route::get('/reports/hr/loans', [ReportController::class, 'hrLoans'])->name('reports.hr.loans');
    Route::get('/reports/hr/employees', [ReportController::class, 'hrEmployees'])->name('reports.hr.employees');
    Route::get('/reports/hr/bonuses-deductions', [ReportController::class, 'hrBonusesDeductions'])->name('reports.hr.bonuses-deductions');
    Route::get('/reports/hr/leaves', [ReportController::class, 'hrLeaves'])->name('reports.hr.leaves');
});

// ===== قسم المستودعات: تحويل منتجات بين الفروع (سند صرف + سند
// استلام) - جديد كليًا، منفصل عن سندات التسليم للعميل (delivery-note)
// وعن فواتير ZATCA. نفس ترتيب الروتس المتبع في باقي المشروع: الروتس
// الثابتة (choose-branch/products/branches) لازم تسبق أي روت فيه
// باراميتر وحيد ({stockTransfer}) عشان لارافيل ميحاولش يفهمها كـ ID.
// ترجمة سريعة (اسم منتج عربي -> إنجليزي) لصندوق "تفعيل الترجمة" في
// مودالات "منتج جديد" في شاشات المبيعات/المشتريات/التسعيرات/التسليمات.
Route::middleware(['auth'])->get('/products/translate', [TranslationController::class, 'translate'])->name('products.translate');

// (تحويلات المخزون اتشالت)

Route::get('/lang/{locale}', function (string $locale, Request $request) {
    if (in_array($locale, ['ar', 'en'])) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang.switch');

Route::middleware(['auth'])->group(function () {
    Route::get('/settings/employee-discounts', [EmployeeDiscountSettingController::class, 'index'])
        ->name('employee-discounts.index');

    Route::put('/settings/employee-discounts/branch-default', [EmployeeDiscountSettingController::class, 'updateBranchDefault'])
        ->name('employee-discounts.branch-default');

    Route::put('/settings/employee-discounts/user-override', [EmployeeDiscountSettingController::class, 'updateUserOverride'])
        ->name('employee-discounts.user-override');
});

require __DIR__ . '/auth.php';

// ===================== النقليات: الشاحنات / السائقين / فواتير النقليات =====================
// الحماية الفعلية بالصلاحيات جوه الكنترولرات ($this->authorize(...)).
Route::middleware(['auth'])->prefix('transport')->name('transport.')->group(function () {
    // حركة الشاحنات (لوحة الشاحنات + تحميل/تفريغ + تقرير الأحمال)
    Route::get('loads', [TruckLoadController::class, 'board'])->name('loads.board');
    Route::get('loads/report', [TruckLoadController::class, 'report'])->name('loads.report');
    Route::post('trucks/{truck}/load', [TruckLoadController::class, 'store'])->name('loads.store');
    Route::post('trucks/{truck}/location', [TruckLoadController::class, 'setLocation'])->name('loads.location');
    Route::post('loads/{load}/unload', [TruckLoadController::class, 'unload'])->name('loads.unload');
    Route::post('loads/{load}/cancel', [TruckLoadController::class, 'cancel'])->name('loads.cancel');
    Route::get('loads/{load}/edit', [TruckLoadController::class, 'edit'])->name('loads.edit');
    Route::put('loads/{load}', [TruckLoadController::class, 'update'])->name('loads.update');

    Route::post('drivers/quick', [DriverController::class, 'quick'])->name('drivers.quick');
    Route::post('trucks/quick', [TruckController::class, 'quick'])->name('trucks.quick');

    Route::resource('trucks', TruckController::class)->except(['show']);
    Route::resource('drivers', DriverController::class)->except(['show']);
    Route::post('invoices/{invoice}/approve', [TransportInvoiceController::class, 'approve'])->name('invoices.approve');
    Route::resource('invoices', TransportInvoiceController::class);

    // الإشعارات الدائنة على فواتير النقليات (+ إرسالها للزكاة 381)
    Route::get('credit-notes', [TransportCreditNoteController::class, 'index'])->name('credit-notes.index');
    Route::get('invoices/{invoice}/credit-notes/create', [TransportCreditNoteController::class, 'create'])->name('credit-notes.create');
    Route::post('invoices/{invoice}/credit-notes', [TransportCreditNoteController::class, 'store'])->name('credit-notes.store');
    Route::get('credit-notes/{creditNote}', [TransportCreditNoteController::class, 'show'])->name('credit-notes.show');
    Route::delete('credit-notes/{creditNote}', [TransportCreditNoteController::class, 'destroy'])->name('credit-notes.destroy');
    Route::post('credit-notes/{creditNote}/zatca', [TransportCreditNoteController::class, 'sendZatca'])->name('credit-notes.zatca');
    Route::get('credit-notes/{creditNote}/xml', [TransportCreditNoteController::class, 'downloadXml'])->name('credit-notes.xml');

    // إرسال فواتير النقليات للزكاة (بنفس كود إرسال فواتير المبيعات)
    Route::get('zatca', [TransportZatcaController::class, 'index'])->name('zatca.index');
    Route::post('zatca/send-all', [TransportZatcaController::class, 'sendAll'])->name('zatca.send-all');
    Route::post('zatca/{invoice}/send', [TransportZatcaController::class, 'send'])->name('zatca.send');
    Route::get('zatca/{invoice}/xml', [TransportZatcaController::class, 'downloadXml'])->name('zatca.xml');

    // عروض أسعار النقليات
    Route::post('quotations/{quotation}/status', [TransportQuotationController::class, 'status'])->name('quotations.status');
    Route::get('quotations/{quotation}/convert', [TransportQuotationController::class, 'convert'])->name('quotations.convert');
    Route::resource('quotations', TransportQuotationController::class);

    // بوالص الشحن
    Route::post('waybills/{waybill}/deliver', [WaybillController::class, 'deliver'])->name('waybills.deliver');
    Route::post('waybills/{waybill}/cancel', [WaybillController::class, 'cancel'])->name('waybills.cancel');
    Route::get('waybills/{waybill}/invoice', [WaybillController::class, 'toInvoice'])->name('waybills.invoice');
    Route::resource('waybills', WaybillController::class);

    // تقارير: الشاحنات (صيانة/مصروفات/وجهات/أحمال) + مخطط عُهد الموظفين
    Route::get('reports/fleet', [TransportReportController::class, 'fleet'])->name('reports.fleet');
    Route::get('reports/custody', [TransportReportController::class, 'custody'])->name('reports.custody');

    // الأحمال غير المفوترة (جاهزة للفوترة)
    Route::get('reports/unbilled', [TransportReportController::class, 'unbilled'])->name('reports.unbilled');

    // مركز تقارير النقليات + تقارير الفواتير/العملاء/المسارات/السائقين/كشف حساب شاحنة
    Route::get('reports', [TransportReportController::class, 'index'])->name('reports.index');
    Route::get('reports/sales', [TransportReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/customers', [TransportReportController::class, 'customers'])->name('reports.customers');
    Route::get('reports/routes', [TransportReportController::class, 'routes'])->name('reports.routes');
    Route::get('reports/drivers', [TransportReportController::class, 'drivers'])->name('reports.drivers');
    Route::get('reports/truck', [TransportReportController::class, 'truck'])->name('reports.truck');
    Route::get('reports/maintenance', [TransportReportController::class, 'maintenance'])->name('reports.maintenance');

    // المناطق (مناطق المملكة الأساسية + مناطق مضافة)
    Route::get('regions', [RegionController::class, 'index'])->name('regions.index');
    Route::post('regions', [RegionController::class, 'store'])->name('regions.store');
    Route::delete('regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');
});
