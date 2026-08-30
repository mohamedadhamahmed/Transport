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

use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DeliveryReturnController;
use App\Http\Controllers\TaxController;
// تسليم منتج
    use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DeliveryNoteReturnController;
use App\Http\Controllers\DeliveryNoteConvertController;


use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductInventoryController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

// ===== الموردين =====
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');

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
});

Route::middleware('auth')->group(function () {
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('products', ProductController::class);
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
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::post('/purchases/suppliers/quick', [PurchaseController::class, 'quickStoreSupplier'])->name('purchases.suppliers.quick');
    Route::get('/purchases/product-cost-history/{product}', [PurchaseController::class, 'productCostHistory'])->name('purchases.product-cost-history');

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
    Route::post('/invoices/customers/quick', [InvoiceController::class, 'quickStoreCustomer'])->name('invoices.customers.quick');
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
