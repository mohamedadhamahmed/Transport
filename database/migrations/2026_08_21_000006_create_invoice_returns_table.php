<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // كان اسم الجدول ده "return_sales" - سميته invoice_returns عشان يوضح
    // إنه "أصناف مرتجعة من فاتورة" (نفس فكرة invoice_items بس للمرتجعات).
    public function up(): void
    {
        Schema::create('invoice_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // الغرض الدقيق منها مش موثق من النظام القديم (كان اسمها "value")
            $table->string('reference_value')->nullable()->default('empty');

            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->string('tax_rate')->nullable();

            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('invoice_discount_amount', 10, 2)->default(0);
            // مبلغ المرتجع اللي رجع على الشبكة/البطاقة تحديدًا
            $table->decimal('card_refund_amount', 10, 2)->default(0);

            $table->boolean('is_sent_to_zatca')->default(false);
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_returns');
    }
};
