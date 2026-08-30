<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة cost_center_id لجدول purchases
|--------------------------------------------------------------------------
| بدل ما "مركز التكلفة" يكون نص حر (cost_center string)، بقى بيتاخد من
| جدول cost_centers الموجود بالفعل عندك (نفس الجدول اللي بترسليه لي في
| ملف SQL). العمود القديم cost_center (نص) سايبينه زي ما هو من غير حذف
| تجنبًا لأي فقدان بيانات على فواتير قديمة - بس الشاشة الجديدة بقت
| بتستخدم cost_center_id بس.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('cost_center');
            $table->index('cost_center_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });
    }
};
