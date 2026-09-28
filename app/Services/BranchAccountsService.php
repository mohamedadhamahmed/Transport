<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\FinancialAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * كل منطق ربط "الفرع" بشجرة الحسابات (financialaccount) مجمّع هنا في
 * مكان واحد - نفس فكرة App\Services\Hr\HrAccountService لكن للفروع.
 *
 * كل فرع (قديم أو جديد) لازم يكون ليه 7 حسابات فرعية تحت الحسابات
 * الأساسية السبعة دي (IDs ثابتة زي ما هي مستخدمة فعليًا في كل كنترولرز
 * الفواتير/المشتريات/التسليم/المرتجعات - راجع AccountController::search
 * scope=treasury وتعليقات InvoiceController/PurchaseController/
 * DeliveryController/DeliveryReturnController/InvoiceReturnController/
 * PurchaseReturnController)، عشان الفواتير والمشتريات وسندات القبض/
 * الصرف تسجل حركاتها على حساب الفرع الصح بدل ما تتجاهل الفرع بالكامل
 * (لو الحساب مش موجود، كل الأماكن دي بتتجاهل القيد بصمت - if ($account)):
 *
 *   - id=5   الصندوق/الخزينة   → "خزينة فرع {اسم الفرع}"
 *   - id=4   البنوك            → "حساب البنك فرع {اسم الفرع}"
 *   - id=112 الإيرادات          → "المبيعات فرع {اسم الفرع}"
 *   - id=181 المخزون           → "مخزون فرع {اسم الفرع}"
 *   - id=102 ضريبة القيمة المضافة → "ضريبة القيمة المضافة فرع {اسم الفرع}"
 *   - id=183 تكلفة البضاعة المباعة → "تكاليف المبيعات فرع {اسم الفرع}"
 *   - id=184 مرتجعات المبيعات    → "مردود المبيعات فرع {اسم الفرع}"
 *
 * الأسماء دي مطابقة حرفيًا لنفس التسمية المستخدمة فعليًا في نظامك
 * (راجعتها من شجرة حساباتك الحقيقية لفرع "الرياض": "خزينة فرع الرياض"،
 * "حساب البنك فرع الرياض"، "ضريبة القيمة المضافة فرع الرياض"،
 * "المبيعات فرع الرياض"، "مخزون فرع الرياض"، "تكاليف المبيعات فرع
 * الرياض") - غير حساب "مردود المبيعات" اللي كان في نظامك القديم من
 * غير اسم الفرع في الاسم العربي نفسه (بس في name_en بس، وعمود
 * name_en أصلاً مش موجود في جدول financialaccount هنا)، فحطيت اسم
 * الفرع في الاسم العربي زي باقي الحسابات عشان يبقى واضح ومميز لو
 * عندك أكتر من فرع.
 *
 * الفرق عن HrAccountService في التمييز بين الحسابات: هنا مفيش
 * orginal_type/orginal_id (الفرع مش عميل/مورد/موظف)، فالتمييز بيبقى
 * بـ (parent_account_number + branchs_id) مباشرة - كل فرع له حساب واحد
 * بس تحت كل أب من السبعة، وده مفتاح طبيعي واضح (مفيش لبس زي مطابقة
 * الاسم اللي HrAccountService حذرت منها).
 *
 * بتتنادى من مكانين:
 *   1. BranchController::store - أول ما يتعمل فرع جديد من الشاشة.
 *   2. Database\Seeders\ChartOfAccountsSeeder - للفروع الموجودة بالفعل.
 */
class BranchAccountsService
{
    public const TREASURY_PARENT_ACCOUNT_ID = 5;
    public const BANK_PARENT_ACCOUNT_ID = 4;
    public const REVENUE_PARENT_ACCOUNT_ID = 112;
    public const INVENTORY_PARENT_ACCOUNT_ID = 181;
    public const VAT_PARENT_ACCOUNT_ID = 102;
    public const COGS_PARENT_ACCOUNT_ID = 183;
    public const SALES_RETURNS_PARENT_ACCOUNT_ID = 184;

    /**
     * قالب اسم كل حساب فرعي، بالترتيب اللي هيتعمل بيه (مش بيؤثر على أي
     * حاجة وظيفية، بس ترتيب منطقي في العرض).
     */
    private const NAME_TEMPLATES = [
        self::TREASURY_PARENT_ACCOUNT_ID => 'خزينة فرع :branch',
        self::BANK_PARENT_ACCOUNT_ID => 'حساب البنك فرع :branch',
        self::REVENUE_PARENT_ACCOUNT_ID => 'المبيعات فرع :branch',
        self::INVENTORY_PARENT_ACCOUNT_ID => 'مخزون فرع :branch',
        self::VAT_PARENT_ACCOUNT_ID => 'ضريبة القيمة المضافة فرع :branch',
        self::COGS_PARENT_ACCOUNT_ID => 'تكاليف المبيعات فرع :branch',
        self::SALES_RETURNS_PARENT_ACCOUNT_ID => 'مردود المبيعات فرع :branch',
    ];

    /**
     * بتعمل (أو تجيب لو موجودة بالفعل) الحسابات السبعة الافتراضية
     * للفرع ده مع بعض. آمنة تتنادى أكتر من مرة لنفس الفرع (idempotent) -
     * لو الحساب موجود بالفعل بيترجع هو زي ما هو من غير تكرار.
     *
     * @return array<int, FinancialAccount> مفتاح كل عنصر = id الحساب الأب (5/4/112/181/102/183/184)
     */
    public function ensureDefaultAccountsForBranch(Branch $branch): array
    {
        $accounts = [];

        foreach (self::NAME_TEMPLATES as $parentAccountId => $nameTemplate) {
            $accounts[$parentAccountId] = $this->ensureBranchAccount(
                $branch,
                $parentAccountId,
                str_replace(':branch', $branch->name, $nameTemplate)
            );
        }

        return $accounts;
    }

    private function ensureBranchAccount(Branch $branch, int $parentAccountId, string $name): FinancialAccount
    {
        $existing = FinancialAccount::where('parent_account_number', $parentAccountId)
            ->where('branchs_id', $branch->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $parentAccount = FinancialAccount::find($parentAccountId);

        // تصنيف الحساب من أقرب أب ليه تصنيف صحيح (1-5). قبل كده لو الأب
        // account_category_id بتاعه فاضي، حساب الفرع (زي "المبيعات فرع ...")
        // كان بيتعمل من غير تصنيف، فإيراده مكانش بيدخل في صافي الربح
        // والميزانية العمومية تطلع غير متزنة.
        $category = null;
        $node = $parentAccount;
        for ($guard = 0; $node && $guard < 50; $guard++) {
            foreach ([$node->account_category_id, $node->account_type] as $candidate) {
                if (in_array((int) $candidate, [1, 2, 3, 4, 5], true)) {
                    $category = (int) $candidate;
                    break 2;
                }
            }
            $node = $node->parent_account_number ? FinancialAccount::find($node->parent_account_number) : null;
        }

        $nextAccountNumber = (int) (FinancialAccount::where('parent_account_number', $parentAccountId)->max('account_number') ?? 0) + 1;

        return FinancialAccount::create([
            'name' => $name,
            // account_type و account_category_id بيتورثوا من الأب
            // الحقيقي مباشرة (نفس أسلوب HrAccountService).
            'account_type' => $category,
            'account_category_id' => $category,
            'parent_account_number' => $parentAccountId,
            'account_number' => $nextAccountNumber,
            'start_balance' => 0,
            'current_balance' => 0,
            'start_balance_status' => 3,
            'added_by' => Auth::id() ?? 1,
            'com_code' => 1,
            'date' => Carbon::now('Asia/Riyadh'),
            'active' => 1,
            'is_parent' => 0,
            'orginal_id' => null,
            'orginal_type' => null,
            'branchs_id' => $branch->id,
        ]);
    }
}
