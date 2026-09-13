<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_settings') && !Schema::hasColumn('system_settings', 'currency')) {
            Schema::table('system_settings', function (Blueprint $table) {
                $table->string('currency', 20)->default('SAR')->after('name_en');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('system_settings') && Schema::hasColumn('system_settings', 'currency')) {
            Schema::table('system_settings', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};