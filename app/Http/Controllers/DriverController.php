<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Driver;
use App\Models\Employee;
use App\Services\Hr\HrAccountService;
use Illuminate\Support\Facades\DB;
use App\Support\SaudiPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('drivers.view');

        $drivers = Driver::withCount('trucks')
            ->with(['activeLoad.truck:id,plate_number', 'trucks:id,plate_number,driver_id', 'employee:id,name,employee_number'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->input('search');
                $phone = SaudiPhone::normalize($s);
                $q->where(function ($qq) use ($s, $phone) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->when($phone !== '', fn ($x) => $x->orWhere('phone', 'like', "%{$phone}%"))
                        ->orWhere('id_number', 'like', "%{$s}%")
                        ->orWhere('license_number', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('driver_type'), fn ($q) => $q->where('driver_type', $request->input('driver_type')))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('transport.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $this->authorize('drivers.create');

        return view('transport.drivers.create', ['employees' => $this->employeeOptions()]);
    }

    public function store(Request $request)
    {
        $this->authorize('drivers.create');

        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        DB::transaction(function () use ($data, $request) {
            $data['employee_id'] = $this->resolveEmployee($data, $request);
            Driver::create($data);
        });

        return redirect()->route('transport.drivers.index')->with('success', __('transport.driver_created'));
    }

    public function edit(Driver $driver)
    {
        $this->authorize('drivers.edit');

        return view('transport.drivers.edit', ['driver' => $driver, 'employees' => $this->employeeOptions($driver->employee_id)]);
    }

    public function update(Request $request, Driver $driver)
    {
        $this->authorize('drivers.edit');

        DB::transaction(function () use ($driver, $request) {
            $data = $this->validated($request);
            $data['employee_id'] = $this->resolveEmployee($data, $request, $driver);
            $driver->update($data);

            if ($driver->employee_id && $driver->employee) {
                $driver->employee->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? $driver->employee->phone,
                    'national_id' => $data['id_number'] ?? $driver->employee->national_id,
                    'basic_salary' => $data['salary'] ?? $driver->employee->basic_salary,
                    'status' => ($data['status'] ?? 'active') === 'active' ? Employee::STATUS_ACTIVE : Employee::STATUS_INACTIVE,
                ]);
            }
        });

        return redirect()->route('transport.drivers.index')->with('success', __('transport.driver_updated'));
    }

    public function destroy(Driver $driver)
    {
        $this->authorize('drivers.delete');

        // الشاحنات المرتبطة بيه هتفضل موجودة، بس من غير سائق (nullOnDelete)
        $driver->delete();

        return redirect()->route('transport.drivers.index')->with('success', __('transport.driver_deleted'));
    }

    /** إضافة سائق سريعة (Ajax) من نافذة التحميل في لوحة الشاحنات */
    public function quick(Request $request)
    {
        $this->authorize('drivers.create');

        $request->merge(['phone' => SaudiPhone::normalize($request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^05\d{8}$/'],
            'driver_type' => ['nullable', 'in:company,external'],
        ], ['phone.regex' => __('transport.phone_bad')]);

        $driverType = $request->input('driver_type') === 'external' ? 'external' : 'company';

        $driver = DB::transaction(function () use ($data, $driverType, $request) {
            $driverData = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'driver_type' => $driverType,
                'status' => 'active',
                'salary' => 0,
                'created_by' => Auth::id(),
            ];

            if ($driverType === 'company') {
                $driverData['employee_id'] = $this->resolveEmployee($driverData, $request);
            }

            return Driver::create($driverData);
        });

        return response()->json(['id' => $driver->id, 'name' => $driver->name, 'phone' => $driver->phone]);
    }

    /**
     * السائق تبع الشركة:
     * 1. لو تم اختيار موظف مسجل يدويًا من القائمة، يتم الربط به.
     * 2. لو السائق له موظف مرتبط مسبقاً، نحتفظ به.
     * 3. لو وُجد موظف مسجل بنفس رقم الهوية أو الهاتف، يتم الربط به منعاً للتكرار.
     * 4. خلاف ذلك: يتم تلقائياً إنشاء ملف موظف جديد له في جدول الموظفين (قسم السائقين)،
     *    وإنشاء حساباته المالية في شجرة الحسابات، ليظهر فوراً في قسم الموارد البشرية.
     */
    private function resolveEmployee(array $data, Request $request, ?Driver $driver = null): ?int
    {
        if (($data['driver_type'] ?? 'company') === 'external') {
            return null;
        }

        if (!empty($data['employee_id'])) {
            return (int) $data['employee_id'];
        }

        if ($driver && $driver->employee_id) {
            return (int) $driver->employee_id;
        }

        $existing = null;
        if (!empty($data['id_number'])) {
            $existing = Employee::where('national_id', $data['id_number'])->first();
        }
        if (!$existing && !empty($data['phone'])) {
            $existing = Employee::where('phone', $data['phone'])->first();
        }

        if ($existing) {
            return $existing->id;
        }

        $employee = Employee::create([
            'employee_number' => Employee::nextEmployeeNumber(),
            'name' => $data['name'],
            'national_id' => $data['id_number'] ?? null,
            'phone' => $data['phone'] ?? null,
            'job_title' => 'سائق',
            'department' => Department::drivers()->name,
            'hire_date' => now()->toDateString(),
            'basic_salary' => $data['salary'] ?? 0,
            'allowances' => 0,
            'pay_method' => 'Cash',
            'status' => ($data['status'] ?? 'active') === 'active' ? Employee::STATUS_ACTIVE : Employee::STATUS_INACTIVE,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        try {
            app(HrAccountService::class)->ensureAllEmployeeAccounts($employee);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('HrAccountService notice while creating driver employee: ' . $e->getMessage());
        }

        return $employee->id;
    }

    private function employeeOptions(?int $include = null)
    {
        return Employee::where('status', Employee::STATUS_ACTIVE)
            ->when($include, fn ($q) => $q->orWhere('id', $include))
            ->orderBy('name')
            ->get(['id', 'name', 'employee_number']);
    }

    private function validated(Request $request): array
    {
        // تنظيف الرقم قبل الفحص: أرقام عربية، مسافات، +966 ...
        $request->merge(['phone' => SaudiPhone::normalize($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'driver_type' => ['required', 'in:company,external'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'phone' => ['required', 'regex:/^05\d{8}$/'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'date'],
            'id_expiry' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ], [
            'phone.required' => __('transport.phone_required'),
            'phone.regex' => __('transport.phone_bad'),
        ]);

        $data['salary'] = $data['salary'] ?? 0;

        return $data;
    }
}
