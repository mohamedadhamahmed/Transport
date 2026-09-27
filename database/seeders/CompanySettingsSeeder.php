<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

/**
 * إعدادات المنشأة (بديل آمن لـ SettingsSeeder القديم).
 *
 * بيملى جدولين لكل فرع:
 *  - system_settings: اسم الشركة/السجل/الرقم الضريبي/العنوان/البنك (هيدر الفواتير والـ QR)
 *  - settings: العنوان الوطني وبيانات الزكاة الأساسية (من غير أي شهادات أو مفاتيح)
 *
 * القيم بتيجي من config/company.php (يعني من .env). الـ seeder آمن يتشغل كذا مرة:
 *  - القيمة الفاضية في .env مش بتمسح الموجود.
 *  - مش بيلمس أبدًا شهادات/مفاتيح الزكاة ولا invoices_count ولا previous_hash_invoice
 *    ولا is_production، عشان سلسلة الهاش مع الزكاة ماتتكسرش.
 *
 * التشغيل:
 *   php artisan config:clear
 *   php artisan db:seed --class=CompanySettingsSeeder --force
 */
class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('company', []);
        $v = fn (string $k) => ($x = trim((string) ($cfg[$k] ?? ''))) === '' ? null : $x;
        $digits = fn (?string $s) => $s === null ? null : (preg_replace('/\D+/', '', $s) ?: null);

        $branches = Branch::query()->orderBy('id')->get();
        if ($branches->isEmpty()) {
            $this->call(BranchSeeder::class);
            $branches = Branch::query()->orderBy('id')->get();
        }

        foreach ($branches as $branch) {
            $this->seedSystemSetting($branch, $v);
            $this->seedZatcaSetting($branch, $v, $digits);
        }

        if (!$v('name_ar') || !$v('vat_number')) {
            $this->command?->warn('⚠ COMPANY_NAME_AR أو COMPANY_VAT فاضيين في .env - اتحطت قيم مؤقتة، عدّلها من شاشة الإعدادات أو من .env وشغّل الـ seeder تاني.');
        }
        $this->command?->info('✓ تم تجهيز إعدادات المنشأة لـ ' . $branches->count() . ' فرع.');
    }

    private function seedSystemSetting(Branch $branch, callable $v): void
    {
        $row = SystemSetting::firstOrNew(['branchs_id' => $branch->id]);
        $isNew = !$row->exists;

        $values = [
            'name_ar' => $v('name_ar'),
            'name_en' => $v('name_en'),
            'currency' => $v('currency'),
            'SR' => $v('cr_number'),
            'Tax' => $v('vat_number'),
            'logo' => $v('logo'),
            'address_ar' => $v('address_ar'),
            'address_en' => $v('address_en'),
            'descriptionarbic' => $v('description_ar'),
            'descriptionenglish' => $v('description_en'),
            'bankname' => $v('bank_name'),
            'bank_acount_iban' => $v('bank_iban'),
            'bank_acount_number' => $v('bank_account'),
        ];

        // صف جديد ومفيش قيمة في .env → قيم مؤقتة واضحة بدل 'empty'
        $placeholders = [
            'name_ar' => 'اسم المنشأة',
            'name_en' => 'Company Name',
            'currency' => 'SAR',
            'SR' => '0000000000',
            'Tax' => '300000000000003',
            'address_ar' => 'المملكة العربية السعودية',
            'address_en' => 'Kingdom of Saudi Arabia',
            'descriptionarbic' => 'خدمات النقل',
            'descriptionenglish' => 'Transportation Services',
        ];

        foreach ($values as $col => $val) {
            if ($val !== null) {
                $row->{$col} = $val;
            } elseif ($isNew && isset($placeholders[$col])) {
                $row->{$col} = $placeholders[$col];
            }
        }
        $row->branchs_id = $branch->id;
        $row->save();

        $this->command?->line(($isNew ? '  + ' : '  ~ ') . "system_settings [{$branch->name}]: {$row->name_ar}");
    }

    private function seedZatcaSetting(Branch $branch, callable $v, callable $digits): void
    {
        $row = Setting::firstOrNew(['branchs_id' => $branch->id]);
        $isNew = !$row->exists;

        $sys = SystemSetting::where('branchs_id', $branch->id)->first();
        $name = $v('name_ar') ?? $sys?->name_ar;
        $invoiceType = in_array($v('invoice_type'), ['1100', '1000', '0100'], true) ? $v('invoice_type') : null;

        $values = [
            'name' => $name,
            'common_name' => $name,
            'organization_name' => $name,
            'organization_unit_name' => null,
            'mobile' => $v('mobile'),
            'email_address' => $v('email'),
            'trn' => $digits($v('vat_number') ?? ($isNew ? $sys?->Tax : null)),
            'crn' => $digits($v('cr_number') ?? ($isNew ? $sys?->SR : null)),
            'street_name' => $v('street_name'),
            'registered_address' => $v('street_name'),
            'building_number' => $digits($v('building_number')),
            'plot_identification' => $digits($v('plot_identification')),
            'region' => $v('district'),
            'city' => $v('city'),
            'postal_number' => $digits($v('postal_code')),
            'egs_serial_number' => $v('egs_serial_number'),
            'invoice_type' => $invoiceType,
        ];

        $placeholders = [
            'name' => 'اسم المنشأة',
            'common_name' => 'اسم المنشأة',
            'organization_name' => 'اسم المنشأة',
            'organization_unit_name' => $branch->name,
            'mobile' => '966500000000',
            'email_address' => 'info@example.com',
            'trn' => '300000000000003',
            'crn' => '0000000000',
            'street_name' => '-',
            'registered_address' => '-',
            'building_number' => 0,
            'plot_identification' => 0,
            'region' => '-',
            'city' => 'الرياض',
            'postal_number' => 0,
            'egs_serial_number' => '1-Transport|2-ERP|3-' . str_pad((string) $branch->id, 3, '0', STR_PAD_LEFT),
            'invoice_type' => '1100',
        ];

        foreach ($values as $col => $val) {
            if ($val !== null && $val !== '') {
                $row->{$col} = $val;
            } elseif ($isNew) {
                $row->{$col} = $placeholders[$col];
            }
        }

        if ($isNew) {
            // قيم أول مرة بس - بعد كده بيديرها ربط الزكاة (Onboarding) مش الـ seeder
            $row->forceFill([
                'business_category' => 'IT',
                'country_name' => 'SA',
                'otp' => '',
                'is_production' => false,
                'company_id' => 1,
                'invoices_count' => 1,
                'previous_hash_invoice' => null,
            ]);
        }
        $row->branchs_id = $branch->id;
        $row->save();

        $linked = !empty($row->production_certificate) ? ' (مربوط بالزكاة - الشهادات ماتلمستش)' : '';
        $this->command?->line(($isNew ? '  + ' : '  ~ ') . "settings [{$branch->name}]: {$row->name}{$linked}");
    }
}
