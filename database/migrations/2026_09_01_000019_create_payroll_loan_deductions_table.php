<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل تفصيلي لكل خصم سلفة تم تنفيذه فعليًا وقت ترحيل رواتب شهر معيّن
 * (PayrollController@postMonth) - صف واحد لكل (سلفة × ترحيل). الغرض
 * الوحيد منه هو تمكين إلغاء الترحيل (destroyPosting) من إرجاع كل خصم
 * سلفة بالظبط لصاحبه (تحديث paid_amount في employee_loans وعكس أثره في
 * حساب الموظف) من غير ما نحتاج نعيد حساب أي حاجة أو نخمّن - بدل ما
 * تفضل payroll_postings.total_loan_deductions رقم إجمالي مالوش تفاصيل
 * ترجع منه.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_loan_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_posting_id')->constrained('payroll_postings')->cascadeOnDelete();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index('employee_loan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_loan_deductions');
    }
};
