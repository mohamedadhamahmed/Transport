<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    @php
        $maxLoads = $rows->max('loads') ?: 0;
        $maxTotal = $rows->max('total') ?: 0;
    @endphp

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.top_customers_report'),
            'badge' => $from . ' → ' . $to,
            'icon' => 'chart',
            'crumbs' => [['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')], ['label' => __('transport.top_customers_report')]],
            'buttons' => [
                ['label' => __('transport.export_excel'), 'url' => request()->fullUrlWithQuery(['export' => 'excel']), 'style' => 'green', 'icon' => 'excel'],
                ['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print'],
            ],
        ])
        @include('transport.partials.report-tabs', ['active' => 'customers'])
        @include('transport.partials.period-filter', ['from' => $from, 'to' => $to, 'sort' => $sort, 'sorts' => [
            'loads' => __('transport.loads_count'),
            'total' => __('transport.grand_total'),
            'weight' => __('transport.total_weight'),
            'unbilled_value' => __('transport.unbilled_value'),
        ]])

        <div class="tv-kpis tv-kpis-4">
            <div class="tv-kpi"><div class="l">👥 {{ __('transport.customers') }}</div><div class="v">{{ $rows->count() }}</div></div>
            <div class="tv-kpi"><div class="l">📦 {{ __('transport.loads_count') }}</div><div class="v">{{ number_format($rows->sum('loads')) }}</div></div>
            <div class="tv-kpi"><div class="l">✅ {{ __('transport.grand_total_incl') }}</div><div class="v" style="color:#047857">{{ number_format($rows->sum('total'), 2) }}</div></div>
            <div class="tv-kpi"><div class="l">⏳ {{ __('transport.unbilled_value') }}</div><div class="v" style="color:#be123c">{{ number_format($rows->sum('unbilled_value'), 2) }}</div></div>
        </div>

        <div class="tv-card tv-pad">
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>#</th><th>{{ __('transport.customer') }}</th><th>{{ __('transport.loads_count') }}</th><th>{{ __('transport.total_weight') }}</th>
                        <th>{{ __('transport.top_destination') }}</th><th>{{ __('transport.last_load') }}</th><th>{{ __('transport.invoices_count') }}</th>
                        <th>{{ __('transport.grand_total') }}</th><th>{{ __('transport.unbilled_loads') }}</th><th class="tv-no-print"></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}@if ($i < 3) {{ ['🥇', '🥈', '🥉'][$i] }}@endif</td>
                                <td style="font-weight:700">{{ $r['name'] }}</td>
                                <td>@include('transport.partials.rank-bar', ['value' => $r['loads'], 'max' => $maxLoads, 'color' => '#F5811E'])</td>
                                <td>{{ $r['weight'] ? rtrim(rtrim(number_format($r['weight'], 2), '0'), '.') : '-' }}</td>
                                <td>{{ $r['top_dest'] }}</td>
                                <td>{{ $r['last']?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $r['invoices'] }}</td>
                                <td>@include('transport.partials.rank-bar', ['value' => $r['total'], 'max' => $maxTotal, 'label' => number_format($r['total'], 2), 'color' => '#10b981'])</td>
                                <td>
                                    @if ($r['unbilled_count'])
                                        <span class="tv-pill tv-pill-red">{{ $r['unbilled_count'] }} · {{ number_format($r['unbilled_value'], 2) }}</span>
                                    @else
                                        <span class="tv-pill tv-pill-green">✓</span>
                                    @endif
                                </td>
                                <td class="tv-no-print">
                                    <div class="flex gap-1">
                                        <a href="{{ route('transport.reports.sales', ['customer_id' => $r['id'], 'date_from' => $from, 'date_to' => $to]) }}" class="tr-btn tr-btn-gray">🧾</a>
                                        @if ($r['unbilled_count'])
                                            @can('transport_invoices.create')
                                                <a href="{{ route('transport.invoices.create', ['customer_id' => $r['id']]) }}" class="tr-btn tr-btn-green">{{ __('transport.bill_customer_loads') }}</a>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
