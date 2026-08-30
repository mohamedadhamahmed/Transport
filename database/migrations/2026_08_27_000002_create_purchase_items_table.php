<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id')->nullable();

            // سعر الشراء للوحدة (اللي هيتحدث بيه purchase_price المنتج)
            $table->decimal('unit_price', 12, 2)->default(0);
            // سعر البيع بدون ضريبة (اختياري) - لو اتبعت بيحدث sale_price
            // المنتج، ولو سايبها فاضية بيفضل سعر البيع القديم زي ما هو.
            $table->decimal('sale_price', 12, 2)->nullable();

            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 6, 4)->default(0.15);
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->string('product_name_snapshot')->nullable();
            $table->string('product_code_snapshot')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('purchase_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
