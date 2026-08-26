<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول quotations (التسعيرات)
|--------------------------------------------------------------------------
| "تسعيرة" = عرض سعر لعميل، منفصل تمامًا عن جدول invoices - عشان التسعيرة
| متاخدش رقم فاتورة رسمي ومتأثرش في المخزون أو القيود المحاسبية إلا لما
| حد يعتمدها فعلاً (status = approved)، وقتها بس بتتحول لفاتورة حقيقية
| في جدول invoices (عن طريق QuotationController@approve).
|
| ملحوظة: زي جدول draft_invoices بالظبط - من غير foreign key constraints
| على customer_id/branch_id/product_id عشان نتجنب أي فشل في الـ migration
| لو أنواع الأعمدة عندك مختلفة شوية. التحقق من الوجود بيتم على مستوى
| الكود (validation) في QuotationController.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();

            // بيانات الدفع المتوقعة - بتتحفظ من دلوقتي عشان لو التسعيرة
            // اتعمدت، تتحول لفاتورة فورًا من غير ما نرجع نسأل تاني.
            $table->string('payment_method')->default('cash');
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->decimal('bank_amount', 12, 2)->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('invoice_level_discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('total_quantity', 12, 2)->default(0);

            $table->text('note')->nullable();
            $table->string('purchase_order_number')->nullable();

            // pending: لسه تحت المراجعة | approved: اتعمدت وتحولت لفاتورة
            // فعلية | rejected: اتلغت من غير ما تتحول لفاتورة.
            $table->string('status')->default('pending');

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // بترتبط بالفاتورة الناتجة بعد الاعتماد (لو حبيتي تشوفي
            // الفاتورة مباشرة من شاشة التسعيرة).
            $table->unsignedBigInteger('invoice_id')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index('branch_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
