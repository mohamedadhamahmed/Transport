<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FinancialAccount;
use App\Models\Branch;
use App\Models\Department;
use App\Services\Hr\HrAccountService;
use App\Services\Hr\EmployeesImporter;
use App\Services\Hr\EmployeesTemplateExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * إدارة الموظفين - أساس قسم الموارد البشرية. نفس أسلوب SupplierController
 * بالظبط (index بحث+pagination، create/store، edit/update) زائد:
 * - رقم موظف تسلسلي (employee_number) بنفس منطق أرقام السندات/القيود
 *   المتبع في VoucherController/JournalEntryController (max(id)+1 مع
 *   str_pad)، مش auto_increment عادي عشان يبان بصيغة "EMP-000001".
 * - حساب مالي (FinancialAccount) بيتعمل تلقائيًا مع كل موظف جديد بنفس
 *   أسلوب SupplierController@store (رصيد افتتاحي صفر، وبيتحدث بعدين مع
 *   أي سلفة أو قيد مكافأة نهاية خدمة عن طريق App\Support\AccountEffect).
 * - toggleStatus بدل destroy: مينفعش نمسح موظف ليه حساب مالي وسجل سلف/
 *   حضور مرتبط بيه، فالتعطيل (status=inactive) هو البديل الآمن، بنفس
 *   فكرة AccountController@toggleActive.
 * - إنشاء/تعديل الحساب المالي منقول بالكامل لـ App\Services\Hr\HrAccountService
 *   (كان جوه الكونترولر ده لحاله، اتنقل عشان EmployeesImporter (استيراد
 *   إكسيل) يقدر يستخدم نفس المنطق بالظبط من غير تكرار).
 */
class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('employees.view');

        $query = Employee::with('branch');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $request->input('department') === '__none'
                ? $query->where(fn ($q) => $q->whereNull('department')->orWhere('department', ''))
                : $query->where('department', $request->input('department'));
        }

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        $departments = Department::orderBy('name')->pluck('name');

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $this->authorize('employees.create');

        // شركة نقليات: الموظف الجديد بيتحط افتراضيًا في قسم السائقين (يقدر يغيّره)
        $employee = new Employee(['department' => Department::drivers()->name]);
        $branches = Branch::orderBy('name')->get();
        $departments = Department::options();

        return view('employees.create', compact('employee', 'branches', 'departments'));
    }

    public function store(Request $request, HrAccountService $accounts)
    {
        $this->authorize('employees.create');

        $validated = $this->validated($request);

        $employee = DB::transaction(function () use ($validated, $accounts) {
            $employee = Employee::create([
                'employee_number' => Employee::nextEmployeeNumber(),
                'name' => $validated['name'],
                'name_en' => $validated['name_en'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'department' => $validated['department'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
                'basic_salary' => $validated['basic_salary'] ?? 0,
                'allowances' => $validated['allowances'] ?? 0,
                'pay_method' => $validated['pay_method'] ?? 'Cash',
                'bank_name' => $validated['bank_name'] ?? null,
                'iban' => $validated['iban'] ?? null,
                'national_address' => $validated['national_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => Employee::STATUS_ACTIVE,
                'created_by' => Auth::id(),
            ]);

            $accounts->ensureAllEmployeeAccounts($employee);

            return $employee;
        });

        return redirect()->route('employees.index')->with('success', __('employees.created_success'));
    }

    /**
     * فورم رفع ملف إكسيل لإضافة/تحديث دفعة موظفين مرة واحدة - بدل
     * إضافتهم واحد واحد من شاشة "موظف جديد".
     */
    public function importForm()
    {
        $this->authorize('employees.create');

        return view('employees.import');
    }

    public function downloadTemplate(EmployeesTemplateExporter $exporter)
    {
        $this->authorize('employees.create');

        return $exporter->download();
    }

    /**
     * معالجة ملف الإكسيل - نفس منطق PurchaseController@importItems
     * (مسؤولية القراءة/الإنشاء منقولة لكلاس منفصل EmployeesImporter في
     * app/Services/Hr، بنفس تنظيم PurchaseItemsImporter بالظبط).
     */
    public function import(Request $request, EmployeesImporter $importer)
    {
        $this->authorize('employees.create');

        $validated = $request->validate([
            'employees_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $result = $importer->import($request->file('employees_excel'));

        $message = __('employees.import_success', ['count' => count($result['created'])]);
        if (!empty($result['updated'])) {
            $message .= ' - ' . __('employees.import_updated', ['count' => count($result['updated'])]);
        }
        if (!empty($result['skipped'])) {
            $message .= ' - ' . __('employees.import_skipped', ['count' => count($result['skipped'])]);
        }

        return redirect()->route('employees.index')->with('success', $message);
    }

    public function edit(Employee $employee)
    {
        $this->authorize('employees.edit');

        $branches = Branch::orderBy('name')->get();
        $departments = Department::options($employee->department);

        return view('employees.edit', compact('employee', 'branches', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorize('employees.edit');

        $validated = $this->validated($request, $employee->id);

        $employee->update([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? $employee->name_en,
            'national_id' => $validated['national_id'] ?? $employee->national_id,
            'phone' => $validated['phone'] ?? $employee->phone,
            'email' => $validated['email'] ?? $employee->email,
            'job_title' => $validated['job_title'] ?? $employee->job_title,
            'department' => $validated['department'] ?? null,
            'branch_id' => $validated['branch_id'] ?? $employee->branch_id,
            'hire_date' => $validated['hire_date'] ?? $employee->hire_date,
            'basic_salary' => $validated['basic_salary'] ?? $employee->basic_salary,
            'allowances' => $validated['allowances'] ?? $employee->allowances,
            'pay_method' => $validated['pay_method'] ?? $employee->pay_method,
            'bank_name' => $validated['bank_name'] ?? $employee->bank_name,
            'iban' => $validated['iban'] ?? $employee->iban,
            'national_address' => $validated['national_address'] ?? $employee->national_address,
            'notes' => $validated['notes'] ?? $employee->notes,
        ]);

        FinancialAccount::where('orginal_type', Employee::ORGINAL_TYPE_EMPLOYEE)
            ->where('orginal_id', $employee->id)
            ->update(['name' => $employee->name]);

        return redirect()->route('employees.index')->with('success', __('employees.updated_success'));
    }

    /**
     * تعطيل/تفعيل الموظف بدل مسحه نهائيًا - موظف اتصرفله سلف أو قيود
     * محاسبية مينفعش يتمسح من غير ما نكسر الربط بحسابه المالي.
     */
    public function toggleStatus(Employee $employee)
    {
        $this->authorize('employees.edit');

        $employee->update([
            'status' => $employee->isActive() ? Employee::STATUS_INACTIVE : Employee::STATUS_ACTIVE,
        ]);

        return redirect()->route('employees.index')->with('success', __('employees.status_updated_success'));
    }

    private function validated(Request $request, ?int $ignoreEmployeeId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255', function ($attr, $value, $fail) use ($ignoreEmployeeId) {
                // لازم يكون من الأقسام، إلا لو هو نفس القسم الحالي للموظف (قديم)
                $current = $ignoreEmployeeId ? Employee::whereKey($ignoreEmployeeId)->value('department') : null;
                if ($value !== $current && !Department::where('name', $value)->exists()) {
                    $fail(__('validation.exists', ['attribute' => __('employees.department')]));
                }
            }],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'hire_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'pay_method' => ['nullable', 'in:Cash,Bank'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:40'],
            'national_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }

}
