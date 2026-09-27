{{-- شريط نسبة صغير جنب الرقم: @include('transport.partials.rank-bar', ['value' => 5, 'max' => 10, 'color' => '#1456E8']) --}}
@php $pct = ($max ?? 0) > 0 ? max(2, round(($value / $max) * 100)) : 0; @endphp
<div style="display:flex;align-items:center;gap:.5rem;min-width:140px">
    <div style="flex:1;height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden">
        <div style="width:{{ $pct }}%;height:100%;background:{{ $color ?? '#1456E8' }};border-radius:99px"></div>
    </div>
    <b style="min-width:2.5rem;text-align:end">{{ $label ?? $value }}</b>
</div>
