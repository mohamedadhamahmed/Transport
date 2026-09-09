<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * بنود أمر التصنيع: نسخة من bom_items بتتحسب على كمية الأمر الفعلية،
 * وبتسجل الكمية المستهلكة فعليًا من المخزون (ممكن تختلف عن المطلوبة
 * لو حصل هدر مثلاً).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained('manufacturing_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->decimal('required_quantity', 14, 3);
            $table->decimal('consumed_quantity', 14, 3)->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_items');
    }
};
