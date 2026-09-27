{{-- ستايل شاشات الفواتير / الأحمال غير المفوترة / المناطق (مكتوب هنا عشان يشتغل من غير npm run build) --}}
@once
<style>
    .tv-page { display: flex; flex-direction: column; gap: 1.25rem; }
    .tv-card { background: #fff; border: 1px solid #eef0f5; border-radius: 14px; box-shadow: 0 1px 2px rgba(15,27,76,.04); }
    .tv-pad { padding: 1.25rem 1.5rem; }
    .tv-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1.1rem 1.5rem; border-inline-start: 3px solid #1456E8; border-bottom: 2px solid #F5B041; }
    .tv-head-main { display: flex; align-items: center; gap: .85rem; }
    .tv-head-icon { width: 38px; height: 38px; border-radius: 10px; background: #eaf1ff; color: #1456E8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .tv-title { font-size: 1.25rem; font-weight: 800; color: #0F1B4C; line-height: 1.3; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .tv-title small { font-size: .75rem; font-weight: 600; color: #6b7280; }
    .tv-crumb { font-size: .75rem; color: #9ca3af; margin-top: .15rem; display: flex; gap: .35rem; align-items: center; flex-wrap: wrap; }
    .tv-crumb a { color: #6b7fa8; } .tv-crumb a:hover { color: #1456E8; }
    .tv-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
    .tv-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .5rem 1rem; border-radius: 8px; font-size: .8rem; font-weight: 700; white-space: nowrap; transition: .15s; border: 1px solid transparent; cursor: pointer; }
    .tv-btn svg { width: 15px; height: 15px; }
    .tv-btn-blue { background: #1456E8; color: #fff; } .tv-btn-blue:hover { background: #0f47c4; }
    .tv-btn-green { background: #10b981; color: #fff; } .tv-btn-green:hover { background: #059669; }
    .tv-btn-outline { background: #fff; color: #1456E8; border-color: #cddcfb; } .tv-btn-outline:hover { background: #f3f7ff; }
    .tv-btn-gray { background: #fff; color: #374151; border-color: #e5e7eb; } .tv-btn-gray:hover { background: #f9fafb; }
    .tv-btn-red { background: #fff1f2; color: #e11d48; } .tv-btn-red:hover { background: #ffe4e6; }
    .tv-btn-lg { padding: .7rem 1.4rem; font-size: .9rem; border-radius: 10px; }
    .tv-btn-block { width: 100%; }
    .tv-tabs { display: flex; gap: .5rem; flex-wrap: wrap; }
    .tv-tab { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem 1rem; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; font-size: .8rem; font-weight: 700; color: #374151; }
    .tv-tab:hover { border-color: #cddcfb; color: #1456E8; }
    .tv-tab.active { border-color: #1456E8; color: #1456E8; background: #f3f7ff; box-shadow: 0 0 0 2px rgba(20,86,232,.08); }
    .tv-label { display: block; font-size: .78rem; font-weight: 700; color: #1f2937; margin-bottom: .35rem; }
    .tv-label small { font-weight: 500; color: #9ca3af; font-size: .7rem; }
    .tv-input { width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: .55rem .75rem; font-size: .85rem; background: #fff; color: #111827; min-height: 40px; }
    .tv-input:focus { outline: none; border-color: #1456E8; box-shadow: 0 0 0 2px rgba(20,86,232,.12); }
    .tv-filters { display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: end; }
    @media (min-width: 900px) {
        .tv-filters-5 { grid-template-columns: 1fr 1fr 1.4fr 1fr auto; }
        .tv-filters-6 { grid-template-columns: 1fr 1fr 1.3fr 1fr 1fr auto; }
    }
    .tv-kpis { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    @media (min-width: 1000px) { .tv-kpis-4 { grid-template-columns: repeat(4, 1fr); } .tv-kpis-5 { grid-template-columns: repeat(5, 1fr); } }
    .tv-kpi { background: #fff; border: 1px solid #eef0f5; border-radius: 14px; padding: 1rem 1.2rem; }
    .tv-kpi .l { display: flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 700; color: #374151; }
    .tv-kpi .l svg { width: 15px; height: 15px; }
    .tv-kpi .v { font-size: 1.35rem; font-weight: 800; color: #0F1B4C; margin-top: .45rem; }
    .tv-kpi .v small { font-size: .75rem; font-weight: 600; color: #6b7280; }
    .tv-table-wrap { overflow-x: auto; border: 1px solid #eef0f5; border-radius: 10px; }
    .tv-table { width: 100%; font-size: .82rem; border-collapse: collapse; }
    .tv-table th { background: #f8fafc; color: #374151; font-weight: 700; font-size: .76rem; padding: .7rem .8rem; text-align: start; white-space: nowrap; border-bottom: 1px solid #eef0f5; }
    .tv-table td { padding: .65rem .8rem; border-bottom: 1px solid #f3f4f6; color: #1f2937; vertical-align: middle; }
    .tv-table tbody tr:hover { background: #f8faff; }
    .tv-table tfoot td { background: #f8fafc; font-weight: 800; color: #0F1B4C; }
    .tv-empty { padding: 2.5rem 1rem; text-align: center; color: #9ca3af; font-size: .85rem; }
    .tv-empty-ok { display: flex; flex-direction: column; align-items: center; gap: .5rem; }
    .tv-pill { display: inline-flex; align-items: center; gap: .25rem; padding: .15rem .55rem; border-radius: 999px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .tv-pill-blue { background: #eaf1ff; color: #1456E8; }
    .tv-pill-green { background: #ecfdf5; color: #047857; }
    .tv-pill-amber { background: #fffbeb; color: #b45309; }
    .tv-pill-red { background: #fff1f2; color: #be123c; }
    .tv-pill-gray { background: #f3f4f6; color: #4b5563; }
    .tv-count { display: inline-flex; min-width: 22px; height: 22px; padding: 0 .4rem; align-items: center; justify-content: center; border-radius: 999px; background: #eaf1ff; color: #1456E8; font-size: .7rem; font-weight: 800; }
    .tv-section-title { display: flex; align-items: center; gap: .5rem; font-size: 1rem; font-weight: 800; color: #0F1B4C; }
    .tv-section-title svg { width: 18px; height: 18px; color: #1456E8; }
    .tv-hint { font-size: .72rem; color: #6b7280; margin-top: .4rem; }
    .tv-alert-info { display: flex; gap: .6rem; align-items: flex-start; background: #eef4ff; border: 1px solid #d6e3ff; border-radius: 10px; padding: .8rem 1rem; font-size: .76rem; color: #1e3a8a; line-height: 1.7; }
    .tv-alert-info .i { width: 22px; height: 22px; border-radius: 50%; background: #1456E8; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .75rem; flex-shrink: 0; }
    @media print {
        aside, header, nav, footer, .tv-no-print, .tv-actions, .tv-tabs, #assistant-widget { display: none !important; }
        main { padding: 0 !important; }
        .tv-card, .tv-kpi { box-shadow: none !important; }
    }
</style>
@endonce
