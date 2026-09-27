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
            $data['employee_id'] = $this->resolveEmployee($data, $request);
            $driver->update($data);
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
        ], ['phone.regex' => __('transport.phone_bad')]);

        $driver = Driver::create($data + ['driver_type' => $request->input('driver_type') === 'external' ? 'external' : 'company', 'status' => 'active', 'salary' => 0, 'created_by' => Auth::id()]);

        return response()->json(['id' => $driver->id, 'name' => $driver->name, 'phone' => $driver->phone]);
    }

    /**
     * السائق تبع الشركة: يا إما مربوط بموظف موجود في الموارد البشرية، يا
     * إما (لو علّم "أضفه كموظف") بيتعمل له ملف موظف جديد بوظيفة "سائق".
     * السائق الخارجي مالوش موظف.
     */
    private function resolveEmployee(array $data, Request $request): ?int
    {
        if (($data['driver_type'] ?? 'company') === 'external') {
            return null;
        }

        if (!empty($data['employee_id'])) {
            return (int) $data['employee_id'];
        }

        if ($request->boolean('create_employee') && auth()->user()?->can('employees.create')) {
            $employee = Employee::create([
                'employee_number' => Employee::nextEmployeeNumber(),
                'name' => $data['name'],
                'national_id' => $data['id_number'] ?? null,
                'phone' => $data['phone'] ?? null,
                'job_title' => 'سائق',
                'department' => Department::drivers()->name, // قسم السائقين تلقائي
                'hire_date' => now()->toDateString(),
                'basic_salary' => $data['salary'] ?? 0,
                'allowances' => 0,
                'pay_method' => 'Cash',
                'status' => Employee::STATUS_ACTIVE,
                'created_by' => Auth::id(),
            ]);
            app(HrAccountService::class)->ensureAllEmployeeAccounts($employee);

            return $employee->id;
        }

        return null;
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
