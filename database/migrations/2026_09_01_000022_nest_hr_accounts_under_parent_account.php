<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const HR_PARENT_ACCOUNT_NAME = 'الموارد البشرية';

    private const HR_ACCOUNT_NAMES = [
        'ذمم الموظفين',
        'رواتب الموظفين',
        'مكافآت الموظفين',
        'خصومات الموظفين',
        'مستحقات رواتب الموظفين',
        'مصروف مكافأة نهاية الخدمة',
        'مخصص مكافأة نهاية الخدمة',
    ];

    public function up(): void
    {
        // ميجريشن إصلاح بيانات قديمة - على قاعدة بيانات جديدة فاضية مالهاش
        // لازمة، ولو اشتغلت كانت بتعمل حسابات جذر بـ ids (1..8) بتتعارض مع
        // الـ ids الثابتة في ChartOfAccountsSeeder (العملاء=2، البنوك=4،
        // الخزينة=5...) وتخلي الحسابات تتحط في أماكن غلط.
        if (!DB::table('financialaccount')->exists()) {
            return;
        }

        $now = now();

        $hrParentId = DB::table('financialaccount')
            ->where('name', self::HR_PARENT_ACCOUNT_NAME)
            ->whereNull('orginal_id')
            ->value('id');

        if (!$hrParentId) {
            $nextAccountNumber = (int) (DB::table('financialaccount')->max('account_number') ?? 0) + 1;

            $hrParentId = DB::table('financialaccount')->insertGetId([
                'name' => self::HR_PARENT_ACCOUNT_NAME,
                'account_type' => 4,
                'parent_account_number' => null,
                'account_number' => $nextAccountNumber,
                'start_balance' => 0,
                'current_balance' => 0,
                'start_balance_status' => 3,
                'added_by' => 1,
                'com_code' => 1,
                'date' => $now,
                'active' => 1,
                'is_parent' => 1,
                'orginal_id' => null,
                'orginal_type' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('financialaccount')
            ->whereIn('name', self::HR_ACCOUNT_NAMES)
            ->whereNull('orginal_id')
            ->whereNull('parent_account_number')
            ->update(['parent_account_number' => $hrParentId, 'updated_at' => $now]);
    }

    public function down(): void
    {
        $hrParentId = DB::table('financialaccount')
            ->where('name', self::HR_PARENT_ACCOUNT_NAME)
            ->whereNull('orginal_id')
            ->value('id');

        if ($hrParentId) {
            DB::table('financialaccount')
                ->whereIn('name', self::HR_ACCOUNT_NAMES)
                ->where('parent_account_number', $hrParentId)
                ->update(['parent_account_number' => null]);
        }


    }
};
