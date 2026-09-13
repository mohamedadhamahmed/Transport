<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ZatcaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\EmployeeDiscountSettingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InvoiceReturnController;
use App\Http\Controllers\DraftInvoiceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\TranslationController;

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DeliveryReturnController;
use App\Http\Controllers\TaxController;
// تسليم منتج
    use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DeliveryNoteReturnController;
use App\Http\Controllers\DeliveryNoteConvertController;


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
use App\Http\Controllers\BomController;
use App\Http\Controllers\ManufacturingOrderController;
use App\Http\Controllers\ManufacturingOrderStatusController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\WorkstationController;
use App\Http\Controllers\EmployeeContractController;
use App\Http\Controllers\NotificationController;


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


Route::prefix('manufacturing')->name('manufacturing.')->group(function () {

    // محطات العمل
    Route::get('workstations', [WorkstationController::class, 'index'])->name('workstations.index');
    Route::post('workstations', [WorkstationController::class, 'store'])->name('workstations.store');
    Route::put('workstations/{workstation}', [WorkstationController::class, 'update'])->name('workstations.update');
    Route::delete('workstations/{workstation}', [WorkstationController::class, 'destroy'])->name('workstations.destroy');

    // حالات الأوامر (قابلة للتخصيص)
    Route::get('statuses', [ManufacturingOrderStatusController::class, 'index'])->name('statuses.index');
    Route::post('statuses', [ManufacturingOrderStatusController::class, 'store'])->name('statuses.store');
    Route::put('statuses/{status}', [ManufacturingOrderStatusController::class, 'update'])->name('statuses.update');
    Route::delete('statuses/{status}', [ManufacturingOrderStatusController::class, 'destroy'])->name('statuses.destroy');

    // قوائم مواد الإنتاج (BOM)
    Route::get('bom', [BomController::class, 'index'])->name('bom.index');
    Route::get('bom/create', [BomController::class, 'create'])->name('bom.create');
    Route::post('bom', [BomController::class, 'store'])->name('bom.store');
    Route::get('bom/{bom}/edit', [BomController::class, 'edit'])->name('bom.edit');
    Route::put('bom/{bom}', [BomController::class, 'update'])->name('bom.update');
    Route::delete('bom/{bom}', [BomController::class, 'destroy'])->name('bom.destroy');

    // خطط الإنتاج
    Route::get('production-plans', [ProductionPlanController::class, 'index'])->name('production-plans.index');
    Route::get('production-plans/create', [ProductionPlanController::class, 'create'])->name('production-plans.create');
    Route::post('production-plans', [ProductionPlanController::class, 'store'])->name('production-plans.store');
    Route::get('production-plans/{productionPlan}/edit', [ProductionPlanController::class, 'edit'])->name('production-plans.edit');
    Route::put('production-plans/{productionPlan}', [ProductionPlanController::class, 'update'])->name('production-plans.update');
    Route::delete('production-plans/{productionPlan}', [ProductionPlanController::class, 'destroy'])->name('production-plans.destroy');
    Route::post('production-plans/{productionPlan}/convert', [ProductionPlanController::class, 'convertToOrder'])->name('production-plans.convert');

    // أوامر التصنيع
    Route::get('orders', [ManufacturingOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [ManufacturingOrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [ManufacturingOrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}/edit', [ManufacturingOrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{order}', [ManufacturingOrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [ManufacturingOrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('orders/{order}/complete', [ManufacturingOrderController::class, 'complete'])->name('orders.complete');
    Route::put('orders/{order}/items/{itemId}', [ManufacturingOrderController::class, 'updateItem'])->name('orders.items.update');
    Route::post('orders/{order}/indirect-costs', [ManufacturingOrderController::class, 'addIndirectCost'])->name('orders.indirect-costs.store');
    Route::delete('orders/{order}/indirect-costs/{indirectCostId}', [ManufacturingOrderController::class, 'removeIndirectCost'])->name('orders.indirect-costs.destroy');
});



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

// ===== سند تسليم جديد =====
Route::get('/delivery-note', [DeliveryNoteController::class, 'create'])->name('deliverynote.create');
Route::post('/delivery-note', [DeliveryNoteController::class, 'store'])->name('deliverynote.store');
Route::get('/delivery-note/products/search', [DeliveryNoteController::class, 'searchProducts'])->name('deliverynote.products.search');
Route::get('/delivery-note/products/pick', [DeliveryNoteController::class, 'pickProducts'])->name('deliverynote.products.pick');
Route::post('/delivery-note/customers/quick', [DeliveryNoteController::class, 'quickStoreCustomer'])->name('deliverynote.customers.quick');
Route::post('/delivery-note/products/quick', [DeliveryNoteController::class, 'quickStoreProduct'])->name('deliverynote.products.quick');

// ===== سجل سندات التسليم =====
Route::get('/delivery-note/history', [DeliveryNoteController::class, 'history'])->name('deliverynote.history');
Route::get('/delivery-note/{id}/show', [DeliveryNoteController::class, 'show'])->name('deliverynote.show');

// ===== تعديل سند تسليم معلّق (لسه محولش/رجعش منه حاجة) =====
Route::get('/delivery-note/{id}/edit', [DeliveryNoteController::class, 'edit'])->name('deliverynote.edit');
Route::put('/delivery-note/{id}', [DeliveryNoteController::class, 'update'])->name('deliverynote.update');

// ===== مرتجع سند تسليم =====
Route::get('/delivery-note/{id}/return', [DeliveryNoteReturnController::class, 'create'])->name('deliverynote.return.create');
Route::post('/delivery-note/{id}/return', [DeliveryNoteReturnController::class, 'store'])->name('deliverynote.return.store');

// ===== اعتماد وتحويل سندات التسليم لفاتورة ضريبية حقيقية =====
Route::get('/delivery-note/convert', [DeliveryNoteConvertController::class, 'index'])->name('deliverynote.convert.index');
Route::get('/delivery-note/convert/{customer}', [DeliveryNoteConvertController::class, 'create'])->name('deliverynote.convert.create');
Route::post('/delivery-note/convert/{customer}', [DeliveryNoteConvertController::class, 'store'])->name('deliverynote.convert.store');



    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/zatca', [ZatcaController::class, 'index'])->name('zatca.index');
    Route::post('/zatca/{invoice}/send', [ZatcaController::class, 'send'])->name('zatca.send');
    Route::post('/zatca/send-all', [ZatcaController::class, 'sendAll'])->name('zatca.send-all');
    Route::get('/zatca/{invoice}/download-xml', [ZatcaController::class, 'downloadXml'])->name('zatca.download-xml');

    // إشعار دائن (مرتجع مبيعات) - نفس فكرة إرسال/تحميل XML الفاتورة
    // العادية فوق، بس بمرجع reference_value لمجموعة صفوف invoice_returns
    // (راجع InvoiceReturnController@store/print) بدل invoice id.
    Route::post('/zatca/returns/{referenceValue}/send', [ZatcaController::class, 'sendReturn'])->name('zatca.send-return');
    Route::get('/zatca/returns/{referenceValue}/download-xml', [ZatcaController::class, 'downloadCreditNoteReturnXml'])->name('zatca.download-credit-note');

    // جرس الإشعارات في الهيدر - endpoint خفيف بيرجع عدّادين (فواتير
    // زاتكا فشلت + منتجات وصلت لحد تنبيه المخزون)، كل واحد متفلتر على
    // صلاحية المستخدم. راجع NotificationController@summary.
    Route::get('/notifications/summary', [NotificationController::class, 'summary'])->name('notifications.summary');

    // صفحة تانية من "عمليات اليوم" (زرار "عرض المزيد" في القايمة) -
    // راجع NotificationController@recentOperationsPage.
    Route::get('/notifications/recent', [NotificationController::class, 'recentOperationsPage'])->name('notifications.recent');
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
    // تسليم منتج
    Route::get('/delivery-product', [DeliveryController::class, 'create'])->name('delivery.create');
    Route::post('/delivery-product', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery-product/products/search', [DeliveryController::class, 'searchProducts'])->name('delivery.products.search');
    Route::get('/delivery-product/products/pick', [DeliveryController::class, 'pickProducts'])->name('delivery.products.pick');
    Route::post('/delivery-product/customers/quick', [DeliveryController::class, 'quickStoreCustomer'])->name('delivery.customers.quick');
    Route::post('/delivery-product/products/quick', [DeliveryController::class, 'quickStoreProduct'])->name('delivery.products.quick');

    // التسليمات السابقة
    Route::get('/delivery-history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::get('/delivery-history/{id}/show', [DeliveryController::class, 'show'])->name('delivery.show');

    // مرتجع تسليمات
    Route::get('/delivery-return/{id}', [DeliveryReturnController::class, 'create'])->name('delivery.return.create');
    Route::post('/delivery-return/{id}', [DeliveryReturnController::class, 'store'])->name('delivery.return.store');


    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/system', [SettingsController::class, 'updateSystemSettings'])->name('settings.system.update');
    Route::put('/settings/zakat', [SettingsController::class, 'updateZakatSettings'])->name('settings.zakat.update');
    Route::get('/settings/onboarding', [SettingsController::class, 'onboarding'])->name('settings.onboarding');
    Route::post('/settings/onboarding', [SettingsController::class, 'storeOnboarding'])->name('settings.onboarding.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');

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
    // مسارات الفواتير الأساسية والإضافية
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/drafts', [DraftInvoiceController::class, 'index'])->name('invoices.drafts.index');
    Route::delete('/invoices/drafts/{draft}', [DraftInvoiceController::class, 'destroy'])->name('invoices.drafts.destroy');
    Route::post('/invoices/drafts/{draft}/approve', [InvoiceController::class, 'approveDraft'])->name('invoices.drafts.approve');
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

    // مسارات مرتجع المبيعات - لازم تكون هنا، قبل /invoices/{invoice}،
    // عشان لارافيل ميفهمش "returns" على إنها ID فاتورة (route model binding).
    // الترتيب هنا كمان مهم: الروتس الثابتة (create/search/index) لازم تسبق
    // أي روت فيه باراميتر (زي {invoice}/items أو {referenceValue}/print).
    Route::get('/invoices/returns', [InvoiceReturnController::class, 'index'])->name('invoices.returns.index');
    Route::get('/invoices/returns/create', [InvoiceReturnController::class, 'create'])->name('invoices.returns.create');
    Route::get('/invoices/returns/search', [InvoiceReturnController::class, 'searchInvoice'])->name('invoices.returns.search');
    Route::post('/invoices/returns', [InvoiceReturnController::class, 'store'])->name('invoices.returns.store');
    Route::get('/invoices/returns/{invoice}/items', [InvoiceReturnController::class, 'invoiceItems'])->name('invoices.returns.items');
    Route::get('/invoices/returns/{referenceValue}/print', [InvoiceReturnController::class, 'print'])->name('invoices.returns.print');

    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::get('/quotations/customer-history/{customer}', [QuotationController::class, 'customerHistory'])->name('quotations.customer-history');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
    Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'showQuotation'])
        ->name('quotations.show')
        ->middleware('auth');
    Route::get('/quotations/{quotation}/download', [QuotationController::class, 'downloadQuotationPdf'])
        ->name('quotations.download');
    // مسارات التعديل والـ PDF التي كانت ناقصة
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/public-pdf', [InvoiceController::class, 'publicPdf'])
        ->name('invoices.public-pdf')
        ->middleware('signed');
    // مسارات البحث والمنتجات والعملاء السريعة
    Route::get('/invoices/products/pick', [InvoiceController::class, 'pickProducts'])->name('invoices.products.pick');
    Route::get('/invoices/products/search', [InvoiceController::class, 'searchProducts'])->name('invoices.products.search');
    Route::post('/invoices/products/quick', [InvoiceController::class, 'quickStoreProduct'])->name('invoices.products.quick');
    // آخر سعر بيع لكل منتج لعميل معيّن - بادچ "آخر سعر لهذا العميل" في
    // مودال اختيار منتج وجدول أصناف الفاتورة.
    Route::get('/invoices/products/last-prices', [InvoiceController::class, 'lastCustomerPrices'])->name('invoices.products.last-prices');
    Route::post('/invoices/customers/quick', [InvoiceController::class, 'quickStoreCustomer'])->name('invoices.customers.quick');
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
Route::get('/reports/stock-adjustments', [ReportController::class, 'stockAdjustments'])
    ->name('reports.stock_adjustments')
    ->middleware(['auth']);
    Route::get('/reports/accounts', [ReportController::class, 'accountsIndex'])->name('reports.accounts.index');
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

    // ===== قسم المبيعات =====
    Route::get('/reports/sales', [ReportController::class, 'salesIndex'])->name('reports.sales.index');
    Route::get('/reports/sales/summary', [ReportController::class, 'salesSummary'])->name('reports.sales.summary');
    Route::get('/reports/sales/profits', [ReportController::class, 'salesProfits'])->name('reports.sales.profits');
    Route::get('/reports/sales/employee-profits', [ReportController::class, 'salesEmployeeProfits'])->name('reports.sales.employee-profits');
    Route::get('/reports/sales/top-products', [ReportController::class, 'topSellingProducts'])->name('reports.sales.top-products');
    Route::get('/reports/sales/by-customer', [ReportController::class, 'salesByCustomer'])->name('reports.sales.by-customer');
    Route::get('/reports/sales/by-product', [ReportController::class, 'salesByProduct'])->name('reports.sales.by-product');
    Route::get('/reports/sales/returns', [ReportController::class, 'salesReturns'])->name('reports.sales.returns');
    Route::get('/reports/sales/by-employee', [ReportController::class, 'salesByEmployee'])->name('reports.sales.by-employee');

    // ===== قسم تسليم المنتج =====
    Route::get('/reports/delivery', [ReportController::class, 'deliveryIndex'])->name('reports.delivery.index');
    Route::get('/reports/delivery/summary', [ReportController::class, 'deliverySummary'])->name('reports.delivery.summary');
    Route::get('/reports/delivery/pending', [ReportController::class, 'deliveryPending'])->name('reports.delivery.pending');
    Route::get('/reports/delivery/by-employee', [ReportController::class, 'deliveryByEmployee'])->name('reports.delivery.by-employee');

    // ===== قسم المشتريات =====
    Route::get('/reports/purchases', [ReportController::class, 'purchasesIndex'])->name('reports.purchases.index');
    Route::get('/reports/purchases/summary', [ReportController::class, 'purchasesSummary'])->name('reports.purchases.summary');
    Route::get('/reports/purchases/by-supplier', [ReportController::class, 'purchasesBySupplier'])->name('reports.purchases.by-supplier');
    Route::get('/reports/purchases/by-product', [ReportController::class, 'purchasesByProduct'])->name('reports.purchases.by-product');
    Route::get('/reports/purchases/purchases-vs-sales', [ReportController::class, 'purchasesVsSales'])->name('reports.purchases.purchases-vs-sales');
    Route::get('/reports/purchases/returns', [ReportController::class, 'purchasesReturns'])->name('reports.purchases.returns');
    Route::get('/reports/purchases/by-employee', [ReportController::class, 'purchasesByEmployee'])->name('reports.purchases.by-employee');

    // ===== قسم المنتجات =====
    Route::get('/reports/products', [ReportController::class, 'productsIndex'])->name('reports.products.index');
    Route::get('/reports/products/stock', [ReportController::class, 'productsStock'])->name('reports.products.stock');
    Route::get('/reports/products/low-stock', [ReportController::class, 'productsLowStock'])->name('reports.products.low-stock');
    Route::get('/reports/products/transfers', [ReportController::class, 'productsStockTransfers'])->name('reports.products.transfers');
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

Route::middleware(['auth'])->prefix('stock-transfers')->name('stock-transfers.')->group(function () {
    Route::get('/choose-branch', [StockTransferController::class, 'chooseBranch'])->name('choose-branch');
    Route::get('/products/search', [StockTransferController::class, 'searchProducts'])->name('products.search');
    Route::get('/branches/{branch}/users', [StockTransferController::class, 'branchUsers'])->name('branch-users');
    Route::get('/create/{branch}', [StockTransferController::class, 'create'])->name('create');
    Route::post('/', [StockTransferController::class, 'store'])->name('store');
    Route::get('/receive/{branch}', [StockTransferController::class, 'receiveForm'])->name('receive-form');
    Route::get('/{stockTransfer}/details', [StockTransferController::class, 'transferDetails'])->name('details');
    Route::post('/{stockTransfer}/receive', [StockTransferController::class, 'confirmReceive'])->name('confirm-receive');
    Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->name('show');
    Route::get('/', [StockTransferController::class, 'index'])->name('index');
});

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
