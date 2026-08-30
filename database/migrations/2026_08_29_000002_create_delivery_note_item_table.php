<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_note_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('invoice_id');
            $table->double('Discount_Value', 8, 2)->default(0.00);
            $table->unsignedBigInteger('branch_id');
            $table->double('Added_Value', 8, 2)->default(0.00);
            $table->decimal('Unit_Price', 8, 2)->default(0.00);
            $table->double('quantity')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->double('discountreturn')->default(0);
            $table->double('quantityreturn')->default(0);
            $table->integer('save')->default(0);
            $table->double('reamingQuantity')->default(0);
            $table->text('unit')->nullable();

            $table->index('product_id', 'sales_product_id_foreign');
            $table->index('invoice_id', 'sales_invoice_id_foreign');
            $table->index('branch_id', 'sales_branch_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_item');
    }
};
