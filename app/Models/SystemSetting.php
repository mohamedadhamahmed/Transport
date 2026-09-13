<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'name_ar', 'name_en', 'currency', 'SR', 'Tax', 'logo',
        'address_ar', 'address_en',
        'serviceCost', 'deliveryCost',
        'descriptionarbic', 'descriptionenglish',
        'discount_on_invoice', 'max_employee_discount', 'requires_approval_above',
        'bank_acount_iban', 'bank_acount_number', 'bankname',
        'branchs_id',
    ];

    /**
     * قائمة العملات المعتمدة في النظام
     */
    public static function getCurrencyList(): array
    {
        return [
            'SAR' => [
                'code' => 'SAR',
                'name_ar' => 'ريال سعودي',
                'symbol_ar' => 'ر.س',
                'name_en' => 'Saudi Riyal',
                'symbol_en' => 'SAR',
                'flag' => '🇸🇦',
            ],
            'EGP' => [
                'code' => 'EGP',
                'name_ar' => 'جنيه مصري',
                'symbol_ar' => 'ج.م',
                'name_en' => 'Egyptian Pound',
                'symbol_en' => 'EGP',
                'flag' => '🇪🇬',
            ],
            'AED' => [
                'code' => 'AED',
                'name_ar' => 'درهم إماراتي',
                'symbol_ar' => 'د.إ',
                'name_en' => 'UAE Dirham',
                'symbol_en' => 'AED',
                'flag' => '🇦🇪',
            ],
            'KWD' => [
                'code' => 'KWD',
                'name_ar' => 'دينار كويتي',
                'symbol_ar' => 'د.ك',
                'name_en' => 'Kuwaiti Dinar',
                'symbol_en' => 'KWD',
                'flag' => '🇰🇼',
            ],
            'OMR' => [
                'code' => 'OMR',
                'name_ar' => 'ريال عماني',
                'symbol_ar' => 'ر.ع',
                'name_en' => 'Omani Rial',
                'symbol_en' => 'OMR',
                'flag' => '🇴🇲',
            ],
            'QAR' => [
                'code' => 'QAR',
                'name_ar' => 'ريال قطري',
                'symbol_ar' => 'ر.ق',
                'name_en' => 'Qatari Riyal',
                'symbol_en' => 'QAR',
                'flag' => '🇶🇦',
            ],
        ];
    }

    /**
     * جلب كود العملة النشطة حالياً (مثلاً SAR أو EGP)
     */
    public static function getActiveCurrencyCode(?int $branchId = null): string
    {
        static $cached = [];
        $key = $branchId ?: 0;
        if (isset($cached[$key])) {
            return $cached[$key];
        }

        try {
            $setting = self::when($branchId, fn($q) => $q->where('branchs_id', $branchId))->first()
                ?? self::first();
            $code = $setting?->currency ?: 'SAR';
            $cached[$key] = $code;
            return $code;
        } catch (\Throwable $e) {
            return 'SAR';
        }
    }

    /**
     * جلب رمز العملة النشطة (مثلاً ر.س أو ج.م أو د.إ)
     */
    public static function getActiveCurrencySymbol(?int $branchId = null, ?string $locale = null): string
    {
        $code = self::getActiveCurrencyCode($branchId);
        $list = self::getCurrencyList();
        $curr = $list[$code] ?? $list['SAR'];
        $locale = $locale ?: (function_exists('app') && app()->getLocale() === 'en' ? 'en' : 'ar');
        return ($locale === 'en') ? $curr['symbol_en'] : $curr['symbol_ar'];
    }

    /**
     * جلب الاسم الكامل للعملة
     */
    public static function getActiveCurrencyName(?int $branchId = null, ?string $locale = null): string
    {
        $code = self::getActiveCurrencyCode($branchId);
        $list = self::getCurrencyList();
        $curr = $list[$code] ?? $list['SAR'];
        $locale = $locale ?: (function_exists('app') && app()->getLocale() === 'en' ? 'en' : 'ar');
        return ($locale === 'en') ? $curr['name_en'] : $curr['name_ar'];
    }
}