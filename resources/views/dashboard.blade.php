<x-app-layout>
    @include('transport.partials.tv-styles')
    @php
        $u = auth()->user();
        $canMoney = $u?->can('transport_invoices.view');
        $canFleet = $u?->can('truck_loads.view');
        $canExp = $u?->can('transport_reports.fleet') || $u?->can('maintenance.view');
        $canCash = $u?->can('accounts.view') || $u?->can('vouchers.view');
        $hour = (int) now('Asia/Riyadh')->format('H');
        $greet = $hour < 12 ? __('transport.db_good_morning') : __('transport.db_good_evening');
        $money = fn ($v) => number_format((float) $v, 2);
        $short = function ($v) {
            $v = (float) $v; $a = abs($v);
            if ($a >= 1000000) return rtrim(rtrim(number_format($v / 1000000, 2), '0'), '.') . ' ' . __('transport.db_million');
            if ($a >= 10000) return rtrim(rtrim(number_format($v / 1000, 1), '0'), '.') . ' ' . __('transport.db_thousand');
            return number_format($v, 0);
        };
        $trend = function ($pct, $inverse = false) {
            if ($pct === null) return '';
            $up = $pct >= 0; $good = $inverse ? !$up : $up;
            return '<span class="db-trend ' . ($good ? 'up' : 'down') . '">' . ($up ? '▲' : '▼') . ' ' . abs($pct) . '%</span>';
        };
        $quick = collect([
            $u?->can('truck_loads.manage') ? ['label' => __('transport.db_load_truck'), 'url' => route('transport.loads.board', ['status' => 'empty']), 'icon' => '📦'] : null,
            $u?->can('transport_invoices.create') ? ['label' => __('transport.new_invoice'), 'url' => route('transport.invoices.create'), 'icon' => '🧾'] : null,
            $u?->can('waybills.create') ? ['label' => __('transport.new_waybill'), 'url' => route('transport.waybills.create'), 'icon' => '📄'] : null,
            $u?->can('maintenance.create') ? ['label' => __('transport.new_maintenance'), 'url' => route('vouchers.create', ['type' => 'payment', 'maintenance' => 1]), 'icon' => '🔧'] : null,
            $u?->can('vouchers.create') ? ['label' => __('vouchers.new_receipt'), 'url' => route('vouchers.create', ['type' => 'receipt']), 'icon' => '💵'] : null,
            $u?->can('customers.create') ? ['label' => __('customers.new_customer'), 'url' => route('customers.create'), 'icon' => '👤'] : null,
        ])->filter()->values();
        $f = $fleet;
        $seg = fn ($n) => $f['total'] ? ($n * 100 / $f['total']) : 0;
    @endphp

    <style>
        .db { display: flex; flex-direction: column; gap: 1.25rem; max-width: 1680px; margin: 0 auto; }
        .db-hero { position: relative; overflow: hidden; border-radius: 20px; padding: 1.5rem 1.75rem; color: #fff;
            background: radial-gradient(1200px 300px at 10% -40%, rgba(20,86,232,.55), transparent 60%), linear-gradient(135deg, #0B1640 0%, #13235C 55%, #1B2C63 100%); box-shadow: 0 10px 30px rgba(15,27,76,.18); }
        .db-hero::after { content: ''; position: absolute; inset-inline-end: -40px; bottom: -60px; width: 260px; height: 260px; border-radius: 50%; background: rgba(245,129,30,.12); }
        .db-hero-top { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
        .db-hero h1 { font-size: 1.45rem; font-weight: 800; }
        .db-hero p { color: rgba(255,255,255,.62); font-size: .85rem; margin-top: .25rem; }
        .db-clock { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: .5rem .9rem; text-align: center; }
        .db-clock b { font-size: 1.15rem; font-variant-numeric: tabular-nums; display: block; }
        .db-clock span { font-size: .72rem; color: rgba(255,255,255,.6); }
        .db-hero-stats { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-top: 1.25rem; }
        @media (min-width: 900px) { .db-hero-stats { grid-template-columns: repeat(4, 1fr); } }
        .db-hs { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1); border-radius: 14px; padding: .8rem 1rem; }
        .db-hs .l { font-size: .74rem; color: rgba(255,255,255,.6); }
        .db-hs .v { font-size: 1.35rem; font-weight: 800; margin-top: .2rem; }
        .db-hs .v small { font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.55); }
        .db-quick { position: relative; z-index: 1; display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1.1rem; }
        .db-quick a { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .9rem; border-radius: 10px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); font-size: .8rem; font-weight: 700; color: #fff; transition: .15s; }
        .db-quick a:hover { background: #fff; color: #0F1B4C; }
        .db-branch { position: relative; z-index: 1; }
        .db-branch select { background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.2); border-radius: 10px; padding: .35rem 2rem .35rem .75rem; font-size: .8rem; }
        .db-branch option { color: #111; }

        .db-kpis { display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem; }
        @media (min-width: 640px) { .db-kpis { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1200px) { .db-kpis { grid-template-columns: repeat(4, 1fr); } }
        .db-kpi { background: #fff; border: 1px solid #eef0f5; border-radius: 16px; padding: 1.1rem 1.2rem; position: relative; overflow: hidden; transition: .15s; display: block; }
        .db-kpi:hover { box-shadow: 0 8px 22px rgba(15,27,76,.07); transform: translateY(-2px); }
        .db-kpi .ic { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .db-kpi .l { font-size: .78rem; font-weight: 700; color: #6b7280; margin-top: .8rem; }
        .db-kpi .v { font-size: 1.6rem; font-weight: 800; color: #0F1B4C; margin-top: .15rem; line-height: 1.2; }
        .db-kpi .v small { font-size: .75rem; color: #9ca3af; font-weight: 600; }
        .db-kpi .s { font-size: .74rem; color: #9ca3af; margin-top: .35rem; display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
        .db-kpi .top { display: flex; justify-content: space-between; align-items: flex-start; }
        .db-trend { font-size: .72rem; font-weight: 800; padding: .15rem .5rem; border-radius: 99px; }
        .db-trend.up { background: #ecfdf5; color: #047857; }
        .db-trend.down { background: #fff1f2; color: #be123c; }

        .db-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 1200px) { .db-grid-2-1 { grid-template-columns: 2fr 1fr; } .db-grid-3 { grid-template-columns: repeat(3, 1fr); } .db-grid-1-1 { grid-template-columns: 1fr 1fr; } }
        .db-card { background: #fff; border: 1px solid #eef0f5; border-radius: 16px; padding: 1.2rem 1.3rem; min-width: 0; }
        .db-card-h { display: flex; justify-content: space-between; align-items: center; gap: .5rem; margin-bottom: 1rem; }
        .db-card-h h3 { font-weight: 800; color: #0F1B4C; font-size: .98rem; display: flex; align-items: center; gap: .45rem; }
        .db-card-h a { font-size: .75rem; font-weight: 700; color: #1456E8; }

        .db-fleet-bar { display: flex; height: 14px; border-radius: 99px; overflow: hidden; background: #f1f5f9; gap: 2px; }
        .db-fleet-bar span { display: block; height: 100%; }
        .db-fleet-legend { display: grid; grid-template-columns: repeat(2, 1fr); gap: .6rem; margin-top: 1rem; }
        @media (min-width: 700px) { .db-fleet-legend { grid-template-columns: repeat(4, 1fr); } }
        .db-fl { border: 1px solid #eef0f5; border-radius: 12px; padding: .6rem .8rem; display: block; }
        .db-fl:hover { border-color: #cddcfb; }
        .db-fl .n { font-size: 1.4rem; font-weight: 800; }
        .db-fl .t { font-size: .74rem; color: #6b7280; display: flex; align-items: center; gap: .35rem; }
        .db-fl .t i { width: 9px; height: 9px; border-radius: 3px; display: inline-block; }
        .db-ring { --p: 0; width: 92px; height: 92px; border-radius: 50%; background: conic-gradient(#1456E8 calc(var(--p) * 1%), #eef2ff 0); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .db-ring div { width: 72px; height: 72px; border-radius: 50%; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .db-ring b { font-size: 1.2rem; color: #0F1B4C; font-weight: 800; }
        .db-ring small { font-size: .62rem; color: #6b7280; }

        .db-list { display: flex; flex-direction: column; }
        .db-li { display: flex; align-items: center; gap: .75rem; padding: .6rem 0; border-bottom: 1px dashed #eef0f5; }
        .db-li:last-child { border-bottom: 0; }
        .db-li .rk { width: 26px; height: 26px; border-radius: 8px; background: #f1f5f9; color: #475569; font-size: .75rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .db-li .nm { flex: 1; min-width: 0; font-size: .85rem; font-weight: 700; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .db-li .nm small { display: block; font-weight: 500; color: #9ca3af; font-size: .72rem; }
        .db-li .vl { font-size: .85rem; font-weight: 800; color: #0F1B4C; white-space: nowrap; }
        .db-li .bar { height: 5px; border-radius: 99px; background: #f1f5f9; margin-top: .3rem; overflow: hidden; }
        .db-li .bar span { display: block; height: 100%; border-radius: 99px; }

        .db-truck { display: flex; align-items: center; gap: .75rem; padding: .65rem .75rem; border-radius: 12px; border: 1px solid #eef0f5; border-inline-start: 4px solid #F5811E; }
        .db-truck + .db-truck { margin-top: .5rem; }
        .db-truck.late { border-inline-start-color: #e11d48; background: #fff8f8; }
        .db-truck .p { font-weight: 800; color: #0F1B4C; font-size: .9rem; }
        .db-truck .r { font-size: .76rem; color: #6b7280; }
        .db-truck .tm { margin-inline-start: auto; font-size: .72rem; font-weight: 800; padding: .25rem .55rem; border-radius: 8px; background: #fff7ed; color: #c2410c; white-space: nowrap; }
        .db-truck.late .tm { background: #ffe4e6; color: #be123c; }

        .db-alert { display: flex; gap: .7rem; align-items: center; padding: .7rem .8rem; border-radius: 12px; font-size: .82rem; font-weight: 600; }
        .db-alert + .db-alert { margin-top: .5rem; }
        .db-alert .n { margin-inline-start: auto; font-weight: 800; font-size: .95rem; }
        .db-alert.red { background: #fff1f2; color: #9f1239; }
        .db-alert.amber { background: #fffbeb; color: #92400e; }
        .db-alert.blue { background: #eef4ff; color: #1e3a8a; }
        .db-alert.green { background: #ecfdf5; color: #065f46; }

        .db-act { display: flex; gap: .75rem; padding: .55rem 0; align-items: flex-start; }
        .db-act .dot { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
        .db-act .t { font-size: .82rem; font-weight: 700; color: #1f2937; }
        .db-act .s { font-size: .72rem; color: #9ca3af; }
        .db-regions { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: 1rem; }
        .db-regions a { font-size: .74rem; font-weight: 700; padding: .3rem .65rem; border-radius: 99px; background: #ecfdf5; color: #047857; }
        .db-empty { text-align: center; color: #9ca3af; font-size: .82rem; padding: 1.5rem 0; }
    </style>

    <div class="db">
        {{-- ===== الهيدر ===== --}}
        <div class="db-hero">
            <div class="db-hero-top">
                <div>
                    <h1>{{ $greet }}{{ $u?->name ? '، ' . $u->name : '' }} 👋</h1>
                    <p>{{ __('transport.db_subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($branches->count() > 1 && $canMoney)
                        <form method="GET" class="db-branch">
                            <select name="branch_id" onchange="this.form.submit()">
                                <option value="">{{ __('messages.dashboard_all_branches') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId === $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <div class="db-clock"><b id="db-time">--:--</b><span id="db-date"></span></div>
                </div>
            </div>

            <div class="db-hero-stats">
                @if ($canMoney)
                    <div class="db-hs"><div class="l">{{ __('transport.db_today_invoices') }}</div><div class="v">{{ $money($kpi['today_revenue']) }} <small>{{ __('transport.sar') }} · {{ $kpi['today_invoices'] }} {{ __('transport.db_invoice_unit') }}</small></div></div>
                @endif
                @if ($canFleet)
                    <div class="db-hs"><div class="l">{{ __('transport.db_on_road_now') }}</div><div class="v">{{ $f['loaded'] + $f['overdue'] }} <small>/ {{ $f['total'] }} {{ __('transport.db_truck_unit') }}</small></div></div>
                    <div class="db-hs"><div class="l">{{ __('transport.db_empty_ready') }}</div><div class="v">{{ $f['empty'] }} <small>{{ __('transport.db_truck_unit') }}</small></div></div>
                    <div class="db-hs"><div class="l">{{ __('transport.db_overdue_now') }}</div><div class="v" style="color:{{ $f['overdue'] ? '#fda4af' : '#fff' }}">{{ $f['overdue'] }} <small>{{ __('transport.db_truck_unit') }}</small></div></div>
                @endif
            </div>

            @if ($quick->isNotEmpty())
                <div class="db-quick">
                    @foreach ($quick as $q)
                        <a href="{{ $q['url'] }}"><span>{{ $q['icon'] }}</span>{{ $q['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===== مؤشرات الشهر ===== --}}
        @if ($canMoney || $canFleet)
            <div class="db-kpis">
                @if ($canMoney)
                    <a href="{{ route('transport.reports.sales') }}" class="db-kpi">
                        <div class="top"><span class="ic" style="background:#eaf1ff">💵</span>{!! $trend($kpi['revenue_change']) !!}</div>
                        <div class="l">{{ __('transport.db_month_revenue') }}</div>
                        <div class="v">{{ $short($kpi['revenue']) }} <small>{{ __('transport.sar') }}</small></div>
                        <div class="s">{{ $kpi['invoices'] }} {{ __('transport.db_invoice_unit') }} · {{ __('transport.db_incl_vat') }} {{ $money($kpi['total_incl']) }}</div>
                    </a>
                @endif
                @if ($canMoney && $canExp)
                    <a href="{{ route('transport.reports.fleet') }}" class="db-kpi">
                        <div class="top"><span class="ic" style="background:#ecfdf5">📈</span>{!! $trend($kpi['profit_change']) !!}</div>
                        <div class="l">{{ __('transport.db_month_profit') }}</div>
                        <div class="v" style="color:{{ $kpi['profit'] >= 0 ? '#047857' : '#be123c' }}">{{ $short($kpi['profit']) }} <small>{{ __('transport.sar') }}</small></div>
                        <div class="s">{{ __('transport.db_truck_expenses') }} {{ $money($kpi['expenses']) }}</div>
                    </a>
                @endif
                @if ($canFleet)
                    <a href="{{ route('transport.loads.report') }}" class="db-kpi">
                        <div class="top"><span class="ic" style="background:#fff7ed">📦</span>{!! $trend($kpi['loads_change']) !!}</div>
                        <div class="l">{{ __('transport.db_month_loads') }}</div>
                        <div class="v">{{ number_format($kpi['loads']) }} <small>{{ __('transport.db_load_unit') }}</small></div>
                        <div class="s">{{ __('transport.db_vs_last_month') }}</div>
                    </a>
                @endif
                @if ($canMoney)
                    <a href="{{ route('transport.reports.unbilled') }}" class="db-kpi">
                        <div class="top"><span class="ic" style="background:#fff1f2">⏳</span>@if ($kpi['unbilled_count'])<span class="db-trend down">{{ __('transport.db_needs_billing') }}</span>@endif</div>
                        <div class="l">{{ __('transport.unbilled_loads') }}</div>
                        <div class="v">{{ $kpi['unbilled_count'] }} <small>· {{ $money($kpi['unbilled_value']) }} {{ __('transport.sar') }}</small></div>
                        <div class="s">{{ __('transport.db_unbilled_hint') }}</div>
                    </a>
                @endif
                @if ($canCash)
                    <div class="db-kpi">
                        <div class="top"><span class="ic" style="background:#f5f3ff">🏦</span></div>
                        <div class="l">{{ __('transport.db_cash') }}</div>
                        <div class="v">{{ $short($kpi['cash']) }} <small>{{ __('transport.sar') }}</small></div>
                        <div class="s">{{ __('transport.db_cash_hint') }}</div>
                    </div>
                @endif
                @if ($canMoney)
                    <a href="{{ route('transport.reports.customers', ['sort' => 'total']) }}" class="db-kpi">
                        <div class="top"><span class="ic" style="background:#fffbeb">👥</span></div>
                        <div class="l">{{ __('transport.db_receivables') }}</div>
                        <div class="v">{{ $short($kpi['receivables']) }} <small>{{ __('transport.sar') }}</small></div>
                        <div class="s">{{ __('transport.db_receivables_hint') }}</div>
                    </a>
                @endif
            </div>
        @endif

        {{-- ===== الأسطول + التنبيهات ===== --}}
        <div class="db-grid db-grid-2-1">
            @if ($canFleet)
                <div class="db-card">
                    <div class="db-card-h">
                        <h3>🚚 {{ __('transport.db_fleet_status') }}</h3>
                        <a href="{{ route('transport.loads.board') }}">{{ __('transport.board_title') }} ←</a>
                    </div>
                    <div class="flex items-center gap-5 flex-wrap">
                        <div class="db-ring" style="--p: {{ $f['utilization'] }}"><div><b>{{ $f['utilization'] }}%</b><small>{{ __('transport.utilization') }}</small></div></div>
                        <div style="flex:1;min-width:240px">
                            <div class="db-fleet-bar" role="img" aria-label="{{ __('transport.db_fleet_status') }}">
                                @if ($f['loaded'])<span style="width:{{ $seg($f['loaded']) }}%;background:#F5811E" title="{{ __('transport.loaded_trucks') }}: {{ $f['loaded'] }}"></span>@endif
                                @if ($f['overdue'])<span style="width:{{ $seg($f['overdue']) }}%;background:#e11d48" title="{{ __('transport.overdue_trucks') }}: {{ $f['overdue'] }}"></span>@endif
                                @if ($f['empty'])<span style="width:{{ $seg($f['empty']) }}%;background:#10b981" title="{{ __('transport.empty_trucks') }}: {{ $f['empty'] }}"></span>@endif
                                @if ($f['maintenance'])<span style="width:{{ $seg($f['maintenance']) }}%;background:#94a3b8" title="{{ __('transport.status_maintenance') }}: {{ $f['maintenance'] }}"></span>@endif
                            </div>
                            <div class="db-fleet-legend">
                                <a href="{{ route('transport.loads.board', ['status' => 'loaded']) }}" class="db-fl"><div class="n" style="color:#c2410c">{{ $f['loaded'] }}</div><div class="t"><i style="background:#F5811E"></i>{{ __('transport.loaded_trucks') }}</div></a>
                                <a href="{{ route('transport.loads.board', ['status' => 'overdue']) }}" class="db-fl"><div class="n" style="color:#be123c">{{ $f['overdue'] }}</div><div class="t"><i style="background:#e11d48"></i>{{ __('transport.overdue_trucks') }}</div></a>
                                <a href="{{ route('transport.loads.board', ['status' => 'empty']) }}" class="db-fl"><div class="n" style="color:#047857">{{ $f['empty'] }}</div><div class="t"><i style="background:#10b981"></i>{{ __('transport.empty_trucks') }}</div></a>
                                <a href="{{ route('transport.loads.board', ['status' => 'maintenance']) }}" class="db-fl"><div class="n" style="color:#475569">{{ $f['maintenance'] }}</div><div class="t"><i style="background:#94a3b8"></i>{{ __('transport.status_maintenance') }}</div></a>
                            </div>
                        </div>
                    </div>

                    @if ($emptyByRegion->isNotEmpty())
                        <div class="db-regions">
                            <span class="text-xs text-gray-500 font-bold" style="align-self:center">{{ __('transport.db_empty_where') }}</span>
                            @foreach ($emptyByRegion as $r)
                                <a href="{{ route('transport.loads.board', ['status' => 'empty', 'region' => $r['key'] === '_none' ? null : $r['key']]) }}">📍 {{ $r['label'] }} · {{ $r['count'] }}</a>
                            @endforeach
                        </div>
                    @endif

                    <div class="db-grid db-grid-1-1" style="margin-top:1.1rem">
                        <div>
                            <div class="text-xs font-bold text-rose-700" style="margin-bottom:.5rem">🔴 {{ __('transport.db_overdue_list') }}</div>
                            @forelse ($overdue as $t)
                                <div class="db-truck late">
                                    <div><div class="p">{{ $t->plate_number }}</div><div class="r">{{ $t->activeLoad->from_label }} ← {{ $t->activeLoad->to_label }}{{ $t->activeLoad->driver ? ' · ' . $t->activeLoad->driver->name : '' }}</div></div>
                                    <span class="tm">{{ $t->activeLoad->remainingText() }}</span>
                                </div>
                            @empty
                                <div class="db-alert green">✓ {{ __('transport.db_no_overdue') }}</div>
                            @endforelse
                        </div>
                        <div>
                            <div class="text-xs font-bold text-orange-700" style="margin-bottom:.5rem">⏱ {{ __('transport.db_next_unloads') }}</div>
                            @forelse ($upcoming as $t)
                                <div class="db-truck">
                                    <div><div class="p">{{ $t->plate_number }}</div><div class="r">{{ $t->activeLoad->from_label }} ← {{ $t->activeLoad->to_label }}{{ $t->activeLoad->customer ? ' · ' . $t->activeLoad->customer->name : '' }}</div></div>
                                    <span class="tm">{{ $t->activeLoad->remainingText() }}</span>
                                </div>
                            @empty
                                <div class="db-empty">{{ __('transport.db_no_loaded') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <div class="db-card">
                <div class="db-card-h"><h3>🔔 {{ __('transport.db_attention') }}</h3></div>
                @php $anyAlert = false; @endphp
                @if ($canFleet && $f['overdue'])
                    @php $anyAlert = true; @endphp
                    <a href="{{ route('transport.loads.board', ['status' => 'overdue']) }}" class="db-alert red">⏰ {{ __('transport.db_alert_overdue') }}<span class="n">{{ $f['overdue'] }}</span></a>
                @endif
                @if ($u?->can('trucks.view') && $alerts['docsAlert'])
                    @php $anyAlert = true; @endphp
                    <a href="{{ route('transport.trucks.index', ['docs' => 'alert']) }}" class="db-alert {{ $alerts['docsExpired'] ? 'red' : 'amber' }}">🪪 {{ __('transport.db_alert_docs', ['expired' => $alerts['docsExpired']]) }}<span class="n">{{ $alerts['docsAlert'] }}</span></a>
                @endif
                @if ($canMoney && $kpi['unbilled_count'])
                    @php $anyAlert = true; @endphp
                    <a href="{{ route('transport.reports.unbilled') }}" class="db-alert amber">⏳ {{ __('transport.db_alert_unbilled') }}<span class="n">{{ $kpi['unbilled_count'] }}</span></a>
                @endif
                @if ($canMoney && $alerts['noPrice'])
                    @php $anyAlert = true; @endphp
                    <a href="{{ route('transport.reports.unbilled') }}" class="db-alert amber">🏷 {{ __('transport.db_alert_no_price') }}<span class="n">{{ $alerts['noPrice'] }}</span></a>
                @endif
                @if ($u?->can('zatca.view') && $alerts['zatcaFailed'])
                    @php $anyAlert = true; @endphp
                    <a href="{{ route('transport.zatca.index', ['sent' => 0, 'status' => 'FAIL']) }}" class="db-alert red">🏛 {{ __('transport.db_alert_zatca_failed') }}<span class="n">{{ $alerts['zatcaFailed'] }}</span></a>
                @endif
                @if ($u?->can('zatca.view') && $alerts['zatcaPending'])
                    @php $anyAlert = true; @endphp
                    <a href="{{ route('transport.zatca.index', ['sent' => 0]) }}" class="db-alert blue">🏛 {{ __('transport.db_alert_zatca_pending') }}<span class="n">{{ $alerts['zatcaPending'] }}</span></a>
                @endif
                @unless ($anyAlert)
                    <div class="db-alert green">✓ {{ __('transport.db_all_good') }}</div>
                @endunless

                <div class="db-card-h" style="margin-top:1.3rem;margin-bottom:.4rem"><h3>🕘 {{ __('transport.db_recent_activity') }}</h3></div>
                @forelse ($activity as $a)
                    @continue($a['type'] === 'invoice' ? !$canMoney : !$canFleet)
                    <a href="{{ $a['url'] }}" class="db-act">
                        <span class="dot" style="background:{{ ['loaded' => '#fff7ed', 'unloaded' => '#ecfdf5', 'invoice' => '#eaf1ff'][$a['type']] }}">{{ ['loaded' => '📦', 'unloaded' => '✅', 'invoice' => '🧾'][$a['type']] }}</span>
                        <div style="min-width:0">
                            <div class="t">{{ __('transport.db_act_' . $a['type']) }} · {{ $a['title'] }}</div>
                            <div class="s">{{ $a['sub'] ? $a['sub'] . ' · ' : '' }}{{ $a['at']?->diffForHumans() }}</div>
                        </div>
                    </a>
                @empty
                    <div class="db-empty">{{ __('transport.no_data') }}</div>
                @endforelse
            </div>
        </div>

        {{-- ===== الرسوم ===== --}}
        <div class="db-grid db-grid-2-1">
            @if ($canMoney)
                <div class="db-card">
                    <div class="db-card-h">
                        <h3>📊 {{ $canExp ? __('transport.db_revenue_vs_expenses') : __('transport.db_revenue_6m') }}</h3>
                        <a href="{{ route('transport.reports.sales', ['date_from' => now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString()]) }}">{{ __('transport.db_details') }} ←</a>
                    </div>
                    <div style="position:relative;height:280px"><canvas id="db-finance" aria-label="{{ __('transport.db_revenue_vs_expenses') }}"></canvas></div>
                    <details style="margin-top:.6rem">
                        <summary class="text-xs text-gray-500 cursor-pointer">{{ __('transport.db_show_table') }}</summary>
                        <div class="tv-table-wrap" style="margin-top:.5rem">
                            <table class="tv-table">
                                <thead><tr><th>{{ __('transport.db_month') }}</th><th>{{ __('transport.db_revenue') }}</th>@if ($canExp)<th>{{ __('transport.total_expenses') }}</th><th>{{ __('transport.net') }}</th>@endif</tr></thead>
                                <tbody>
                                    @foreach ($finance as $r)
                                        <tr><td>{{ $r['label'] }}</td><td>{{ $money($r['revenue']) }}</td>@if ($canExp)<td>{{ $money($r['expenses']) }}</td><td style="font-weight:800;color:{{ $r['net'] >= 0 ? '#047857' : '#be123c' }}">{{ $money($r['net']) }}</td>@endif</tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            @endif
            @if ($canFleet)
                <div class="db-card">
                    <div class="db-card-h">
                        <h3>📦 {{ __('transport.db_loads_14d') }}</h3>
                        <a href="{{ route('transport.loads.report') }}">{{ __('transport.db_details') }} ←</a>
                    </div>
                    <div style="position:relative;height:280px"><canvas id="db-loads" aria-label="{{ __('transport.db_loads_14d') }}"></canvas></div>
                </div>
            @endif
        </div>

        {{-- ===== الأكثر (الشهر ده) ===== --}}
        <div class="db-grid db-grid-3">
            @php
                $tops = array_filter([
                    $canMoney ? ['title' => '🏆 ' . __('transport.db_top_customers'), 'rows' => $topCustomers, 'color' => '#1456E8', 'money' => true, 'url' => route('transport.reports.customers', ['sort' => 'total']), 'subLabel' => __('transport.db_invoice_unit')] : null,
                    $canFleet ? ['title' => '📍 ' . __('transport.db_top_destinations'), 'rows' => $topDestinations, 'color' => '#F5811E', 'money' => false, 'url' => route('transport.reports.routes'), 'subLabel' => null] : null,
                    $canFleet ? ['title' => '🚚 ' . __('transport.db_top_trucks'), 'rows' => $topTrucks, 'color' => '#6B2FD6', 'money' => false, 'url' => $u?->can('transport_reports.fleet') ? route('transport.reports.fleet') : route('transport.loads.report'), 'subLabel' => __('transport.ton')] : null,
                ]);
            @endphp
            @foreach ($tops as $t)
                @php $mx = collect($t['rows'])->max('value') ?: 0; @endphp
                <div class="db-card">
                    <div class="db-card-h"><h3>{{ $t['title'] }}</h3><a href="{{ $t['url'] }}">{{ __('transport.db_details') }} ←</a></div>
                    <div class="db-list">
                        @forelse ($t['rows'] as $i => $r)
                            <div class="db-li">
                                <span class="rk">{{ $i + 1 }}</span>
                                <div class="nm">
                                    {{ $r['label'] }}
                                    @if ($t['subLabel'] && !empty($r['sub']))<small>{{ is_float($r['sub']) ? rtrim(rtrim(number_format($r['sub'], 2), '0'), '.') : $r['sub'] }} {{ $t['subLabel'] }}</small>@endif
                                    <div class="bar"><span style="width:{{ $mx ? max(3, $r['value'] * 100 / $mx) : 0 }}%;background:{{ $t['color'] }}"></span></div>
                                </div>
                                <span class="vl">{{ $t['money'] ? $short($r['value']) : $r['value'] }}</span>
                            </div>
                        @empty
                            <div class="db-empty">{{ __('transport.db_no_month_data') }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            // الساعة والتاريخ
            const loc = @json(app()->getLocale() === 'ar' ? 'ar-SA-u-ca-gregory-nu-latn' : 'en-GB');
            function tick() {
                const n = new Date();
                const t = document.getElementById('db-time'), d = document.getElementById('db-date');
                if (t) t.textContent = n.toLocaleTimeString(loc, { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Riyadh' });
                if (d) d.textContent = n.toLocaleDateString(loc, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Riyadh' });
            }
            tick(); setInterval(tick, 15000);

            if (typeof Chart === 'undefined') return;
            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.color = '#6b7280';
            const grid = { color: '#f1f5f9' };
            const fmt = v => Number(v).toLocaleString('en-US', { maximumFractionDigits: 0 });

            const fin = document.getElementById('db-finance');
            if (fin) {
                const rows = @json($finance);
                const sets = [{ label: @json(__('transport.db_revenue')), data: rows.map(r => r.revenue), backgroundColor: '#1456E8', borderRadius: 4, maxBarThickness: 28 }];
                @if ($canExp)
                    sets.push({ label: @json(__('transport.total_expenses')), data: rows.map(r => r.expenses), backgroundColor: '#F5811E', borderRadius: 4, maxBarThickness: 28 });
                @endif
                new Chart(fin, {
                    type: 'bar',
                    data: { labels: rows.map(r => r.label), datasets: sets },
                    options: {
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                            tooltip: { callbacks: {
                                label: c => ' ' + c.dataset.label + ': ' + fmt(c.parsed.y),
                                @if ($canExp)
                                footer: items => @json(__('transport.net')) + ': ' + fmt(rows[items[0].dataIndex].net),
                                @endif
                            } },
                        },
                        scales: { x: { grid: { display: false } }, y: { grid, ticks: { callback: fmt } } },
                    },
                });
            }

            const ld = document.getElementById('db-loads');
            if (ld) {
                const rows = @json($loadsTrend);
                new Chart(ld, {
                    type: 'line',
                    data: { labels: rows.map(r => r.label), datasets: [{ label: @json(__('transport.loads_count')), data: rows.map(r => r.count),
                        borderColor: '#F5811E', backgroundColor: 'rgba(245,129,30,.12)', fill: true, tension: .35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, pointBackgroundColor: '#F5811E' }] },
                    options: {
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false } },
                        scales: { x: { grid: { display: false } }, y: { grid, beginAtZero: true, ticks: { precision: 0 } } },
                    },
                });
            }
        })();
    </script>
    @endpush
</x-app-layout>
