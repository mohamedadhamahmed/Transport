<x-app-layout>
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
        $trend = function ($pct) {
            if ($pct === null) return '';
            $up = $pct >= 0;
            return '<span class="dx-trend ' . ($up ? 'up' : 'down') . '">'
                . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="' . ($up ? 'M7 17 17 7M9 7h8v8' : 'M7 7l10 10M17 9v8H9') . '"/></svg>'
                . abs($pct) . '%</span>';
        };
        // أيقونات SVG موحّدة (بدل الإيموجي)
        $I = [
            'truck' => '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
            'box' => '<path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8M12 13v8"/>',
            'file' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
            'money' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
            'chart' => '<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'bank' => '<path d="M3 21h18M5 21V10M19 21V10M9 21V10M15 21V10M2 10l10-6 10 6"/>',
            'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>',
            'alert' => '<path d="M12 9v4M12 17h.01"/><path d="M10.3 4.3 2.7 18a1.5 1.5 0 0 0 1.3 2.2h16a1.5 1.5 0 0 0 1.3-2.2L13.7 4.3a1.5 1.5 0 0 0-2.6 0Z"/>',
            'id' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16a3 3 0 0 1 6 0M15 10h3M15 13h3"/>',
            'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
            'gov' => '<path d="M3 21h18M4 10h16M12 3 3 8h18l-9-5ZM6 10v8M10 10v8M14 10v8M18 10v8"/>',
            'check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.8 2.8L16.5 9.5"/>',
            'pin' => '<path d="M12 22s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
            'trophy' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5Z"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'arrow' => '<path d="M15 6l-6 6 6 6"/>',
            'down' => '<path d="M12 4v12M6 10l6 6 6-6M5 20h14"/>',
            'up' => '<path d="M12 20V8M6 14l6-6 6 6M5 4h14"/>',
        ];
        $ic = fn ($k, $cls = '') => '<svg class="' . $cls . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($I[$k] ?? '') . '</svg>';
        $region = fn ($l) => \App\Support\SaudiRegions::name($l->from_region) . ' ← ' . \App\Support\SaudiRegions::name($l->to_region);

        $quick = collect([
            $u?->can('truck_loads.manage') ? ['label' => __('transport.db_load_truck'), 'url' => route('transport.loads.board', ['status' => 'empty']), 'icon' => 'box', 'primary' => true] : null,
            $u?->can('transport_invoices.create') ? ['label' => __('transport.new_invoice'), 'url' => route('transport.invoices.create'), 'icon' => 'file'] : null,
            $u?->can('waybills.create') ? ['label' => __('transport.new_waybill'), 'url' => route('transport.waybills.create'), 'icon' => 'tag'] : null,
            $u?->can('maintenance.create') ? ['label' => __('transport.new_maintenance'), 'url' => route('vouchers.create', ['type' => 'payment', 'maintenance' => 1]), 'icon' => 'wrench'] : null,
            $u?->can('vouchers.create') ? ['label' => __('vouchers.new_receipt'), 'url' => route('vouchers.create', ['type' => 'receipt']), 'icon' => 'money'] : null,
            $u?->can('customers.create') ? ['label' => __('customers.new_customer'), 'url' => route('customers.create'), 'icon' => 'user'] : null,
        ])->filter()->values();
        $f = $fleet;
        $seg = fn ($n) => $f['total'] ? ($n * 100 / $f['total']) : 0;
        $circ = 2 * M_PI * 42;
    @endphp

    <style>
        .dx { --ink: #0F1B4C; --ink2: #334155; --mut: #64748b; --mut2: #94a3b8; --line: #e8ecf3; --bg2: #f8fafc; --blue: #1456E8; --orange: #F5811E; --red: #e11d48; --green: #059669;
            display: flex; flex-direction: column; gap: 1.25rem; max-width: 1680px; margin: 0 auto; color: var(--ink2); }
        .dx svg { width: 18px; height: 18px; flex-shrink: 0; }
        .dx a { text-decoration: none; }

        /* ---------- header ---------- */
        .dx-head { background: linear-gradient(120deg, #0B1640 0%, #13235C 60%, #1E3276 100%); border-radius: 18px; padding: 1.4rem 1.6rem; color: #fff; position: relative; overflow: hidden; }
        .dx-head::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px); background-size: 18px 18px; mask-image: linear-gradient(to left, #000 0%, transparent 70%); }
        .dx-head > * { position: relative; }
        .dx-head-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
        .dx-head h1 { font-size: 1.3rem; font-weight: 800; letter-spacing: -.01em; }
        .dx-head p { font-size: .82rem; color: rgba(255,255,255,.6); margin-top: .2rem; }
        .dx-meta { display: flex; align-items: center; gap: .6rem; }
        .dx-chip { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .8rem; border-radius: 10px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); font-size: .78rem; color: rgba(255,255,255,.85); }
        .dx-chip svg { width: 15px; height: 15px; opacity: .7; }
        .dx-chip b { font-variant-numeric: tabular-nums; color: #fff; }
        .dx-chip select { background: transparent; color: #fff; border: 0; font-size: .78rem; padding: 0 1.2rem 0 0; }
        .dx-chip option { color: #111; }
        .dx-strip { display: grid; grid-template-columns: repeat(2, 1fr); margin-top: 1.25rem; border-top: 1px solid rgba(255,255,255,.1); padding-top: 1.1rem; gap: 1rem; }
        @media (min-width: 900px) { .dx-strip { grid-template-columns: repeat(4, 1fr); } }
        .dx-strip > div { padding-inline-start: .9rem; border-inline-start: 2px solid rgba(255,255,255,.15); }
        .dx-strip > div.hot { border-inline-start-color: #fb7185; }
        .dx-strip .l { font-size: .72rem; color: rgba(255,255,255,.55); }
        .dx-strip .v { font-size: 1.35rem; font-weight: 800; margin-top: .15rem; font-variant-numeric: tabular-nums; }
        .dx-strip .v small { font-size: .7rem; font-weight: 500; color: rgba(255,255,255,.5); margin-inline-start: .15rem; }
        .dx-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1.2rem; }
        .dx-actions a { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .85rem; border-radius: 9px; font-size: .78rem; font-weight: 700; color: #fff; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); transition: .15s; }
        .dx-actions a svg { width: 15px; height: 15px; }
        .dx-actions a:hover { background: rgba(255,255,255,.16); }
        .dx-actions a.primary { background: #fff; color: var(--ink); border-color: #fff; }
        .dx-actions a.primary:hover { background: #eef2ff; }

        /* ---------- cards ---------- */
        .dx-card { background: #fff; border: 1px solid var(--line); border-radius: 16px; min-width: 0; }
        .dx-card-h { display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: 1rem 1.2rem .2rem; }
        .dx-card-h h3 { display: flex; align-items: center; gap: .5rem; font-size: .92rem; font-weight: 800; color: var(--ink); }
        .dx-card-h h3 .hi { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; background: #eef2ff; color: var(--blue); }
        .dx-card-h h3 .hi svg { width: 16px; height: 16px; }
        .dx-card-h a { display: inline-flex; align-items: center; gap: .2rem; font-size: .75rem; font-weight: 700; color: var(--blue); }
        .dx-card-h a svg { width: 14px; height: 14px; }
        .dx-card-b { padding: .9rem 1.2rem 1.2rem; }

        /* ---------- KPIs ---------- */
        .dx-kpis { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 640px) { .dx-kpis { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1280px) { .dx-kpis { grid-template-columns: repeat(3, 1fr); } }
        .dx-kpi { display: flex; gap: .9rem; align-items: flex-start; padding: 1.05rem 1.15rem; transition: .15s; }
        a.dx-kpi:hover { border-color: #c7d4f5; box-shadow: 0 6px 20px rgba(15,27,76,.06); }
        .dx-kpi .ki { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dx-kpi .ki svg { width: 20px; height: 20px; }
        .dx-kpi .kb { flex: 1; min-width: 0; }
        .dx-kpi .kl { font-size: .76rem; font-weight: 600; color: var(--mut); display: flex; justify-content: space-between; gap: .5rem; align-items: center; }
        .dx-kpi .kv { font-size: 1.5rem; font-weight: 800; color: var(--ink); margin-top: .2rem; font-variant-numeric: tabular-nums; letter-spacing: -.01em; }
        .dx-kpi .kv small { font-size: .72rem; font-weight: 600; color: var(--mut2); margin-inline-start: .2rem; }
        .dx-kpi .ks { font-size: .72rem; color: var(--mut2); margin-top: .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dx-trend { display: inline-flex; align-items: center; gap: .15rem; font-size: .7rem; font-weight: 800; padding: .12rem .45rem; border-radius: 99px; white-space: nowrap; }
        .dx-trend svg { width: 11px !important; height: 11px !important; }
        .dx-trend.up { background: #ecfdf5; color: #047857; }
        .dx-trend.down { background: #fff1f2; color: #be123c; }
        .dx-badge { font-size: .68rem; font-weight: 800; padding: .12rem .45rem; border-radius: 99px; background: #fff1f2; color: #be123c; }

        .dx-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        .dx-seg { display: inline-flex; align-items: center; padding: .3rem .75rem; border-radius: 99px; border: 1px solid #e5e7eb; background: #fff; color: #475569; font-size: .74rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .dx-seg:hover { border-color: #fecdd3; color: #be123c; }
        .dx-seg.on { background: #e11d48; border-color: #e11d48; color: #fff; }
        .dx-date { border: 1px solid #e5e7eb; border-radius: 8px; padding: .2rem .4rem; font-size: .74rem; min-height: 30px; }
        .dx-link { display: inline-flex; align-items: center; gap: .2rem; font-size: .76rem; font-weight: 700; color: #1456E8; }
        .dx-link svg { width: 14px; height: 14px; }
        .dx-mstats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        @media (min-width: 900px) { .dx-mstats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .dx-mstats > div { background: #f8fafc; border: 1px solid #eef0f5; border-radius: 12px; padding: .7rem .9rem; display: flex; flex-direction: column; gap: .2rem; }
        .dx-mstats span { font-size: .72rem; color: #64748b; font-weight: 700; }
        .dx-mstats b { font-size: 1.15rem; color: #0F1B4C; font-weight: 800; }
        .dx-mstats b small { font-size: .7rem; color: #64748b; font-weight: 600; }
        .dx-mcol { border: 1px solid #eef0f5; border-radius: 12px; padding: .8rem .9rem; min-width: 0; }
        .dx-mcol-h { display: flex; align-items: center; gap: .4rem; font-size: .82rem; font-weight: 800; color: #0F1B4C; margin-bottom: .3rem; }
        .dx-mcol-h svg { width: 16px; height: 16px; color: #e11d48; }
        @media (min-width: 1200px) { .dx-2-1 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } .dx-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } .dx-1-1 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        /* ---------- fleet ---------- */
        .dx-fleet-top { display: flex; align-items: center; gap: 1.4rem; flex-wrap: wrap; }
        .dx-ring { position: relative; width: 104px; height: 104px; flex-shrink: 0; }
        .dx-ring svg { width: 104px !important; height: 104px !important; transform: rotate(-90deg); }
        .dx-ring .c { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .dx-ring b { font-size: 1.35rem; font-weight: 800; color: var(--ink); line-height: 1; }
        .dx-ring small { font-size: .64rem; color: var(--mut); margin-top: .2rem; }
        .dx-fstats { flex: 1; min-width: 260px; display: grid; grid-template-columns: repeat(2, 1fr); gap: .6rem; }
        @media (min-width: 700px) { .dx-fstats { grid-template-columns: repeat(4, 1fr); } }
        .dx-fs { border: 1px solid var(--line); border-radius: 12px; padding: .65rem .8rem; transition: .15s; display: block; }
        .dx-fs:hover { border-color: #c7d4f5; background: var(--bg2); }
        .dx-fs .n { font-size: 1.4rem; font-weight: 800; color: var(--ink); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .dx-fs .t { display: flex; align-items: center; gap: .35rem; font-size: .72rem; color: var(--mut); margin-top: .2rem; white-space: nowrap; }
        .dx-fs .t i { width: 8px; height: 8px; border-radius: 2px; }
        .dx-bar { display: flex; height: 8px; border-radius: 99px; overflow: hidden; background: #f1f5f9; gap: 2px; margin-top: 1.1rem; }
        .dx-bar span { display: block; height: 100%; }
        .dx-regions { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-top: .9rem; font-size: .72rem; color: var(--mut); }
        .dx-regions a { display: inline-flex; align-items: center; gap: .25rem; padding: .22rem .6rem; border-radius: 99px; background: #f0fdf4; color: #047857; font-weight: 700; border: 1px solid #dcfce7; }
        .dx-regions a svg { width: 12px; height: 12px; }
        .dx-sub { display: flex; align-items: center; gap: .4rem; font-size: .74rem; font-weight: 800; margin-bottom: .55rem; }
        .dx-sub i { width: 7px; height: 7px; border-radius: 50%; }

        .dx-rows { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
        .dx-row { display: flex; align-items: center; gap: .75rem; padding: .6rem .8rem; border-bottom: 1px solid var(--line); }
        .dx-row:last-child { border-bottom: 0; }
        .dx-row:hover { background: var(--bg2); }
        .dx-plate { font-size: .78rem; font-weight: 800; color: var(--ink); background: #fff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: .12rem .45rem; white-space: nowrap; letter-spacing: .02em; }
        .dx-row .info { flex: 1; min-width: 0; }
        .dx-row .rt { font-size: .8rem; font-weight: 700; color: var(--ink2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dx-row .mt { font-size: .7rem; color: var(--mut2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dx-time { font-size: .7rem; font-weight: 800; padding: .2rem .5rem; border-radius: 7px; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .dx-time.late { background: #fff1f2; color: #be123c; }
        .dx-time.ok { background: #fff7ed; color: #c2410c; }
        .dx-ok { display: flex; align-items: center; gap: .5rem; padding: .75rem .9rem; border-radius: 12px; background: #f0fdf4; color: #047857; font-size: .78rem; font-weight: 700; }
        .dx-ok svg { width: 16px; height: 16px; }
        .dx-empty { padding: 1.2rem; text-align: center; font-size: .78rem; color: var(--mut2); border: 1px dashed var(--line); border-radius: 12px; }

        /* ---------- alerts ---------- */
        .dx-alert { display: flex; align-items: center; gap: .7rem; padding: .65rem .75rem; border-radius: 12px; border: 1px solid var(--line); transition: .15s; }
        .dx-alert + .dx-alert { margin-top: .5rem; }
        .dx-alert:hover { background: var(--bg2); }
        .dx-alert .ai { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dx-alert .ai svg { width: 16px; height: 16px; }
        .dx-alert .at { flex: 1; font-size: .78rem; font-weight: 700; color: var(--ink2); }
        .dx-alert .an { font-size: .78rem; font-weight: 800; padding: .15rem .55rem; border-radius: 99px; min-width: 2rem; text-align: center; font-variant-numeric: tabular-nums; }
        .dx-alert.red .ai, .dx-alert.red .an { background: #fff1f2; color: #be123c; }
        .dx-alert.amber .ai, .dx-alert.amber .an { background: #fffbeb; color: #b45309; }
        .dx-alert.blue .ai, .dx-alert.blue .an { background: #eef2ff; color: #1d4ed8; }

        /* ---------- activity ---------- */
        .dx-tl { position: relative; }
        .dx-tl::before { content: ''; position: absolute; top: 8px; bottom: 8px; inset-inline-start: 15px; width: 2px; background: var(--line); }
        .dx-ev { position: relative; display: flex; gap: .75rem; padding: .45rem 0; }
        .dx-ev .dot { position: relative; z-index: 1; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #fff; flex-shrink: 0; }
        .dx-ev .dot svg { width: 14px; height: 14px; }
        .dx-ev .eb { min-width: 0; flex: 1; padding-top: .15rem; }
        .dx-ev .et { font-size: .78rem; color: var(--ink2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dx-ev .et b { color: var(--ink); font-weight: 800; }
        .dx-ev .es { font-size: .7rem; color: var(--mut2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: .1rem; }

        /* ---------- tops ---------- */
        .dx-top { display: flex; align-items: center; gap: .7rem; padding: .55rem 0; border-bottom: 1px solid #f1f5f9; }
        .dx-top:last-child { border-bottom: 0; }
        .dx-top .rk { width: 24px; height: 24px; border-radius: 7px; background: #f1f5f9; color: var(--mut); font-size: .72rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dx-top .rk.g1 { background: #fef3c7; color: #b45309; }
        .dx-top .tb { flex: 1; min-width: 0; }
        .dx-top .tn { font-size: .8rem; font-weight: 700; color: var(--ink2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dx-top .tr { height: 4px; border-radius: 99px; background: #f1f5f9; margin-top: .35rem; overflow: hidden; }
        .dx-top .tr span { display: block; height: 100%; border-radius: 99px; }
        .dx-top .tv { font-size: .8rem; font-weight: 800; color: var(--ink); white-space: nowrap; font-variant-numeric: tabular-nums; }
        .dx-top .tv small { display: block; text-align: end; font-size: .66rem; color: var(--mut2); font-weight: 600; }
        .dx details summary { font-size: .72rem; color: var(--mut); cursor: pointer; }
        .dx-tbl { width: 100%; font-size: .76rem; margin-top: .5rem; border-collapse: collapse; }
        .dx-tbl th { text-align: start; color: var(--mut); font-weight: 700; padding: .4rem .5rem; border-bottom: 1px solid var(--line); }
        .dx-tbl td { padding: .4rem .5rem; border-bottom: 1px solid #f1f5f9; font-variant-numeric: tabular-nums; }
    </style>

    <div class="dx">
        {{-- ================= الهيدر ================= --}}
        <div class="dx-head">
            <div class="dx-head-top">
                <div>
                    <h1>{{ $greet }}{{ $u?->name ? '، ' . $u->name : '' }}</h1>
                    <p>{{ __('transport.db_subtitle') }}</p>
                </div>
                <div class="dx-meta">
                    @if ($branches->count() > 1 && $canMoney)
                        <form method="GET" class="dx-chip">
                            {!! $ic('pin') !!}
                            <select name="branch_id" onchange="this.form.submit()">
                                <option value="">{{ __('messages.dashboard_all_branches') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}" @selected($branchId === $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <div class="dx-chip">{!! $ic('clock') !!}<b id="dx-time">--:--</b><span id="dx-date"></span></div>
                </div>
            </div>

            <div class="dx-strip">
                @if ($canMoney)
                    <div><div class="l">{{ __('transport.db_today_invoices') }}</div><div class="v">{{ $money($kpi['today_revenue']) }}<small>{{ __('transport.sar') }} · {{ $kpi['today_invoices'] }} {{ __('transport.db_invoice_unit') }}</small></div></div>
                @endif
                @if ($canFleet)
                    <div><div class="l">{{ __('transport.db_on_road_now') }}</div><div class="v">{{ $f['loaded'] + $f['overdue'] }}<small>/ {{ $f['total'] }} {{ __('transport.db_truck_unit') }}</small></div></div>
                    <div><div class="l">{{ __('transport.db_empty_ready') }}</div><div class="v">{{ $f['empty'] }}<small>{{ __('transport.db_truck_unit') }}</small></div></div>
                    <div class="{{ $f['overdue'] ? 'hot' : '' }}"><div class="l">{{ __('transport.db_overdue_now') }}</div><div class="v" @if ($f['overdue']) style="color:#fda4af" @endif>{{ $f['overdue'] }}<small>{{ __('transport.db_truck_unit') }}</small></div></div>
                @endif
            </div>

            @if ($quick->isNotEmpty())
                <div class="dx-actions">
                    @foreach ($quick as $q)
                        <a href="{{ $q['url'] }}" class="{{ !empty($q['primary']) ? 'primary' : '' }}">{!! $ic($q['icon']) !!}{{ $q['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ================= مؤشرات الشهر ================= --}}
        @if ($canMoney || $canFleet)
            <div class="dx-kpis">
                @if ($canMoney)
                    <a href="{{ route('transport.reports.sales') }}" class="dx-card dx-kpi">
                        <span class="ki" style="background:#eef2ff;color:#1456E8">{!! $ic('money') !!}</span>
                        <div class="kb">
                            <div class="kl"><span>{{ __('transport.db_month_revenue') }}</span>{!! $trend($kpi['revenue_change']) !!}</div>
                            <div class="kv">{{ $short($kpi['revenue']) }}<small>{{ __('transport.sar') }}</small></div>
                            <div class="ks">{{ $kpi['invoices'] }} {{ __('transport.db_invoice_unit') }} · {{ __('transport.db_incl_vat') }} {{ $money($kpi['total_incl']) }}</div>
                        </div>
                    </a>
                @endif
                @if ($canMoney && $canExp)
                    <a href="{{ route('transport.reports.fleet') }}" class="dx-card dx-kpi">
                        <span class="ki" style="background:#ecfdf5;color:#059669">{!! $ic('chart') !!}</span>
                        <div class="kb">
                            <div class="kl"><span>{{ __('transport.db_month_profit') }}</span>{!! $trend($kpi['profit_change']) !!}</div>
                            <div class="kv" style="color:{{ $kpi['profit'] >= 0 ? '#047857' : '#be123c' }}">{{ $short($kpi['profit']) }}<small>{{ __('transport.sar') }}</small></div>
                            <div class="ks">{{ __('transport.db_truck_expenses') }} {{ $money($kpi['expenses']) }}</div>
                        </div>
                    </a>
                @endif
                @if ($canFleet)
                    <a href="{{ route('transport.loads.report') }}" class="dx-card dx-kpi">
                        <span class="ki" style="background:#fff7ed;color:#ea580c">{!! $ic('box') !!}</span>
                        <div class="kb">
                            <div class="kl"><span>{{ __('transport.db_month_loads') }}</span>{!! $trend($kpi['loads_change']) !!}</div>
                            <div class="kv">{{ number_format($kpi['loads']) }}<small>{{ __('transport.db_load_unit') }}</small></div>
                            <div class="ks">{{ __('transport.db_vs_last_month') }}</div>
                        </div>
                    </a>
                @endif
                @if ($canMoney)
                    <a href="{{ route('transport.reports.unbilled') }}" class="dx-card dx-kpi">
                        <span class="ki" style="background:#fff1f2;color:#e11d48">{!! $ic('file') !!}</span>
                        <div class="kb">
                            <div class="kl"><span>{{ __('transport.unbilled_loads') }}</span>@if ($kpi['unbilled_count'])<span class="dx-badge">{{ __('transport.db_needs_billing') }}</span>@endif</div>
                            <div class="kv">{{ number_format($kpi['unbilled_count']) }}<small>{{ __('transport.db_load_unit') }}</small></div>
                            <div class="ks">{{ $money($kpi['unbilled_value']) }} {{ __('transport.sar') }} · {{ __('transport.db_unbilled_hint') }}</div>
                        </div>
                    </a>
                @endif
                @if ($canCash)
                    <div class="dx-card dx-kpi">
                        <span class="ki" style="background:#f5f3ff;color:#7c3aed">{!! $ic('bank') !!}</span>
                        <div class="kb">
                            <div class="kl"><span>{{ __('transport.db_cash') }}</span></div>
                            <div class="kv">{{ $short($kpi['cash']) }}<small>{{ __('transport.sar') }}</small></div>
                            <div class="ks">{{ __('transport.db_cash_hint') }}</div>
                        </div>
                    </div>
                @endif
                @if ($canMoney)
                    <a href="{{ route('transport.reports.customers', ['sort' => 'total']) }}" class="dx-card dx-kpi">
                        <span class="ki" style="background:#fffbeb;color:#d97706">{!! $ic('users') !!}</span>
                        <div class="kb">
                            <div class="kl"><span>{{ __('transport.db_receivables') }}</span></div>
                            <div class="kv">{{ $short($kpi['receivables']) }}<small>{{ __('transport.sar') }}</small></div>
                            <div class="ks">{{ __('transport.db_receivables_hint') }}</div>
                        </div>
                    </a>
                @endif
            </div>
        @endif

        {{-- ================= الأسطول + التنبيهات ================= --}}
        <div class="dx-grid dx-2-1">
            @if ($canFleet)
                <div class="dx-card">
                    <div class="dx-card-h">
                        <h3><span class="hi">{!! $ic('truck') !!}</span>{{ __('transport.db_fleet_status') }}</h3>
                        <a href="{{ route('transport.loads.board') }}">{{ __('transport.board_title') }}{!! $ic('arrow') !!}</a>
                    </div>
                    <div class="dx-card-b">
                        <div class="dx-fleet-top">
                            <div class="dx-ring" role="img" aria-label="{{ __('transport.utilization') }} {{ $f['utilization'] }}%">
                                <svg viewBox="0 0 104 104">
                                    <circle cx="52" cy="52" r="42" fill="none" stroke="#eef2ff" stroke-width="10"/>
                                    <circle cx="52" cy="52" r="42" fill="none" stroke="#1456E8" stroke-width="10" stroke-linecap="round"
                                            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ * (1 - $f['utilization'] / 100) }}"/>
                                </svg>
                                <div class="c"><b>{{ $f['utilization'] }}%</b><small>{{ __('transport.utilization') }}</small></div>
                            </div>
                            <div class="dx-fstats">
                                <a href="{{ route('transport.loads.board', ['status' => 'loaded']) }}" class="dx-fs"><div class="n">{{ $f['loaded'] }}</div><div class="t"><i style="background:#F5811E"></i>{{ __('transport.loaded_trucks') }}</div></a>
                                <a href="{{ route('transport.loads.board', ['status' => 'overdue']) }}" class="dx-fs"><div class="n" style="{{ $f['overdue'] ? 'color:#be123c' : '' }}">{{ $f['overdue'] }}</div><div class="t"><i style="background:#e11d48"></i>{{ __('transport.overdue_trucks') }}</div></a>
                                <a href="{{ route('transport.loads.board', ['status' => 'empty']) }}" class="dx-fs"><div class="n">{{ $f['empty'] }}</div><div class="t"><i style="background:#10b981"></i>{{ __('transport.empty_trucks') }}</div></a>
                                <a href="{{ route('transport.loads.board', ['status' => 'maintenance']) }}" class="dx-fs"><div class="n">{{ $f['maintenance'] }}</div><div class="t"><i style="background:#94a3b8"></i>{{ __('transport.status_maintenance') }}</div></a>
                            </div>
                        </div>

                        <div class="dx-bar">
                            @if ($f['loaded'])<span style="width:{{ $seg($f['loaded']) }}%;background:#F5811E"></span>@endif
                            @if ($f['overdue'])<span style="width:{{ $seg($f['overdue']) }}%;background:#e11d48"></span>@endif
                            @if ($f['empty'])<span style="width:{{ $seg($f['empty']) }}%;background:#10b981"></span>@endif
                            @if ($f['maintenance'])<span style="width:{{ $seg($f['maintenance']) }}%;background:#94a3b8"></span>@endif
                        </div>

                        @if ($emptyByRegion->isNotEmpty())
                            <div class="dx-regions">
                                <span>{{ __('transport.db_empty_where') }}</span>
                                @foreach ($emptyByRegion as $r)
                                    <a href="{{ route('transport.loads.board', array_filter(['status' => 'empty', 'region' => $r['key'] === '_none' ? null : $r['key']])) }}">{!! $ic('pin') !!}{{ $r['label'] }} · {{ $r['count'] }}</a>
                                @endforeach
                            </div>
                        @endif

                        <div class="dx-grid dx-1-1" style="margin-top:1.2rem;gap:1rem">
                            <div>
                                <div class="dx-sub" style="color:#be123c"><i style="background:#e11d48"></i>{{ __('transport.db_overdue_list') }}</div>
                                @if ($overdue->isEmpty())
                                    <div class="dx-ok">{!! $ic('check') !!}{{ __('transport.db_no_overdue') }}</div>
                                @else
                                    <div class="dx-rows">
                                        @foreach ($overdue as $t)
                                            <div class="dx-row" title="{{ $t->activeLoad->from_label }} ← {{ $t->activeLoad->to_label }}">
                                                <span class="dx-plate">{{ $t->plate_number }}</span>
                                                <div class="info">
                                                    <div class="rt">{{ $region($t->activeLoad) }}</div>
                                                    <div class="mt">{{ $t->activeLoad->driver?->name ?? '-' }}{{ $t->activeLoad->customer ? ' · ' . $t->activeLoad->customer->name : '' }}</div>
                                                </div>
                                                <span class="dx-time late">{{ $t->activeLoad->remainingText() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="dx-sub" style="color:#c2410c"><i style="background:#F5811E"></i>{{ __('transport.db_next_unloads') }}</div>
                                @if ($upcoming->isEmpty())
                                    <div class="dx-empty">{{ __('transport.db_no_loaded') }}</div>
                                @else
                                    <div class="dx-rows">
                                        @foreach ($upcoming as $t)
                                            <div class="dx-row" title="{{ $t->activeLoad->from_label }} ← {{ $t->activeLoad->to_label }}">
                                                <span class="dx-plate">{{ $t->plate_number }}</span>
                                                <div class="info">
                                                    <div class="rt">{{ $region($t->activeLoad) }}</div>
                                                    <div class="mt">{{ $t->activeLoad->customer?->name ?? '-' }} · {{ $t->activeLoad->expected_unload_at?->format('m/d H:i') }}</div>
                                                </div>
                                                <span class="dx-time ok">{{ $t->activeLoad->remainingText() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="dx-card">
                <div class="dx-card-h"><h3><span class="hi" style="background:#fff1f2;color:#e11d48">{!! $ic('alert') !!}</span>{{ __('transport.db_attention') }}</h3></div>
                <div class="dx-card-b">
                    @php
                        $alertRows = array_values(array_filter([
                            $canFleet && $f['overdue'] ? ['red', 'clock', __('transport.db_alert_overdue'), $f['overdue'], route('transport.loads.board', ['status' => 'overdue'])] : null,
                            $u?->can('trucks.view') && $alerts['docsAlert'] ? [$alerts['docsExpired'] ? 'red' : 'amber', 'id', __('transport.db_alert_docs', ['expired' => $alerts['docsExpired']]), $alerts['docsAlert'], route('transport.trucks.index', ['docs' => 'alert'])] : null,
                            $canMoney && $kpi['unbilled_count'] ? ['amber', 'file', __('transport.db_alert_unbilled'), $kpi['unbilled_count'], route('transport.reports.unbilled')] : null,
                            $canMoney && $alerts['noPrice'] ? ['amber', 'tag', __('transport.db_alert_no_price'), $alerts['noPrice'], route('transport.reports.unbilled')] : null,
                            $u?->can('zatca.view') && $alerts['zatcaFailed'] ? ['red', 'gov', __('transport.db_alert_zatca_failed'), $alerts['zatcaFailed'], route('transport.zatca.index', ['sent' => 0, 'status' => 'FAIL'])] : null,
                            $u?->can('zatca.view') && $alerts['zatcaPending'] ? ['blue', 'gov', __('transport.db_alert_zatca_pending'), $alerts['zatcaPending'], route('transport.zatca.index', ['sent' => 0])] : null,
                        ]));
                    @endphp
                    @forelse ($alertRows as [$lvl, $icon, $label, $n, $url])
                        <a href="{{ $url }}" class="dx-alert {{ $lvl }}">
                            <span class="ai">{!! $ic($icon) !!}</span>
                            <span class="at">{{ $label }}</span>
                            <span class="an">{{ number_format($n) }}</span>
                        </a>
                    @empty
                        <div class="dx-ok">{!! $ic('check') !!}{{ __('transport.db_all_good') }}</div>
                    @endforelse
                </div>

                <div class="dx-card-h" style="padding-top:.2rem"><h3><span class="hi">{!! $ic('clock') !!}</span>{{ __('transport.db_recent_activity') }}</h3></div>
                <div class="dx-card-b" style="padding-top:.5rem">
                    @php
                        $evStyle = ['loaded' => ['#fff7ed', '#ea580c', 'up'], 'unloaded' => ['#ecfdf5', '#059669', 'down'], 'invoice' => ['#eef2ff', '#1456E8', 'file']];
                        $events = $activity->filter(fn ($a) => $a['type'] === 'invoice' ? $canMoney : $canFleet)->values();
                    @endphp
                    @if ($events->isEmpty())
                        <div class="dx-empty">{{ __('transport.no_data') }}</div>
                    @else
                        <div class="dx-tl">
                            @foreach ($events as $a)
                                @php [$bg, $fg, $icn] = $evStyle[$a['type']]; @endphp
                                <a href="{{ $a['url'] }}" class="dx-ev">
                                    <span class="dot" style="background:{{ $bg }};color:{{ $fg }}">{!! $ic($icn) !!}</span>
                                    <div class="eb">
                                        <div class="et">{{ __('transport.db_act_' . $a['type']) }} <b>{{ $a['title'] }}</b> · {{ $a['route'] ?? '' }}</div>
                                        <div class="es">{{ $a['sub'] ? $a['sub'] . ' · ' : '' }}{{ $a['at']?->diffForHumans() }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================= الرسوم ================= --}}
        @if ($canMoney || $canFleet)
            <div class="dx-grid dx-2-1">
                @if ($canMoney)
                    <div class="dx-card">
                        <div class="dx-card-h">
                            <h3><span class="hi">{!! $ic('chart') !!}</span>{{ $canExp ? __('transport.db_revenue_vs_expenses') : __('transport.db_revenue_6m') }}</h3>
                            <a href="{{ route('transport.reports.sales', ['date_from' => now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString()]) }}">{{ __('transport.db_details') }}{!! $ic('arrow') !!}</a>
                        </div>
                        <div class="dx-card-b">
                            <div style="position:relative;height:270px"><canvas id="dx-finance" aria-label="{{ __('transport.db_revenue_vs_expenses') }}"></canvas></div>
                            <details style="margin-top:.6rem">
                                <summary>{{ __('transport.db_show_table') }}</summary>
                                <table class="dx-tbl">
                                    <thead><tr><th>{{ __('transport.db_month') }}</th><th>{{ __('transport.db_revenue') }}</th>@if ($canExp)<th>{{ __('transport.total_expenses') }}</th><th>{{ __('transport.net') }}</th>@endif</tr></thead>
                                    <tbody>
                                        @foreach ($finance as $r)
                                            <tr><td>{{ $r['label'] }}</td><td>{{ $money($r['revenue']) }}</td>@if ($canExp)<td>{{ $money($r['expenses']) }}</td><td style="font-weight:800;color:{{ $r['net'] >= 0 ? '#047857' : '#be123c' }}">{{ $money($r['net']) }}</td>@endif</tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </details>
                        </div>
                    </div>
                @endif
                @if ($canFleet)
                    <div class="dx-card">
                        <div class="dx-card-h">
                            <h3><span class="hi" style="background:#fff7ed;color:#ea580c">{!! $ic('box') !!}</span>{{ __('transport.db_loads_14d') }}</h3>
                            <a href="{{ route('transport.loads.report') }}">{{ __('transport.db_details') }}{!! $ic('arrow') !!}</a>
                        </div>
                        <div class="dx-card-b"><div style="position:relative;height:270px"><canvas id="dx-loads" aria-label="{{ __('transport.db_loads_14d') }}"></canvas></div></div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ================= الأكثر ================= --}}
        @php
            $tops = array_values(array_filter([
                $canMoney ? ['title' => __('transport.db_top_customers'), 'icon' => 'trophy', 'rows' => $topCustomers, 'color' => '#1456E8', 'money' => true, 'url' => route('transport.reports.customers', ['sort' => 'total']), 'subLabel' => __('transport.db_invoice_unit')] : null,
                $canFleet ? ['title' => __('transport.db_top_destinations'), 'icon' => 'pin', 'rows' => $topDestinations, 'color' => '#F5811E', 'money' => false, 'url' => route('transport.reports.routes'), 'subLabel' => __('transport.db_load_unit')] : null,
                $canFleet ? ['title' => __('transport.db_top_trucks'), 'icon' => 'truck', 'rows' => $topTrucks, 'color' => '#6B2FD6', 'money' => false, 'url' => $u?->can('transport_reports.fleet') ? route('transport.reports.fleet') : route('transport.loads.report'), 'subLabel' => __('transport.db_load_unit')] : null,
            ]));
        @endphp
        @if ($tops)
            <div class="dx-grid dx-3">
                @foreach ($tops as $t)
                    @php $mx = collect($t['rows'])->max('value') ?: 0; @endphp
                    <div class="dx-card">
                        <div class="dx-card-h"><h3><span class="hi">{!! $ic($t['icon']) !!}</span>{{ $t['title'] }}</h3><a href="{{ $t['url'] }}">{{ __('transport.db_details') }}{!! $ic('arrow') !!}</a></div>
                        <div class="dx-card-b" style="padding-top:.4rem">
                            @forelse ($t['rows'] as $i => $r)
                                <div class="dx-top">
                                    <span class="rk {{ $i === 0 ? 'g1' : '' }}">{{ $i + 1 }}</span>
                                    <div class="tb">
                                        <div class="tn">{{ $r['label'] }}</div>
                                        <div class="tr"><span style="width:{{ $mx ? max(3, $r['value'] * 100 / $mx) : 0 }}%;background:{{ $t['color'] }}"></span></div>
                                    </div>
                                    <span class="tv">
                                        {{ $t['money'] ? $short($r['value']) : number_format($r['value']) }}
                                        <small>{{ $t['money'] ? ($r['sub'] ?? 0) . ' ' . __('transport.db_invoice_unit') : $t['subLabel'] }}</small>
                                    </span>
                                </div>
                            @empty
                                <div class="dx-empty">{{ __('transport.db_no_month_data') }}</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ================= الصيانة (فترة قابلة للتغيير) ================= --}}
        @if (!empty($maint))
            @php
                $mp = [
                    'month' => __('transport.db_m_month'),
                    '3m' => __('transport.db_m_3m'),
                    '6m' => __('transport.db_m_6m'),
                    'year' => __('transport.db_m_year'),
                ];
                $mq = fn ($extra) => route('dashboard', array_filter(['branch_id' => $branchId] + $extra)) . '#dx-maint';
                $maintCols = [
                    ['title' => __('transport.mr_by_truck'), 'icon' => 'truck', 'rows' => $maint['trucks'], 'color' => '#e11d48', 'money' => true, 'unit' => __('transport.mr_times')],
                    ['title' => __('transport.mr_by_category'), 'icon' => 'wrench', 'rows' => $maint['types'], 'color' => '#F5811E', 'money' => true, 'unit' => __('transport.mr_times')],
                    ['title' => __('transport.mr_by_item'), 'icon' => 'tag', 'rows' => $maint['items'], 'color' => '#6B2FD6', 'money' => false, 'unit' => __('transport.mr_times')],
                ];
            @endphp
            <div class="dx-card" id="dx-maint">
                <div class="dx-card-h" style="flex-wrap:wrap;gap:.75rem">
                    <h3><span class="hi" style="background:#fff1f2;color:#e11d48">{!! $ic('wrench') !!}</span>{{ __('transport.db_maint_title') }}</h3>
                    <div style="display:flex;flex-wrap:wrap;gap:.4rem;align-items:center">
                        @foreach ($mp as $k => $label)
                            <a href="{{ $mq(['m_period' => $k]) }}" class="dx-seg {{ $maint['period'] === $k ? 'on' : '' }}">{{ $label }}</a>
                        @endforeach
                        <form method="GET" action="{{ route('dashboard') }}#dx-maint" style="display:flex;gap:.3rem;align-items:center">
                            @if ($branchId)<input type="hidden" name="branch_id" value="{{ $branchId }}">@endif
                            <input type="hidden" name="m_period" value="custom">
                            <input type="date" name="m_from" value="{{ $maint['from'] }}" class="dx-date">
                            <span style="color:#94a3b8">→</span>
                            <input type="date" name="m_to" value="{{ $maint['to'] }}" class="dx-date">
                            <button type="submit" class="dx-seg {{ $maint['period'] === 'custom' ? 'on' : '' }}">{{ __('transport.show') }}</button>
                        </form>
                        @if ($u?->can('maintenance.view'))
                            <a href="{{ route('transport.reports.maintenance', ['date_from' => $maint['from'], 'date_to' => $maint['to']]) }}" class="dx-link">{{ __('transport.maintenance_report') }}{!! $ic('arrow') !!}</a>
                        @endif
                    </div>
                </div>
                <div class="dx-card-b">
                    <div class="dx-mstats">
                        <div><span>{{ __('transport.mr_total_cost') }}</span><b>{{ $money($maint['total']) }} <small>{{ __('transport.sar') }}</small></b>{!! $trend($maint['change']) !!}</div>
                        <div><span>{{ __('transport.mr_visits') }}</span><b>{{ number_format($maint['visits']) }}</b></div>
                        <div><span>{{ __('transport.mr_trucks_serviced') }}</span><b>{{ number_format($maint['trucks_count']) }}</b></div>
                        <div><span>{{ __('transport.db_m_range') }}</span><b style="font-size:.85rem" dir="ltr">{{ $maint['from'] }} → {{ $maint['to'] }}</b></div>
                    </div>
                    <div class="dx-grid dx-3" style="margin-top:1rem;gap:1rem">
                        @foreach ($maintCols as $col)
                            @php $mx = collect($col['rows'])->max('value') ?: 0; @endphp
                            <div class="dx-mcol">
                                <div class="dx-mcol-h">{!! $ic($col['icon']) !!} {{ $col['title'] }}</div>
                                @forelse ($col['rows'] as $i => $r)
                                    <div class="dx-top">
                                        <span class="rk {{ $i === 0 ? 'g1' : '' }}">{{ $i + 1 }}</span>
                                        <div class="tb">
                                            <div class="tn">
                                                @if (!empty($r['id']) && $u?->can('transport_reports.fleet'))
                                                    <a href="{{ route('transport.reports.truck', ['truck_id' => $r['id'], 'date_from' => $maint['from'], 'date_to' => $maint['to']]) }}" class="hover:underline">{{ $r['label'] }}</a>
                                                @else
                                                    {{ \Illuminate\Support\Str::limit($r['label'], 40) }}
                                                @endif
                                            </div>
                                            <div class="tr"><span style="width:{{ $mx ? max(3, $r['value'] * 100 / $mx) : 0 }}%;background:{{ $col['color'] }}"></span></div>
                                        </div>
                                        <span class="tv">
                                            @if ($col['money'])
                                                {{ $short($r['value']) }}
                                                <small>{{ $r['sub'] }} {{ $col['unit'] }}@if (isset($r['share'])) · {{ $r['share'] }}%@endif</small>
                                            @else
                                                {{ number_format($r['value']) }} <small>{{ $col['unit'] }} · {{ $short($r['sub']) }}</small>
                                            @endif
                                        </span>
                                    </div>
                                @empty
                                    <div class="dx-empty">{{ __('transport.db_m_empty') }}</div>
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const loc = @json(app()->getLocale() === 'ar' ? 'ar-SA-u-ca-gregory-nu-latn' : 'en-GB');
            function tick() {
                const n = new Date();
                const t = document.getElementById('dx-time'), d = document.getElementById('dx-date');
                if (t) t.textContent = n.toLocaleTimeString(loc, { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Riyadh' });
                if (d) d.textContent = n.toLocaleDateString(loc, { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'Asia/Riyadh' });
            }
            tick(); setInterval(tick, 15000);

            if (typeof Chart === 'undefined') return;
            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#64748b';
            const grid = { color: '#f1f5f9', drawTicks: false };
            const fmt = v => Number(v).toLocaleString('en-US', { maximumFractionDigits: 0 });
            const tip = { backgroundColor: '#0F1B4C', padding: 10, cornerRadius: 8, titleFont: { weight: '700' }, boxPadding: 4 };

            const fin = document.getElementById('dx-finance');
            if (fin) {
                const rows = @json($finance);
                const sets = [{ label: @json(__('transport.db_revenue')), data: rows.map(r => r.revenue), backgroundColor: '#1456E8', borderRadius: 4, maxBarThickness: 26, categoryPercentage: .6 }];
                @if ($canExp)
                    sets.push({ label: @json(__('transport.total_expenses')), data: rows.map(r => r.expenses), backgroundColor: '#F5811E', borderRadius: 4, maxBarThickness: 26, categoryPercentage: .6 });
                @endif
                new Chart(fin, {
                    type: 'bar',
                    data: { labels: rows.map(r => r.label), datasets: sets },
                    options: {
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 8, boxHeight: 8 } },
                            tooltip: Object.assign({}, tip, { callbacks: {
                                label: c => ' ' + c.dataset.label + ': ' + fmt(c.parsed.y),
                                @if ($canExp)
                                footer: items => @json(__('transport.net')) + ': ' + fmt(rows[items[0].dataIndex].net),
                                @endif
                            } }),
                        },
                        scales: { x: { grid: { display: false }, border: { display: false } }, y: { grid, border: { display: false }, ticks: { callback: fmt, padding: 8 } } },
                    },
                });
            }

            const ld = document.getElementById('dx-loads');
            if (ld) {
                const rows = @json($loadsTrend);
                const ctx = ld.getContext('2d');
                const g = ctx.createLinearGradient(0, 0, 0, 260);
                g.addColorStop(0, 'rgba(245,129,30,.22)'); g.addColorStop(1, 'rgba(245,129,30,0)');
                new Chart(ld, {
                    type: 'line',
                    data: { labels: rows.map(r => r.label), datasets: [{ label: @json(__('transport.loads_count')), data: rows.map(r => r.count),
                        borderColor: '#F5811E', backgroundColor: g, fill: true, tension: .35, borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, pointBackgroundColor: '#F5811E', pointBorderColor: '#fff', pointBorderWidth: 2 }] },
                    options: {
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false }, tooltip: tip },
                        scales: { x: { grid: { display: false }, border: { display: false } }, y: { grid, border: { display: false }, beginAtZero: true, ticks: { precision: 0, padding: 8 } } },
                    },
                });
            }
        })();
    </script>
    @endpush
</x-app-layout>
