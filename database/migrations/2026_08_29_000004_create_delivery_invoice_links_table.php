<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول تتبع (Audit Trail): كل مرة يتم فيها تحويل جزء أو كل كمية من بند
 * تسليم لفاتورة ضريبية حقيقية، بيتسجل سطر هنا يربط بند التسليم الأصلي
 * بالفاتورة الناتجة - عشان نقدر نرجع نشوف "الفاتورة دي طلعت من إيه"
 * ولو حصل فوترة جزئية لنفس البند أكتر من مرة في فواتير مختلفة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_invoice_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_item_id'); // FK -> delivery_note_item.id
            $table->unsignedBigInteger('invoice_id');    // FK -> invoices.id (النظام الحقيقي)
            $table->unsignedBigInteger('invoice_item_id')->nullable(); // FK -> invoice_items.id
            $table->double('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->index('sales_item_id');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_invoice_links');
    }
};
