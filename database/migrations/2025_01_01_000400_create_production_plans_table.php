<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * خطة الإنتاج: نية/جدولة لإنتاج كمية من منتج تام خلال فترة زمنية،
 * ممكن تتحول لاحقًا لأمر تصنيع فعلي (manufacturing_orders.production_plan_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('code')->unique();
            $table->string('name');

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('bill_of_material_id')->nullable()->constrained('bill_of_materials')->nullOnDelete();

            // عميل مرتبط بالخطة لو موجود (اختياري - بدون قيد أجنبي صارم
            // لأن جدول العملاء ممكن يكون باسم مختلف في المشروع)
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            $table->decimal('quantity', 14, 3);
            $table->date('date_start');
            $table->date('date_end');

            // مصدر الخطة: يدوي، من طلب بيع... إلخ
            $table->string('source')->default('manual');

            $table->foreignId('status_id')->nullable()->constrained('manufacturing_order_statuses')->nullOnDelete();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
