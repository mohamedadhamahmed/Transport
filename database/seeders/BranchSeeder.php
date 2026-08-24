<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * بيدخل فرع "الرياض" (نفس الفرع اللي ظاهر في فواتيرك القديمة).
     * مستخدم updateOrCreate بدل create عشان لو شغلتي الـ seeder أكتر
     * من مرة متتكررش نفس الفرع.
     */
    public function run(): void
    {
        Branch::updateOrCreate(
            ['name' => 'الرياض'],
            [
                'name_en' => 'Riyadh',
                'location' => 'الرياض',
                'type' => 'main',
                'parent_branch_id' => null,
            ]
        );
    }
}
