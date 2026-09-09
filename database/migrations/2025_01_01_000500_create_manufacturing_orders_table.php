<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أمر التصنيع: التنفيذ الفعلي - بيسحب المواد الخام من المخزون
 * (حسب bom_items) وبيضيف الكمية المنتجة لرصيد المنتج التام في
 * جدول products عند اكتماله. ممكن يتولد من خطة إنتاج أو يتعمل يدوي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('code')->unique();
            $table->string('name');

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('bill_of_material_id')->nullable()->constrained('bill_of_materials')->nullOnDelete();
            $table->foreignId('production_plan_id')->nullable()->constrained('production_plans')->nullOnDelete();
            $table->foreignId('workstation_id')->nullable()->constrained('workstations')->nullOnDelete();

            $table->unsignedBigInteger('customer_id')->nullable()->index();

            $table->decimal('quantity', 14, 3);
            $table->date('date_start');
            $table->date('date_end');

            $table->decimal('direct_materials_cost', 14, 2)->default(0);
            $table->decimal('indirect_costs_total', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);

            $table->foreignId('status_id')->nullable()->constrained('manufacturing_order_statuses')->nullOnDelete();

            // تاريخ اكتمال الأمر فعليًا (لحظة إضافة الكمية المنتجة للمخزون)
            $table->timestamp('completed_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_orders');
    }
};
