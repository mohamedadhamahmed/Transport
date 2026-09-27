<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    @php
        $fmt = fn ($n) => number_format((float) $n, 2);
        $maxTruck = $byTruck->max('net') ?: 0;
        $maxCat = $byCategory->max('net') ?: 0;
        $maxItem = $byItem->max('count') ?: 0;
        $catColors = ['#1456E8', '#F5811E', '#10b981', '#e11d48', '#8b5cf6', '#0ea5e9', '#f59e0b', '#64748b', '#14b8a6', '#a855f7'];
        $u = auth()->user();
    @endphp
    <style>
        .mr-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 1100px) { .mr-grid { grid-template-columns: 1fr 1fr; } }
        .mr-top { display: flex; flex-direction: column; gap: .15rem; }
        .mr-top b { font-size: 1.05rem; color: #0F1B4C; }
        .mr-top small { color: #6b7280; font-size: .75rem; }
        .mr-rank { display: inline-flex; width: 26px; height: 26px; border-radius: 8px; align-items: center; justify-content: center; font-weight: 800; font-size: .78rem; background: #f1f5f9; color: #475569; }
        .mr-rank.r1 { background: #fee2e2; color: #b91c1c; } .mr-rank.r2 { background: #ffedd5; color: #c2410c; } .mr-rank.r3 { background: #fef3c7; color: #b45309; }
        .mr-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
        .mr-chip { border: 1px solid #e5e7eb; border-radius: 99px; padding: .2rem .7rem; font-size: .75rem; color: #374151; background: #fff; }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.maintenance_report'),
            'badge' => $from . ' → ' . $to,
            'icon' => 'chart',
            'crumbs' => [['label' => __('transport.transport_reports'), 'url' => route('transport.reports.index')], ['label' => __('transport.maintenance_report')]],
            'buttons' => [
                ['label' => __('transport.export_excel'), 'url' => request()->fullUrlWithQuery(['export' => 'excel']), 'style' => 'green', 'icon' => 'excel'],
                ['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print'],
            ],
        ])
        @include('transport.partials.report-tabs', ['active' => 'maintenance'])

        {{-- الفلاتر --}}
        <form method="GET" class="tv-card tv-pad tv-no-print">
            <div class="tv-filters" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
                <div>
                    <label class="tv-label">{{ __('transport.date_from') }}</label>
                    <input type="date" name="date_from" value="{{ $from }}" class="tv-input">
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.date_to') }}</label>
                    <input type="date" name="date_to" value="{{ $to }}" class="tv-input">
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.truck') }}</label>
                    <select name="truck_id" class="tv-input tv-select">
                        <option value="">{{ __('transport.all') }}</option>
                        @foreach ($trucks as $t)
                            <option value="{{ $t->id }}" @selected((string) request('truck_id') === (string) $t->id)>{{ $t->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.mr_scope') }}</label>
                    <select name="scope" class="tv-input">
                        <option value="maintenance" @selected($scope === 'maintenance')>{{ __('transport.mr_scope_maintenance') }}</option>
                        <option value="all" @selected($scope === 'all')>{{ __('transport.mr_scope_all') }}</option>
                        @foreach ($categories as $k => $label)
                            <option value="{{ $k }}" @selected($scope === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.sort_by') }}</label>
                    <select name="sort" class="tv-input">
                        <option value="net" @selected($sort === 'net')>{{ __('transport.mr_sort_net') }}</option>
                        <option value="visits" @selected($sort === 'visits')>{{ __('transport.mr_sort_visits') }}</option>
                        <option value="per_load" @selected($sort === 'per_load')>{{ __('transport.mr_sort_per_load') }}</option>
                    </select>
                </div>
                <div style="align-self:end">
                    <button type="submit" class="tv-btn tv-btn-blue tv-btn-lg" style="min-width:100px">{{ __('transport.show') }}</button>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap" style="margin-top:.75rem">
                @php
                    $q = request()->except(['date_from', 'date_to', 'page', 'export']);
                    $presets = [
                        'this_month' => [now()->startOfMonth(), now()],
                        'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                        'this_quarter' => [now()->firstOfQuarter(), now()],
                        'this_year' => [now()->startOfYear(), now()],
                    ];
                @endphp
                @foreach ($presets as $k => [$a, $b])
                    <a href="{{ request()->url() . '?' . http_build_query($q + ['date_from' => $a->toDateString(), 'date_to' => $b->toDateString()]) }}" class="tv-tab" style="padding:.25rem .75rem;font-size:.72rem">{{ __('transport.preset_' . $k) }}</a>
                @endforeach
            </div>
        </form>

        {{-- المؤشرات --}}
        <div class="tv-kpis tv-kpis-5">
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/></svg>{{ __('transport.mr_total_cost') }}</div>
                <div class="v">{{ $fmt($summary['net']) }} <small>{{ __('transport.sar') }}</small></div>
                <div class="text-xs text-gray-500" style="margin-top:.2rem">{{ __('transport.vat') }}: {{ $fmt($summary['tax']) }}</div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#1456E8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>{{ __('transport.mr_visits') }}</div>
                <div class="v">{{ number_format($summary['visits']) }}</div>
                <div class="text-xs text-gray-500" style="margin-top:.2rem">{{ __('transport.mr_avg_visit') }}: {{ $fmt($summary['avg']) }}</div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>{{ __('transport.mr_trucks_serviced') }}</div>
                <div class="v">{{ $summary['trucks'] }} <small>{{ __('transport.mr_of_fleet', ['n' => $summary['fleet']]) }}</small></div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#F5811E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>{{ __('transport.mr_top_truck') }}</div>
                @if ($summary['top_truck'])
                    <div class="mr-top" style="margin-top:.45rem">
                        <b>{{ $summary['top_truck']['truck']?->plate_number ?? '#' . $summary['top_truck']['truck_id'] }}</b>
                        <small>{{ $fmt($summary['top_truck']['net']) }} · {{ $summary['top_truck']['visits'] }} {{ __('transport.mr_times') }}</small>
                    </div>
                @else
                    <div class="v">-</div>
                @endif
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9v9z"/><path d="M21 12h-9"/></svg>{{ __('transport.mr_top_category') }}</div>
                @if ($summary['top_category'])
                    <div class="mr-top" style="margin-top:.45rem">
                        <b>{{ $summary['top_category']['label'] }}</b>
                        <small>{{ $summary['top_category']['share'] }}% · {{ $summary['top_category']['visits'] }} {{ __('transport.mr_times') }}</small>
                    </div>
                @else
                    <div class="v">-</div>
                @endif
            </div>
        </div>

        {{-- أكثر الشاحنات صيانة --}}
        <div class="tv-card tv-pad">
            <div class="tv-section-title" style="margin-bottom:1rem">{{ __('transport.mr_by_truck') }}</div>
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>#</th><th>{{ __('transport.truck') }}</th><th>{{ __('transport.mr_total_cost') }}</th><th>{{ __('transport.mr_share') }}</th>
                        <th>{{ __('transport.mr_visits') }}</th><th>{{ __('transport.mr_top_category') }}</th><th>{{ __('transport.loads_count') }}</th>
                        <th>{{ __('transport.mr_per_load') }}</th><th>{{ __('transport.mr_last') }}</th><th class="tv-no-print"></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($byTruck as $i => $r)
                            <tr>
                                <td><span class="mr-rank {{ $i < 3 ? 'r' . ($i + 1) : '' }}">{{ $i + 1 }}</span></td>
                                <td style="font-weight:700;text-align:start">
                                    {{ $r['truck']?->plate_number ?? '#' . $r['truck_id'] }}
                                    <div class="text-xs text-gray-400">{{ $r['truck']?->name }}{{ $r['truck']?->type ? ' · ' . $r['truck']->type : '' }}</div>
                                </td>
                                <td>@include('transport.partials.rank-bar', ['value' => $r['net'], 'max' => $maxTruck, 'color' => '#e11d48', 'label' => $fmt($r['net'])])</td>
                                <td>{{ $r['share'] }}%</td>
                                <td>{{ $r['visits'] }}</td>
                                <td><span class="tv-pill tv-pill-gray">{{ $r['top_category'] }}</span></td>
                                <td>{{ $r['loads'] ?: '-' }}</td>
                                <td>{{ $r['per_load'] !== null ? $fmt($r['per_load']) : '-' }}</td>
                                <td>
                                    {{ $r['last'] ? \Carbon\Carbon::parse($r['last'])->format('Y-m-d') : '-' }}
                                    @if ($r['days_since'] !== null)<div class="text-xs text-gray-400">{{ __('transport.mr_days_since', ['n' => $r['days_since']]) }}</div>@endif
                                </td>
                                <td class="tv-no-print">
                                    <div class="flex gap-1.5">
                                        @if ($u?->can('maintenance.view'))
                                            <a href="{{ route('vouchers.index', ['type' => 'payment', 'maintenance' => 1, 'truck_id' => $r['truck_id']]) }}" class="tr-btn tr-btn-gray">{{ __('transport.maintenance_vouchers') }}</a>
                                        @endif
                                        @if ($u?->can('transport_reports.fleet'))
                                            <a href="{{ route('transport.reports.truck', ['truck_id' => $r['truck_id'], 'date_from' => $from, 'date_to' => $to]) }}" class="tr-btn tr-btn-blue">{{ __('transport.mr_statement') }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if ($byTruck->count())
                        <tfoot><tr>
                            <td colspan="2">{{ __('transport.period_total') }}</td>
                            <td>{{ $fmt($summary['net']) }}</td><td>100%</td><td>{{ $summary['visits'] }}</td><td colspan="5"></td>
                        </tr></tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="mr-grid">
            {{-- أكثر أنواع الصيانة --}}
            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:1rem">{{ __('transport.mr_by_category') }}</div>
                @if ($byCategory->count())
                    <div style="height:220px;margin-bottom:1rem"><canvas id="ch-cat"></canvas></div>
                @endif
                <div class="tv-table-wrap">
                    <table class="tv-table">
                        <thead><tr>
                            <th>{{ __('transport.expense_category') }}</th><th>{{ __('transport.mr_total_cost') }}</th><th>{{ __('transport.mr_share') }}</th>
                            <th>{{ __('transport.mr_visits') }}</th><th>{{ __('transport.mr_trucks_count') }}</th><th>{{ __('transport.mr_avg') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($byCategory as $i => $c)
                                <tr>
                                    <td style="font-weight:700;text-align:start"><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:{{ $catColors[$i % count($catColors)] }};margin-inline-end:.4rem"></span>{{ $c['label'] }}</td>
                                    <td>@include('transport.partials.rank-bar', ['value' => $c['net'], 'max' => $maxCat, 'color' => $catColors[$i % count($catColors)], 'label' => $fmt($c['net'])])</td>
                                    <td>{{ $c['share'] }}%</td>
                                    <td>{{ $c['visits'] }}</td>
                                    <td>{{ $c['trucks'] }}</td>
                                    <td>{{ $fmt($c['avg']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- أكثر البنود تكرارًا --}}
            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:1rem">{{ __('transport.mr_by_item') }}</div>
                <div class="tv-table-wrap">
                    <table class="tv-table">
                        <thead><tr>
                            <th>#</th><th>{{ __('transport.description') }}</th><th>{{ __('transport.mr_visits') }}</th>
                            <th>{{ __('transport.mr_trucks_count') }}</th><th>{{ __('transport.mr_total_cost') }}</th><th>{{ __('transport.mr_avg') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($byItem as $i => $it)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td style="font-weight:700;text-align:start">{{ \Illuminate\Support\Str::limit($it['label'], 60) }}<div class="text-xs text-gray-400">{{ $it['category'] }}</div></td>
                                    <td>@include('transport.partials.rank-bar', ['value' => $it['count'], 'max' => $maxItem, 'color' => '#F5811E'])</td>
                                    <td>{{ $it['trucks'] }}</td>
                                    <td>{{ $fmt($it['net']) }}</td>
                                    <td>{{ $fmt($it['avg']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="tv-hint">ⓘ {{ __('transport.mr_by_item_hint') }}</div>
            </div>
        </div>

        {{-- شهري --}}
        @if ($monthly->count() > 1)
            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:1rem">{{ __('transport.mr_monthly') }}</div>
                <div style="height:260px"><canvas id="ch-monthly"></canvas></div>
            </div>
        @endif

        {{-- شاحنات بدون صيانة --}}
        @if ($noMaintenance->count() && $noMaintenance->count() < $summary['fleet'])
            <div class="tv-card tv-pad">
                <div class="tv-section-title" style="margin-bottom:.8rem">{{ __('transport.mr_no_maintenance') }} ({{ $noMaintenance->count() }})</div>
                <div class="mr-chips">
                    @foreach ($noMaintenance as $t)
                        <span class="mr-chip">{{ $t->display_name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- تفاصيل السندات --}}
        <div class="tv-card tv-pad">
            <div class="tv-section-title" style="margin-bottom:1rem">{{ __('transport.mr_vouchers') }}</div>
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>{{ __('transport.voucher_number') }}</th><th>{{ __('transport.date') }}</th><th>{{ __('transport.truck') }}</th>
                        <th>{{ __('transport.expense_category') }}</th><th>{{ __('transport.description') }}</th>
                        <th>{{ __('transport.before_tax') }}</th><th>{{ __('transport.tax_amount') }}</th><th>{{ __('transport.grand_total') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($vouchers as $v)
                            <tr>
                                <td style="font-weight:800">
                                    @if ($u?->can('maintenance.view'))
                                        <a href="{{ route('vouchers.show', $v->id) }}" class="hover:underline" style="color:#1456E8">{{ $v->number }}</a>
                                    @else
                                        {{ $v->number }}
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($v->date)->format('Y-m-d') }}</td>
                                <td>{{ $v->truck?->plate_number ?? '-' }}</td>
                                <td>{{ $v->category }}</td>
                                <td style="text-align:start;max-width:320px">{{ \Illuminate\Support\Str::limit($v->items, 90) }}</td>
                                <td>{{ $fmt($v->net) }}</td>
                                <td>{{ $fmt($v->tax) }}</td>
                                <td style="font-weight:800">{{ $fmt($v->net + $v->tax) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if ($vouchers->count())
                        <tfoot><tr>
                            <td colspan="5">{{ __('transport.period_total') }}</td>
                            <td>{{ $fmt($summary['net']) }}</td><td>{{ $fmt($summary['tax']) }}</td><td>{{ $fmt($summary['net'] + $summary['tax']) }}</td>
                        </tr></tfoot>
                    @endif
                </table>
            </div>
            @if ($summary['visits'] > $vouchers->count())
                <div class="tv-hint">{{ __('transport.export_excel') }} ({{ $summary['visits'] }})</div>
            @endif
        </div>
    </div>

    @include('transport.partials.tv-select-script')
    @if ($byCategory->count() || $monthly->count() > 1)
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                if (typeof Chart === 'undefined') return;
                Chart.defaults.font.family = 'Cairo, sans-serif';
                const colors = @json($catColors);
                const cat = document.getElementById('ch-cat');
                if (cat) {
                    new Chart(cat, {
                        type: 'doughnut',
                        data: {
                            labels: @json($byCategory->pluck('label')),
                            datasets: [{ data: @json($byCategory->pluck('net')), backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }],
                        },
                        options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } },
                    });
                }
                const mon = document.getElementById('ch-monthly');
                if (mon) {
                    new Chart(mon, {
                        data: {
                            labels: @json($monthly->pluck('month')),
                            datasets: [
                                { type: 'bar', label: @json(__('transport.mr_total_cost')), data: @json($monthly->pluck('net')), backgroundColor: '#e11d48', borderRadius: 6, yAxisID: 'y' },
                                { type: 'line', label: @json(__('transport.mr_visits')), data: @json($monthly->pluck('visits')), borderColor: '#1456E8', backgroundColor: '#1456E8', tension: .3, yAxisID: 'y1' },
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 } } },
                        },
                    });
                }
            })();
        </script>
        @endpush
    @endif
</x-app-layout>
