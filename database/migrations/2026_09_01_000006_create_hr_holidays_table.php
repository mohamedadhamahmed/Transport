<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول hr_holidays (الإجازات/العطلات الرسمية)
|--------------------------------------------------------------------------
| تاريخ واحد لكل صف - بيستخدمه AttendanceCalculator عشان أي يوم موجود
| هنا يتحسب "إجازة رسمية" مش "غياب"، حتى لو مفيش بصمة تسجيل دخول/خروج
| ليه. branchs_id nullable = إجازة عامة لكل الفروع (لو محددة، بتخص
| الفرع ده بس - مفيد لو فرع معين قافل يوم مختلف عن باقي الفروع).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_holidays', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->string('name');
            $table->integer('branchs_id')->nullable();

            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_holidays');
    }
};
