<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة cost_center_id لجدول purchase_orders
|--------------------------------------------------------------------------
| نفس التعديل اللي اتعمل في جدول purchases بالظبط - مركز التكلفة بقى
| بياخد من جدول cost_centers الموجود عندك بدل ما يكون نص حر. العمود
| القديم cost_center (نص) سايبينه زي ما هو من غير حذف.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('cost_center');
            $table->index('cost_center_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });
    }
};
