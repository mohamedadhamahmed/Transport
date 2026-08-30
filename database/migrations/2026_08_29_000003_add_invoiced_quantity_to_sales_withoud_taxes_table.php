<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * عمود لتتبع الكمية اللي اتحوّلت بالفعل لفاتورة ضريبية حقيقية من كل
 * بند تسليم - عشان نحسب "الكمية المتاحة" = quantity - quantityreturn -
 * invoiced_quantity، ونمنع فوترة نفس الكمية مرتين.
 *
 * ⚠️ الاسم المستهدف هنا 'delivery_note_item' (الاسم الجديد النظيف).
 * لو عندك الجدول لسه باسمه القديم 'sales_withoud_taxes' ولم تُشغّل
 * migration إعادة التسمية (رقم 000005) بعد، شغّلها هي الأول قبل هذه.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_note_item', function (Blueprint $table) {
            $table->double('invoiced_quantity')->default(0)->after('quantityreturn');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note_item', function (Blueprint $table) {
            $table->dropColumn('invoiced_quantity');
        });
    }
};
