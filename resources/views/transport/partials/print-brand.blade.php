{{-- هيدر مستندات الطباعة: بيقرا بيانات المنشأة من قاعدة البيانات مباشرة (CompanyInfo) --}}
@php
    $co = \App\Support\CompanyInfo::get($brandBranchId ?? null);
@endphp
@unless ($co['configured'])
    <div class="alert no-print-hint" style="background:#fff7ed;color:#c2410c;border-color:#fed7aa;max-width:none;margin:0;border-radius:0">
        ⚠ بيانات المنشأة (الاسم / الرقم الضريبي) مش متسجلة - كمّلها من الإعدادات أو شغّل: php artisan db:seed --class=CompanySettingsSeeder --force
    </div>
@endunless
    <div class="head">
        <div class="co">
            <div class="name">{{ $co['name_ar'] }}</div>
            @if ($co['desc_ar'])<p>{{ $co['desc_ar'] }}</p>@endif
            @if ($co['cr'])<p>السجل التجاري: {{ $co['cr'] }}</p>@endif
            @if ($co['vat'])<p>الرقم الضريبي: {{ $co['vat'] }}</p>@endif
            @if ($co['address_ar'])<p>{{ $co['address_ar'] }}</p>@endif
            @if ($co['mobile'])<p>جوال: <span dir="ltr">{{ $co['mobile'] }}</span></p>@endif
        </div>
        @if ($co['logo_url'])
            <img src="{{ $co['logo_url'] }}" alt="logo">
        @endif
        <div class="co en">
            <div class="name">{{ $co['name_en'] ?? $co['name_ar'] }}</div>
            @if ($co['desc_en'])<p>{{ $co['desc_en'] }}</p>@endif
            @if ($co['cr'])<p>C.R: {{ $co['cr'] }}</p>@endif
            @if ($co['vat'])<p>VAT No: {{ $co['vat'] }}</p>@endif
            @if ($co['address_en'])<p>{{ $co['address_en'] }}</p>@endif
        </div>
    </div>
