<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول حالات أوامر التصنيع - بيسمح للمستخدم يعرّف حالاته الخاصة
 * (زي "قيد التنفيذ"، "مراجعة الطلب"، "مكتمل") بدل ما تكون enum ثابتة
 * في الكود، بالظبط زي شاشة "حالات الأوامر" في نظام Daftra اللي المشروع
 * بيتبنى على نفس فكرته.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_order_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name');
            $table->string('color', 20)->default('#1456E8');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            // نوع الحالة يوضح هل هي بتتحسب "مفتوحة" ولا "مغلقة" في التقارير
            $table->enum('type', ['open', 'in_progress', 'closed', 'cancelled'])->default('open');
            $table->timestamps();
        });

        // إدخال حالات افتراضية جاهزة عشان النظام يشتغل من غير إعداد يدوي
        \DB::table('manufacturing_order_statuses')->insert([
            ['name' => 'مسودة', 'color' => '#9CA3AF', 'type' => 'open', 'is_default' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مراجعة الطلب', 'color' => '#F59E0B', 'type' => 'open', 'is_default' => false, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'قيد التنفيذ', 'color' => '#1456E8', 'type' => 'in_progress', 'is_default' => false, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مكتمل', 'color' => '#10B981', 'type' => 'closed', 'is_default' => false, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ملغي', 'color' => '#EF4444', 'type' => 'cancelled', 'is_default' => false, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_statuses');
    }
};
