<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * بنود سند تحويل المخزون. كل فرع في my-erp عنده صفوف Product منفصلة
 * بتاعته (مفيش منتج واحد مشترك بين الفروع بكمية لكل فرع)، فبنخزّن هنا
 * "نسخة" من بيانات المنتج وقت التحويل (زي فكرة product_name_snapshot
 * في InvoiceItem بالظبط) عشان لو المنتج الأصلي اتعدل أو اتمسح بعد كده،
 * بيانات السند تفضل زي ما كانت وقت التنفيذ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            // منتج الفرع المرسل الأصلي
            $table->foreignId('from_product_id')->nullable()
                ->constrained('products')->nullOnDelete();
            // منتج الفرع المستلم بعد التأكيد (بيتحدد وقت الاستلام فقط)
            $table->foreignId('to_product_id')->nullable()
                ->constrained('products')->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->string('product_code_snapshot')->nullable();
            $table->string('unit_snapshot')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost_snapshot', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
