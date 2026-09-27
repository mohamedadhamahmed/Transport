<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    <style>
        .rg-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: .9rem; }
        @media (min-width: 640px) { .rg-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1000px) { .rg-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 1300px) { .rg-grid { grid-template-columns: repeat(6, 1fr); } }
        .rg-card { background: #fff; border: 1px solid #eef0f5; border-radius: 12px; padding: .9rem 1rem; display: flex; align-items: center; gap: .75rem; position: relative; }
        .rg-card:hover { border-color: #cddcfb; }
        .rg-pin { width: 34px; height: 34px; border-radius: 9px; background: #eaf1ff; color: #1456E8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .rg-pin.custom { background: #ecfdf5; color: #059669; }
        .rg-name { font-weight: 800; color: #0F1B4C; font-size: .95rem; line-height: 1.3; display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
        .rg-meta { font-size: .72rem; color: #6b7280; margin-top: .2rem; }
        .rg-del { position: absolute; top: .45rem; inset-inline-end: .5rem; width: 22px; height: 22px; border-radius: 6px; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 1rem; line-height: 1; }
        .rg-del:hover { background: #fff1f2; color: #e11d48; }
        .rg-add { display: flex; gap: .75rem; align-items: stretch; flex-wrap: wrap; }
        .rg-add .tv-input { flex: 1; min-width: 220px; }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.regions'),
            'crumbs' => [['label' => __('transport.regions')]],
            'buttons' => [
                ['label' => __('transport.board_title'), 'url' => route('transport.loads.board'), 'style' => 'gray', 'icon' => 'truck', 'can' => 'truck_loads.view'],
            ],
        ])

        @include('transport.partials.flash')

        @can('truck_loads.manage')
            <div class="tv-card tv-pad">
                <form method="POST" action="{{ route('transport.regions.store') }}">
                    @csrf
                    <label class="tv-label">{{ __('transport.new_region_name') }}</label>
                    <div class="rg-add">
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="100" class="tv-input" placeholder="{{ __('transport.region_placeholder') }}">
                        <button type="submit" class="tv-btn tv-btn-green tv-btn-lg">+ {{ __('transport.add_region') }}</button>
                    </div>
                    <div class="tv-hint">ⓘ {{ __('transport.region_hint') }}</div>
                </form>
            </div>
        @endcan

        {{-- المناطق المضافة --}}
        <div>
            <div class="tv-section-title" style="margin-bottom:.75rem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z"/><path d="M12 7v6M9 10h6"/></svg>
                {{ __('transport.custom_regions') }}
                <span class="tv-count">{{ $custom->count() }}</span>
            </div>
            @if ($custom->isEmpty())
                <div class="tv-card tv-empty">{{ __('transport.no_custom_regions') }}</div>
            @else
                <div class="rg-grid">
                    @foreach ($custom as $r)
                        <div class="rg-card">
                            <span class="rg-pin custom">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            </span>
                            <div>
                                <div class="rg-name">{{ $r['name'] }} <span class="tv-pill tv-pill-green">{{ __('transport.region_custom') }}</span></div>
                                <div class="rg-meta">{{ __('transport.region_counts', ['trucks' => $r['trucks'], 'loads' => $r['loads']]) }}</div>
                            </div>
                            @can('truck_loads.manage')
                                <form method="POST" action="{{ route('transport.regions.destroy', $r['id']) }}" onsubmit="return confirm('{{ __('transport.confirm_delete_region') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rg-del" title="{{ __('transport.delete') }}">×</button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- مناطق المملكة الأساسية --}}
        <div>
            <div class="tv-section-title" style="margin-bottom:.75rem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
                {{ __('transport.base_regions') }}
                <span class="tv-count">{{ $base->count() }}</span>
            </div>
            <div class="rg-grid">
                @foreach ($base as $r)
                    <div class="rg-card">
                        <span class="rg-pin">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        </span>
                        <div>
                            <div class="rg-name">{{ $r['name'] }} <span class="tv-pill tv-pill-gray">{{ __('transport.region_base') }}</span></div>
                            <div class="rg-meta">{{ __('transport.region_counts', ['trucks' => $r['trucks'], 'loads' => $r['loads']]) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
