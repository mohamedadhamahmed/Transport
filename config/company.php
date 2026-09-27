<?php

/*
|--------------------------------------------------------------------------
| بيانات المنشأة الافتراضية (بيستخدمها CompanySettingsSeeder بس)
|--------------------------------------------------------------------------
| اكتب بيانات شركتك في ملف .env على السيرفر، وبعدين:
|   php artisan config:clear
|   php artisan db:seed --class=CompanySettingsSeeder --force
|
| أي قيمة سايبها فاضية في .env مش بتمسح القيمة الموجودة في قاعدة البيانات.
*/

return [
    // ===== بيانات العرض (هيدر الفواتير / الـ QR) → جدول system_settings =====
    'name_ar' => env('COMPANY_NAME_AR'),
    'name_en' => env('COMPANY_NAME_EN'),
    'cr_number' => env('COMPANY_CR'),            // السجل التجاري
    'vat_number' => env('COMPANY_VAT'),          // الرقم الضريبي (15 رقم)
    'currency' => env('COMPANY_CURRENCY', 'SAR'),
    'logo' => env('COMPANY_LOGO'),
    'address_ar' => env('COMPANY_ADDRESS_AR'),
    'address_en' => env('COMPANY_ADDRESS_EN'),
    'description_ar' => env('COMPANY_DESCRIPTION_AR'),
    'description_en' => env('COMPANY_DESCRIPTION_EN'),
    'bank_name' => env('COMPANY_BANK_NAME'),
    'bank_iban' => env('COMPANY_BANK_IBAN'),
    'bank_account' => env('COMPANY_BANK_ACCOUNT'),

    // ===== العنوان الوطني وبيانات الربط مع الزكاة → جدول settings =====
    'mobile' => env('COMPANY_MOBILE'),
    'email' => env('COMPANY_EMAIL'),
    'street_name' => env('COMPANY_STREET'),
    'building_number' => env('COMPANY_BUILDING_NO'),
    'plot_identification' => env('COMPANY_PLOT_NO'),   // الرقم الإضافي
    'district' => env('COMPANY_DISTRICT'),              // الحي
    'city' => env('COMPANY_CITY'),
    'postal_code' => env('COMPANY_POSTAL_CODE'),
    'egs_serial_number' => env('ZATCA_EGS_SERIAL'),     // مثال: 1-Transport|2-ERP|3-001
    'invoice_type' => env('ZATCA_INVOICE_TYPE', '1100'), // 1100 ضريبية+مبسطة، 1000 ضريبية بس، 0100 مبسطة بس
];
