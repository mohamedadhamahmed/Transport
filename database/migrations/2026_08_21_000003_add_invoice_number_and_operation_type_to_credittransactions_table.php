<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة عمود رقم الفاتورة، وعمود نوع العملية (رقم من 1 لـ 6) لجدول
     * credittransactions، زي ما طلبتِ:
     *
     *   1 = مبيعات
     *   2 = مشتريات
     *   3 = سند قبض
     *   4 = صرف
     *   5 = قيد يومية
     *   6 = قيد افتتاحي
     *
     * القيم دي متعرفة كـ constants في App\Models\CreditTransaction
     * (CreditTransaction::TYPE_SALES وهكذا) عشان تستخدميها في الكود
     * بدل ما تكتبي الرقم صريح.
     */
    public function up(): void
    {
        Schema::table('credittransactions', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('decument_id');
            $table->unsignedTinyInteger('operation_type')->nullable()->after('type');

            $table->index('invoice_number');
            $table->index('operation_type');
        });
    }

    public function down(): void
    {
        Schema::table('credittransactions', function (Blueprint $table) {
            $table->dropIndex(['invoice_number']);
            $table->dropIndex(['operation_type']);
            $table->dropColumn(['invoice_number', 'operation_type']);
        });
    }
};
