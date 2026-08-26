<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول quotation_items (بنود التسعيرة)
|--------------------------------------------------------------------------
| نفس شكل بيانات invoice_items بالظبط (unit_price, quantity,
| discount_amount, tax_rate...) عشان لو التسعيرة اتعمدت، تحويلها لبنود
| فاتورة حقيقية (InvoiceItem) يبقى مباشر من غير أي تعديل في الحسابات.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 6, 4)->default(0.15);
            $table->decimal('tax_amount', 12, 2)->default(0);

            // نسخة من اسم/كود المنتج وقت إنشاء التسعيرة - عشان لو المنتج
            // اتغيّر اسمه أو اتشال بعدين، التسعيرة القديمة تفضل واضحة.
            $table->string('product_name_snapshot')->nullable();
            $table->string('product_code_snapshot')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('quotation_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
