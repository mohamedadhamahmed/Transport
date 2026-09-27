<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * فواتير النقليات: بدل ما السطر يبقى "منتج × كمية"، كل سطر هو
     * "نقلة" = شاحنة + من/إلى + سعر النقلة + (تحويلة اختيارية بمبلغ).
     */
    public function up(): void
    {
        Schema::create('transport_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('issue_date');
            $table->time('issue_time')->nullable();

            $table->unsignedInteger('trips_count')->default(0);
            $table->decimal('trips_total', 14, 2)->default(0);      // مجموع أسعار النقلات
            $table->decimal('transfers_total', 14, 2)->default(0);  // مجموع التحويلات
            $table->decimal('discount_amount', 14, 2)->default(0);  // خصم على مستوى الفاتورة
            $table->decimal('subtotal', 14, 2)->default(0);         // قبل الضريبة (بعد الخصم)
            $table->decimal('tax_rate', 6, 4)->default(0.15);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);            // الإجمالي شامل الضريبة

            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit', 'split'])->default('cash');
            $table->decimal('cash_amount', 14, 2)->default(0);
            $table->decimal('bank_amount', 14, 2)->default(0);
            $table->decimal('credit_amount', 14, 2)->default(0);

            $table->string('po_number')->nullable();   // رقم أمر الشراء / مرجع العميل
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_invoice_id')->constrained('transport_invoices')->cascadeOnDelete();
            $table->foreignId('truck_id')->constrained('trucks')->restrictOnDelete();
            $table->string('truck_snapshot')->nullable();   // رقم اللوحة/الاسم وقت الفاتورة

            $table->date('trip_date')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->string('waybill_number')->nullable();   // رقم بوليصة/سند الشحن
            $table->decimal('trip_price', 12, 2)->default(0);

            $table->boolean('has_transfer')->default(false);
            $table->string('transfer_location')->nullable(); // مكان التحويلة
            $table->decimal('transfer_price', 12, 2)->default(0);

            $table->decimal('line_total', 12, 2)->default(0); // سعر النقلة + التحويلة (قبل الضريبة)
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_invoice_items');
        Schema::dropIfExists('transport_invoices');
    }
};
