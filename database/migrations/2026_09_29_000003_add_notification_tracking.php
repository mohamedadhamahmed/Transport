<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * الإشعارات:
     * - users.notifications_seen_at: آخر مرة المستخدم فتح الجرس - اللي بعدها بس
     *   بيتحسب "جديد" في الرقم الأحمر.
     * - truck_loads.unload_recorded_at: وقت تسجيل "تم التفريغ" الفعلي على
     *   السيستم (غير unloaded_at اللي المستخدم بيكتبه وممكن يكون بتاريخ قديم)
     *   - عشان إشعار "شاحنة فضيت" ميتكررش مع أي تعديل على الحمل.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'notifications_seen_at')) {
                $table->timestamp('notifications_seen_at')->nullable();
            }
        });

        Schema::table('truck_loads', function (Blueprint $table) {
            if (!Schema::hasColumn('truck_loads', 'unload_recorded_at')) {
                $table->timestamp('unload_recorded_at')->nullable()->after('unloaded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('notifications_seen_at'));
        Schema::table('truck_loads', fn (Blueprint $t) => $t->dropColumn('unload_recorded_at'));
    }
};
