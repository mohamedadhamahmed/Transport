<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * بتربط كل مستخدم بدور واحد من جدول roles. نسيبها nullable عشان لو
     * فيه مستخدم من غير دور (نادرًا) ميقفش تسجيل الدخول، وGate::before
     * بيتعامل مع role_id فاضي على إنه من غير أي صلاحية أصلًا.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('branch_id')
                ->constrained('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
