<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranchId = $request->get('branch_id', $branches->first()?->id);

        $systemSetting = SystemSetting::firstOrNew(['branchs_id' => $selectedBranchId]);
        $zakatSetting  = Setting::firstOrNew(['branchs_id' => $selectedBranchId]);

        return view('settings.index', compact(
            'branches', 'selectedBranchId', 'systemSetting', 'zakatSetting'
        ));
    }

    public function updateSystemSettings(Request $request)
    {$validated = $request->validate([
    'branchs_id'         => 'required|exists:branches,id',
    'name_ar'            => 'required|string|max:255',
    'name_en'            => 'required|string|max:255',
    'SR'                 => 'required|string|max:255',
    'Tax'                => 'required|string|max:255',
    'address_ar'         => 'required|string|max:255',
    'address_en'         => 'required|string|max:255',
    'serviceCost'        => 'nullable|numeric|min:0',
    'deliveryCost'       => 'nullable|numeric|min:0',
    'bank_acount_iban'   => 'nullable|string',
    'bank_acount_number' => 'nullable|string',
    'bankname'           => 'nullable|string',
    'logo'               => 'nullable|image|max:2048',
]);

// جلب الإعدادات الحالية للفرع للتأكد من اللوجو القديم
$systemSetting = SystemSetting::firstOrNew(['branchs_id' => $validated['branchs_id']]);

if ($request->hasFile('logo')) {
    // رفع الصورة الجديدة وتخزينها في مجلد public/assets/img/brand أو storage حسب رغبتك
    $logoName = time() . '.' . $request->file('logo')->extension();
    $request->file('logo')->move(public_path('assets/img/brand'), $logoName);
    $validated['logo'] = $logoName;
} else {
    // الاحتفاظ باللوجو القديم إذا لم يتم رفع جديد
    unset($validated['logo']);
}

$systemSetting->fill($validated);
$systemSetting->save();

return back()->with('success', __('settings.company_info_updated'));

}

    public function updateZakatSettings(Request $request)
    {
        $validated = $request->validate([
            'branchs_id'    => 'required|exists:branches,id',
            'name'          => 'required|string|max:255',
            'mobile'        => 'required|string|max:255',
            'trn'           => 'required|numeric',
            'crn'           => 'required|numeric',
            'street_name'   => 'required|string|max:255',
            'building_number'      => 'required|integer',
            'plot_identification'  => 'required|integer',
            'region'        => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'postal_number' => 'required|integer',
            'business_category' => 'required|in:IT,Food,Film Festivals',
            'invoice_type'  => 'required|in:1100,0100,1000',
            'email_address' => 'required|email',
            'is_production' => 'boolean',
            'company_id'    => 'required|integer',
        ]);

        Setting::updateOrCreate(
            ['branchs_id' => $validated['branchs_id']],
            $validated
        );

        return back()->with('success', __('settings.zakat_settings_updated'));
    }
}