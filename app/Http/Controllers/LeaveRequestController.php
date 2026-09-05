<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\HrSetting;
use App\Models\LeaveRequest;
use App\Services\Hr\AttendanceCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * طلبات الإجازة - سجل طلب/موافقة/رفض بسيط (راجع تعليق موديل LeaveRequest
 * لتفاصيل القرار)، مع رصيد فعلي لنوع "سنوية" بس (EmployeeLeaveBalance).
 *
 * الموافقة (approve) هي نقطة التكامل مع شاشة الحضور: بتحوّل كل يوم في
 * مدى الطلب لحالة "إجازة" (بدل ما يفضل من غير بصمة = غياب تلقائيًا)،
 * وبتشيل أي خصم غياب متصل كان مسجل على الأيام دي قبل الموافقة (عن طريق
 * AttendanceCalculator::syncConnectedOffDayPenalty - نفس الحاسبة
 * المستخدمة في شاشة الحضور).
 */
class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('leave_requests.view');

        $query = LeaveRequest::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $leaveRequests = $query->orderByDesc('start_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_number', 'hire_date', 'branch_id']);

        // بنجهّز رصيد كل موظف مقدمًا (بما فيهم اللي لسه معملوش سجل رصيد
        // أصلاً - forEmployee() بترجع سجل افتراضي غير محفوظ) عشان الشاشة
        // متعملش استعلام لكل موظف وقت العرض.
        $balances = $employees->mapWithKeys(fn ($employee) => [$employee->id => EmployeeLeaveBalance::forEmployee($employee)]);

        return view('leave-requests.index', compact('leaveRequests', 'employees', 'balances'));
    }

    public function store(Request $request)
    {
        $this->authorize('leave_requests.view');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'in:annual,sick,unpaid,emergency,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        LeaveRequest::create(array_merge($validated, [
            'days_count' => $start->diffInDays($end) + 1,
            'status' => LeaveRequest::STATUS_PENDING,
            'created_by' => Auth::id(),
        ]));

        return redirect()->route('leave-requests.index')->with('success', __('leave_requests.created_success'));
    }

    public function approve(LeaveRequest $leaveRequest, AttendanceCalculator $calculator)
    {
        $this->authorize('leave_requests.view');

        if (!$leaveRequest->isPending()) {
            abort(422, __('leave_requests.already_decided'));
        }

        if ($leaveRequest->consumesAnnualBalance()) {
            $balance = EmployeeLeaveBalance::forEmployee($leaveRequest->employee);
            if ((float) $balance->balance_days < $leaveRequest->days_count) {
                abort(422, __('leave_requests.insufficient_balance', ['balance' => $balance->balance_days]));
            }
        }

        DB::transaction(function () use ($leaveRequest, $calculator) {
            if ($leaveRequest->consumesAnnualBalance()) {
                $balance = EmployeeLeaveBalance::forEmployee($leaveRequest->employee);
                $balance->balance_days = (float) $balance->balance_days - $leaveRequest->days_count;
                $balance->updated_by = Auth::id();
                $balance->save();
            }

            $employee = $leaveRequest->employee;
            $setting = HrSetting::forBranch($employee->branch_id);
            $discountPerDay = $leaveRequest->isUnpaid() ? round($employee->dailySalary(), 2) : 0.0;

            $cursor = $leaveRequest->start_date->copy();
            while ($cursor->lte($leaveRequest->end_date)) {
                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $cursor->toDateString()],
                    [
                        'status' => Attendance::STATUS_LEAVE,
                        'late_minutes' => 0,
                        'overtime_hours' => 0,
                        'overtime_amount' => 0,
                        'discount_amount' => $discountPerDay,
                        'is_connected_penalty' => false,
                        'source' => Attendance::SOURCE_MANUAL,
                        'notes' => __('leave_requests.attendance_note', ['type' => __('leave_requests.type_' . $leaveRequest->type)]),
                    ]
                );

                // نشيل أي خصم غياب متصل كان مسجل على الأيام المجاورة بسبب
                // غياب كان هيتحسب "غير مصرح به" قبل ما الإجازة تتوافق.
                $calculator->syncConnectedOffDayPenalty($employee, $cursor->copy(), $setting);

                $cursor->addDay();
            }

            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('leave-requests.index')->with('success', __('leave_requests.approved_success'));
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('leave_requests.view');

        if (!$leaveRequest->isPending()) {
            abort(422, __('leave_requests.already_decided'));
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('leave-requests.index')->with('success', __('leave_requests.rejected_success'));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->authorize('leave_requests.view');

        if (!$leaveRequest->isPending()) {
            abort(422, __('leave_requests.cannot_delete_decided'));
        }

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')->with('success', __('leave_requests.deleted_success'));
    }

    /**
     * تعديل رصيد الإجازة السنوية يدويًا (رصيد افتتاحي أو تسوية إدارية) -
     * مش جزء من دورة طلب/موافقة عادية.
     */
    public function updateBalance(Request $request, Employee $employee)
    {
        $this->authorize('leave_requests.view');

        $validated = $request->validate([
            'balance_days' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $balance = EmployeeLeaveBalance::forEmployee($employee);
        $balance->balance_days = $validated['balance_days'];
        $balance->notes = $validated['notes'] ?? $balance->notes;
        $balance->updated_by = Auth::id();
        $balance->save();

        return redirect()->route('leave-requests.index')->with('success', __('leave_requests.balance_updated_success'));
    }
}
