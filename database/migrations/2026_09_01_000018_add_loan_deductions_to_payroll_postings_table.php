<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * total_deductions في payroll_postings كان بيسجل خصومات الحضور بس
 * (غياب/تأخير/إجازة بدون راتب). دلوقتي بقى معناه إجمالي كل الخصومات
 * (حضور + سلف - راجع PayrollCalculator::calculateForEmployee())، فمحتاجين
 * عمود منفصل يحفظ نصيب السلف من الإجمالي ده عشان لما نلغي الترحيل
 * (PayrollController@destroyPosting) نقدر نرجع مبلغ خصم الحضور الصح
 * لحساب "خصومات الموظفين" من غير ما نلخبط فيه نصيب السلف (اللي بيترجع
 * لحساب كل موظف لوحده - راجع جدول payroll_loan_deductions).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_postings', function (Blueprint $table) {
            $table->decimal('total_loan_deductions', 14, 2)->default(0)->after('total_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_postings', function (Blueprint $table) {
            $table->dropColumn('total_loan_deductions');
        });
    }
};
