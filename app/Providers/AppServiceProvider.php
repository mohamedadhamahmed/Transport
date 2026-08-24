<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        $setting = Setting::find(1);
        $systemSetting = SystemSetting::find(1);

        if ($setting && $systemSetting) {
            define('postal_number', $setting->postal_number);
            define('street_name', $setting->street_name);
            define('building_number', $setting->building_number);
            define('plot_identification', $setting->plot_identification);
            define('region', $setting->region);
            define('city', $setting->city);
            define('PAGINATION_COUNT', 20);

            define('serviceCost', $systemSetting->serviceCost);
            define('bank_acount_iban', $systemSetting->bank_acount_iban);
            define('bank_acount_number', $systemSetting->bank_acount_number);
            define('bankname', $systemSetting->bankname);
            define('Namear', $systemSetting->name_ar);
            define('describtionar', $systemSetting->descriptionarbic);
            define('STar', ' س . ت  :' . $systemSetting->SR);
            define('Taxar', '  الرقم الضريبي : ' . $systemSetting->Tax);
            define('TaxQrCode', $systemSetting->Tax);
            define('sallerQrCode', $systemSetting->name_ar);
            define('Nameen', $systemSetting->name_en);
            define('describtionen', $systemSetting->descriptionenglish);
            define('STen', '  C.R : ' . $systemSetting->SR);
            define('Taxen', 'VAT Number : ' . $systemSetting->Tax);
            define('addressar', $systemSetting->address_ar);
            define('addressen', $systemSetting->address_en);
            define('camplogo', $systemSetting->logo);
        }
    }
}