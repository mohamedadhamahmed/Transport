<?php

use App\Models\Department;
use App\Models\Driver;
use App\Models\Employee;
use App\Services\Hr\HrAccountService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * مزامنة السائقين التابعين للمؤسسة غير المربوطين بموظف:
     * إنشاء سجل موظف في قسم السائقين بالموارد البشرية وربط السائق به وحساباته المالية.
     */
    public function up(): void
    {
        if (!Schema::hasTable('drivers') || !Schema::hasTable('employees')) {
            return;
        }

        $drivers = Driver::where('driver_type', 'company')
            ->whereNull('employee_id')
            ->get();

        foreach ($drivers as $driver) {
            try {
                // البحث عن موظف مسجل بنفس الهوية أو رقم الجوال
                $existing = null;
                if (!empty($driver->id_number)) {
                    $existing = Employee::where('national_id', $driver->id_number)->first();
                }
                if (!$existing && !empty($driver->phone)) {
                    $existing = Employee::where('phone', $driver->phone)->first();
                }

                if ($existing) {
                    $driver->update(['employee_id' => $existing->id]);
                    continue;
                }

                $employee = Employee::create([
                    'employee_number' => Employee::nextEmployeeNumber(),
                    'name' => $driver->name,
                    'national_id' => $driver->id_number,
                    'phone' => $driver->phone,
                    'job_title' => 'سائق',
                    'department' => Department::drivers()->name,
                    'hire_date' => now()->toDateString(),
                    'basic_salary' => $driver->salary ?? 0,
                    'allowances' => 0,
                    'pay_method' => 'Cash',
                    'status' => $driver->status === 'active' ? Employee::STATUS_ACTIVE : Employee::STATUS_INACTIVE,
                    'notes' => $driver->notes,
                    'created_by' => $driver->created_by,
                ]);

                try {
                    app(HrAccountService::class)->ensureAllEmployeeAccounts($employee);
                } catch (\Throwable $e) {
                    Log::warning('HrAccountService notice: ' . $e->getMessage());
                }

                $driver->update(['employee_id' => $employee->id]);
            } catch (\Throwable $e) {
                Log::error("Failed to sync driver #{$driver->id} to employee: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // لا نحذف الموظفين لتجنب فقدان أي بيانات محاسبية
    }
};
