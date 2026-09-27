{{-- شكل البوليصة الورقية (مشترك بين الإنشاء/التعديل والعرض والطباعة) --}}
<style>
    .wbp { --ink:#1f2a78; --ink2:#0F1B4C; --line:#8a93c7; --red:#d61f26;
        background:#fff; color:var(--ink); max-width:210mm; margin:0 auto; padding:14mm 12mm 10mm;
        font-family:'Cairo', 'Tajawal', Tahoma, sans-serif; font-size:12.5px; line-height:1.55; direction:rtl;
        box-shadow:0 2px 14px rgba(15,27,76,.08); border:1px solid #e5e7eb; border-radius:4px; }
    .wbp * { box-sizing:border-box; }
    .wbp b, .wbp strong { font-weight:800; }

    /* الهيدر */
    .wbp-head { display:grid; grid-template-columns:1fr 1.25fr 1fr; align-items:start; gap:10px; }
    .wbp-dates { font-weight:700; font-size:12px; }
    .wbp-dates .r { display:flex; align-items:center; gap:6px; margin-bottom:3px; }
    .wbp-dates .r .v { min-width:95px; text-align:center; border-bottom:1px dotted var(--line); padding:0 4px; font-weight:800; }
    .wbp-dates .r .u { font-size:11px; }
    .wbp-dates .ph { font-size:11px; margin-top:2px; }
    .wbp-brand { text-align:center; }
    .wbp-brand img { max-height:46px; max-width:120px; object-fit:contain; display:block; margin:0 auto 4px; }
    .wbp-brand .ar { font-size:19px; font-weight:900; color:var(--ink2); line-height:1.3; }
    .wbp-brand .en { font-size:11.5px; color:var(--ink); }
    .wbp-brand .cr { display:flex; align-items:center; gap:8px; justify-content:center; font-size:11px; font-weight:800; margin-top:4px; }
    .wbp-brand .cr::before, .wbp-brand .cr::after { content:''; height:2px; width:70px; background:var(--ink); }
    .wbp-no { justify-self:end; text-align:left; direction:ltr; }
    .wbp-no .box { display:inline-block; border:2px solid var(--ink); padding:3px 12px; font-weight:900; font-size:16px; color:var(--ink2); min-width:72px; text-align:center; }
    .wbp-no .box span { color:var(--red); font-weight:700; margin-left:4px; }
    .wbp-no .hint { font-size:9px; color:#9ca3af; direction:rtl; text-align:center; }
    .wbp-no .mob { font-size:10.5px; font-weight:800; margin-top:4px; }
    .wbp-title { text-align:center; margin:10px 0 12px; }
    .wbp-title span { display:inline-block; font-size:22px; font-weight:900; color:var(--ink2); border-bottom:3px double var(--ink); padding:0 14px 2px; }

    /* السطور المنقطة */
    .wbp-line { display:flex; align-items:flex-end; gap:6px; margin-bottom:7px; font-weight:700; }
    .wbp-line .lb { white-space:nowrap; }
    .wbp-line .fill { flex:1; min-height:20px; border-bottom:1px dotted var(--line); padding:0 4px; font-weight:600; color:#111827; }
    .wbp-line .tail { white-space:nowrap; }
    .wbp-2col { display:grid; grid-template-columns:1fr 1fr; column-gap:22px; }

    /* جدول البضاعة */
    .wbp-tbl-wrap { border:3px double var(--ink); padding:2px; margin:10px 0 12px; }
    .wbp-tbl { width:100%; border-collapse:collapse; }
    .wbp-tbl th, .wbp-tbl td { border:1px solid var(--ink); padding:4px 5px; text-align:center; font-size:12px; }
    .wbp-tbl th { font-weight:800; }
    .wbp-tbl td { height:24px; color:#111827; font-weight:600; }
    .wbp-tbl .n { width:28px; color:var(--ink); font-weight:800; }
    .wbp-tbl tfoot td { font-weight:800; color:var(--ink2); background:#f5f7ff; }

    .wbp-row2 { display:flex; justify-content:space-between; gap:20px; font-weight:800; margin:6px 0 10px; }
    .wbp-strong { font-weight:900; font-size:13px; margin:6px 0; color:var(--ink2); }
    .wbp-sign { display:grid; grid-template-columns:1fr 1fr; gap:22px; margin-top:12px; }
    .wbp-foot { border-top:2px solid var(--ink); margin-top:14px; padding-top:8px; text-align:center; font-size:11px; font-weight:700; }
    .wbp-foot div { margin:2px 0; }

    /* وضع الإدخال */
    .wbp input.wbp-in, .wbp select.wbp-in, .wbp textarea.wbp-in {
        width:100%; border:0; border-bottom:1px dotted var(--line); background:transparent; font:inherit; font-weight:600;
        color:#111827; padding:1px 4px; min-height:24px; outline:none; border-radius:0; box-shadow:none; }
    .wbp input.wbp-in:focus, .wbp select.wbp-in:focus { border-bottom:1px solid #1456E8; background:#f5f8ff; }
    .wbp .wbp-tbl input.wbp-in { border-bottom:0; text-align:center; min-height:22px; }
    .wbp .wbp-sel { display:flex; align-items:center; gap:6px; flex:1; min-width:0; }
    .wbp .wbp-sel .ts-wrapper, .wbp .wbp-sel select { flex:1; min-width:0; }
    .wbp .wbp-plus { flex:none; width:24px; height:24px; border-radius:99px; border:1.5px solid var(--ink); color:var(--ink); background:#fff;
        display:inline-flex; align-items:center; justify-content:center; font-weight:900; cursor:pointer; line-height:1; }
    .wbp .wbp-plus:hover { background:var(--ink); color:#fff; }
    .wbp .wbp-del { color:#dc2626; background:none; border:0; cursor:pointer; font-weight:900; }
    .wbp .wbp-add { font-size:11px; border:1px solid #c7d2fe; color:#1456E8; background:#fff; border-radius:6px; padding:2px 10px; cursor:pointer; font-weight:700; }
    .wbp .ts-control { border:0 !important; border-bottom:1px dotted var(--line) !important; box-shadow:none !important; border-radius:0 !important; min-height:26px; padding:2px 4px !important; background:transparent !important; }
    .wbp .err { color:#dc2626; font-size:11px; }

    @media (max-width: 760px) {
        .wbp { padding:14px 10px; }
        .wbp-head { grid-template-columns:1fr; text-align:center; }
        .wbp-no { justify-self:center; }
        .wbp-2col, .wbp-sign { grid-template-columns:1fr; }
        .wbp-tbl-wrap { overflow-x:auto; }
        .wbp-tbl { min-width:620px; }
    }

    @media print {
        @page { size:A4; margin:8mm; }
        body { background:#fff !important; }
        .wbp { box-shadow:none; border:0; max-width:none; padding:0; }
        .no-print { display:none !important; }
        .wbp { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    }
</style>
