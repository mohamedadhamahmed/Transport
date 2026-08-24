<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('purchase_price', 10, 2)->unsigned()->default(0);
            $table->decimal('sale_price', 10, 2)->unsigned()->default(0);
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->string('location')->nullable();
            $table->string('code')->nullable();
            $table->decimal('tax_value', 10, 2)->default(0);
            $table->bigInteger('total_sold')->default(0);
            $table->string('name_en')->nullable();
            $table->text('notes')->nullable();
            $table->string('unit')->default('piece');
            $table->unsignedInteger('low_stock_alert_quantity')->default(10);
            // منتج "أب" في حالة كان ده منتج بديل/مرتبط (كان اسمه main_product)
            $table->unsignedBigInteger('parent_product_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->decimal('wholesale_price', 10, 2)->default(0);
            $table->string('photo')->nullable();
            // مرتبط بميزة "تركيب المنتجات" (products_mixes) - لسه مش مبني كجدول
            $table->unsignedBigInteger('product_mix_id')->nullable();
            // مرتبط بمجموعة المنتج (products_groups) - لسه مش مبني كجدول
            $table->unsignedBigInteger('product_group_id')->nullable()->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
