<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * إصلاح غلطة الموارد البشرية: HrAccountService القديمة كانت بتدوّر
 * بالاسم (find-or-create) على حسابات زي "ذمم الموظفين"، "رواتب
 * الموظفين"، "مكافآت الموظفين"، "خصومات الموظفين"، "مصروف مكافأة نهاية
 * الخدمة" - لكن الأسماء دي مكنتش مطابقة حرفيًا لأسماء حساباتك الحقيقية
 * الموجودة بالفعل في شجرتك (الاسم الحقيقي "ذمم العاملين" مثلاً، مش
 * "ذمم الموظفين")، فالكود القديم كان بيفشل في إيجاد الحساب الحقيقي
 * وبيعمل حساب جديد مكرر منفصل تمامًا عن تاريخ حساباتك وحركاتك الفعلية.
 *
 * الميجريشن دي بترجع الوضع لطبيعته:
 *   1. أي حساب موظف شخصي (orginal_type=5) كان اتعمل تحت "ذمم الموظفين"
 *      المكرر بترحّله لتحت "ذمم العاملين" الحقيقي (id=82) - بدون ما
 *      تلمس تاريخ حركاته (credittransaction) خالص، رقمه وتاريخه فاضلين
 *      زي ما هم، بس أبوه (parent_account_number) بس اللي بيتغير.
 *   2. بعد كده، أي حساب مكرر من الخمسة دول بقى فاضي من غير حسابات
 *      تحته *و* من غير أي حركة قيد (credittransaction) مسجلة عليه هو
 *      مباشرة - بيتمسح تمامًا، لإنه أصلاً مكانش المفروض يتعمل. لو لقت
 *      حساب فيه حركات فعلية (يعني اتترحّل عليه راتب/مكافأة حقيقي قبل
 *      الإصلاح ده)، الميجريشن بتسيبه زي ما هو من غير ما تمسحه (عشان
 *      منضيعش تاريخ مالي حقيقي)، وبتسجل تحذير في اللوج عشان تتراجع
 *      عليه يدويًا من شاشة "شجرة الحسابات" لو احتاج الأمر.
 *   3. مجموعة "مصروفات الموارد البشرية" (اللي كانت بتجمع الحسابات
 *      المكررة التلاتة: رواتب/مكافآت/مصروف نهاية الخدمة) بتتمسح هي
 *      كمان لو بقت فاضية بعد الخطوة اللي فاتت.
 *   4. account_category_id بتاع الآباء الحقيقيين الخمسة (82/142/146/
 *      118/117) بيتظبط لو لسه فاضي (بيتورّث من أقرب جد ليه ليه تصنيف).
 *
 * حساب "مستحقات رواتب الموظفين" ومجموعة "التزامات الموارد البشرية"
 * فضلوا من غير تغيير عمدًا - دول مش من الخمسة المكررين، وفضلوا شغالين
 * صح زي ما هما (راجع تعليق HrAccountService::payrollPayableAccountId).
 */
return new class extends Migration
{
    private const DUES_DUPLICATE_NAME = 'ذمم الموظفين';
    private const DUES_REAL_PARENT_ID = 82;

    private const EMPLOYEE_ORGINAL_TYPE = 5;

    // اسم كل حساب مكرر ← ملوش أي هدف إعادة توزيع (مفيش حسابات شخصية
    // تحته أصلاً، كان بيتقيد عليه هو نفسه كإجمالي واحد) - بيتمسح لو فاضي
    // وآمن بس.
    private const OBSOLETE_POOLED_ACCOUNT_NAMES = [
        'رواتب الموظفين',
        'مكافآت الموظفين',
        'خصومات الموظفين',
        'مصروف مكافأة نهاية الخدمة',
        'مخصص مكافأة نهاية الخدمة',
    ];

    private const HR_EXPENSES_GROUP_NAME = 'مصروفات الموارد البشرية';

    private const REAL_PARENT_IDS = [82, 142, 146, 118, 117];

    public function up(): void
    {
        $now = now();

        $this->reparentDuesAccountsToRealParent($now);
        $this->deleteObsoletePooledAccounts();
        $this->deleteHrExpensesGroupIfEmpty();
        $this->backfillAccountCategoryForRealParents($now);
    }

    public function down(): void
    {
        // ميجريشن إصلاح بيانات (مش مجرد نقل) - مفيش down فعلي دقيق ليها
        // لإن حسابات اتمسحت نهائيًا (لو كانت فاضية وآمنة) معرفش أرجعها
        // بنفس الـ ID القديم. لو محتاج تراجع، الأنسب نسخة احتياطية من
        // قاعدة البيانات قبل تشغيل الميجريشن دي، مش down تلقائي.
    }

    /**
     * أي حساب موظف شخصي (orginal_type=5) كان اتعمل تحت "ذمم الموظفين"
     * المكرر، بيترحّل لتحت "ذمم العاملين" الحقيقي (id=82) - رقمه وتاريخ
     * حركاته زي ما هما، بس أبوه بس اللي بيتغير. وبعدين لو الحساب المكرر
     * بقى فاضي وآمن، بيتمسح.
     */
    private function reparentDuesAccountsToRealParent($now): void
    {
        $duplicateId = DB::table('financialaccount')
            ->where('name', self::DUES_DUPLICATE_NAME)
            ->whereNull('orginal_id')
            ->value('id');

        if (!$duplicateId || (int) $duplicateId === self::DUES_REAL_PARENT_ID) {
            return;
        }

        $realParentExists = DB::table('financialaccount')->where('id', self::DUES_REAL_PARENT_ID)->exists();
        if (!$realParentExists) {
            // الحساب الحقيقي id=82 مش موجود في قاعدة البيانات دي أصلاً -
            // ملهاش داعي نكمل، نسيب المكرر زي ما هو عشان منضيعش الحسابات
            // الشخصية اللي تحته.
            Log::warning('HR fix migration: real dues parent account (id=82) not found - skipping reparent of ' . self::DUES_DUPLICATE_NAME);

            return;
        }

        DB::table('financialaccount')
            ->where('parent_account_number', $duplicateId)
            ->where('orginal_type', self::EMPLOYEE_ORGINAL_TYPE)
            ->update(['parent_account_number' => self::DUES_REAL_PARENT_ID, 'updated_at' => $now]);

        $this->deleteAccountIfEmptyAndUnused($duplicateId, self::DUES_DUPLICATE_NAME);
    }

    private function deleteObsoletePooledAccounts(): void
    {
        foreach (self::OBSOLETE_POOLED_ACCOUNT_NAMES as $name) {
            $id = DB::table('financialaccount')->where('name', $name)->whereNull('orginal_id')->value('id');
            if ($id) {
                $this->deleteAccountIfEmptyAndUnused((int) $id, $name);
            }
        }
    }

    private function deleteHrExpensesGroupIfEmpty(): void
    {
        $id = DB::table('financialaccount')->where('name', self::HR_EXPENSES_GROUP_NAME)->whereNull('orginal_id')->value('id');
        if ($id) {
            $this->deleteAccountIfEmptyAndUnused((int) $id, self::HR_EXPENSES_GROUP_NAME);
        }
    }

    /**
     * بيمسح حساب بس لو (أ) مفيهوش أي حساب تحته، و(ب) مفيش أي حركة قيد
     * (credittransaction) مسجلة عليه هو مباشرة - يعني فعلاً مجرد حساب
     * فاضي مكرر بالغلط، مش حساب فيه تاريخ مالي حقيقي. غير كده بيسيبه
     * زي ما هو ويسجل تحذير في اللوج.
     */
    private function deleteAccountIfEmptyAndUnused(int $accountId, string $name): void
    {
        $hasChildren = DB::table('financialaccount')->where('parent_account_number', $accountId)->exists();
        $hasTransactions = DB::table('credittransactions')->where('customer_id', $accountId)->exists();

        if ($hasChildren || $hasTransactions) {
            Log::warning("HR fix migration: duplicate account '{$name}' (id={$accountId}) left in place because it still has " . ($hasChildren ? 'child accounts' : 'transaction history') . ' - review manually from the accounts tree screen.');

            return;
        }

        DB::table('financialaccount')->where('id', $accountId)->delete();
    }

    /**
     * account_category_id بتاع الآباء الحقيقيين الخمسة لو لسه فاضي -
     * بيتورّث من أقرب جد ليه تصنيف (مش بس أبوه المباشر، عشان لو أبوه هو
     * كمان فاضي التصنيف).
     */
    private function backfillAccountCategoryForRealParents($now): void
    {
        foreach (self::REAL_PARENT_IDS as $accountId) {
            $account = DB::table('financialaccount')->where('id', $accountId)->first();
            if (!$account || $account->account_category_id !== null) {
                continue;
            }

            $categoryId = $this->resolveNearestAncestorCategoryId($account->parent_account_number);
            if ($categoryId !== null) {
                DB::table('financialaccount')->where('id', $accountId)->update([
                    'account_category_id' => $categoryId,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function resolveNearestAncestorCategoryId(?int $parentId): ?int
    {
        $guard = 0;

        while ($parentId !== null && $guard < 50) {
            $parent = DB::table('financialaccount')->where('id', $parentId)->first();
            if (!$parent) {
                return null;
            }

            if ($parent->account_category_id !== null) {
                return (int) $parent->account_category_id;
            }

            $parentId = $parent->parent_account_number;
            $guard++;
        }

        return null;
    }
};
