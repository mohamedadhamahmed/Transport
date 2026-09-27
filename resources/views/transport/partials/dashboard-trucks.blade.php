{{-- قسم "حالة الشاحنات" في الشاشة الرئيسية - ظاهر لكل المستخدمين:
     عدد الفاضية/المحمّلة/المتأخرة + الفاضية في كل منطقة + المحمّلة من فين لفين --}}
@php
    $tsReady = \Illuminate\Support\Facades\Schema::hasTable('truck_loads');
    if ($tsReady) {
        $tsTrucks = \App\Models\Truck::with(['driver', 'activeLoad.driver'])
            ->where('status', '!=', 'inactive')->get();
        $tsLoaded = $tsTrucks->filter(fn ($t) => $t->activeLoad)
            ->sortBy(fn ($t) => $t->activeLoad->expected_unload_at)->values();
        $tsEmpty = $tsTrucks->filter(fn ($t) => !$t->activeLoad && $t->status === 'active');
        $tsOverdue = $tsLoaded->filter(fn ($t) => $t->activeLoad->isOverdue())->count();
        $tsRegions = \App\Support\SaudiRegions::options();
        $tsByRegion = $tsEmpty->groupBy(fn ($t) => $t->current_region ?: '_none')->map->count();
    }
@endphp

@if ($tsReady)
<style>
    .ts-box { background:#fff; border:1px solid #f3f4f6; border-radius:1rem; padding:1.1rem; box-shadow:0 1px 2px rgba(0,0,0,.04); }
    .ts-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; }
    .ts-kpi { border-radius:.8rem; padding:.8rem 1rem; }
    .ts-kpi .v { font-size:1.7rem; font-weight:800; line-height:1.1; }
    .ts-kpi .l { font-size:.78rem; font-weight:600; }
    .ts-regions { display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; }
    @media (min-width: 700px) { .ts-regions { grid-template-columns:repeat(4,1fr); } }
    @media (min-width: 1200px) { .ts-regions { grid-template-columns:repeat(7,1fr); } }
    .ts-reg { border:1px solid #e5e7eb; border-radius:.6rem; padding:.5rem .6rem; display:flex; justify-content:space-between; align-items:center; font-size:.8rem; text-decoration:none; color:#374151; }
    .ts-reg b { font-size:1rem; color:#059669; }
    .ts-reg.zero { opacity:.45; }
    .ts-reg.zero b { color:#9ca3af; }
    .ts-list { display:grid; grid-template-columns:1fr; gap:.6rem; }
    @media (min-width: 1000px) { .ts-list { grid-template-columns:1fr 1fr; } }
    .ts-row { border:1px solid #e5e7eb; border-inline-start:4px solid #F5811E; border-radius:.6rem; padding:.6rem .8rem; display:flex; flex-direction:column; gap:.35rem; }
    .ts-row.late { border-inline-start-color:#e11d48; background:#fff8f8; }
    .ts-row .top { display:flex; justify-content:space-between; gap:.5rem; flex-wrap:wrap; font-size:.85rem; }
    .ts-row .rt { font-weight:800; color:#0F1B4C; }
    .ts-row .sm { font-size:.75rem; color:#6b7280; }
</style>

<div class="dash-anim ts-box space-y-4" style="--dash-delay: 10ms">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h3 class="font-bold text-[#0F1B4C]">🚚 {{ __('transport.trucks_status') }}</h3>
        @can('truck_loads.view')
            <a href="{{ route('transport.loads.board') }}" class="text-sm font-semibold" style="color:#1456E8">{{ __('transport.open_board') }} ←</a>
        @endcan
    </div>

    <div class="ts-kpis">
        <div class="ts-kpi" style="background:#ecfdf5;color:#047857"><div class="v">{{ $tsEmpty->count() }}</div><div class="l">🟢 {{ __('transport.empty_trucks') }}</div></div>
        <div class="ts-kpi" style="background:#fff7ed;color:#c2410c"><div class="v">{{ $tsLoaded->count() }}</div><div class="l">🟠 {{ __('transport.loaded_trucks') }}</div></div>
        <div class="ts-kpi" style="background:#fff1f2;color:#be123c"><div class="v">{{ $tsOverdue }}</div><div class="l">🔴 {{ __('transport.overdue_trucks') }}</div></div>
    </div>

    <div>
        <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('transport.empty_by_region') }}</p>
        <div class="ts-regions">
            @foreach ($tsRegions as $key => $name)
                @php $n = $tsByRegion[$key] ?? 0; @endphp
                <div class="ts-reg {{ $n ? '' : 'zero' }}"><span>{{ $name }}</span><b>{{ $n }}</b></div>
            @endforeach
            @if (($tsByRegion['_none'] ?? 0) > 0)
                <div class="ts-reg"><span>{{ __('transport.location_unknown') }}</span><b>{{ $tsByRegion['_none'] }}</b></div>
            @endif
        </div>
    </div>

    @if ($tsLoaded->isNotEmpty())
        <div>
            <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('transport.loaded_trucks') }}</p>
            <div class="ts-list">
                @foreach ($tsLoaded as $t)
                    @php
                        $l = $t->activeLoad;
                        $drv = $l->driver ?? $t->driver;
                    @endphp
                    <div class="ts-row {{ $l->isOverdue() ? 'late' : '' }}">
                        <div class="top">
                            <span><b>{{ $t->plate_number }}</b> · <span class="rt">{{ $l->from_label }} ← {{ $l->to_label }}</span></span>
                            <span class="tr-badge {{ $l->isOverdue() ? 'tr-badge-red' : 'tr-badge-amber' }}" style="display:inline-block;padding:.1rem .5rem;border-radius:99px;font-size:.7rem;font-weight:700;{{ $l->isOverdue() ? 'background:#fff1f2;color:#be123c' : 'background:#fffbeb;color:#b45309' }}">{{ $l->remainingText() }}</span>
                        </div>
                        <div class="sm">{{ __('transport.load_type') }}: {{ $l->load_type }} · {{ __('transport.expected_unload_at') }}: {{ $l->expected_unload_at->format('Y-m-d H:i') }}</div>
                        @include('transport.partials.driver-contact', ['driver' => $drv, 'compact' => true,
                            'msg' => 'السلام عليكم ' . ($drv?->name ?? '') . '، بخصوص حمولة الشاحنة ' . $t->plate_number . ' من ' . $l->from_label . ' إلى ' . $l->to_label])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endif
