<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول purchase_orders (أوامر الشراء من الموردين)
|--------------------------------------------------------------------------
| أمر الشراء هو طلب رسمي بيتبعت للمورد قبل وصول البضاعة، ومالوش أي تأثير
| على المخزون أو الحسابات المالية إطلاقًا (بعكس فاتورة المشتريات). لما
| البضاعة توصل فعليًا، أمر الشراء بيتحول لفاتورة مشتريات حقيقية (دفعة
| واحدة كاملة في هذه المرحلة) عن طريق شاشة "فاتورة مشتريات جديدة" نفسها
| (مع تعبئة تلقائية للبيانات من أمر الشراء)، وعندها بس بيتسجل تأثيره على
| المخزون والحسابات.
|
| status: pending (لسه منتظر) / converted (اتحول لفاتورة) / cancelled
| (اتلغى).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->string('order_number')->nullable();
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

            // pending / converted / cancelled
            $table->string('status')->default('pending');

            // بيتعبوا بس لما الأمر يتحول لفاتورة مشتريات فعلية.
            $table->unsignedBigInteger('converted_purchase_id')->nullable();
            $table->unsignedBigInteger('converted_by')->nullable();
            $table->timestamp('converted_at')->nullable();

            $table->timestamps();

            $table->index('supplier_id');
            $table->index('branch_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
