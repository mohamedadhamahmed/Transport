<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .fr-cards { display:grid; grid-template-columns:repeat(2,1fr); gap:.75rem; }
        @media (min-width: 1000px) { .fr-cards { grid-template-columns:repeat(4,1fr); } }
        .fr-two { display:grid; grid-template-columns:1fr; gap:1rem; }
        @media (min-width: 1100px) { .fr-two { grid-template-columns:1fr 1fr; } }
        .fr-box { background:#fff; border:1px solid #f3f4f6; border-radius:.9rem; padding:1.1rem; }
        .fr-box h3 { font-weight:800; color:#0F1B4C; margin-bottom:.8rem; }
        .fr-chart { position:relative; height:300px; }
        .fr-table th { background:#0F1B4C; color:rgba(255,255,255,.85); font-size:.72rem; padding:.6rem .5rem; text-align:start; white-space:nowrap; }
        .fr-table td { padding:.55rem .5rem; font-size:.82rem; border-bottom:1px solid #f3f4f6; }
        @media print { aside, header, nav, .no-print, footer { display:none !important; } main { padding:0 !important; } .fr-chart { height:240px; } }
    </style>

    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-white font-bold text-lg">📊 {{ __('transport.fleet_report') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('transport.period') }}: {{ $from }} → {{ $to }}</p>
                </div>
                <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6]">🖨 {{ __('transport.print') }}</button>
            </div>

            <form method="GET" class="no-print bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4 flex flex-wrap gap-3 items-end">
                <div><label class="tr-label">{{ __('transport.date_from') }}</label><input type="date" name="date_from" value="{{ $from }}" class="tr-input"></div>
                <div><label class="tr-label">{{ __('transport.date_to') }}</label><input type="date" name="date_to" value="{{ $to }}" class="tr-input"></div>
                <button class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
            </form>

            <div class="fr-cards">
                <div class="tr-stat"><div class="l">{{ __('transport.total_expenses') }}</div><div class="v" style="color:#be123c">{{ number_format($totals['expenses'], 2) }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.maintenance_vouchers') }}</div><div class="v">{{ $totals['vouchers'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.loads_count') }}</div><div class="v" style="color:#ea580c">{{ $totals['loads'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.trips_revenue') }}</div><div class="v" style="color:#047857">{{ number_format($totals['revenue'], 2) }}</div></div>
            </div>

            <div class="fr-two">
                <div class="fr-box"><h3>🔧 {{ __('transport.top_expense_trucks') }}</h3><div class="fr-chart"><canvas id="ch-exp"></canvas></div></div>
                <div class="fr-box"><h3>📦 {{ __('transport.top_load_trucks') }}</h3><div class="fr-chart"><canvas id="ch-loads"></canvas></div></div>
                <div class="fr-box"><h3>📍 {{ __('transport.top_destinations') }}</h3><div class="fr-chart"><canvas id="ch-dest"></canvas></div></div>
                <div class="fr-box"><h3>🧾 {{ __('transport.expenses_by_category') }}</h3><div class="fr-chart"><canvas id="ch-cat"></canvas></div></div>
            </div>

            <div class="fr-box">
                <h3>🛣 {{ __('transport.top_routes') }}</h3>
                @forelse ($routes as $r)
                    <div class="flex justify-between text-sm" style="padding:.35rem 0;border-bottom:1px dashed #eee"><span>{{ $r['label'] }}</span><b>{{ $r['count'] }}</b></div>
                @empty
                    <p class="text-gray-400 text-sm">{{ __('transport.no_data') }}</p>
                @endforelse
            </div>

            <div class="fr-box">
                <h3>🚚 {{ __('transport.trucks_details') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full fr-table">
                        <thead><tr>
                            <th>{{ __('transport.truck') }}</th>
                            <th>{{ __('transport.ownership') }}</th>
                            <th>{{ __('transport.loads_count') }}</th>
                            <th>{{ __('transport.total_weight') }}</th>
                            <th>{{ __('transport.trips_count') }}</th>
                            <th>{{ __('transport.trips_revenue') }}</th>
                            <th>{{ __('transport.maintenance_only') }}</th>
                            <th>{{ __('transport.total_expenses') }}</th>
                            <th>{{ __('transport.net') }}</th>
                            <th class="no-print"></th>
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr>
                                    <td><b>{{ $r['truck']->plate_number }}</b> <span class="text-gray-500">{{ $r['truck']->name }}</span></td>
                                    <td>{{ ($r['truck']->ownership ?? 'owned') === 'owned' ? __('transport.owned') : __('transport.external_truck') }}</td>
                                    <td>{{ $r['loads'] }}</td>
                                    <td>{{ $r['weight'] ? rtrim(rtrim(number_format($r['weight'], 2), '0'), '.') : '-' }}</td>
                                    <td>{{ $r['trips'] }}</td>
                                    <td>{{ number_format($r['revenue'], 2) }}</td>
                                    <td>{{ number_format($r['maintenance'], 2) }}</td>
                                    <td style="color:#be123c;font-weight:700">{{ number_format($r['expenses'], 2) }}</td>
                                    <td style="font-weight:800;color:{{ $r['net'] >= 0 ? '#047857' : '#be123c' }}">{{ number_format($r['net'], 2) }}</td>
                                    <td class="no-print"><a class="tr-btn tr-btn-gray" href="{{ route('vouchers.index', ['type' => 'payment', 'maintenance' => 1, 'truck_id' => $r['truck']->id, 'date_from' => $from, 'date_to' => $to]) }}">{{ __('transport.maintenance_vouchers') }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = 'Cairo, sans-serif';
        const pal = ['#1456E8','#F5811E','#6B2FD6','#10b981','#e11d48','#0ea5e9','#eab308','#64748b','#14b8a6','#a855f7'];
        const bar = (id, labels, data, color, label) => new Chart(document.getElementById(id), {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor: color, borderRadius: 6 }] },
            options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } },
        });
        const doughnut = (id, labels, data) => new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: pal }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
        bar('ch-exp', @json($topExpenses->map(fn ($r) => $r['truck']->plate_number)), @json($topExpenses->pluck('expenses')), '#e11d48', @json(__('transport.total_expenses')));
        bar('ch-loads', @json($topLoads->map(fn ($r) => $r['truck']->plate_number)), @json($topLoads->pluck('loads')), '#F5811E', @json(__('transport.loads_count')));
        doughnut('ch-dest', @json($destinations->pluck('label')), @json($destinations->pluck('count')));
        doughnut('ch-cat', @json($expByCategory->pluck('label')), @json($expByCategory->pluck('total')));
    })();
    </script>
    @endpush
</x-app-layout>
