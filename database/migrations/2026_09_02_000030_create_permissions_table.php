<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول "الصلاحيات" - كل صف هنا مفتاح فريد زي customers.create أو
     * invoices.delete، بيتزرع (seed) تلقائيًا من config/permissions.php
     * (المصدر الوحيد اللي بيوصف كل صلاحية في النظام). الـ module بيستخدم
     * لتجميع الصلاحيات في شاشة إدارة الأدوار (كل قسم في كارت لوحده).
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('module');
            $table->string('label');
            $table->string('label_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
