<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Services\Zatca\OnBoarding;
use Illuminate\Http\Request;
use Throwable;

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
            'common_name'            => 'required|string|max:255',
            'organization_unit_name' => 'required|string|max:255',
            'organization_name'      => 'required|string|max:255',
            'country_name'           => 'nullable|string|max:5',
            'registered_address'     => 'required|string|max:255',
            'egs_serial_number'      => 'required|string|max:255',
        ]);

        $validated['country_name'] = $validated['country_name'] ?? 'SA';

        // عمود otp في جدول settings مطلوب (NOT NULL) لكنه لا يُدخل من نموذج الإعدادات
        // العام، بل من شاشة الربط مع زكاة نفسها، لذلك نحافظ على قيمته الحالية إن وجدت
        // أو نخزن نص فارغ مبدئيًا حتى يتم إدخاله فعليًا أثناء الربط.
        $validated['otp'] = Setting::where('branchs_id', $validated['branchs_id'])->value('otp') ?? '';

        Setting::updateOrCreate(
            ['branchs_id' => $validated['branchs_id']],
            $validated
        );

        return back()->with('success', __('settings.zakat_settings_updated'));
    }

    /**
     * عرض شاشة الربط مع منظومة زكاة والفوترة الإلكترونية (ZATCA Onboarding)
     */
    public function onboarding(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranchId = $request->get('branch_id', $branches->first()?->id);

        $zakatSetting = Setting::firstOrNew(['branchs_id' => $selectedBranchId]);

        return view('settings.onboarding', compact('branches', 'selectedBranchId', 'zakatSetting'));
    }

    /**
     * تنفيذ عملية الربط مع زكاة (توليد الشهادة والاعتماد الإنتاجي)
     */
    public function storeOnboarding(Request $request)
    {
        $validated = $request->validate([
            'branchs_id'    => 'required|exists:branches,id',
            'invoice_type'  => 'required|in:1100,0100,1000',
            'is_production' => 'nullable|boolean',
            'otp'           => 'required|string|max:255',
        ]);

        $zakatSetting = Setting::where('branchs_id', $validated['branchs_id'])->first();

        if (!$zakatSetting) {
            return back()->with('error', __('settings.onboarding_missing_zakat_info'));
        }

        $requiredFields = [
            'common_name', 'organization_unit_name', 'organization_name',
            'country_name', 'registered_address', 'egs_serial_number',
            'email_address', 'trn', 'business_category',
        ];

        foreach ($requiredFields as $field) {
            if (empty($zakatSetting->$field)) {
                return back()->with('error', __('settings.onboarding_missing_zakat_info'));
            }
        }

        $zakatSetting->invoice_type  = $validated['invoice_type'];
        $zakatSetting->is_production = $request->boolean('is_production');
        $zakatSetting->otp           = $validated['otp'];
        $zakatSetting->save();

        $env = $zakatSetting->is_production ? 'core' : 'simulation';

        try {
            $result = (new OnBoarding())
                ->setZatcaEnv($env)
                ->setZatcaLang(app()->getLocale() === 'ar' ? 'ar' : 'en')
                ->setAuthOtp($zakatSetting->otp)
                ->setEmailAddress($zakatSetting->email_address)
                ->setCommonName($zakatSetting->common_name)
                ->setCountryCode($zakatSetting->country_name)
                ->setOrganizationUnitName($zakatSetting->organization_unit_name)
                ->setOrganizationName($zakatSetting->organization_name)
                ->setEgsSerialNumber($zakatSetting->egs_serial_number)
                ->setVatNumber((string) $zakatSetting->trn)
                ->setInvoiceType($zakatSetting->invoice_type)
                ->setRegisteredAddress($zakatSetting->registered_address)
                ->setBusinessCategory($zakatSetting->business_category)
                ->getAuthorization();
        } catch (Throwable $e) {
            return back()->with('error', $this->onboardingFriendlyMessage($e->getMessage()));
        }

        if (!($result['success'] ?? false)) {
            $rawMessage = $result['message'] ?? null;
            $rawMessage = is_string($rawMessage) ? $rawMessage : null;

            return back()->with('error', $rawMessage !== null
                ? $this->onboardingFriendlyMessage($rawMessage)
                : __('settings.onboarding_failed'));
        }

        $data = $result['data'] ?? [];

        $zakatSetting->cnf                     = $data['configData'] ?? null;
        $zakatSetting->private_key             = $data['privateKey'] ?? null;
        $zakatSetting->public_key              = $data['publicKey'] ?? null;
        $zakatSetting->csr_request             = $data['csrKey'] ?? null;
        $zakatSetting->certificate             = $data['complianceCertificate'] ?? null;
        $zakatSetting->secret                  = $data['complianceSecret'] ?? null;
        $zakatSetting->csid                    = $data['complianceRequestID'] ?? null;
        $zakatSetting->production_certificate  = $data['productionCertificate'] ?? null;
        $zakatSetting->production_secret       = $data['productionCertificateSecret'] ?? null;
        $zakatSetting->production_csid         = $data['productionCertificateRequestID'] ?? null;
        $zakatSetting->save();

        return redirect()
            ->route('settings.onboarding', ['branch_id' => $zakatSetting->branchs_id])
            ->with('success', __('settings.onboarding_success'));
    }

    /**
     * منظومة الزكاة بترجع رسائلها بالإنجليزي دايمًا (زي "The provided OTP is
     * invalid") - الدالة دي بتترجم الرسائل المعروفة والمتكررة لنص عربي واضح
     * ومحدد، وبترجع أي رسالة تانية غير معروفة كما هي لكن بعد ترجمة الحقول
     * الشائعة ("... is required") المتعلقة ببيانات الزكاة الناقصة في الإعدادات.
     */
    private function onboardingFriendlyMessage(string $rawMessage): string
    {
        $fieldTranslations = [
            'Common Name' => 'الاسم الشائع (Common Name)',
            'Country Code' => 'رمز الدولة',
            'Organization Unit Name' => 'اسم الوحدة التنظيمية',
            'Organization Name' => 'اسم المنشأة (Organization Name)',
            'Egs Serial Number' => 'الرقم التسلسلي للجهاز (EGS Serial Number)',
            'Vat Number' => 'الرقم الضريبي',
            'Invoice Type' => 'نوع الفاتورة',
            'Registered Address' => 'العنوان المسجل',
            'Business Category' => 'نشاط المنشأة',
            'Email Address' => 'البريد الإلكتروني',
            'Zatca Otp' => 'رمز التحقق (OTP)',
            'Zatca environment' => 'بيئة الزاتكا (تجربة/إنتاج)',
        ];

        if (str_contains($rawMessage, 'The provided OTP is invalid') || str_contains($rawMessage, 'Invalid-OTP')) {
            return 'رمز التحقق (OTP) الذي أدخلته غير صحيح. تأكد من استخدام آخر رمز حصلت عليه من بوابة فاتورة (Fatoora) قبل انتهاء صلاحيته، ثم أعد المحاولة.';
        }

        if (str_contains($rawMessage, 'Missing-ComplianceSteps') || str_contains($rawMessage, 'compliance')) {
            return 'لم تكتمل خطوات التوافق مع منظومة الفوترة الإلكترونية بشكل صحيح. أعد محاولة الربط من جديد.';
        }

        if (str_contains($rawMessage, 'Zatca Basic Auth is required')) {
            return 'بيانات الاعتماد (الشهادة أو الرمز السري) الخاصة بالزاتكا غير مكتملة.';
        }

        foreach ($fieldTranslations as $english => $arabic) {
            if ($rawMessage === $english . ' is required') {
                return "الحقل \"{$arabic}\" مطلوب - تأكد من تعبئته في بيانات الزكاة بصفحة الإعدادات.";
            }
        }

        return 'حدث خطأ أثناء الربط مع منظومة الزكاة والفوترة الإلكترونية (تفاصيل تقنية: ' . $rawMessage . ')';
    }
}