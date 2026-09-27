<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    <style>
        .rt-two { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 1100px) { .rt-two { grid-template-columns: 1fr 1fr; } }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.routes_report'),
            'badge' => $from . ' → ' . $to,
            'icon' => 'truck',
            'crumbs' => [['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')], ['label' => __('transport.routes_report')]],
            'buttons' => [['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print']],
        ])
        @include('transport.partials.report-tabs', ['active' => 'routes'])
        @include('transport.partials.period-filter', ['from' => $from, 'to' => $to])

        <div class="tv-kpis tv-kpis-4">
            <div class="tv-kpi"><div class="l">📦 {{ __('transport.loads_count') }}</div><div class="v">{{ number_format($total) }}</div></div>
            <div class="tv-kpi"><div class="l">⬆️ {{ __('transport.top_origin') }}</div><div class="v" style="font-size:1.05rem">{{ $origins->first()['label'] ?? '-' }}</div></div>
            <div class="tv-kpi"><div class="l">⬇️ {{ __('transport.top_destination') }}</div><div class="v" style="font-size:1.05rem">{{ $destinations->first()['label'] ?? '-' }}</div></div>
            <div class="tv-kpi"><div class="l">🛣 {{ __('transport.top_route') }}</div><div class="v" style="font-size:1.05rem">{{ $routes->first()['label'] ?? '-' }}</div></div>
        </div>

        @php
            $tables = [
                ['title' => '⬆️ ' . __('transport.top_origins'), 'rows' => $origins, 'color' => '#1456E8'],
                ['title' => '⬇️ ' . __('transport.top_destinations'), 'rows' => $destinations, 'color' => '#F5811E'],
            ];
        @endphp
        <div class="rt-two">
            @foreach ($tables as $t)
                <div class="tv-card tv-pad">
                    <div class="tv-section-title" style="margin-bottom:.8rem">{{ $t['title'] }}</div>
                    <div class="tv-table-wrap">
                        <table class="tv-table">
                            <thead><tr><th>{{ __('transport.region') }}</th><th>{{ __('transport.loads_count') }}</th><th>{{ __('transport.total_weight') }}</th><th>{{ __('transport.trips_revenue') }}</th></tr></thead>
                            <tbody>
                                @php $mx = $t['rows']->max('count') ?: 0; @endphp
                                @forelse ($t['rows'] as $r)
                                    <tr>
                                        <td style="font-weight:700">{{ $r['label'] }}</td>
                                        <td>@include('transport.partials.rank-bar', ['value' => $r['count'], 'max' => $mx, 'color' => $t['color']])</td>
                                        <td>{{ $r['weight'] ? rtrim(rtrim(number_format($r['weight'], 2), '0'), '.') : '-' }}</td>
                                        <td>{{ number_format($r['revenue'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="tv-card tv-pad">
            <div class="tv-section-title" style="margin-bottom:.8rem">🛣 {{ __('transport.top_routes') }}</div>
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>#</th><th>{{ __('transport.route') }}</th><th>{{ __('transport.loads_count') }}</th><th>{{ __('transport.total_weight') }}</th>
                        <th>{{ __('transport.avg_trip_hours') }}</th><th>{{ __('transport.late_loads') }}</th><th>{{ __('transport.trips_revenue') }}</th><th>{{ __('transport.avg_load_revenue') }}</th>
                    </tr></thead>
                    <tbody>
                        @php $mx = $routes->max('count') ?: 0; @endphp
                        @forelse ($routes as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="font-weight:700">{{ $r['label'] }}</td>
                                <td>@include('transport.partials.rank-bar', ['value' => $r['count'], 'max' => $mx, 'color' => '#6B2FD6'])</td>
                                <td>{{ $r['weight'] ? rtrim(rtrim(number_format($r['weight'], 2), '0'), '.') : '-' }}</td>
                                <td>{{ $r['avg_hours'] !== null ? $r['avg_hours'] . ' ' . __('transport.hours_short') : '-' }}</td>
                                <td>@if ($r['late'])<span class="tv-pill tv-pill-red">{{ $r['late'] }}</span>@else<span class="tv-pill tv-pill-green">0</span>@endif</td>
                                <td>{{ number_format($r['revenue'], 2) }}</td>
                                <td>{{ $r['count'] ? number_format($r['revenue'] / $r['count'], 2) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($cities->isNotEmpty())
            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:.8rem">🏙 {{ __('transport.top_cities') }}</div>
                @php $mx = $cities->max('count') ?: 0; @endphp
                <div class="tr-grid tr-grid-2">
                    @foreach ($cities as $c)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span>{{ $c['label'] }}</span>
                            <div style="width:55%">@include('transport.partials.rank-bar', ['value' => $c['count'], 'max' => $mx, 'color' => '#0ea5e9'])</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
