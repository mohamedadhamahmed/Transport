{{--
    هيدر الشاشة (كارت أبيض: أيقونة + عنوان + مسار + أزرار)
    @include('transport.partials.tv-header', [
        'title' => '...', 'badge' => 'رقم متوقع #1' (اختياري),
        'crumbs' => [['label' => '...', 'url' => '...'], ...],
        'buttons' => [['label' => '...', 'url' => '...', 'style' => 'green|blue|outline|gray', 'icon' => 'plus|print|excel|list|layers|truck|file', 'onclick' => '...', 'can' => 'perm']],
    ])
--}}
@php
    $tvIcons = [
        'plus' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'print' => '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/>',
        'excel' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 9v12"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'layers' => '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/>',
        'truck' => '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
        'file' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
    ];
@endphp
<div class="tv-card tv-head">
    <div class="tv-head-main">
        <span class="tv-head-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $tvIcons[$icon ?? 'layers'] ?? $tvIcons['layers'] !!}</svg>
        </span>
        <div>
            <div class="tv-title">{{ $title }} @if (!empty($badge))<small>{{ $badge }}</small>@endif</div>
            <div class="tv-crumb">
                <a href="{{ route('dashboard') }}">{{ __('transport.home') }}</a>
                @foreach ($crumbs ?? [] as $c)
                    <span>‹</span>
                    @if (!empty($c['url']))<a href="{{ $c['url'] }}">{{ $c['label'] }}</a>@else<span>{{ $c['label'] }}</span>@endif
                @endforeach
            </div>
        </div>
    </div>
    @if (!empty($buttons))
        <div class="tv-actions">
            @foreach ($buttons as $b)
                @continue(!empty($b['can']) && !auth()->user()?->can($b['can']))
                @if (!empty($b['url']))
                    <a href="{{ $b['url'] }}" class="tv-btn tv-btn-{{ $b['style'] ?? 'outline' }}">
                @else
                    <button type="button" onclick="{{ $b['onclick'] ?? '' }}" class="tv-btn tv-btn-{{ $b['style'] ?? 'outline' }}">
                @endif
                    @if (!empty($b['icon']))
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $tvIcons[$b['icon']] ?? '' !!}</svg>
                    @endif
                    {{ $b['label'] }}
                @if (!empty($b['url']))</a>@else</button>@endif
            @endforeach
        </div>
    @endif
</div>
