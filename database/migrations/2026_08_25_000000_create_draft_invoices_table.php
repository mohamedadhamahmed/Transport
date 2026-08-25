<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول draft_invoices
|--------------------------------------------------------------------------
| جدول منفصل تمامًا عن invoices - عشان "المسودة" متاخدش رقم فاتورة رسمي
| (invoice_number) خالص. الرقم الرسمي بياخد قيمته بس لما الفاتورة تتأكد
| (is_finalized = true) وتتسجل في جدول invoices الحقيقي. كده لو مسودة
| اتلغت مفيش أي فجوة في تسلسل أرقام الفواتير.
|
| ملحوظة: مفيش هنا foreign key constraints على customer_id/branch_id
| عشان نتجنب أي فشل في الـ migration لو أنواع الأعمدة عندك مختلفة شوية -
| التحقق من وجود العميل/الفرع بيتم على مستوى الكود (validation) بدل كده،
| زي باقي التحقق في InvoiceController.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draft_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('payment_method')->default('cash');
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->decimal('bank_amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->decimal('invoice_level_discount', 12, 2)->default(0);
            // مصفوفة الأصناف كاملة (product_id, name, code, quantity,
            // unit_price, purchase_price, discount_amount, tax_rate) -
            // بنفس الشكل اللي بتبعته صفحة إنشاء الفاتورة في items_json.
            $table->json('items');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_invoices');
    }
};
