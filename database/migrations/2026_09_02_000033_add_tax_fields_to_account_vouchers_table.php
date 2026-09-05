<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دعم "خاضع لضريبة؟" في سندات القبض والصرف (App\Models\AccountVoucher).
 * لو السند خاضع لضريبة، المبلغ المُدخل (amount) بيتعامل معاه كمبلغ
 * شامل الضريبة (زي فاتورة عادي) وبيتقسم لـ:
 *   - net_amount: بيروح لحساب الطرف التاني (counterpart_account) زي
 *     ما هو، وده اللي فعليًا "قيمة" السند من غير ضريبة.
 *   - tax_amount: بيروح لحساب ضريبة القيمة المضافة (parent_account_number
 *     = 102) الخاص بفرع حساب الخزينة/البنك المختار في السند - نفس
 *     الحساب اللي بيتسجل عليه ضريبة المبيعات/المشتريات بالظبط
 *     (راجع InvoiceController/PurchaseController).
 *
 * حساب الخزينة (treasury_account) نفسه بياخد أثر المبلغ الكامل
 * (amount) زي ما هو - لإن الفلوس اللي فعليًا دخلت/خرجت من/لحساب
 * الخزينة هي المبلغ الكامل شامل الضريبة.
 *
 * tax_rate بيتسجل هنا كـ "نسخة" وقت إنشاء السند (snapshot) - عشان لو
 * نسبة الضريبة في جدول taxes اتغيرت بعدين، السند القديم يفضل عارض
 * النسبة اللي اتحسب بيها فعليًا وقتها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table) {
            $table->boolean('is_taxable')->default(false)->after('amount');
            $table->unsignedBigInteger('tax_id')->nullable()->after('is_taxable');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax_id');
            $table->decimal('net_amount', 15, 2)->nullable()->after('tax_rate');
            $table->decimal('tax_amount', 15, 2)->nullable()->after('net_amount');
            // الحساب الفعلي اللي اتسجلت عليه الضريبة (ضريبة فرع كذا) -
            // بنحفظه صريح هنا عشان تعديل/حذف السند لاحقًا يعرف يرجّع
            // نفس الحساب ده بالظبط، حتى لو حساب الخزينة اتغيّر في التعديل.
            $table->unsignedBigInteger('vat_account_id')->nullable()->after('tax_amount');

            $table->index('tax_id');
            $table->index('vat_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table) {
            $table->dropIndex(['tax_id']);
            $table->dropIndex(['vat_account_id']);
            $table->dropColumn(['is_taxable', 'tax_id', 'tax_rate', 'net_amount', 'tax_amount', 'vat_account_id']);
        });
    }
};
