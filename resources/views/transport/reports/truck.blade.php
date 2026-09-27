<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    <style>
        .ts-two { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 1100px) { .ts-two { grid-template-columns: 1fr 1fr; } }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.truck_statement') . ($truck ? ' — ' . $truck->display_name : ''),
            'badge' => $from . ' → ' . $to,
            'icon' => 'truck',
            'crumbs' => [['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')], ['label' => __('transport.truck_statement')]],
            'buttons' => array_values(array_filter([
                $truck ? ['label' => __('transport.edit_truck'), 'url' => route('transport.trucks.edit', $truck), 'style' => 'gray', 'icon' => 'truck', 'can' => 'trucks.edit'] : null,
                ['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print'],
            ])),
        ])
        @include('transport.partials.report-tabs', ['active' => 'truck'])
        @include('transport.partials.period-filter', ['from' => $from, 'to' => $to, 'trucks' => $trucks])

        @if (!$truck)
            <div class="tv-card tv-empty">🚚 {{ __('transport.choose_truck_hint') }}</div>
        @else
            @php $s = $data['summary']; @endphp
            <div class="tv-kpis tv-kpis-4">
                <div class="tv-kpi"><div class="l">📦 {{ __('transport.loads_count') }}</div><div class="v">{{ $s['loads'] }} <small>· {{ rtrim(rtrim(number_format($s['weight'], 2), '0'), '.') ?: 0 }} {{ __('transport.ton') }}</small></div></div>
                <div class="tv-kpi"><div class="l">💵 {{ __('transport.trips_revenue') }}</div><div class="v" style="color:#047857">{{ number_format($s['revenue'], 2) }}</div></div>
                <div class="tv-kpi"><div class="l">🔧 {{ __('transport.total_expenses') }}</div><div class="v" style="color:#be123c">{{ number_format($s['expenses'], 2) }} <small>({{ __('transport.maintenance_only') }} {{ number_format($s['maintenance'], 2) }})</small></div></div>
                <div class="tv-kpi"><div class="l">📊 {{ __('transport.net') }}</div><div class="v" style="color:{{ $s['net'] >= 0 ? '#047857' : '#be123c' }}">{{ number_format($s['net'], 2) }}</div></div>
                <div class="tv-kpi">
                    <div class="l">⏱ {{ __('transport.utilization') }}</div>
                    <div class="v">{{ $s['utilization'] }}%</div>
                    @include('transport.partials.rank-bar', ['value' => $s['utilization'], 'max' => 100, 'label' => '', 'color' => $s['utilization'] >= 60 ? '#10b981' : ($s['utilization'] >= 30 ? '#F59E0B' : '#e11d48')])
                </div>
                <div class="tv-kpi"><div class="l">⚠️ {{ __('transport.late_loads') }}</div><div class="v">{{ $s['late'] }}</div></div>
                <div class="tv-kpi"><div class="l">👷 {{ __('transport.driver') }}</div><div class="v" style="font-size:1rem">{{ $truck->driver?->name ?? '-' }}</div></div>
                <div class="tv-kpi"><div class="l">🏷 {{ __('transport.ownership') }}</div><div class="v" style="font-size:1rem">{{ $truck->isOwned() ? __('transport.owned') : __('transport.external_truck') }}</div></div>
            </div>

            <div class="ts-two">
                <div class="tv-card tv-pad">
                    <div class="tv-section-title" style="margin-bottom:.8rem">🔧 {{ __('transport.expenses_by_category') }}</div>
                    @php $mx = $data['byCategory']->max('total') ?: 0; @endphp
                    @forelse ($data['byCategory'] as $c)
                        <div class="flex items-center justify-between gap-3 text-sm" style="padding:.35rem 0">
                            <span>{{ $c['label'] }} <span class="text-gray-400">({{ $c['count'] }})</span></span>
                            <div style="width:60%">@include('transport.partials.rank-bar', ['value' => $c['total'], 'max' => $mx, 'label' => number_format($c['total'], 2), 'color' => '#e11d48'])</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">{{ __('transport.no_data') }}</p>
                    @endforelse
                </div>
                <div class="tv-card tv-pad">
                    <div class="tv-section-title" style="margin-bottom:.8rem">🧾 {{ __('transport.maintenance_vouchers') }} <span class="tv-count">{{ $data['vouchers']->count() }}</span></div>
                    <div class="tv-table-wrap">
                        <table class="tv-table">
                            <thead><tr><th>#</th><th>{{ __('transport.date') }}</th><th>{{ __('transport.type') }}</th><th>{{ __('transport.price') }}</th></tr></thead>
                            <tbody>
                                @forelse ($data['vouchers'] as $v)
                                    <tr>
                                        <td><a href="{{ route('vouchers.show', $v) }}" class="hover:underline">{{ $v->voucher_number ?: ('#' . $v->id) }}</a></td>
                                        <td>{{ $v->voucher_date?->format('Y-m-d') }}</td>
                                        <td>{{ $v->expenseCategoryLabel() ?? '-' }}</td>
                                        <td style="font-weight:700">{{ number_format($v->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:.8rem">📦 {{ __('transport.loads') }} <span class="tv-count">{{ $data['loads']->count() }}</span></div>
                <div class="tv-table-wrap">
                    <table class="tv-table">
                        <thead><tr>
                            <th>{{ __('transport.loaded_at') }}</th><th>{{ __('transport.from') }}</th><th>{{ __('transport.to') }}</th><th>{{ __('transport.load_type') }}</th>
                            <th>{{ __('transport.customer') }}</th><th>{{ __('transport.driver') }}</th><th>{{ __('transport.unloaded_at') }}</th><th>{{ __('transport.status') }}</th><th>{{ __('transport.price') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($data['loads'] as $l)
                                <tr>
                                    <td>{{ $l->loaded_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $l->from_label }}</td>
                                    <td>{{ $l->to_label }}</td>
                                    <td>{{ $l->load_type }}</td>
                                    <td>{{ $l->customer?->name ?? '-' }}</td>
                                    <td>{{ $l->driver?->name ?? '-' }}</td>
                                    <td>{{ $l->unloaded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($l->status === 'loaded')
                                            <span class="tv-pill {{ $l->isOverdue() ? 'tv-pill-red' : 'tv-pill-amber' }}">{{ $l->isOverdue() ? __('transport.overdue') : __('transport.load_status_loaded') }}</span>
                                        @else
                                            <span class="tv-pill {{ $l->wasLate() ? 'tv-pill-amber' : 'tv-pill-green' }}">{{ $l->wasLate() ? __('transport.unloaded_late') : __('transport.load_status_unloaded') }}</span>
                                        @endif
                                        @if ($l->transport_invoice_id)<span class="tv-pill tv-pill-blue">🧾</span>@endif
                                    </td>
                                    <td>{{ $l->price !== null ? number_format((float) $l->price, 2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:.8rem">💵 {{ __('transport.invoiced_trips') }} <span class="tv-count">{{ $data['items']->count() }}</span></div>
                <div class="tv-table-wrap">
                    <table class="tv-table">
                        <thead><tr><th>{{ __('transport.invoice_number') }}</th><th>{{ __('transport.date') }}</th><th>{{ __('transport.customer') }}</th><th>{{ __('transport.from') }}</th><th>{{ __('transport.to') }}</th><th>{{ __('transport.before_tax') }}</th></tr></thead>
                        <tbody>
                            @forelse ($data['items'] as $it)
                                <tr>
                                    <td><a href="{{ route('transport.invoices.show', $it->transport_invoice_id) }}" class="hover:underline" style="font-weight:700">{{ $it->invoice?->invoice_number }}</a></td>
                                    <td>{{ $it->invoice?->issue_date?->format('Y-m-d') }}</td>
                                    <td>{{ $it->invoice?->customer?->name ?? '-' }}</td>
                                    <td>{{ $it->from_location ?? '-' }}</td>
                                    <td>{{ $it->to_location ?? '-' }}</td>
                                    <td style="font-weight:700">{{ number_format((float) $it->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                        @if ($data['items']->count())
                            <tfoot><tr><td colspan="5">{{ __('transport.period_total') }}</td><td>{{ number_format($s['revenue'], 2) }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @endif
    </div>

    @include('transport.partials.tv-select-script')
</x-app-layout>
