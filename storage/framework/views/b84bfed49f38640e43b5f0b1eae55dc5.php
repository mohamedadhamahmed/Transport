
<style>
    :root {
        --brand-navy: #0F1B4C;
        --brand-blue: #1456E8;
        --brand-orange: #F5811E;
        --ink: #1F2937;
        --muted: #6B7280;
        --border: #E5E7EB;
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'Cairo', 'DejaVu Sans', sans-serif;
        background: #F3F4F6;
        margin: 0;
        padding: 20px;
        color: var(--ink);
    }

    .doc-wrap {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }

    .doc-topbar {
        display: flex;
        justify-content: center;
        padding: 14px 0;
    }
    .doc-topbar button {
        background: linear-gradient(90deg, var(--brand-navy), var(--brand-blue));
        color: #fff;
        border: none;
        padding: 10px 26px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .doc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 22px 28px;
        border-bottom: 3px solid var(--brand-orange);
        gap: 16px;
        background: linear-gradient(135deg, var(--brand-navy), #1B2C63);
        color: #fff;
    }
    /* بيانات الشركة ثنائية اللغة (عربي / شعار / إنجليزي) - نفس تقسيم
       الأعمدة التلاتة المستخدم في resources/views/invoices/returns/print.blade.php
       بس بألوان قسم الحسابات. لو مفيش اسم إنجليزي، partials.print-header
       بيسيب العمود ده من غير ما يطبعه فيرجع الهيدر عمودين بس. */
    .doc-header .company-block { flex: 1; min-width: 0; text-align: center; }
    .doc-header .company-block .name { font-weight: 700; font-size: 15px; color: #fff; }
    .doc-header .company-block p { font-size: 11px; color: rgba(255,255,255,.65); margin: 2px 0; }
    .doc-header .logo-block { flex: 0 0 auto; }
    .doc-header .logo-block img.logo {
        width: 72px;
        height: 72px;
        object-fit: contain;
        border-radius: 10px;
        background: #fff;
        padding: 6px;
    }

    .doc-type-badge {
        text-align: center;
        padding: 16px 0 4px;
    }
    .doc-type-badge span {
        display: inline-block;
        padding: 7px 26px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        background: var(--brand-blue);
        letter-spacing: .3px;
    }
    .doc-type-badge .doc-no {
        display: block;
        margin-top: 8px;
        font-size: 13px;
        color: var(--muted);
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        padding: 18px 28px;
        background: #F9FAFB;
        margin: 16px 28px 0;
        border-radius: 10px;
    }
    .meta-grid .label { font-size: 11px; color: var(--muted); margin-bottom: 3px; }
    .meta-grid .value { font-size: 13px; font-weight: 700; color: var(--ink); }

    .parties-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        padding: 20px 28px 0;
    }
    .party-card {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
    }
    .party-card .title {
        font-size: 11px; color: var(--muted); margin-bottom: 8px;
        font-weight: 700; text-transform: uppercase;
    }
    .party-card .name { font-size: 14px; font-weight: 700; color: var(--brand-navy); }
    .party-card .row { display: flex; justify-content: space-between; font-size: 12px; padding: 4px 0; border-bottom: 1px dashed var(--border); }
    .party-card .row:last-child { border-bottom: none; }
    .party-card .row .k { color: var(--muted); }
    .party-card .row .v { font-weight: 600; color: var(--ink); }

    .items-table-wrap { padding: 20px 28px 0; }
    table.items-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    table.items-table thead th {
        background: var(--brand-navy);
        color: #fff;
        font-weight: 700;
        font-size: 11px;
        padding: 10px 8px;
        text-align: center;
    }
    table.items-table tbody td {
        padding: 9px 8px;
        text-align: center;
        border-bottom: 1px solid var(--border);
        color: var(--ink);
    }
    table.items-table tbody tr:nth-child(even) { background: #FAFAFB; }
    table.items-table tfoot td {
        padding: 10px 8px;
        text-align: center;
        font-weight: 700;
        background: #F3F4F6;
        border-top: 2px solid var(--brand-navy);
    }

    .amount-box {
        margin: 20px 28px 0;
        border-radius: 10px;
        overflow: hidden;
        background: linear-gradient(90deg, var(--brand-navy), var(--brand-blue));
        color: #fff;
        padding: 16px 20px;
        text-align: center;
    }
    .amount-box .label { font-size: 12px; opacity: .75; }
    .amount-box .value { font-size: 26px; font-weight: 700; margin-top: 4px; }

    .amount-words-box {
        margin: 12px 28px 0;
        border: 1px dashed var(--brand-blue);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 12.5px;
        text-align: center;
        color: var(--ink);
    }

    .totals-box { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin: 16px 28px 0; }
    .totals-box .row {
        display: flex; justify-content: space-between;
        padding: 10px 16px; font-size: 13px;
        border-bottom: 1px solid var(--border);
        color: var(--ink);
    }
    .totals-box .row.grand {
        background: linear-gradient(90deg, var(--brand-navy), var(--brand-blue));
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        border-bottom: none;
    }

    .note-box {
        margin: 16px 28px 0;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 12.5px;
    }
    .note-box .label { font-size: 11px; color: var(--muted); margin-bottom: 4px; }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        padding: 32px 28px 8px;
    }
    .signature-box { text-align: center; font-size: 12px; color: var(--muted); }
    .signature-line {
        border-top: 1px solid var(--ink);
        margin-top: 40px;
        padding-top: 6px;
        font-weight: 600;
        color: var(--ink);
    }

    .doc-footer {
        text-align: center;
        padding: 16px 28px 24px;
        border-top: 1px solid var(--border);
        font-size: 11px;
        color: var(--muted);
        margin-top: 12px;
    }

    @media print {
        .doc-topbar { display: none !important; }
        body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .doc-wrap { box-shadow: none; border-radius: 0; max-width: 100%; margin: 0; }
        @page { size: A4; margin: 8mm; }
    }
</style>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/partials/print-styles.blade.php ENDPATH**/ ?>