<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Http\Request;

class EmployeeContractController extends Controller implements HasMiddleware
{
    // العرض بصلاحية عرض الموظفين، والإضافة/التعديل/الحذف بصلاحية تعديل الموظفين
    public static function middleware(): array
    {
        return [
            new Middleware('can:employees.view', only: ['index']),
            new Middleware('can:employees.edit', except: ['index']),
        ];
    }

    public function index()
    {
        $contracts = EmployeeContract::with('employee')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('contracts.index', compact('contracts'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();

        return view('contracts.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        EmployeeContract::create($data);

        return redirect()->route('contracts.index')->with('success', __('contracts.added'));
    }

    public function edit(EmployeeContract $contract)
    {
        $employees = Employee::orderBy('name')->get();

        return view('contracts.edit', compact('contract', 'employees'));
    }

    public function update(Request $request, EmployeeContract $contract)
    {
        $contract->update($this->validated($request));

        return redirect()->route('contracts.index')->with('success', __('contracts.updated'));
    }

    public function destroy(EmployeeContract $contract)
    {
        $contract->delete();

        return redirect()->route('contracts.index')->with('success', __('contracts.deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id'        => ['required', 'exists:employees,id'],
            'contract_type'      => ['required', 'string', 'max:255'],
            'start_date'         => ['required', 'date'],
            'end_date'           => ['required', 'date', 'after_or_equal:start_date'],
            'residency_expiry'   => ['nullable', 'date'],
            'work_permit_expiry' => ['nullable', 'date'],
            'notes'              => ['nullable', 'string'],
        ]);
    }
}