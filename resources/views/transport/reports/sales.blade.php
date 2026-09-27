<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    @php $maxCust = $byCustomer->max('total') ?: 0; @endphp

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.sales_report'),
            'badge' => $from . ' → ' . $to,
            'icon' => 'file',
            'crumbs' => [['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')], ['label' => __('transport.sales_report')]],
            'buttons' => [
                ['label' => __('transport.export_excel'), 'url' => request()->fullUrlWithQuery(['export' => 'excel']), 'style' => 'green', 'icon' => 'excel'],
                ['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print'],
            ],
        ])
        @include('transport.partials.report-tabs', ['active' => 'sales'])
        @include('transport.partials.period-filter', ['from' => $from, 'to' => $to, 'customers' => $customers])

        <div class="tv-kpis tv-kpis-4">
            <div class="tv-kpi"><div class="l">🧾 {{ __('transport.invoices_count') }}</div><div class="v">{{ number_format($summary['count']) }} <small>· {{ $summary['trips'] }} {{ __('transport.trips') }}</small></div></div>
            <div class="tv-kpi"><div class="l">💵 {{ __('transport.net_before_tax') }}</div><div class="v">{{ number_format($summary['subtotal'], 2) }} <small>{{ __('transport.sar') }}</small></div></div>
            <div class="tv-kpi"><div class="l">🏛 {{ __('transport.vat') }}</div><div class="v">{{ number_format($summary['tax'], 2) }} <small>{{ __('transport.sar') }}</small></div></div>
            <div class="tv-kpi"><div class="l">✅ {{ __('transport.grand_total_incl') }}</div><div class="v" style="color:#047857">{{ number_format($summary['total'], 2) }} <small>{{ __('transport.sar') }}</small></div></div>
            <div class="tv-kpi"><div class="l">📈 {{ __('transport.avg_invoice') }}</div><div class="v">{{ number_format($summary['avg'], 2) }}</div></div>
            <div class="tv-kpi"><div class="l">⏳ {{ __('transport.credit_sales') }}</div><div class="v" style="color:#b45309">{{ number_format($summary['credit'], 2) }}</div></div>
            <div class="tv-kpi"><div class="l">👥 {{ __('transport.customers') }}</div><div class="v">{{ $byCustomer->count() }}</div></div>
            <div class="tv-kpi"><div class="l">🏛 {{ __('transport.status_zatca_pending') }}</div><div class="v" style="color:{{ $summary['zatca_pending'] ? '#be123c' : '#0F1B4C' }}">{{ $summary['zatca_pending'] }}</div></div>
        </div>

        @if ($monthly->count() > 1)
            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:.8rem">📅 {{ __('transport.monthly_sales') }}</div>
                <div style="position:relative;height:280px"><canvas id="ch-monthly"></canvas></div>
            </div>
        @endif

        <div class="tv-card tv-pad">
            <div class="tv-section-title" style="margin-bottom:.8rem">👥 {{ __('transport.sales_by_customer') }}</div>
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>#</th><th>{{ __('transport.customer') }}</th><th>{{ __('transport.invoices_count') }}</th><th>{{ __('transport.trips_count') }}</th>
                        <th>{{ __('transport.before_tax') }}</th><th>{{ __('transport.tax_amount') }}</th><th>{{ __('transport.grand_total') }}</th><th>{{ __('transport.share') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($byCustomer as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="font-weight:700">
                                    <a href="{{ request()->fullUrlWithQuery(['customer_id' => $r['customer']?->id]) }}" class="hover:underline">{{ $r['customer']?->name ?? '-' }}</a>
                                </td>
                                <td>{{ $r['count'] }}</td>
                                <td>{{ $r['trips'] }}</td>
                                <td>{{ number_format($r['subtotal'], 2) }}</td>
                                <td>{{ number_format($r['tax'], 2) }}</td>
                                <td>@include('transport.partials.rank-bar', ['value' => $r['total'], 'max' => $maxCust, 'label' => number_format($r['total'], 2), 'color' => '#10b981'])</td>
                                <td>{{ $summary['total'] > 0 ? round($r['total'] * 100 / $summary['total'], 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="tv-empty">{{ __('transport.no_invoices_in_period') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tv-card tv-pad">
            <div class="tv-section-title" style="margin-bottom:.8rem">🧾 {{ __('transport.invoices') }} <span class="tv-count">{{ $summary['count'] }}</span></div>
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>{{ __('transport.invoice_number') }}</th><th>{{ __('transport.date') }}</th><th>{{ __('transport.customer') }}</th><th>{{ __('transport.type') }}</th>
                        <th>{{ __('transport.trips_count') }}</th><th>{{ __('transport.before_tax') }}</th><th>{{ __('transport.tax_amount') }}</th><th>{{ __('transport.grand_total') }}</th><th>ZATCA</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td style="font-weight:800"><a href="{{ route('transport.invoices.show', $inv) }}" class="hover:underline" style="color:#0F1B4C">{{ $inv->invoice_number }}</a></td>
                                <td>{{ $inv->issue_date?->format('Y-m-d') }}</td>
                                <td>{{ $inv->customer?->name ?? '-' }}</td>
                                <td>@if ($inv->isSimplified())<span class="tv-pill tv-pill-gray">{{ __('transport.simplified') }}</span>@else<span class="tv-pill tv-pill-blue">{{ __('transport.tax_invoice_short') }}</span>@endif</td>
                                <td>{{ $inv->trips_count }}</td>
                                <td>{{ number_format((float) $inv->subtotal, 2) }}</td>
                                <td>{{ number_format((float) $inv->tax_amount, 2) }}</td>
                                <td style="font-weight:800">{{ number_format((float) $inv->total, 2) }}</td>
                                <td>@if ($inv->is_sent_to_zatca)<span class="tv-pill tv-pill-green">✓</span>@elseif ($inv->zatca_status === 'FAIL')<span class="tv-pill tv-pill-red">✗</span>@else<span class="tv-pill tv-pill-gray">—</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="tv-empty">{{ __('transport.no_invoices_in_period') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if ($invoices->count())
                        <tfoot><tr>
                            <td colspan="5">{{ __('transport.period_total') }}</td>
                            <td>{{ number_format($summary['subtotal'], 2) }}</td><td>{{ number_format($summary['tax'], 2) }}</td><td>{{ number_format($summary['total'], 2) }}</td><td></td>
                        </tr></tfoot>
                    @endif
                </table>
            </div>
            @if ($summary['count'] > $invoices->count())
                <div class="tv-hint">{{ __('transport.list_limited', ['n' => $invoices->count()]) }}</div>
            @endif
        </div>
    </div>

    @include('transport.partials.tv-select-script')
    @if ($monthly->count() > 1)
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                if (typeof Chart === 'undefined') return;
                new Chart(document.getElementById('ch-monthly'), {
                    type: 'bar',
                    data: {
                        labels: @json($monthly->pluck('month')),
                        datasets: [
                            { label: @json(__('transport.before_tax')), data: @json($monthly->pluck('subtotal')), backgroundColor: '#1456E8', borderRadius: 6, stack: 's' },
                            { label: @json(__('transport.vat')), data: @json($monthly->pluck('tax')), backgroundColor: '#F5B041', borderRadius: 6, stack: 's' },
                        ],
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true } } },
                });
            })();
        </script>
        @endpush
    @endif
</x-app-layout>
