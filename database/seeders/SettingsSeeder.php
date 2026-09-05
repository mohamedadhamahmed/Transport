<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

/**
 * نفس بيانات فرع "الرياض" الحقيقية المستخرجة من نسخة قاعدة بيانات قديمة
 * (جداول system_settings و settings) - بما في ذلك شهادات ومفاتيح الزكاة
 * الحقيقية اللي كانت اتولدت فعلاً من قبل (onboarding قديم ناجح)، عشان
 * نسترجعها بدل ما نعمل onboarding تاني من الصفر على نفس المنشأة.
 *
 * كل القيم هنا اتولدت آليًا من ملفي CSV الحقيقيين اللي بعتهملي (تصدير
 * phpMyAdmin لجدولي settings و system_settings) - نسخ مباشر بالسكريبت
 * من غير أي كتابة يدوية، عشان القيم الطويلة (الشهادة والمفتاح الخاص)
 * تتنسخ بالظبط زي ما هي من غير أي خطأ حرف.
 *
 * ملحوظات مهمة:
 *  - الجدول المصدر فيه أعمدة قديمة/زيادة مش موجودة في سكيمة settings
 *    الحالية (scander_number, TOKEN, sendbox, token_sendbox, production)
 *    - اتجاهلناها هنا لإنها مش موجودة أصلاً في جدولنا. عمود "production"
 *    القديم ده هو نفسه "is_production" الحالي فعليًا فاستخدمنا قيمته.
 *  - business_category في المصدر كانت نص حر "للبرامج المحاسبية"،
 *    لكن عمود business_category عندنا محدود بـ enum('IT','Food','Film
 *    Festivals')، فاخترنا 'IT' كأقرب قيمة ممكنة (نشاط برمجيات/محاسبة).
 *  - otp قيمة مؤقتة صالحة لمرة واحدة بس ولوقت قصير جدًا من بوابة فاتورة -
 *    مفيش داعي نخزّن القيمة القديمة (منتهية أكيد)، فسيبناها فاضية.
 *  - cnf/private_key/public_key/csr_request/certificate/secret/csid/
 *    production_certificate/production_secret/production_csid مقصودة
 *    بره $fillable في موديل Setting (بتتولد من شاشة Onboarding مش من
 *    تعديل يدوي)، فبنحطها هنا بـ forceFill() بعد الـ updateOrCreate.
 *  - invoices_count و previous_hash_invoice هنا هما آخر رقم/هاش فعليين
 *    استخدمتهم نفس هذه المنشأة مع الزكاة فعلاً - لازم يفضلوا زي ما هما
 *    عشان سلسلة الهاش (PIH) تكمل صح من نفس النقطة ومتتكسرش.
 *  - تنبيه أمان: الملف ده هيحتوي على شهادة ومفتاح خاص ورموز سرية حقيقية
 *    لمنشأتك مع الزكاة - تعامل معاه زي أي ملف فيه بيانات اعتماد حساسة
 *    (متشاركوش في مكان عام).
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('name', 'الرياض')->first();

        if (! $branch) {
            $this->command?->warn('فرع "الرياض" مش موجود - شغّل BranchSeeder الأول.');
            return;
        }

        SystemSetting::updateOrCreate(
            ['branchs_id' => $branch->id],
            [
                'name_ar' => 'مؤسسة دقة الابداع لتقنية نظم المعلومات',
                'name_en' => 'Dakka Al-Ebdah EST for Information Systems Technology',
                'SR' => '1010732127',
                'Tax' => '302167037800003',
                'logo' => '1747054597656.png',
                'address_ar' => 'المملكة العربية السعودية - الرياض - حي الفيصلية - الملك عبدالله - مبنى رقم 4402 - تحويلة رقم 7841 - الرمز البريدي 12874',
                'address_en' => 'Kingdom of Saudi Arabia - Riyadh - Al Faisaliah District - King Abdullah - Building No. 4402 - Extension No. 7841 - Postal Code 12874',
                'serviceCost' => 10.00,
                'deliveryCost' => 15.00,
                'descriptionarbic' => 'للبرامج المحاسبية',
                'descriptionenglish' => 'for accounting software',
                'discount_on_invoice' => 100,
                'bank_acount_iban' => '-',
                'bank_acount_number' => '32563289565544',
                'bankname' => 'مصرف الراجحي',
            ]
        );

        $setting = Setting::updateOrCreate(
            ['branchs_id' => $branch->id],
            [
                'name' => 'مؤسسة دقة الابداع لتقنية نظم المعلومات',
                'mobile' => '966500266200',
                'trn' => '302167037800003',
                'crn' => '1010732127',
                'street_name' => 'الملك عبدالله',
                'building_number' => 3854,
                'plot_identification' => 6873,
                'region' => 'حي الفيصلية',
                'city' => 'الرياض',
                'postal_number' => 16285,
                'egs_serial_number' => '1-ABD|2-ABC|3-ABC',
                'business_category' => 'IT',
                'common_name' => 'مؤسسة دقة الابداع لتقنية نظم المعلومات',
                'organization_unit_name' => 'مؤسسة دقة الابداع لتقنية نظم المعلومات',
                'organization_name' => 'مؤسسة دقة الابداع لتقنية نظم المعلومات',
                'country_name' => 'SA',
                'registered_address' => 'الملك عبدالله',
                'email_address' => 'ebdeasoft@gmail.com',
                'invoice_type' => '1100',
                'is_production' => true,
                'company_id' => 1,
                'invoices_count' => 82,
                'previous_hash_invoice' => 'VmNJmV54LXj+zigC22V3RV2tDD08QcMGb5aCrgfURqw=',
                // otp مقصود سيبناها فاضية - القيمة القديمة منتهية الصلاحية أكيد.
                'otp' => '',
            ]
        );

        // الحقول دي بره $fillable في الموديل عمدًا (راجع تعليق الموديل) -
        // بنحطها مباشرة بعد إنشاء/تحديث الصف.
        $setting->forceFill([
            'cnf' => 'DQogICAgICAgICAgICBvaWRfc2VjdGlvbiA9IE9JRHMNCiAgICAgICAgICAgIFsgT0lEcyBdDQogICAgICAgICAgICBjZXJ0aWZpY2F0ZVRlbXBsYXRlTmFtZT0gMS4zLjYuMS40LjEuMzExLjIwLjINCg0KICAgICAgICAgICAgWyByZXEgXQ0KICAgICAgICAgICAgZGVmYXVsdF9iaXRzIAk9IDIwNDgNCiAgICAgICAgICAgIGVtYWlsQWRkcmVzcyAJPSBlYmRlYXNvZnRAZ21haWwuY29tDQogICAgICAgICAgICByZXFfZXh0ZW5zaW9ucwk9IHYzX3JlcQ0KICAgICAgICAgICAgeDUwOV9leHRlbnNpb25zIAk9IHYzX2NhDQogICAgICAgICAgICBwcm9tcHQgPSBubw0KICAgICAgICAgICAgZGVmYXVsdF9tZCA9IHNoYTI1Ng0KICAgICAgICAgICAgcmVxX2V4dGVuc2lvbnMgPSByZXFfZXh0DQogICAgICAgICAgICBkaXN0aW5ndWlzaGVkX25hbWUgPSBkbg0KDQogICAgICAgICAgICBbIHYzX3JlcSBdDQogICAgICAgICAgICBiYXNpY0NvbnN0cmFpbnRzID0gQ0E6RkFMU0UNCiAgICAgICAgICAgIGtleVVzYWdlID0gZGlnaXRhbFNpZ25hdHVyZSwgbm9uUmVwdWRpYXRpb24sIGtleUVuY2lwaGVybWVudA0KDQogICAgICAgICAgICBbcmVxX2V4dF0NCiAgICAgICAgICAgIGNlcnRpZmljYXRlVGVtcGxhdGVOYW1lID0gQVNOMTpQUklOVEFCTEVTVFJJTkc6UFJFWkFUQ0EtQ29kZS1TaWduaW5nDQogICAgICAgICAgICBzdWJqZWN0QWx0TmFtZSA9IGRpck5hbWU6YWx0X25hbWVzDQoNCiAgICAgICAgICAgIFsgdjNfY2EgXQ0KDQogICAgICAgICAgICAjIEV4dGVuc2lvbnMgZm9yIGEgdHlwaWNhbCBDQQ0KDQogICAgICAgICAgICAjIFBLSVggcmVjb21tZW5kYXRpb24uDQoNCiAgICAgICAgICAgIHN1YmplY3RLZXlJZGVudGlmaWVyID0gaGFzaA0KDQogICAgICAgICAgICBhdXRob3JpdHlLZXlJZGVudGlmaWVyID0ga2V5aWQ6YWx3YXlzLGlzc3VlcjphbHdheXMNCg0KICAgICAgICAgICAgWyBkbiBdDQogICAgICAgICAgICBDTiA9INmF2KTYs9iz2Kkg2K/ZgtipINin2YTYp9io2K/Yp9i5INmE2KrZgtmG2YrYqSDZhti42YUg2KfZhNmF2LnZhNmI2YXYp9iqICAJCQkJICAgICAgICAgICAgICAgICAgICAjIENvbW1vbiBOYW1lDQogICAgICAgICAgICBDID0gU0EJCQkJCQkJICAgICAgICAgICAgIyBDb3VudHJ5IENvZGUgZS5nIFNBDQogICAgICAgICAgICBPVSA9INmF2KTYs9iz2Kkg2K/ZgtipINin2YTYp9io2K/Yp9i5INmE2KrZgtmG2YrYqSDZhti42YUg2KfZhNmF2LnZhNmI2YXYp9iqCQkJCQkJCSMgT3JnYW5pemF0aW9uIFVuaXQgTmFtZQ0KICAgICAgICAgICAgTyA9INmF2KTYs9iz2Kkg2K/ZgtipINin2YTYp9io2K/Yp9i5INmE2KrZgtmG2YrYqSDZhti42YUg2KfZhNmF2LnZhNmI2YXYp9iqCQkJCQkJCSAgICAgICAgIyBPcmdhbml6YXRpb24gTmFtZQ0KDQogICAgICAgICAgICBbYWx0X25hbWVzXQ0KICAgICAgICAgICAgU04gPSAxLVNEU0F8Mi1GR0RTfDMtU0RGRwkJCQkgICAgICAgICAgICAgICAgICAgICMgRUdTIFNlcmlhbCBOdW1iZXIgMS1BQkN8Mi1QUVJ8My1YWVoNCiAgICAgICAgICAgIFVJRCA9IDMwMjE2NzAzNzgwMDAwMwkJCQkJCSAgICAgICAgICAgICAgICAjIE9yZ2FuaXphdGlvbiBJZGVudGlmaWVyIChWQVQgTnVtYmVyKQ0KICAgICAgICAgICAgdGl0bGUgPSAxMTAwCQkJCQkJCQkgICAgIyBJbnZvaWNlIFR5cGUNCiAgICAgICAgICAgIHJlZ2lzdGVyZWRBZGRyZXNzID0g2KfZhNmF2YTZgyDYudio2K/Yp9mE2YTZhyAgCSAJCQkjIEFkZHJlc3MNCiAgICAgICAgICAgIGJ1c2luZXNzQ2F0ZWdvcnkgPSDZhNmE2KjYsdin2YXYrCDYp9mE2YXYrdin2LPYqNmK2KkJCQkJCSMgQnVzaW5lc3MgQ2F0ZWdvcnkNCiAgICAgICAg',
            'private_key' => 'LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JR0VBZ0VBTUJBR0J5cUdTTTQ5QWdFR0JTdUJCQUFLQkcwd2F3SUJBUVFnKzNHbklCREdGNVZ1ZExiUlNJcWEKMjBqS0tqdjFLMUZxck1BaVUzVFFDanFoUkFOQ0FBUVg4Q1ZhQjlvMFlxSkJ5SFdJaFJrN2Z2SzVncmI3YjBoYgp6NWpLaHg0U21BSjU2ZGkrRlFtSTlmSmZieDVlMlEzdkFLZVY4TisvaE04RWFaYXhlb3BaCi0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K',
            'public_key' => 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVGL0FsV2dmYU5HS2lRY2gxaUlVWk8zN3l1WUsyKzI5SQpXOCtZeW9jZUVwZ0NlZW5ZdmhVSmlQWHlYMjhlWHRrTjd3Q25sZkRmdjRUUEJHbVdzWHFLV1E9PQotLS0tLUVORCBQVUJMSUMgS0VZLS0tLS0K',
            'csr_request' => 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0KTUlJQzZEQ0NBbzRDQVFBd2dnRURNVkF3VGdZRFZRUURERWZaaGRpazJMUFlzOWlwSU5pdjJZTFlxU0RZcDltRQoyS2ZZcU5pdjJLZll1U0RaaE5pcTJZTFpodG1LMktrZzJZYll1Tm1GSU5pbjJZVFpoZGk1MllUWmlObUYyS2ZZCnFqRlFNRTRHQTFVRUN3eEgyWVhZcE5pejJMUFlxU0RZcjltQzJLa2cyS2ZaaE5pbjJLallyOWluMkxrZzJZVFkKcXRtQzJZYlppdGlwSU5tRzJMalpoU0RZcDltRTJZWFl1ZG1FMllqWmhkaW4yS294VURCT0JnTlZCQW9NUjltRgoyS1RZczlpejJLa2cySy9aZ3RpcElOaW4yWVRZcDlpbzJLL1lwOWk1SU5tRTJLclpndG1HMllyWXFTRFpodGk0CjJZVWcyS2ZaaE5tRjJMblpoTm1JMllYWXA5aXFNUXN3Q1FZRFZRUUdFd0pUUVRCV01CQUdCeXFHU000OUFnRUcKQlN1QkJBQUtBMElBQkJmd0pWb0gyalJpb2tISWRZaUZHVHQrOHJtQ3R2dHZTRnZQbU1xSEhoS1lBbm5wMkw0VgpDWWoxOGw5dkhsN1pEZThBcDVYdzM3K0V6d1JwbHJGNmlsbWdnZ0VvTUlJQkpBWUpLb1pJaHZjTkFRa09NWUlCCkZUQ0NBUkV3SkFZSkt3WUJCQUdDTnhRQ0JCY1RGVkJTUlZwQlZFTkJMVU52WkdVdFUybG5ibWx1WnpDQjZBWUQKVlIwUkJJSGdNSUhkcElIYU1JSFhNUjB3R3dZRFZRUUVEQlF4TFZORVUwRjhNaTFHUjBSVGZETXRVMFJHUnpFZgpNQjBHQ2dtU0pvbVQ4aXhrQVFFTUR6TXdNakUyTnpBek56Z3dNREF3TXpFTk1Bc0dBMVVFREF3RU1URXdNREU2Ck1EZ0dBMVVFR2d3eHc1akNwOE9ad29URG1jS0Z3NW5DaE1PWndvTWd3NWpDdWNPWXdxakRtTUt2dzVqQ3A4T1oKd29URG1jS0V3NW5DaHpGS01FZ0dBMVVFRHd4Qnc1bkNoTU9ad29URG1NS293NWpDc2NPWXdxZkRtY0tGdzVqQwpyQ0REbU1Lbnc1bkNoTU9ad29YRG1NS3R3NWpDcDhPWXdyUERtTUtvdzVuQ2lzT1l3cWt3Q2dZSUtvWkl6ajBFCkF3SURTQUF3UlFJZ2U0aGlYUUwvd3BRai8waE44Sy82NmRUTGdyaTFHRUhXMkpPTFlmZTV0T01DSVFDb2ZqUHIKMW1ITFlyZHFUNXd3T1FNNTQyUVR3SzBmSGlOU0Y4Vk51TnJvSkE9PQotLS0tLUVORCBDRVJUSUZJQ0FURSBSRVFVRVNULS0tLS0K',
            'certificate' => 'TUlJRENUQ0NBcTZnQXdJQkFnSUdBWmJFbnZBM01Bb0dDQ3FHU000OUJBTUNNQlV4RXpBUkJnTlZCQU1NQ21WSmJuWnZhV05wYm1jd0hoY05NalV3TlRFeU1UTXhNVEEzV2hjTk16QXdOVEV4TWpFd01EQXdXakNDQVFNeFVEQk9CZ05WQkFNTVI5bUYyS1RZczlpejJLa2cySy9aZ3RpcElOaW4yWVRZcDlpbzJLL1lwOWk1SU5tRTJLclpndG1HMllyWXFTRFpodGk0MllVZzJLZlpoTm1GMkxuWmhObUkyWVhZcDlpcU1WQXdUZ1lEVlFRTERFZlpoZGlrMkxQWXM5aXBJTml2MllMWXFTRFlwOW1FMktmWXFOaXYyS2ZZdVNEWmhOaXEyWUxaaHRtSzJLa2cyWWJZdU5tRklOaW4yWVRaaGRpNTJZVFppTm1GMktmWXFqRlFNRTRHQTFVRUNneEgyWVhZcE5pejJMUFlxU0RZcjltQzJLa2cyS2ZaaE5pbjJLallyOWluMkxrZzJZVFlxdG1DMlliWml0aXBJTm1HMkxqWmhTRFlwOW1FMllYWXVkbUUyWWpaaGRpbjJLb3hDekFKQmdOVkJBWVRBbE5CTUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVGL0FsV2dmYU5HS2lRY2gxaUlVWk8zN3l1WUsyKzI5SVc4K1l5b2NlRXBnQ2Vlbll2aFVKaVBYeVgyOGVYdGtON3dDbmxmRGZ2NFRQQkdtV3NYcUtXYU9CL0RDQitUQU1CZ05WSFJNQkFmOEVBakFBTUlIb0JnTlZIUkVFZ2VBd2dkMmtnZG93Z2RjeEhUQWJCZ05WQkFRTUZERXRVMFJUUVh3eUxVWkhSRk44TXkxVFJFWkhNUjh3SFFZS0NaSW1pWlB5TEdRQkFRd1BNekF5TVRZM01ETTNPREF3TURBek1RMHdDd1lEVlFRTURBUXhNVEF3TVRvd09BWURWUVFhRERIRG1NS253NW5DaE1PWndvWERtY0tFdzVuQ2d5RERtTUs1dzVqQ3FNT1l3cS9EbU1Lbnc1bkNoTU9ad29URG1jS0hNVW93U0FZRFZRUVBERUhEbWNLRXc1bkNoTU9Zd3FqRG1NS3h3NWpDcDhPWndvWERtTUtzSU1PWXdxZkRtY0tFdzVuQ2hjT1l3cTNEbU1Lbnc1akNzOE9Zd3FqRG1jS0t3NWpDcVRBS0JnZ3Foa2pPUFFRREFnTkpBREJHQWlFQThlMUo1Tm1QTENsWUg4NVN4SUNtTUUvKzZrQlV4QU1DK2dPdHU0VTF3ZlVDSVFDbTQ4bVZEcndkSThWK0NYRE04N2dRSjg0NXA4anhiWng3cE1yRnU4YWpvdz09',
            'secret' => 'dxFeFFhrImM6iqcWb9p3h5uu7DH3AG3IYxnZDYajQxU=',
            'csid' => '1747055472695',
            'production_certificate' => 'TUlJRS9UQ0NCS09nQXdJQkFnSVRZd0FBVG1jdk1jQXhPNEZuUWdBQkFBQk9aekFLQmdncWhrak9QUVFEQWpCaU1SVXdFd1lLQ1pJbWlaUHlMR1FCR1JZRmJHOWpZV3d4RXpBUkJnb0praWFKay9Jc1pBRVpGZ05uYjNZeEZ6QVZCZ29Ka2lhSmsvSXNaQUVaRmdkbGVIUm5ZWHAwTVJzd0dRWURWUVFERXhKUVJWcEZTVTVXVDBsRFJWTkRRVEV0UTBFd0hoY05NalV3TlRFeU1UTXdNVEV6V2hjTk1qY3dOVEV5TVRNeE1URXpXakNDQVFNeEN6QUpCZ05WQkFZVEFsTkJNVkF3VGdZRFZRUUtERWZaaGRpazJMUFlzOWlwSU5pdjJZTFlxU0RZcDltRTJLZllxTml2MktmWXVTRFpoTmlxMllMWmh0bUsyS2tnMlliWXVObUZJTmluMllUWmhkaTUyWVRaaU5tRjJLZllxakZRTUU0R0ExVUVDd3hIMllYWXBOaXoyTFBZcVNEWXI5bUMyS2tnMktmWmhOaW4yS2pZcjlpbjJMa2cyWVRZcXRtQzJZYlppdGlwSU5tRzJMalpoU0RZcDltRTJZWFl1ZG1FMllqWmhkaW4yS294VURCT0JnTlZCQU1NUjltRjJLVFlzOWl6MktrZzJLL1pndGlwSU5pbjJZVFlwOWlvMksvWXA5aTVJTm1FMktyWmd0bUcyWXJZcVNEWmh0aTQyWVVnMktmWmhObUYyTG5aaE5tSTJZWFlwOWlxTUZZd0VBWUhLb1pJemowQ0FRWUZLNEVFQUFvRFFnQUVGL0FsV2dmYU5HS2lRY2gxaUlVWk8zN3l1WUsyKzI5SVc4K1l5b2NlRXBnQ2Vlbll2aFVKaVBYeVgyOGVYdGtON3dDbmxmRGZ2NFRQQkdtV3NYcUtXYU9DQXBZd2dnS1NNSUhvQmdOVkhSRUVnZUF3Z2Qya2dkb3dnZGN4SFRBYkJnTlZCQVFNRkRFdFUwUlRRWHd5TFVaSFJGTjhNeTFUUkVaSE1SOHdIUVlLQ1pJbWlaUHlMR1FCQVF3UE16QXlNVFkzTURNM09EQXdNREF6TVEwd0N3WURWUVFNREFReE1UQXdNVG93T0FZRFZRUWFEREhEbU1Lbnc1bkNoTU9ad29YRG1jS0V3NW5DZ3lERG1NSzV3NWpDcU1PWXdxL0RtTUtudzVuQ2hNT1p3b1REbWNLSE1Vb3dTQVlEVlFRUERFSERtY0tFdzVuQ2hNT1l3cWpEbU1LeHc1akNwOE9ad29YRG1NS3NJTU9Zd3FmRG1jS0V3NW5DaGNPWXdxM0RtTUtudzVqQ3M4T1l3cWpEbWNLS3c1akNxVEFkQmdOVkhRNEVGZ1FVT3ZMZEJKb3FwSHI2ZTA5REtqN0x3WDhFUUZRd0h3WURWUjBqQkJnd0ZvQVVxbGc0ZzZtV0pVM3FWSHE1bEFmalYxRDRPK2d3Z2M0R0NDc0dBUVVGQndFQkJJSEJNSUcrTUlHN0JnZ3JCZ0VGQlFjd0FvYUJybXhrWVhBNkx5OHZRMDQ5VUVWYVJVbE9WazlKUTBWVFEwRXhMVU5CTEVOT1BVRkpRU3hEVGoxUWRXSnNhV01sTWpCTFpYa2xNakJUWlhKMmFXTmxjeXhEVGoxVFpYSjJhV05sY3l4RFRqMURiMjVtYVdkMWNtRjBhVzl1TEVSRFBXVjRkSHBoZEdOaExFUkRQV2R2ZGl4RVF6MXNiMk5oYkQ5alFVTmxjblJwWm1sallYUmxQMkpoYzJVL2IySnFaV04wUTJ4aGMzTTlZMlZ5ZEdsbWFXTmhkR2x2YmtGMWRHaHZjbWwwZVRBT0JnTlZIUThCQWY4RUJBTUNCNEF3UEFZSkt3WUJCQUdDTnhVSEJDOHdMUVlsS3dZQkJBR0NOeFVJZ1lhb0hZVFEreEtHN1owa2g4NzdHZFBBVldhQm5OZ3RnK1hGWFFJQlpBSUJFREFkQmdOVkhTVUVGakFVQmdnckJnRUZCUWNEQWdZSUt3WUJCUVVIQXdNd0p3WUpLd1lCQkFHQ054VUtCQm93R0RBS0JnZ3JCZ0VGQlFjREFqQUtCZ2dyQmdFRkJRY0RBekFLQmdncWhrak9QUVFEQWdOSUFEQkZBaUVBdE95SmZWYnNHRHJCK1NiL3pvMERpamptTkF4MGhQc0RJbHJjYkVIdEI0b0NJQmp6SEV3UGxCZVRpbGFhdFZSR0dWMjRiekpHRXgxRjZaQktCTUFXa1U2VQ==',
            'production_secret' => 'ujY6kBjzGf5JAuhFKyRpxfFsn+6718R8ZYflXkdHVWE=',
            'production_csid' => '20071258626',
        ])->save();

        $this->command?->info('تم استرجاع بيانات الزكاة والشركة العامة لفرع الرياض من النسخة القديمة.');
    }
}
