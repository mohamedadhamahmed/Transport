<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\SystemSetting;
use App\Support\PermissionRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
        //
    }

    public function boot(): void
    {
        view()->composer('*', function ($view) {
            try {
                $currCode = \App\Models\SystemSetting::getActiveCurrencyCode();
                $currSymbol = \App\Models\SystemSetting::getActiveCurrencySymbol();
                $currName = \App\Models\SystemSetting::getActiveCurrencyName();
                $currList = \App\Models\SystemSetting::getCurrencyList();

                $view->with([
                    'currencyCode' => $currCode,
                    'currencySymbol' => $currSymbol,
                    'currencyName' => $currName,
                    'currencyList' => $currList,
                ]);
            } catch (\Throwable $e) {}
        });

        Paginator::useBootstrap();

        Gate::before(function ($user, string $ability) {
            if (! $user) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            if (! PermissionRegistry::exists($ability)) {
                return null;
            }

            return $user->hasPermission($ability);
        });

        $setting = null;
        $systemSetting = null;

        // بيانات الشركة (هيدر الفواتير/الـ QR) من system_settings، وبيانات الزكاة
        // والعنوان الوطني من settings - كل واحدة لوحدها. قبل كده الثوابت كلها
        // كانت بتتعرّف بس لو الجدولين فيهم بيانات مع بعض، فعلى أي قاعدة جديدة
        // من غير إعدادات زكاة كان هيدر الفاتورة بيختفي والـ QR يطلع غلط.
        try {
            if (Schema::hasTable('settings')) {
                $setting = Setting::find(1) ?? Setting::query()->orderBy('id')->first();
            }
            if (Schema::hasTable('system_settings')) {
                $systemSetting = SystemSetting::find(1) ?? SystemSetting::query()->orderBy('id')->first();
            }
        } catch (\Throwable $e) {
            // قاعدة البيانات مش جاهزة (أثناء التثبيت/الـ migrate)
        }

        if (!defined('PAGINATION_COUNT')) {
            define('PAGINATION_COUNT', 20);
        }

        if ($setting) {
            define('postal_number', $setting->postal_number);
            define('street_name', $setting->street_name);
            define('building_number', $setting->building_number);
            define('plot_identification', $setting->plot_identification);
            define('region', $setting->region);
            define('city', $setting->city);
        }

        if ($systemSetting) {
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
        } elseif ($setting) {
            // مفيش إعدادات نظام: نستخدم بيانات الزكاة على الأقل في الهيدر والـ QR
            define('Namear', $setting->name);
            define('STar', ' س . ت  :' . $setting->crn);
            define('Taxar', '  الرقم الضريبي : ' . $setting->trn);
            define('TaxQrCode', (string) $setting->trn);
            define('sallerQrCode', $setting->name);
            define('addressar', trim($setting->city . ' - ' . $setting->region . ' - ' . $setting->street_name, ' -'));
        }
    }
}