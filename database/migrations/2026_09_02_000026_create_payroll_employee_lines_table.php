<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل تفصيلي لكل بند شخصي (راتب/مكافأة/خصم حضور) اتنفذ فعليًا لموظف
 * معين وقت ترحيل رواتب شهر (PayrollController@postJournal) - صف واحد
 * لكل (موظف × نوع بند × ترحيل). نفس فكرة payroll_loan_deductions
 * بالظبط، بس للبنود التلاتة الجديدة اللي بقت بتترحّل على حساب الموظف
 * الشخصي (HrAccountService::ensureSalaryAccount/ensureBonusAccount/
 * ensureDeductionAccount) بدل ما تترحّل كإجمالي واحد على حساب مجموعة
 * مشترك زي قبل. الغرض الوحيد منه هو تمكين إلغاء الترحيل (destroyPosting)
 * من إرجاع كل بند بالظبط لصاحبه من غير ما نحتاج نعيد حساب أي حاجة أو
 * نخمّن.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_employee_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_posting_id')->constrained('payroll_postings')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('type', 20); // salary | bonus | deduction
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index('employee_id');
            $table->index(['payroll_posting_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_lines');
    }
};
