<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // كان اسم الجدول ده "sales" في النظام القديم وده كان مربك جدًا -
    // هو فعليًا جدول "أصناف الفاتورة" فسميته invoice_items عشان يبقى واضح.
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->string('tax_rate')->nullable();

            $table->decimal('returned_quantity', 10, 2)->default(0);
            $table->decimal('returned_discount_amount', 10, 2)->default(0);
            // كمية المخزون المتبقية من الصنف وقت البيع (Snapshot)
            $table->decimal('remaining_quantity', 10, 2)->default(0);
            // اسم الصنف وقت البيع (نسخة تاريخية حتى لو اتغير اسم المنتج بعدين)
            $table->string('product_name_snapshot')->nullable();

            $table->boolean('is_finalized')->default(false);
            $table->string('unit')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
