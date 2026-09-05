<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * موظف - أساس قسم الموارد البشرية اللي كل حاجة تانية (السلف، مكافأة
 * نهاية الخدمة، الحضور والانصراف) هتعتمد عليه.
 *
 * ⚠️ ORGINAL_TYPE = 5: قيمة جديدة مخصصة للموظفين في عمود
 * financialaccount.orginal_type (نفس فكرة orginal_type=2 المستخدمة
 * للموردين في SupplierController) - القيمة دي مش مستخدمة لحاجة تانية
 * في المشروع وقت كتابة الكود ده. حساب الموظف المالي (ذمم الموظف) بيفضل
 * "مدين بطبيعته" افتراضيًا لإن FinancialAccount::isCreditNormal() بترجع
 * true بس لما orginal_type == 2 (مورد) - وده صحيح محاسبيًا هنا لإن رصيد
 * الموظف (سلفة/عهدة) هو مبلغ الموظف مديون بيه للشركة، زي حساب عميل
 * بالظبط، مش العكس.
 */
class Employee extends Model
{
    use HasFactory;

    public const ORGINAL_TYPE_EMPLOYEE = 5;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'employee_number',
        'name',
        'name_en',
        'national_id',
        'phone',
        'email',
        'job_title',
        'department',
        'branch_id',
        'hire_date',
        'basic_salary',
        'allowances',
        'pay_method',
        'bank_name',
        'iban',
        'national_address',
        'status',
        'termination_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * حساب "ذمة الموظف" الشخصي (سلف/عهدة) - واحد من خمس حسابات شخصية
     * بتتعمل للموظف (راجع HrAccountService::ensureAllEmployeeAccounts)،
     * كلهم بنفس orginal_type/orginal_id، فلازم نفلتر كمان بـ
     * parent_account_number عشان نحدد ده بالذات (تحت "ذمم العاملين"
     * الحقيقي id=82) من باقي الأربعة (مكافأة نهاية الخدمة/مكافأة/راتب/خصم).
     */
    public function financialAccount()
    {
        return $this->hasOne(FinancialAccount::class, 'orginal_id')
            ->where('orginal_type', self::ORGINAL_TYPE_EMPLOYEE)
            ->where('parent_account_number', \App\Services\Hr\HrAccountService::DUES_PARENT_ACCOUNT_ID);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * رقم موظف تسلسلي جديد (EMP-000001) - مستخدمة من EmployeeController@store
     * ومن EmployeesImporter سوا عشان الترقيم يفضل مصدر واحد بدل ما يتكرر
     * المنطق في مكانين ممكن يتفرقوا عن بعض بالغلط لاحقًا.
     */
    public static function nextEmployeeNumber(): string
    {
        $next = (int) (self::max('id') ?? 0) + 1;

        return 'EMP-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * الراتب اليومي - أساس حساب خصم التأخير وقيمة ساعة الأوفرتايم في
     * شاشة الحضور والانصراف لاحقًا (بيتقسم على 30 يوم زي الاتفاقية
     * المتبعة في باقي حسابات الرواتب).
     */
    public function dailySalary(): float
    {
        return round(((float) $this->basic_salary + (float) $this->allowances) / 30, 2);
    }
}
