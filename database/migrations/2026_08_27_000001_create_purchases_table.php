<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول purchases (فواتير المشتريات)
|--------------------------------------------------------------------------
| نفس فلسفة جدول invoices لكن للمشتريات - أهم فرق: طريقة الدفع هنا مش
| قايمة ثابتة (كاش/بنك/شبكة) زي الفواتير، لأن الشاشة القديمة اللي
| بعتيهالي كانت بتخلي "طريقة الدفع" فعليًا هي اختيار حساب مالي محدد
| (مثلاً "نقدي (الخزنة)") من شجرة الحسابات - يعني بندفع من حساب معيّن
| بالظبط، مش نوع دفع عام. فحطينا عمود payment_account_id (رقم حساب من
| جدول financial_accounts) - لو فاضي (null) يبقى معناها "آجل" (على
| حساب المورد)، ولو فيه رقم حساب يبقى الفاتورة اتدفعت فورًا من الحساب
| ده (نقدي/بنك/شبكة، حسب الحساب اللي هي عليه).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();

            // null = آجل على حساب المورد، غير كده = ID حساب مالي
            // (نقدي/بنك/شبكة) اتدفعت منه الفاتورة فورًا.
            $table->unsignedBigInteger('payment_account_id')->nullable();

            $table->string('supplier_invoice_number')->nullable();
            $table->string('purchase_number')->nullable();

            // حقلين نصيين بسيطين (v1) - لو حابب تتحولوا لجداول كاملة
            // (مخازن فرعية / مراكز تكلفة) بعدين قوللي.
            $table->string('warehouse_name')->nullable();
            $table->string('cost_center')->nullable();

            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('invoice_level_discount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('total_quantity', 14, 2)->default(0);

            $table->text('note')->nullable();
            $table->date('issue_date')->nullable();

            $table->timestamps();

            $table->index('supplier_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
