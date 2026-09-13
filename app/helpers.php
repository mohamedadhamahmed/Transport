<?php

use App\Models\SystemSetting;

if (!function_exists('currency_symbol')) {
    /**
     * رمز العملة الحالية (ر.س، ج.م، د.إ، د.ك، ر.ع، ر.ق)
     */
    function currency_symbol(?string $code = null, ?string $locale = null): string
    {
        if ($code) {
            $list = SystemSetting::getCurrencyList();
            $curr = $list[$code] ?? $list['SAR'];
            $locale = $locale ?: app()->getLocale();
            return ($locale === 'en') ? $curr['symbol_en'] : $curr['symbol_ar'];
        }
        return SystemSetting::getActiveCurrencySymbol(null, $locale);
    }
}

if (!function_exists('currency_name')) {
    /**
     * اسم العملة الكامل (ريال سعودي، جنيه مصري، درهم إماراتي...)
     */
    function currency_name(?string $code = null, ?string $locale = null): string
    {
        if ($code) {
            $list = SystemSetting::getCurrencyList();
            $curr = $list[$code] ?? $list['SAR'];
            $locale = $locale ?: app()->getLocale();
            return ($locale === 'en') ? $curr['name_en'] : $curr['name_ar'];
        }
        return SystemSetting::getActiveCurrencyName(null, $locale);
    }
}

if (!function_exists('currency_code')) {
    /**
     * كود العملة الثلاثي (SAR, EGP, AED, KWD, OMR, QAR)
     */
    function currency_code(?int $branchId = null): string
    {
        return SystemSetting::getActiveCurrencyCode($branchId);
    }
}