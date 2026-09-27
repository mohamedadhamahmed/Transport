<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * أقسام الموارد البشرية - قائمة بتتختار منها في إنشاء/تعديل الموظف.
 * تغيير اسم القسم بيتنقل تلقائي لكل الموظفين اللي فيه.
 */
class DepartmentController extends Controller
{
    public function index()
    {
        $this->authorize('departments.view');

        $departments = Department::orderByRaw('name = ? desc', [Department::DRIVERS])->orderBy('name')->get();
        $counts = Employee::query()
            ->whereNotNull('department')
            ->groupBy('department')
            ->selectRaw('department, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active', [Employee::STATUS_ACTIVE])
            ->get()->keyBy('department');
        $unassigned = Employee::where(fn ($q) => $q->whereNull('department')->orWhere('department', ''))->count();

        return view('employees.departments', compact('departments', 'counts', 'unassigned'));
    }

    public function store(Request $request)
    {
        $this->authorize('departments.create');

        $data = $this->validated($request);
        $data['created_by'] = Auth::id();
        Department::create($data);

        return back()->with('success', __('employees.department_created'));
    }

    public function update(Request $request, Department $department)
    {
        $this->authorize('departments.edit');

        $data = $this->validated($request, $department);

        DB::transaction(function () use ($department, $data) {
            $old = $department->name;
            $department->update($data);
            if ($old !== $department->name) {
                Employee::where('department', $old)->update(['department' => $department->name]);
            }
        });

        return back()->with('success', __('employees.department_updated'));
    }

    public function destroy(Department $department)
    {
        $this->authorize('departments.delete');

        if ($department->name === Department::DRIVERS) {
            return back()->with('error', __('employees.department_drivers_locked'));
        }
        if (Employee::where('department', $department->name)->exists()) {
            return back()->with('error', __('employees.department_in_use'));
        }

        $department->delete();

        return back()->with('success', __('employees.department_deleted'));
    }

    private function validated(Request $request, ?Department $department = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department?->id)],
            'name_en' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['name'] = trim($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        // قسم السائقين اسمه ثابت (الربط التلقائي مع شاشة السائقين معتمد عليه)
        if ($department && $department->name === Department::DRIVERS) {
            $data['name'] = Department::DRIVERS;
            $data['is_active'] = true;
        }

        return $data;
    }
}
