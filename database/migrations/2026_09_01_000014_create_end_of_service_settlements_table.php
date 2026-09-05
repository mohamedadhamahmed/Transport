<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تسوية مكافأة نهاية الخدمة - كل صف بيسجل لقطة كاملة من عناصر الحساب
 * وقت التسوية (سنوات الخدمة، الراتب المعتمد، نسبة الاستحقاق حسب سبب
 * الانتهاء) عشان تفاصيل الحساب تفضل موثقة حتى لو بيانات الموظف اتغيرت
 * بعد كده (راجع EndOfServiceCalculator للصيغة الكاملة).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('end_of_service_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('termination_type');
            $table->date('hire_date');
            $table->date('termination_date');
            $table->decimal('years_of_service', 6, 2);
            $table->decimal('wage_basis', 12, 2);
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('applied_percentage', 5, 2)->default(100);
            $table->decimal('net_amount', 12, 2);
            $table->unsignedBigInteger('payment_treasury_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('end_of_service_settlements');
    }
};
