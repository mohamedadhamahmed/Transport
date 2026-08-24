<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EmployeeDiscountSetting;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeDiscountSettingController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $selectedBranchId = $request->get('branch_id', $branches->first()?->id);

        $users = User::where('branch_id', $selectedBranchId)->orderBy('name')->get();

        $overrides = EmployeeDiscountSetting::where('branchs_id', $selectedBranchId)
            ->pluck('max_discount', 'user_id');

        $branchDefault = SystemSetting::where('branchs_id', $selectedBranchId)
            ->value('max_employee_discount') ?? 0;

        $requiresApprovalAbove = SystemSetting::where('branchs_id', $selectedBranchId)
            ->value('requires_approval_above');

        return view('settings.employee-discounts.index', compact(
            'branches',
            'selectedBranchId',
            'users',
            'overrides',
            'branchDefault',
            'requiresApprovalAbove'
        ));
    }

    public function updateBranchDefault(Request $request)
    {
        $validated = $request->validate([
            'branchs_id'               => 'required|exists:branches,id',
            'max_employee_discount'    => 'required|numeric|min:0|max:100',
            'requires_approval_above'  => 'nullable|numeric|min:0|max:100',
        ]);

        SystemSetting::where('branchs_id', $validated['branchs_id'])->update([
            'max_employee_discount'   => $validated['max_employee_discount'],
            'requires_approval_above' => $validated['requires_approval_above'],
        ]);

        return back()->with('success', __('settings.branch_default_updated'));
    }

    public function updateUserOverride(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'branchs_id'  => 'required|exists:branches,id',
            'max_discount'=> 'nullable|numeric|min:0|max:100',
        ]);

        // لو القيمة فاضية، يعني عايز يمسح التخصيص ويرجع للـ default بتاع الفرع
        if ($validated['max_discount'] === null) {
            EmployeeDiscountSetting::where('user_id', $validated['user_id'])
                ->where('branchs_id', $validated['branchs_id'])
                ->delete();

            return back()->with('success', __('settings.user_override_removed'));
        }

        EmployeeDiscountSetting::updateOrCreate(
            ['user_id' => $validated['user_id'], 'branchs_id' => $validated['branchs_id']],
            ['max_discount' => $validated['max_discount']]
        );

        return back()->with('success', __('settings.user_override_updated'));
    }
}