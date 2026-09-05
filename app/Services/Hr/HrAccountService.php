<?php

namespace App\Services\Hr;

use App\Models\Employee;
use App\Models\FinancialAccount;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * كل منطق ربط قسم الموارد البشرية بشجرة الحسابات (financialaccount)
 * مجمّع هنا في مكان واحد - بدل ما يتكرر جوه EmployeeController وبعدين
 * جوه EmployeesImporter وبعدين جوه EmployeeLoanController وهكذا.
 *
 * ⚠️ تصحيح مهم (كانت هنا نسخة قديمة غلط): النسخة القديمة من الكلاس ده
 * كانت بتدوّر بالاسم (find-or-create) على حسابات زي "ذمم الموظفين"،
 * "رواتب الموظفين"، "مكافآت الموظفين"... لكن الأسماء دي مكنتش مطابقة
 * حرفيًا لأسماء حساباتك الحقيقية الموجودة بالفعل في شجرتك (الاسم
 * الحقيقي كان "ذمم العاملين" مثلاً، مش "ذمم الموظفين")، فالكود القديم
 * كان بيفشل في إيجاد الحساب الحقيقي وبيعمل حساب جديد مكرر منفصل تمامًا
 * عن تاريخ حساباتك وحركاتك الفعلية. ده كان بيلخبط شجرة الحسابات بحسابات
 * مكررة فاضية.
 *
 * الإصلاح: بدل الاعتماد على مطابقة الاسم، بقينا نستخدم الـ ID الحقيقي
 * الثابت لكل حساب أب مباشرة - بالظبط زي الأسلوب المتّبع فعلاً في باقي
 * المشروع مع الموردين (PurchaseController::SUPPLIER_PARENT_ACCOUNT_NUMBER).
 * الآباء الخمسة دي حسابات حقيقية موجودة بالفعل في شجرتك ومفروض متتغيرش:
 *
 *   - id=82  "ذمم العاملين"        → تحته حساب "ذمة الموظف" الشخصي (سلف/عهدة).
 *   - id=142 "مكافأة نهاية الخدمة" → تحته حساب "مكافأة نهاية الخدمة" الشخصي.
 *   - id=146 "مكافأة"              → تحته حساب "مكافأة الموظف" الشخصي.
 *   - id=118 "رواتب الأجور"        → تحته حساب "راتب الموظف" الشخصي.
 *   - id=117 "إيرادات أخرى"        → تحته حساب "خصم الموظف" الشخصي.
 *
 * لما يتعمل موظف جديد (EmployeeController::store / EmployeesImporter)
 * بتتعمل الحسابات الشخصية الخمسة دي فورًا مع بعض (ensureAllEmployeeAccounts)
 * - مش كسول (lazy) عند أول حركة فعلية - عشان تظهر في الشجرة من أول لحظة.
 *
 * كل حساب شخصي بيتعرّف بـ:
 *   - orginal_type = Employee::ORGINAL_TYPE_EMPLOYEE (=5، ثابت أصلاً
 *     ومستخدم في Employee::financialAccount() - متغيرش).
 *   - orginal_id = id الموظف في جدول employees.
 *   - parent_account_number = id الأب الحقيقي بتاعه (82/142/146/118/117).
 * بما إن الخمس حسابات كلهم هيبقى ليهم نفس orginal_type/orginal_id، لازم
 * دايمًا نفلتر كمان بـ parent_account_number لما نجيب حساب معين من
 * الخمسة - مش orginal_type/orginal_id لوحدهم.
 *
 * account_type و account_category_id لكل حساب شخصي بيتورثوا من الأب
 * الحقيقي مباشرة (مش قيمة ثابتة زي قبل) - بناءً على طلب إن أي حساب
 * جديد ياخد نوعه من الحساب اللي بيتحط تحته.
 *
 * حساب "مستحقات رواتب الموظفين" (payrollPayableAccountId) فضل من غير
 * تغيير: ده حساب تجميعي واحد (مش شخصي لكل موظف) لإنه بيمثل حركة دفع/
 * التزام واحد فعلي وقت قفل الاستحقاق الشهري (زي حركة نقدية واحدة من
 * الخزينة)، مش ذمة تجاه موظف بعينه - فمفيش داعي يتقسم لكل موظف.
 */
class HrAccountService
{
    /**
     * الآباء الحقيقيون الخمسة (IDs ثابتة من شجرة حساباتك الفعلية -
     * راجع التعليق فوق الكلاس لتفاصيل كل واحد فيهم وسبب الإصلاح).
     */
    public const DUES_PARENT_ACCOUNT_ID = 82;
    public const EOS_PARENT_ACCOUNT_ID = 142;
    public const BONUS_PARENT_ACCOUNT_ID = 146;
    public const SALARY_PARENT_ACCOUNT_ID = 118;
    public const DEDUCTION_PARENT_ACCOUNT_ID = 117;

    private const LIABILITIES_CATEGORY_NAME = 'الخصوم';
    private const HR_LIABILITIES_GROUP_NAME = 'التزامات الموارد البشرية';
    private const PAYROLL_PAYABLE_ACCOUNT_NAME = 'مستحقات رواتب الموظفين';

    private const ROOT_CATEGORY_TYPE_IDS = [
        self::LIABILITIES_CATEGORY_NAME => 2,
    ];

    /**
     * حساب "ذمة الموظف" الشخصي (سلف/عهدة) - تحت "ذمم العاملين" الحقيقي
     * (id=82). ده نفس الحساب اللي EmployeeLoanController بيرحّل عليه
     * السلف والتسويات.
     */
    public function ensureEmployeeAccount(Employee $employee): FinancialAccount
    {
        return $this->ensureEmployeePersonalAccount($employee, self::DUES_PARENT_ACCOUNT_ID);
    }

    /**
     * حساب "مكافأة نهاية الخدمة" الشخصي - تحت الحساب الحقيقي id=142.
     */
    public function ensureEosAccount(Employee $employee): FinancialAccount
    {
        return $this->ensureEmployeePersonalAccount($employee, self::EOS_PARENT_ACCOUNT_ID);
    }

    /**
     * حساب "مكافأة الموظف" الشخصي (المكافأة الشهرية) - تحت الحساب
     * الحقيقي id=146.
     */
    public function ensureBonusAccount(Employee $employee): FinancialAccount
    {
        return $this->ensureEmployeePersonalAccount($employee, self::BONUS_PARENT_ACCOUNT_ID);
    }

    /**
     * حساب "راتب الموظف" الشخصي - تحت الحساب الحقيقي id=118.
     */
    public function ensureSalaryAccount(Employee $employee): FinancialAccount
    {
        return $this->ensureEmployeePersonalAccount($employee, self::SALARY_PARENT_ACCOUNT_ID);
    }

    /**
     * حساب "خصم الموظف" الشخصي - تحت الحساب الحقيقي id=117.
     */
    public function ensureDeductionAccount(Employee $employee): FinancialAccount
    {
        return $this->ensureEmployeePersonalAccount($employee, self::DEDUCTION_PARENT_ACCOUNT_ID);
    }

    /**
     * بتعمل الحسابات الشخصية الخمسة مع بعض دفعة واحدة - بتتنادى فورًا
     * وقت إضافة موظف جديد (EmployeeController::store / EmployeesImporter)،
     * مش لما تتحمل بس عند أول سلفة/راتب/مكافأة.
     */
    public function ensureAllEmployeeAccounts(Employee $employee): void
    {
        $this->ensureEmployeeAccount($employee);
        $this->ensureEosAccount($employee);
        $this->ensureBonusAccount($employee);
        $this->ensureSalaryAccount($employee);
        $this->ensureDeductionAccount($employee);
    }

    /**
     * حساب "مستحقات رواتب الموظفين" (التزام/خصوم) - بيتقيّد عليه صافي
     * الراتب بدل الخزينة وقت ترحيل شهر من غير اختيار حساب دفع (يعني
     * الشركة عارفة إنها لازم تدفع بس لسه ما دفعتش - استحقاق مش دفع
     * فعلي)، وبعدين لما الفلوس تتوفر بيتقفل بقيد منفصل (مدين المستحقات
     * / دائن الخزينة الفعلية - راجع PayrollController@payAccruedPosting).
     * تجميعي واحد مقصود (مش شخصي لكل موظف) - راجع تعليق الكلاس فوق.
     */
    public function payrollPayableAccountId(): ?int
    {
        return $this->findOrCreateGeneralAccount(self::PAYROLL_PAYABLE_ACCOUNT_NAME, $this->resolveHrLiabilitiesGroupId());
    }

    /**
     * مجموعة فرعية "التزامات الموارد البشرية" جوه فرع "الخصوم" - عشان
     * "مستحقات رواتب الموظفين" يبان مجمّع مع أي التزام موارد بشرية تاني
     * في التقرير المالي، مش متفرق بجوار التزامات تانية غير متعلقة.
     */
    private function resolveHrLiabilitiesGroupId(): ?int
    {
        return $this->findOrCreateGeneralAccount(self::HR_LIABILITIES_GROUP_NAME, $this->resolveLiabilitiesCategoryId(), true);
    }

    private function resolveLiabilitiesCategoryId(): ?int
    {
        return $this->findOrCreateGeneralAccount(self::LIABILITIES_CATEGORY_NAME, null, true);
    }

    /**
     * الدالة الأساسية اللي بتعمل/تجيب حساب شخصي لموظف تحت أب حقيقي ثابت.
     * التمييز بين الخمس حسابات المحتملة لنفس الموظف بيبقى عن طريق
     * parent_account_number - مش orginal_type/orginal_id لوحدهم، لإن
     * دول هيبقوا متطابقين للخمس حسابات كلهم.
     */
    private function ensureEmployeePersonalAccount(Employee $employee, int $parentAccountId): FinancialAccount
    {
        $existing = FinancialAccount::where('orginal_type', Employee::ORGINAL_TYPE_EMPLOYEE)
            ->where('orginal_id', $employee->id)
            ->where('parent_account_number', $parentAccountId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $parentAccount = FinancialAccount::findOrFail($parentAccountId);

        $nextAccountNumber = (int) (FinancialAccount::where('parent_account_number', $parentAccountId)
            ->max('account_number') ?? $parentAccountId) + 1;

        return FinancialAccount::create([
            'name' => $employee->name,
            // account_type و account_category_id بيتورثوا من الأب
            // الحقيقي مباشرة - مش قيمة ثابتة.
            'account_type' => $parentAccount->account_type,
            'account_category_id' => $parentAccount->account_category_id,
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
            'orginal_id' => $employee->id,
            'orginal_type' => Employee::ORGINAL_TYPE_EMPLOYEE,
        ]);
    }

    private function findOrCreateGeneralAccount(string $name, ?int $parentAccountId, bool $isParent = false): ?int
    {
        $account = FinancialAccount::where('name', $name)
            ->whereNull('orginal_id')
            ->first();

        if ($account) {
            return $account->id;
        }

        $nextAccountNumber = (int) (FinancialAccount::max('account_number') ?? 0) + 1;

        // حساب جذر (فرع رئيسي) بياخد تصنيفه بالاسم من الخريطة الثابتة،
        // وأي حساب تاني ليه أب بيورّث تصنيف أبوه مباشرة.
        $accountCategoryId = $parentAccountId
            ? FinancialAccount::inheritedCategoryId($parentAccountId)
            : (self::ROOT_CATEGORY_TYPE_IDS[$name] ?? null);

        $account = FinancialAccount::create([
            'name' => $name,
            // account_type و account_category_id بيتورثوا مع بعض من نفس
            // القيمة (بدل قيمة ثابتة قديمة =4/مصروفات كانت غلط لحسابات
            // زي "مستحقات رواتب الموظفين" اللي هي التزام/خصوم مش مصروف).
            'account_type' => $accountCategoryId,
            'account_category_id' => $accountCategoryId,
            'parent_account_number' => $parentAccountId,
            'account_number' => $nextAccountNumber,
            'start_balance' => 0,
            'current_balance' => 0,
            'start_balance_status' => 3,
            'added_by' => Auth::id() ?? 1,
            'com_code' => 1,
            'date' => Carbon::now('Asia/Riyadh'),
            'active' => 1,
            'is_parent' => $isParent ? 1 : 0,
            'orginal_id' => null,
            'orginal_type' => null,
        ]);

        return $account->id;
    }
}
