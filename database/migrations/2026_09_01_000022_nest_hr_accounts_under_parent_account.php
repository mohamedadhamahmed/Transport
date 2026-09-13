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
        $now = now();

        $hrParentId = DB::table('financial_accounts')
            ->where('name', self::HR_PARENT_ACCOUNT_NAME)
            ->whereNull('orginal_id')
            ->value('id');

        if (!$hrParentId) {
            $nextAccountNumber = (int) (DB::table('financial_accounts')->max('account_number') ?? 0) + 1;

            $hrParentId = DB::table('financial_accounts')->insertGetId([
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

        DB::table('financial_accounts')
            ->whereIn('name', self::HR_ACCOUNT_NAMES)
            ->whereNull('orginal_id')
            ->whereNull('parent_account_number')
            ->update(['parent_account_number' => $hrParentId, 'updated_at' => $now]);
    }

    public function down(): void
    {
        $hrParentId = DB::table('financial_accounts')
            ->where('name', self::HR_PARENT_ACCOUNT_NAME)
            ->whereNull('orginal_id')
            ->value('id');

        if ($hrParentId) {
            DB::table('financial_accounts')
                ->whereIn('name', self::HR_ACCOUNT_NAMES)
                ->where('parent_account_number', $hrParentId)
                ->update(['parent_account_number' => null]);
        }


    }
};
