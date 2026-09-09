<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قائمة مواد الإنتاج (BOM): "وصفة" تحدد إيه المنتج التام واللي بيتكون
 * من إيه من مواد خام (منتجات تانية في نفس جدول products) بكميات معينة،
 * لإنتاج كمية معينة (production_quantity) من المنتج التام. أوامر
 * التصنيع بتتبني على أساس BOM معينة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('code')->unique();
            $table->string('name');

            // المنتج التام (الناتج) - نفس جدول المنتجات الموجود بالفعل
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // الكمية اللي القائمة دي بتنتجها من المنتج التام (زي 10 في المثال)
            $table->decimal('production_quantity', 14, 3)->default(1);

            $table->decimal('total_cost', 14, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};
