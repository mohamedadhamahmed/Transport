<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نوع الضريبة على فاتورة / عرض سعر النقليات:
     *   standard      = داخل المملكة 15%
     *   international = شحنة خارج المملكة - معفاة (0%)
     *   custom        = نسبة يكتبها المستخدم
     */
    public function up(): void
    {
        foreach (['transport_invoices', 'transport_quotations'] as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'tax_type')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->string('tax_type', 20)->default('standard')->after('tax_rate');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['transport_invoices', 'transport_quotations'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'tax_type')) {
                Schema::table($t, fn (Blueprint $table) => $table->dropColumn('tax_type'));
            }
        }
    }
};
