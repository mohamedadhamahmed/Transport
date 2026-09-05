<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول "الأدوار" (Roles) - كل دور بيتجمع تحته مجموعة صلاحيات (من
     * جدول permissions عن طريق permission_role)، وكل مستخدم بياخد دور
     * واحد بس (users.role_id). الدور اللي عليه is_super = مدير عام
     * وبياخد كل الصلاحيات تلقائيًا من غير ما نحتاج نحطها كلها في
     * permission_role (شوفي Gate::before في AppServiceProvider).
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->boolean('is_super')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
