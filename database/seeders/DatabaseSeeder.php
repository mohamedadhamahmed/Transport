<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BranchSeeder::class,
            // شجرة الحسابات (بعد الفروع مباشرة عشان محتاج الفروع
            // موجودة أصلاً لحسابات كل فرع - راجع ChartOfAccountsSeeder).
            ChartOfAccountsSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        // بيانات النقليات التجريبية (عملاء/سائقين/شاحنات/أحمال) - بتشتغل
        // على أي بيئة غير production، أو لو SEED_DEMO_DATA=true في ملف .env
        // (مسحها: php artisan db:seed --class=TransportDemoCleanupSeeder)
        if (!app()->environment('production') || filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call(TransportDemoSeeder::class);
        }
    }
}
