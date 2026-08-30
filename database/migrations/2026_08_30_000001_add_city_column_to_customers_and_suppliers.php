<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة عمود "المدينة" (city) لجدولي customers و suppliers - غير
 * موجود أصلاً في الجدولين حسب هيكلة قاعدة البيانات الفعلية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('city');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
