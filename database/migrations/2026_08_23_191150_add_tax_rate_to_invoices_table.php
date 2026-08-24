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
        Schema::table('invoices', function (Blueprint $table) {
            // إضافة حقل نسبة الضريبة، مع تحديد القيمة الافتراضية 0.15 (15%) مثلاً أو حسب رغبتك
            $table->decimal('tax_rate', 5, 4)->default(0.1500)->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Invoice', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};