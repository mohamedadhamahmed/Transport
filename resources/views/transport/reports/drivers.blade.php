<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    @php $maxLoads = $rows->max('loads') ?: 0; @endphp

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.drivers_report'),
            'badge' => $from . ' → ' . $to,
            'icon' => 'truck',
            'crumbs' => [['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')], ['label' => __('transport.drivers_report')]],
            'buttons' => [['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print']],
        ])
        @include('transport.partials.report-tabs', ['active' => 'drivers'])
        @include('transport.partials.period-filter', ['from' => $from, 'to' => $to])

        <div class="tv-card tv-pad">
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>#</th><th>{{ __('transport.driver') }}</th><th>{{ __('transport.loads_count') }}</th><th>{{ __('transport.completed') }}</th>
                        <th>{{ __('transport.on_the_road') }}</th><th>{{ __('transport.late_loads') }}</th><th>{{ __('transport.on_time_rate') }}</th>
                        <th>{{ __('transport.avg_trip_hours') }}</th><th>{{ __('transport.total_weight') }}</th><th>{{ __('transport.trucks') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="font-weight:700">{{ $r['driver']?->name ?? '-' }} <div class="text-xs text-gray-400" dir="ltr" style="text-align:start">{{ $r['driver']?->phone }}</div></td>
                                <td>@include('transport.partials.rank-bar', ['value' => $r['loads'], 'max' => $maxLoads, 'color' => '#F5811E'])</td>
                                <td>{{ $r['done'] }}</td>
                                <td>{{ $r['on_road'] }}@if ($r['overdue']) <span class="tv-pill tv-pill-red">{{ $r['overdue'] }} {{ __('transport.overdue') }}</span>@endif</td>
                                <td>{{ $r['late'] }}</td>
                                <td>
                                    @if ($r['on_time_pct'] === null) -
                                    @else
                                        <span class="tv-pill {{ $r['on_time_pct'] >= 90 ? 'tv-pill-green' : ($r['on_time_pct'] >= 70 ? 'tv-pill-amber' : 'tv-pill-red') }}">{{ $r['on_time_pct'] }}%</span>
                                    @endif
                                </td>
                                <td>{{ $r['avg_hours'] !== null ? $r['avg_hours'] . ' ' . __('transport.hours_short') : '-' }}</td>
                                <td>{{ $r['weight'] ? rtrim(rtrim(number_format($r['weight'], 2), '0'), '.') : '-' }}</td>
                                <td>{{ $r['trucks'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="tv-hint">ⓘ {{ __('transport.on_time_hint') }}</div>
        </div>
    </div>
</x-app-layout>
