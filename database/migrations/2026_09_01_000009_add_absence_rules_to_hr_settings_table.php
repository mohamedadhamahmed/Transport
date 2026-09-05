<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قاعدة خصم الغياب غير المصرح به - جزء من إعدادات الموارد البشرية:
 * - absence_deduction_multiplier: مضاعف قيمة اليوم المخصوم لكل يوم غياب
 *   (1 = يوم كامل، ممكن الإدارة تخليه أعلى كعقوبة إضافية).
 * - extend_deduction_to_weekly_off: لو الموظف غاب من غير إذن في يوم شغل
 *   متصل بيوم/أيام الإجازة الأسبوعية (weekly_off_days الموجودة أصلاً)،
 *   يتحسب خصم أيام الإجازة المتصلة دي كمان - قابلة للتفعيل/الإيقاف من
 *   الإدارة (بناءً على طلب صاحب المشروع: "خليه هو اللي يختارها").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_settings', function (Blueprint $table) {
            $table->decimal('absence_deduction_multiplier', 5, 2)->default(1.00)->after('overtime_multiplier');
            $table->boolean('extend_deduction_to_weekly_off')->default(true)->after('absence_deduction_multiplier');
        });
    }

    public function down(): void
    {
        Schema::table('hr_settings', function (Blueprint $table) {
            $table->dropColumn(['absence_deduction_multiplier', 'extend_deduction_to_weekly_off']);
        });
    }
};
