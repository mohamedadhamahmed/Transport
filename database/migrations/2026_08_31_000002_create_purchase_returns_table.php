<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول purchase_returns (رأس فاتورة مرتجع المشتريات)
|--------------------------------------------------------------------------
| نفس فلسفة جدول purchases بالظبط (رأس + بنود في purchase_return_items)،
| بس هنا بيمثل "إرجاع" كل أو بعض أصناف فاتورة مشتريات (purchase_id)
| موجودة بالفعل ومحفوظة قبل كده - مش ممكن تعملي مرتجع من غير ما تختاري
| فاتورة شراء أصلية (نفس اختيارك في الأسئلة السابقة).
|
| refund_account_id: لو الفاتورة الأصلية كانت "دفع فوري" (purchases.
| payment_account_id مش فاضي) - ده حساب الاسترداد (نقدي/بنك/شبكة) اللي
| المبلغ هيرجعله فعليًا؛ افتراضيًا نفس حساب الدفع الأصلي، لكن سايبين
| الحرية للمستخدم يغيّره وقت عمل المرتجع (مثلاً اتدفعت كاش والاسترداد
| هيبقى تحويل بنكي). لو الفاتورة الأصلية كانت "آجل" (payment_account_id
| فاضي) الحقل ده لازم يفضل فاضي، والمرتجع هيتم بتخفيض رصيد المورد
| مباشرة بدل استرداد فعلي.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();

            // null = مرتجع آجل (تخفيض في رصيد المورد) - غير كده = ID
            // حساب مالي (نقدي/بنك/شبكة) هيسترد منه المبلغ فعليًا.
            $table->unsignedBigInteger('refund_account_id')->nullable();

            $table->string('return_number')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('invoice_level_discount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('total_quantity', 14, 2)->default(0);

            $table->text('reason')->nullable();
            $table->date('return_date')->nullable();

            $table->timestamps();

            $table->index('purchase_id');
            $table->index('supplier_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
