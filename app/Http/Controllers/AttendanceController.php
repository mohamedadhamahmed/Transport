<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Services\Hr\AttendanceCalculator;
use App\Services\Hr\AttendanceImporter;
use App\Services\Hr\AttendanceTemplateExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * الحضور والانصراف - عرض تقرير شهري + إدخال يدوي (تصحيح يوم واحد) +
 * استيراد ملف بصمة إكسيل دفعة واحدة (AttendanceImporter). الحساب
 * الفعلي (تأخير/أوفرتايم/خصم) بيحصل في AttendanceCalculator وبيتشارك
 * بين الإدخال اليدوي والاستيراد عشان النتيجة تفضل واحدة.
 */
class AttendanceController extends Controller
{
    /**
     * تقرير شهري - كل موظف وصفوفه في الشهر المحدد، مع فلتر اختياري
     * بموظف واحد أو فرع.
     */
    public function index(Request $request)
    {
        $this->authorize('attendance.view');

        $month = $request->filled('month') ? $request->input('month') : now()->format('Y-m');
        [$year, $monthNumber] = array_pad(explode('-', $month), 2, now()->format('m'));

        $query = Attendance::with('employee.branch')
            ->whereYear('date', (int) $year)
            ->whereMonth('date', (int) $monthNumber);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->input('branch_id')));
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(30)->withQueryString();

        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_number']);

        $summary = [
            'present' => (clone $query)->where('status', 'present')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'late_minutes' => (clone $query)->sum('late_minutes'),
            'overtime_hours' => (clone $query)->sum('overtime_hours'),
            'discount_amount' => (clone $query)->sum('discount_amount'),
        ];

        return view('attendance.index', compact('attendances', 'employees', 'month', 'summary'));
    }

    public function create()
    {
        $this->authorize('attendance.create');

        $employees = Employee::where('status', Employee::STATUS_ACTIVE)->orderBy('name')->get();
        $attendance = new Attendance();

        return view('attendance.create', compact('employees', 'attendance'));
    }

    public function store(Request $request, AttendanceCalculator $calculator)
    {
        $this->authorize('attendance.create');

        $validated = $this->validated($request);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = Carbon::parse($validated['date']);
        $setting = HrSetting::forBranch($employee->branch_id);

        $computed = $calculator->calculate(
            $employee,
            $date,
            $validated['check_in'] ?? null,
            $validated['check_out'] ?? null,
            $setting
        );

        Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date->toDateString()],
            array_merge($computed, [
                'check_in' => $validated['check_in'] ?? null,
                'check_out' => $validated['check_out'] ?? null,
                'source' => Attendance::SOURCE_MANUAL,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ])
        );

        // لو اليوم ده غياب غير مصرح به - نطبّق/نحدّث خصم أيام الإجازة
        // الأسبوعية/الرسمية المتصلة بيه (راجع تعليق
        // AttendanceCalculator::syncConnectedOffDayPenalty).
        $calculator->syncConnectedOffDayPenalty($employee, $date, $setting);

        return redirect()->route('attendance.index')->with('success', __('attendance.saved_success'));
    }

    public function destroy(Attendance $attendance)
    {
        $this->authorize('attendance.create');

        $attendance->delete();

        return redirect()->route('attendance.index')->with('success', __('attendance.deleted_success'));
    }

    public function importForm()
    {
        $this->authorize('attendance.create');

        return view('attendance.import');
    }

    public function downloadTemplate(AttendanceTemplateExporter $exporter)
    {
        $this->authorize('attendance.create');

        return $exporter->download();
    }

    public function import(Request $request, AttendanceImporter $importer)
    {
        $this->authorize('attendance.create');

        $request->validate([
            'attendance_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $result = $importer->import($request->file('attendance_excel'));

        $message = __('attendance.import_success', ['count' => $result['imported']]);
        if (!empty($result['not_found'])) {
            $message .= ' - ' . __('attendance.import_not_found', ['codes' => implode(', ', array_slice($result['not_found'], 0, 10))]);
        }
        if (!empty($result['skipped'])) {
            $message .= ' - ' . __('attendance.import_skipped', ['count' => count($result['skipped'])]);
        }

        return redirect()->route('attendance.index')->with('success', $message);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
