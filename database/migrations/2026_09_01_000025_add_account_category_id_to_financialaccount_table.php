<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة account_category_id على financialaccount: يربط كل حساب بنوعه
 * من جدول account_types الجديد (الأصول/الخصوم/الإيرادات/المصروفات/
 * حقوق الملكية) - بدون foreign key صريح، بنفس أسلوب
 * journal_entry_lines.account_id (تفادي مشاكل الـ FK constraint مع
 * الجدول القديم financialaccount).
 *
 * بعد إضافة العمود:
 *  1. بنوسم الفروع الخمسة الرئيسية اللي عملناها بالاسم في ميجريشن
 *     2026_09_01_000023 (وهي لسه جذر - parent فاضي) بنوعها الصحيح من
 *     account_types.
 *  2. بننزل الوسم ده لكل الحسابات الفرعية تحت كل فرع بالتوارث عبر
 *     الشجرة (BFS) - كل حساب هياخد نفس نوع أقرب جد ليه من الفروع
 *     الخمسة. ده آمن (على عكس نقل الحسابات في 000023) لإننا بس بنسجل
 *     حقيقة مكان الحساب في الشجرة، مش بنغيّر مكانه أصلاً.
 *
 * ملحوظة مهمة: التاريخ ده بيغطي الحسابات الموجودة وقت تشغيل الميجريشن
 * بس. أي حساب هيتعمل بعد كده (عميل/مورد/موظف جديد، أو حساب عام جديد
 * من HrAccountService) لازم يتوسم وقت الإنشاء نفسه من الكود - راجع
 * FinancialAccount::inheritedCategoryId() المُستخدمة في
 * InvoiceController/PurchaseController/SupplierController/
 * DeliveryNoteController/AccountController/HrAccountService.
 */
return new class extends Migration
{
    private const CATEGORY_TYPE_IDS = [
        'الأصول' => 1,
        'الخصوم' => 2,
        'الإيرادات' => 3,
        'الإيرادات الرئيسية' => 3,
        'المصروفات' => 4,
        'حقوق الملكية' => 5,
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('financialaccount', 'account_category_id')) {
            Schema::table('financialaccount', function (Blueprint $table) {
                $table->unsignedBigInteger('account_category_id')->nullable()->after('account_type');
                $table->index('account_category_id');
            });
        }

        $now = now();

        // الفروع الرئيسية الخمسة بالاسم، وهي لسه جذر (parent فاضي).
        $roots = DB::table('financialaccount')
            ->whereNull('parent_account_number')
            ->whereIn('name', array_keys(self::CATEGORY_TYPE_IDS))
            ->get(['id', 'name']);

        $queue = [];
        foreach ($roots as $root) {
            $typeId = self::CATEGORY_TYPE_IDS[$root->name];
            DB::table('financialaccount')->where('id', $root->id)->update([
                'account_category_id' => $typeId,
                'updated_at' => $now,
            ]);
            $queue[] = ['id' => $root->id, 'type' => $typeId];
        }

        // BFS تنزل النوع لكل الأحفاد.
        while (!empty($queue)) {
            $current = array_shift($queue);

            $children = DB::table('financialaccount')
                ->where('parent_account_number', $current['id'])
                ->get(['id']);

            foreach ($children as $child) {
                DB::table('financialaccount')->where('id', $child->id)->update([
                    'account_category_id' => $current['type'],
                    'updated_at' => $now,
                ]);
                $queue[] = ['id' => $child->id, 'type' => $current['type']];
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('financialaccount', 'account_category_id')) {
            Schema::table('financialaccount', function (Blueprint $table) {
                $table->dropIndex(['account_category_id']);
                $table->dropColumn('account_category_id');
            });
        }
    }
};
