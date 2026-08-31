{{-- هيدر موحّد لكل صفحات طباعة قسم الحسابات (سندات القبض/الصرف والقيود
     اليومية/الافتتاحية) - بيانات الشركة ثنائية اللغة (عربي / شعار /
     إنجليزي) نفس البيانات المستخدمة في resources/views/invoices/returns/print.blade.php
     (constants معرّفة في AppServiceProvider من جدولي Setting/SystemSetting)،
     بس هنا في partial واحد يتضم مرة واحدة في كل صفحة طباعة بدل ما الهيدر
     يتكرر نسخة بنسخة ويحصل فيه اختلاف بينهم بالغلط زي ما حصل قبل كده. --}}
@php
    $brand = [
        'name_ar' => defined('Namear') ? Namear : config('app.name', 'ERP'),
        'desc_ar' => defined('describtionar') ? describtionar : null,
        'st_ar' => defined('STar') ? STar : null,
        'tax_ar' => defined('Taxar') ? Taxar : null,
        'name_en' => defined('Nameen') ? Nameen : null,
        'desc_en' => defined('describtionen') ? describtionen : null,
        'st_en' => defined('STen') ? STen : null,
        'tax_en' => defined('Taxen') ? Taxen : null,
        'logo' => defined('camplogo') ? camplogo : null,
    ];
@endphp

<div class="doc-header" dir="rtl">
    <div class="company-block">
        <div class="name">{{ $brand['name_ar'] }}</div>
        @if ($brand['desc_ar'])
            <p>{{ $brand['desc_ar'] }}</p>
        @endif
        @if ($brand['st_ar'])
            <p>{{ $brand['st_ar'] }}</p>
        @endif
        @if ($brand['tax_ar'])
            <p>{{ $brand['tax_ar'] }}</p>
        @endif
    </div>

    @if ($brand['logo'])
        <div class="logo-block">
            <img class="logo" src="{{ asset('assets/img/brand/' . $brand['logo']) }}" alt="logo">
        </div>
    @endif

    @if ($brand['name_en'])
        <div class="company-block">
            <div class="name">{{ $brand['name_en'] }}</div>
            @if ($brand['desc_en'])
                <p>{{ $brand['desc_en'] }}</p>
            @endif
            @if ($brand['st_en'])
                <p>{{ $brand['st_en'] }}</p>
            @endif
            @if ($brand['tax_en'])
                <p>{{ $brand['tax_en'] }}</p>
            @endif
        </div>
    @endif
</div>
