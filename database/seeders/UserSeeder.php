<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users.
     */
    public function run(): void
    {
        // بيستخدم updateOrCreate عشان لو شغلتي السيدر أكتر من مرة، مش هيعمل يوزر مكرر
        User::updateOrCreate(
            ['email' => 'admin@daftercom.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@12345'),
                'email_verified_at' => now(),
                'roles_name' => json_encode(['Admin']), // أو نص عادي حسب نوع العمود في قاعدة البيانات
                'active' => 1,
                'branchs_id' => 1,
                'discount_allow_limit' => 10,
                'name_en' => 'System Admin',
            ]
        );
    }
}