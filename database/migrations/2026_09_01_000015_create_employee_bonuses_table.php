<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مكافأة/حافز شهري يدوي لكل موظف - منفصل عن "مكافأة نهاية الخدمة"
 * (end_of_service_settlements) اللي بتترحّل مرة واحدة عند انتهاء
 * الخدمة. دي بتظهر كإضافة في كشف الرواتب الشهري (PayrollController)
 * بس، من غير قيد محاسبي منفصل بتاعها - قيمتها بتدخل ضمن إجمالي
 * "رواتب الموظفين" وقت ترحيل الرواتب الشهري (لو حصل).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('month');
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_bonuses');
    }
};
