<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المكافآت الشهرية اليدوية (EmployeeBonus) كانت بتتجمع جوه total_gross
 * وتترحّل كجزء من قيد "رواتب الموظفين" الإجمالي من غير ما يكون ليها
 * حساب مصروف منفصل - فكانت بتختفي داخل رقم الرواتب الأساسية في
 * التقارير المالية. دلوقتي بقى ليها حساب "مكافآت الموظفين" الخاص بيها
 * (HrAccountService::bonusExpenseAccountId) وقيد مدين منفصل وقت
 * الترحيل (PayrollController@postJournal)، فمحتاجين نحفظ نصيبها من
 * total_gross هنا عشان إلغاء الترحيل (destroyPosting) يقدر يرجع كل
 * حساب بالمبلغ الصح بالظبط.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_postings', function (Blueprint $table) {
            $table->decimal('total_bonus', 14, 2)->default(0)->after('total_gross');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_postings', function (Blueprint $table) {
            $table->dropColumn('total_bonus');
        });
    }
};
