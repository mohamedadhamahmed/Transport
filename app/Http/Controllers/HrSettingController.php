<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\HrHoliday;
use App\Models\HrSetting;
use Illuminate\Http\Request;

/**
 * إعدادات الموارد البشرية (لكل فرع) + إدارة الإجازات الرسمية - نفس
 * فكرة SettingsController@index بالظبط (فرع محدد + firstOrNew) بس
 * لإعدادات الحضور والانصراف بدل بيانات المنشأة/الزكاة.
 */
class HrSettingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('hr_settings.manage');

        $branches = Branch::orderBy('name')->get();
        $selectedBranchId = $request->get('branch_id', $branches->first()?->id);

        $hrSetting = HrSetting::forBranch($selectedBranchId);
        $holidays = HrHoliday::orderBy('date', 'desc')->get();

        return view('hr-settings.index', compact('branches', 'selectedBranchId', 'hrSetting', 'holidays'));
    }

    public function update(Request $request)
    {
        $this->authorize('hr_settings.manage');

        $validated = $request->validate([
            'branchs_id' => ['required', 'exists:branches,id'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
            'late_grace_minutes' => ['required', 'integer', 'min:0'],
            'overtime_multiplier' => ['required', 'numeric', 'min:0'],
            'weekly_off_days' => ['nullable', 'array'],
            'weekly_off_days.*' => ['string', 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'absence_deduction_multiplier' => ['required', 'numeric', 'min:0'],
            'extend_deduction_to_weekly_off' => ['nullable', 'boolean'],
        ]);

        HrSetting::updateOrCreate(
            ['branchs_id' => $validated['branchs_id']],
            [
                'work_start_time' => $validated['work_start_time'],
                'work_end_time' => $validated['work_end_time'],
                'late_grace_minutes' => $validated['late_grace_minutes'],
                'overtime_multiplier' => $validated['overtime_multiplier'],
                'weekly_off_days' => $validated['weekly_off_days'] ?? [],
                'absence_deduction_multiplier' => $validated['absence_deduction_multiplier'],
                'extend_deduction_to_weekly_off' => $request->boolean('extend_deduction_to_weekly_off'),
            ]
        );

        return back()->with('success', __('hr_settings.updated_success'));
    }

    public function storeHoliday(Request $request)
    {
        $this->authorize('hr_settings.manage');

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'branchs_id' => ['nullable', 'exists:branches,id'],
        ]);

        HrHoliday::create($validated);

        return back()->with('success', __('hr_settings.holiday_added_success'));
    }

    public function destroyHoliday(HrHoliday $holiday)
    {
        $this->authorize('hr_settings.manage');

        $holiday->delete();

        return back()->with('success', __('hr_settings.holiday_deleted_success'));
    }
}
