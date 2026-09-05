<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول cost_centers
|--------------------------------------------------------------------------
| طلعت إن جدول cost_centers ده كان موجود في قاعدة بيانات تانية عندك
| (fatmaa_v4) مش في قاعدة البيانات اللي شغالة فعليًا (my_erp) - عشان
| كده ظهر الخطأ "Base table or view not found". الميجريشن دي بتنشئ
| الجدول بنفس البنية بالظبط اللي كانت موجودة في fatmaa_v4 (نفس أسماء
| الأعمدة والقيم الافتراضية)، عشان لو عندك بيانات مركز تكلفة قديمة في
| fatmaa_v4 حابب تنقلها بعدين تقدر تعمل كده يدويًا بسهولة (نفس الأعمدة
| بالظبط).
|
| *** لو جدول cost_centers ده أصلاً موجود عندك في my_erp ببنية مختلفة
| شوية (حتى لو باسم مختلف)، متشغلش الميجريشن دي - قوللي وهظبطها. ***
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cost_centers')) {
            return;
        }

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('cost_center_ar')->default('مصروفات نقدية غير مسجلة');
            $table->timestamps();
            $table->bigInteger('expensesAvt')->default(0);
            $table->string('cost_center_en', 250)->default('-');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
