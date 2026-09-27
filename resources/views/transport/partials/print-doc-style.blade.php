{{-- ستايل مستندات الطباعة (فاتورة / عرض سعر نقليات) --}}
<style>
        :root { --navy:#0F1B4C; --blue:#1456E8; --orange:#F5811E; --ink:#1F2937; --muted:#6B7280; --border:#E5E7EB; }
        * { box-sizing: border-box; }
        body { font-family: 'Cairo', 'DejaVu Sans', sans-serif; background:#F3F4F6; margin:0; padding:20px; color:var(--ink); font-size:13px; }
        .toolbar { max-width: 1000px; margin: 0 auto 14px; display:flex; gap:8px; justify-content:center; flex-wrap:wrap; }
        .toolbar a, .toolbar button { border:0; cursor:pointer; text-decoration:none; font-family:inherit; font-weight:700; font-size:14px; padding:9px 22px; border-radius:10px; color:#fff; background:linear-gradient(90deg,var(--navy),var(--blue)); }
        .toolbar .light { background:#fff; color:var(--navy); border:1px solid var(--border); }
        .alert { max-width:1000px; margin:0 auto 12px; background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; border-radius:10px; padding:10px 14px; font-weight:600; text-align:center; }
        .doc { max-width:1000px; margin:0 auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.06); }
        .head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 26px; border-bottom:3px solid var(--orange); }
        .head .co { flex:1; }
        .head .co.en { text-align:left; direction:ltr; }
        .head .name { font-size:17px; font-weight:800; color:var(--navy); }
        .head p { margin:2px 0; color:var(--muted); font-size:12px; }
        .head img { max-height:80px; max-width:140px; object-fit:contain; }
        .title { text-align:center; padding:12px; background:var(--navy); color:#fff; font-weight:800; font-size:16px; letter-spacing:.3px; }
        .meta { display:grid; grid-template-columns: 1fr 1fr; gap:0; border-bottom:1px solid var(--border); }
        .meta .box { padding:14px 26px; }
        .meta .box + .box { border-inline-start:1px solid var(--border); }
        .meta h4 { margin:0 0 8px; color:var(--blue); font-size:13px; }
        .kv { display:flex; justify-content:space-between; gap:10px; padding:3px 0; }
        .kv span:first-child { color:var(--muted); }
        .kv span:last-child { font-weight:600; }
        table { width:100%; border-collapse:collapse; }
        .items { padding:18px 26px; }
        .items th { background:#F1F4FB; color:var(--navy); font-size:11.5px; padding:8px 6px; border:1px solid var(--border); }
        .items td { padding:8px 6px; border:1px solid var(--border); text-align:center; font-size:12.5px; }
        .items td.plate { font-weight:700; }
        .items .transfer { color:#b45309; font-size:11.5px; }
        .bottom { display:grid; grid-template-columns: 1fr 1.1fr; gap:22px; padding:0 26px 22px; align-items:start; }
        .qr { display:flex; gap:14px; align-items:center; }
        .qr svg, .qr img { width:120px; height:120px; }
        .bank { font-size:12px; color:var(--muted); line-height:1.7; }
        .totals .row { display:flex; justify-content:space-between; padding:7px 12px; border-bottom:1px solid var(--border); }
        .totals .row small { color:var(--muted); }
        .totals .grand { background:var(--navy); color:#fff; font-weight:800; font-size:15px; border-radius:8px; border:0; margin-top:6px; }
        .words { margin:0 26px 18px; padding:10px 14px; background:#FFF7ED; border:1px dashed var(--orange); border-radius:8px; font-weight:600; }
        .notes { margin:0 26px 18px; color:var(--muted); }
        .sign { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; padding:24px 26px 30px; text-align:center; color:var(--muted); }
        .sign div { border-top:1px solid var(--border); padding-top:8px; }
        @media print {
            body { background:#fff; padding:0; }
            .toolbar, .alert { display:none !important; }
            .doc { box-shadow:none; border-radius:0; max-width:none; }
            @page { size: A4; margin: 10mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
