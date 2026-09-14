<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * إعادة تنظيم شجرة الحسابات كلها (مش الموارد البشرية بس) تحت 5 فروع
 * رئيسية بمعناها المحاسبي الحقيقي: الأصول / الخصوم / حقوق الملكية /
 * الإيرادات / المصروفات - بدل ما تكون كل الحسابات (عملاء، موردين،
 * بنوك، صندوق، ضريبة، إيرادات، مخزون...) جذور مستقلة متفرقة في الشجرة.
 *
 * ⚠️ الحسابات دي (العملاء=2، الموردين=3، البنوك=4، الصندوق=5، الضريبة=102،
 * الإيرادات=112، المخزون=181، تكلفة البضاعة المباعة=183، مرتجعات
 * المبيعات=184) قديمة من قبل مشروع الميجريشنز ده أصلاً (جدول
 * financialaccount نفسه جدول قديم مش من إنشاء الميجريشنز - راجع تعليق
 * App\Models\FinancialAccount)، فمفيش رؤية مباشرة هنا لحالتها الحالية
 * الفعلية في قاعدة بياناتك (هل هي جذر أصلاً ولا متفرعة من حاجة تانية
 * بالفعل). عشان كده الميجريشن دي بتاخد أسلوب حذر: أي حساب من دول
 * بتلاقيه IDبتاعه موجود فعلاً في الجدول *و* لسه جذر (parent_account_number
 * فاضي) بينضم للفرع الصح بتاعه؛ أي حساب مش موجود أو أصلاً متفرع من
 * حاجة تانية بتسيبه زي ما هو من غير ما تلمسه، عشان منكسرش ترتيب يدوي
 * ممكن يكون موجود بالفعل. لو بعد الترحيل لسه فيه حسابات مش واقعة في
 * مكانها الصح، ينفع تتعدل يدوي من شاشة "شجرة الحسابات".
 *
 * حساب "الإيرادات" حالة خاصة: لو ID=112 موجود وجذر فعلاً، بنستخدمه هو
 * نفسه كفرع الإيرادات الرئيسي (بدل ما نعمل واحد تاني مكرر). لو مش
 * جذر أو مش موجود، بنعمل فرع جديد باسم "الإيرادات الرئيسية" بدل ما
 * نلخبط في حساب إيرادات المبيعات الأصلي.
 *
 * حسابات الموارد البشرية السبعة (اللي أضفناها في ميجريشنز سابقة) بتتوزع
 * على فروعها الصح: ذمم الموظفين→الأصول، رواتب/مكافآت/مصروف نهاية
 * الخدمة→مجموعة "مصروفات الموارد البشرية" جوه المصروفات، خصومات
 * الموظفين→الإيرادات، مستحقات رواتب/مخصص نهاية الخدمة→مجموعة "التزامات
 * الموارد البشرية" جوه الخصوم - بدل ما تكون كلها تحت حساب "الموارد
 * البشرية" الواحد اللي عملناه في الميجريشن اللي قبل دي (يتشال هنا لو
 * فضل من غير أي حساب تحته بعد النقل).
 */
return new class extends Migration
{
    private const ASSETS_NAME = 'الأصول';
    private const LIABILITIES_NAME = 'الخصوم';
    private const EQUITY_NAME = 'حقوق الملكية';
    private const REVENUE_NAME = 'الإيرادات';
    private const REVENUE_FALLBACK_NAME = 'الإيرادات الرئيسية';
    private const EXPENSES_NAME = 'المصروفات';

    private const HR_EXPENSES_GROUP_NAME = 'مصروفات الموارد البشرية';
    private const HR_LIABILITIES_GROUP_NAME = 'التزامات الموارد البشرية';

    private const OLD_HR_PARENT_NAME = 'الموارد البشرية';

    public function up(): void
    {
        $now = now();

        $assetsId = $this->findOrCreateRootCategory(self::ASSETS_NAME, $now);
        $liabilitiesId = $this->findOrCreateRootCategory(self::LIABILITIES_NAME, $now);
        $this->findOrCreateRootCategory(self::EQUITY_NAME, $now);
        $expensesId = $this->findOrCreateRootCategory(self::EXPENSES_NAME, $now);
        $revenueId = $this->resolveRevenueCategoryId($now);

        $hrExpensesGroupId = $this->findOrCreateGroup(self::HR_EXPENSES_GROUP_NAME, $expensesId, $now);
        $hrLiabilitiesGroupId = $this->findOrCreateGroup(self::HR_LIABILITIES_GROUP_NAME, $liabilitiesId, $now);

        // الحسابات القديمة المعروفة بأرقامها الثابتة (لو موجودة ولسه جذر بس).
        $this->moveIfRoot(2, $assetsId, $now);   // العملاء
        $this->moveIfRoot(3, $liabilitiesId, $now); // الموردين
        $this->moveIfRoot(4, $assetsId, $now);   // البنوك
        $this->moveIfRoot(5, $assetsId, $now);   // الصندوق
        $this->moveIfRoot(102, $liabilitiesId, $now); // ضريبة القيمة المضافة
        $this->moveIfRoot(181, $assetsId, $now); // المخزون
        $this->moveIfRoot(183, $expensesId, $now); // تكلفة البضاعة المباعة
        $this->moveIfRoot(184, $revenueId, $now); // مرتجعات المبيعات (مقابل إيرادات)

        // حسابات الموارد البشرية السبعة - بنحطها في مكانها الصح بغض
        // النظر عن أبوها الحالي (يا إما جذر، يا إما تحت "الموارد
        // البشرية" القديم من الميجريشن اللي قبل دي).
        DB::table('financialaccount')->where('name', 'ذمم الموظفين')->whereNull('orginal_id')
            ->update(['parent_account_number' => $assetsId, 'updated_at' => $now]);
        DB::table('financialaccount')->where('name', 'رواتب الموظفين')->whereNull('orginal_id')
            ->update(['parent_account_number' => $hrExpensesGroupId, 'updated_at' => $now]);
        DB::table('financialaccount')->where('name', 'مكافآت الموظفين')->whereNull('orginal_id')
            ->update(['parent_account_number' => $hrExpensesGroupId, 'updated_at' => $now]);
        DB::table('financialaccount')->where('name', 'مصروف مكافأة نهاية الخدمة')->whereNull('orginal_id')
            ->update(['parent_account_number' => $hrExpensesGroupId, 'updated_at' => $now]);
        DB::table('financialaccount')->where('name', 'خصومات الموظفين')->whereNull('orginal_id')
            ->update(['parent_account_number' => $revenueId, 'updated_at' => $now]);
        DB::table('financialaccount')->where('name', 'مستحقات رواتب الموظفين')->whereNull('orginal_id')
            ->update(['parent_account_number' => $hrLiabilitiesGroupId, 'updated_at' => $now]);
        DB::table('financialaccount')->where('name', 'مخصص مكافأة نهاية الخدمة')->whereNull('orginal_id')
            ->update(['parent_account_number' => $hrLiabilitiesGroupId, 'updated_at' => $now]);

        // "الموارد البشرية" (الحساب الوسيط القديم) بقى فاضي من غير أي
        // حساب تحته - نمسحه عشان منسيبش جذر يتيم في الشجرة.
        $oldHrParentId = DB::table('financialaccount')
            ->where('name', self::OLD_HR_PARENT_NAME)
            ->whereNull('orginal_id')
            ->value('id');

        if ($oldHrParentId) {
            $hasChildren = DB::table('financialaccount')->where('parent_account_number', $oldHrParentId)->exists();
            if (!$hasChildren) {
                DB::table('financialaccount')->where('id', $oldHrParentId)->delete();
            }
        }
    }

    public function down(): void
    {
        // ميجريشن تنظيمية بحتة (مجرد نقل parent_account_number) - مفيش
        // down فعلي ليها عشان معرفش أرجع كل حساب لمكانه الأصلي القديم
        // بدقة (خصوصًا الحسابات اللي كانت جذر من الأساس قبل أي ميجريشن
        // من بتوعنا). لو محتاج ترجع، الأنسب تعديل يدوي من شاشة شجرة
        // الحسابات.
    }

    private function findOrCreateRootCategory(string $name, $now): int
    {
        return $this->findOrCreateGroup($name, null, $now);
    }

    private function findOrCreateGroup(string $name, ?int $parentId, $now): int
    {
        $id = DB::table('financialaccount')->where('name', $name)->whereNull('orginal_id')->value('id');
        if ($id) {
            return (int) $id;
        }

        $nextAccountNumber = (int) (DB::table('financialaccount')->max('account_number') ?? 0) + 1;

        return DB::table('financialaccount')->insertGetId([
            'name' => $name,
            'account_type' => 4,
            'parent_account_number' => $parentId,
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

    /**
     * فرع "الإيرادات" - لو ID=112 موجود وجذر فعلاً بنستخدمه هو نفسه،
     * وإلا بنعمل فرع بديل باسم مميز عشان منعملش تكرار بنفس الاسم.
     */
    private function resolveRevenueCategoryId($now): int
    {
        $existing = DB::table('financialaccount')->where('id', 112)->first();
        if ($existing && $existing->parent_account_number === null) {
            return 112;
        }

        return $this->findOrCreateRootCategory(self::REVENUE_FALLBACK_NAME, $now);
    }

    private function moveIfRoot(int $accountId, int $newParentId, $now): void
    {
        DB::table('financialaccount')
            ->where('id', $accountId)
            ->whereNull('parent_account_number')
            ->update(['parent_account_number' => $newParentId, 'updated_at' => $now]);
    }
};
