<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ترحيل رواتب شهري - صف واحد لكل شهر (unique) بيسجل إجمالي إجمالي
 * الرواتب/الخصومات/الصافي وقت الترحيل، ويربطها بقيد محاسبي حقيقي:
 * مدين "رواتب الموظفين" (الإجمالي) / دائن "خصومات الموظفين" (إجمالي
 * الخصومات) / دائن حساب الخزينة المختار (الصافي المدفوع فعليًا) -
 * راجع PayrollController@postMonth. منع الترحيل المزدوج لنفس الشهر
 * عن طريق unique(month).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_postings', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->date('month')->unique();
            $table->unsignedBigInteger('treasury_account_id')->nullable();
            $table->decimal('total_gross', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('total_net', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_postings');
    }
};
