<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول hr_settings (إعدادات الموارد البشرية لكل فرع)
|--------------------------------------------------------------------------
| نفس فكرة settings/system_settings بالظبط (صف واحد لكل فرع، branchs_id
| بنفس التهجئة القديمة المستخدمة في الجدولين دول تحديدًا - مش branch_id
| زي الجداول الحديثة التانية - عشان الشاشة دي هتبقى جنبهم في نفس منطقة
| "الإعدادات"). القيم دي أساس حساب الحضور والانصراف من ملف البصمة
| (AttendanceImporter/AttendanceCalculator):
|
|  - work_start_time / work_end_time: بداية ونهاية الدوام الرسمي.
|  - late_grace_minutes: سماحية بالدقايق قبل ما يتحسب "تأخير" فعلي.
|  - overtime_multiplier: مضاعف قيمة الساعة العادية لحساب قيمة ساعة
|    الأوفرتايم (1.5 افتراضيًا زي نظام العمل السعودي) - بيتضرب في
|    "قيمة ساعة العمل" المحسوبة من راتب كل موظف (مش قيمة ثابتة عامة)،
|    عشان الأوفرتايم يبقى عادل بين موظف وموظف حسب راتبه الفعلي.
|  - weekly_off_days: أيام الإجازة الأسبوعية (JSON array زي ["friday"]).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->id();

            $table->integer('branchs_id')->default(1);

            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->unsignedInteger('late_grace_minutes')->default(15);
            $table->decimal('overtime_multiplier', 5, 2)->default(1.5);
            $table->json('weekly_off_days')->nullable();

            $table->timestamps();

            $table->unique('branchs_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
};
