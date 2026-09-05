<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * رصيد الإجازة السنوية لكل موظف - مخصص لنوع "سنوية" بس (annual) لإنه
 * النوع الوحيد اللي بيتحسب له رصيد رسمي بنظام العمل السعودي (21 يوم
 * لأول 5 سنين خدمة، 30 يوم بعدها). باقي الأنواع (مرضي/بدون راتب/
 * طارئ/أخرى) سجل طلب وموافقة بسيط من غير رصيد - قرار نطاق مقصود
 * (راجع تعليق LeaveRequest).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->unique();
            $table->decimal('balance_days', 8, 2)->default(21);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
