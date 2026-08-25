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


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/zatca', [ZatcaController::class, 'index'])->name('zatca.index');
    Route::post('/zatca/{invoice}/send', [ZatcaController::class, 'send'])->name('zatca.send');
    Route::post('/zatca/send-all', [ZatcaController::class, 'sendAll'])->name('zatca.send-all');
    Route::get('/zatca/{invoice}/download-xml', [ZatcaController::class, 'downloadXml'])->name('zatca.download-xml');
});


Route::middleware(['auth'])->group(function () {
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

    // مسارات التعديل والـ PDF التي كانت ناقصة
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

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