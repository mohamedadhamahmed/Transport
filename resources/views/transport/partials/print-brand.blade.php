@php
    $brand = [
        'name_ar' => defined('Namear') ? constant('Namear') : config('app.name'),
        'name_en' => defined('Nameen') ? constant('Nameen') : null,
        'desc_ar' => defined('describtionar') ? constant('describtionar') : null,
        'desc_en' => defined('describtionen') ? constant('describtionen') : null,
        'cr_ar' => defined('STar') ? constant('STar') : null,
        'cr_en' => defined('STen') ? constant('STen') : null,
        'tax_ar' => defined('Taxar') ? constant('Taxar') : null,
        'tax_en' => defined('Taxen') ? constant('Taxen') : null,
        'addr_ar' => defined('addressar') ? constant('addressar') : null,
        'logo' => defined('camplogo') ? constant('camplogo') : null,
    ];
@endphp
    <div class="head">
        <div class="co">
            <div class="name">{{ $brand['name_ar'] }}</div>
            @if ($brand['desc_ar'])<p>{{ $brand['desc_ar'] }}</p>@endif
            @if ($brand['cr_ar'])<p>{{ $brand['cr_ar'] }}</p>@endif
            @if ($brand['tax_ar'])<p>{{ $brand['tax_ar'] }}</p>@endif
            @if ($brand['addr_ar'])<p>{{ $brand['addr_ar'] }}</p>@endif
        </div>
        @if ($brand['logo'])
            <img src="{{ asset('assets/img/brand/' . $brand['logo']) }}" alt="logo">
        @endif
        @if ($brand['name_en'])
            <div class="co en">
                <div class="name">{{ $brand['name_en'] }}</div>
                @if ($brand['desc_en'])<p>{{ $brand['desc_en'] }}</p>@endif
                @if ($brand['cr_en'])<p>{{ $brand['cr_en'] }}</p>@endif
                @if ($brand['tax_en'])<p>{{ $brand['tax_en'] }}</p>@endif
            </div>
        @endif
    </div>

