<style>
    .fyc { --fyc-ink:#0F1B4C; --fyc-blue:#1456E8; --fyc-line:#E7EAF3; --fyc-muted:#6B7280; --fyc-green:#0E9F6E; --fyc-red:#DC2626; --fyc-soft:#F6F8FC; }
    .fyc-card { background:#fff; border:1px solid var(--fyc-line); border-radius:14px; box-shadow:0 1px 2px rgba(15,27,76,.04); }
    .fyc-card-h { padding:14px 18px; border-bottom:1px solid var(--fyc-line); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .fyc-card-h h3 { margin:0; font-size:15px; font-weight:700; color:var(--fyc-ink); }
    .fyc-card-b { padding:18px; }
    .fyc-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); }
    .fyc-stat { background:var(--fyc-soft); border:1px solid var(--fyc-line); border-radius:12px; padding:14px 16px; }
    .fyc-stat .l { font-size:12px; color:var(--fyc-muted); margin-bottom:6px; }
    .fyc-stat .v { font-size:20px; font-weight:800; color:var(--fyc-ink); font-variant-numeric:tabular-nums; }
    .fyc-stat.pos .v { color:var(--fyc-green); } .fyc-stat.neg .v { color:var(--fyc-red); }
    .fyc-table { width:100%; border-collapse:collapse; font-size:13px; }
    .fyc-table th { text-align:start; font-weight:600; color:var(--fyc-muted); background:var(--fyc-soft); padding:9px 12px; border-bottom:1px solid var(--fyc-line); white-space:nowrap; }
    .fyc-table td { padding:9px 12px; border-bottom:1px solid var(--fyc-line); color:#1F2937; }
    .fyc-table td.num, .fyc-table th.num { text-align:end; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .fyc-table tfoot td { font-weight:700; background:var(--fyc-soft); }
    .fyc-scroll { overflow-x:auto; }
    .fyc-form { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
    .fyc-field label { display:block; font-size:12px; color:var(--fyc-muted); margin-bottom:4px; }
    .fyc-field input[type=date], .fyc-field textarea { border:1px solid #D1D5DB; border-radius:10px; padding:8px 10px; font-size:14px; min-width:200px; }
    .fyc-field textarea { width:100%; min-height:70px; }
    .fyc-btn { display:inline-flex; align-items:center; gap:6px; border-radius:10px; padding:9px 16px; font-size:14px; font-weight:600; border:1px solid transparent; cursor:pointer; text-decoration:none; }
    .fyc-btn-primary { background:var(--fyc-ink); color:#fff; } .fyc-btn-primary:hover { background:#1B2C63; }
    .fyc-btn-light { background:#fff; color:var(--fyc-ink); border-color:#D1D5DB; } .fyc-btn-light:hover { background:var(--fyc-soft); }
    .fyc-btn-danger { background:#fff; color:var(--fyc-red); border-color:#FCA5A5; } .fyc-btn-danger:hover { background:#FEF2F2; }
    .fyc-btn[disabled] { opacity:.5; cursor:not-allowed; }
    .fyc-alert { border-radius:12px; padding:12px 14px; font-size:14px; }
    .fyc-alert-info { background:#EFF6FF; color:#1E3A8A; border:1px solid #BFDBFE; }
    .fyc-alert-warn { background:#FFFBEB; color:#92400E; border:1px solid #FDE68A; }
    .fyc-alert-err { background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; }
    .fyc-badge { display:inline-block; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; background:var(--fyc-soft); color:var(--fyc-muted); border:1px solid var(--fyc-line); }
    .fyc-steps { margin:0; padding-inline-start:18px; color:#374151; font-size:13px; line-height:1.9; }
    .fyc-check { display:flex; gap:8px; align-items:flex-start; font-size:13px; color:#374151; }
    .fyc-stack > * + * { margin-top:18px; }
</style>
