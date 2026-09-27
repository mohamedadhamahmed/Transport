<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\SystemSetting;

/**
 * بيانات المنشأة للطباعة والـ QR - بتتقري مباشرة من قاعدة البيانات
 * (system_settings ثم settings) بدل الثوابت اللي بتتعرّف في AppServiceProvider،
 * عشان الهيدر يظهر حتى لو الثوابت متعرّفتش (كاش/ترتيب تحميل/فرع مختلف).
 */
class CompanyInfo
{
    private static array $cache = [];

    public static function get(?int $branchId = null): array
    {
        $key = (int) $branchId;
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $sys = null;
        $zat = null;
        try {
            $sys = ($branchId ? SystemSetting::where('branchs_id', $branchId)->first() : null)
                ?? SystemSetting::query()->orderBy('id')->first();
            $zat = ($branchId ? Setting::where('branchs_id', $branchId)->first() : null)
                ?? Setting::query()->orderBy('id')->first();
        } catch (\Throwable $e) {
            // الجداول مش جاهزة
        }

        $clean = function ($v) {
            $v = trim((string) $v);
            return ($v === '' || strtolower($v) === 'empty' || $v === '-') ? null : $v;
        };

        $nameAr = $clean($sys?->name_ar) ?? $clean($zat?->name) ?? $clean($zat?->organization_name);
        $vat = $clean($sys?->Tax) ?? $clean($zat?->trn);
        $cr = $clean($sys?->SR) ?? $clean($zat?->crn);
        $addrAr = $clean($sys?->address_ar)
            ?? $clean(implode(' - ', array_filter([$zat?->city, $zat?->region, $zat?->street_name, $zat?->building_number ? 'مبنى ' . $zat->building_number : null, $zat?->postal_number ? 'ص.ب ' . $zat->postal_number : null])));

        $logo = $clean($sys?->logo);
        if ($logo && !is_file(public_path('assets/img/brand/' . $logo))) {
            $logo = null;
        }

        return self::$cache[$key] = [
            'configured' => (bool) ($nameAr && $vat),
            'name_ar' => $nameAr ?? config('app.name'),
            'name_en' => $clean($sys?->name_en),
            'desc_ar' => $clean($sys?->descriptionarbic),
            'desc_en' => $clean($sys?->descriptionenglish),
            'cr' => $cr,
            'vat' => $vat,
            'vat_digits' => preg_replace('/\D/', '', (string) $vat),
            'address_ar' => $addrAr,
            'address_en' => $clean($sys?->address_en),
            'logo_url' => $logo ? asset('assets/img/brand/' . $logo) : null,
            'bank' => $clean($sys?->bankname),
            'iban' => $clean($sys?->bank_acount_iban),
            'account' => $clean($sys?->bank_acount_number),
            'mobile' => $clean($zat?->mobile),
        ];
    }
}
