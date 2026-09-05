<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دعم خصم السلف/العهد تلقائيًا من كشف الرواتب الشهري (PayrollCalculator/
 * PayrollController) بدل ما تفضل السلفة قائمة لحد ما حد يعمل تسوية
 * كاملة يدوي من شاشة السلف والعهد:
 *  - monthly_installment: القسط الشهري المتفق عليه (اختياري - لو فاضي
 *    بيتخصم المتبقي بالكامل دفعة واحدة في أول كشف رواتب بعد الصرف).
 *  - paid_amount: إجمالي اللي اتخصم فعليًا من الرواتب لحد دلوقتي -
 *    بيزيد كل مرة كشف رواتب يترحّل وفيه خصم سلفة لصاحبها (راجع
 *    PayrollController@postMonth). لما paid_amount يوصل amount، السلفة
 *    بتتقفل تلقائيًا (status=settled) من غير ما حد يعمل تسوية يدوية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_loans', function (Blueprint $table) {
            $table->decimal('monthly_installment', 12, 2)->nullable()->after('amount');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('monthly_installment');
        });
    }

    public function down(): void
    {
        Schema::table('employee_loans', function (Blueprint $table) {
            $table->dropColumn(['monthly_installment', 'paid_amount']);
        });
    }
};
