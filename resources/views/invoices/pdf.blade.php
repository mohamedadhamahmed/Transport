<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ __('invoices.invoice_no') }} #{{ $invoice->invoice_number ?? $invoice->id }}</title>
<style>
    /* نفس تقنية الخط اللي شغالة عندك في translation.blade.php بالظبط:
       * { font-family: DejaVu Sans !important; } - سيلكتور شامل، اسم
       الخط بحروف كبيرة من غير quotes، و !important. ده اللي بيخلي
       dompdf يوصل الخط الصح اللي بيدعم العربي بشكل سليم (بدون ما
       الحروف تطلع منفصلة أو مقلوبة). الاعتماد هنا على attribute
       dir="rtl" في الـ HTML (زي عندك بالظبط) مش على خاصية CSS
       direction، لإن كده اللي أثبت إنه شغال. */
    * {
        font-family: DejaVu Sans !important;
    }
    @page { margin: 20px 28px; }
    body {
        font-size: 12px;
        color: #1f2937;
    }
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .header-table td { vertical-align: top; padding: 0; }
    .brand { font-size: 18px; font-weight: bold; color: #0F1B4C; }
    .muted { color: #6b7280; font-size: 11px; }
    .invoice-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        color: #ffffff;
        background-color: #0F1B4C;
    }
    .box {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px 12px;
        margin-bottom: 14px;
    }
    .box-title { font-size: 10px; color: #9ca3af; margin-bottom: 4px; }
    .box-value { font-size: 13px; font-weight: bold; color: #0F1B4C; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.items thead td {
        background-color: #0F1B4C;
        color: #ffffff;
        font-size: 10px;
        padding: 8px 6px;
    }
    table.items tbody td {
        border-bottom: 1px solid #f0f0f0;
        padding: 7px 6px;
        font-size: 11px;
        text-align: center;
    }
    table.items tbody tr:nth-child(even) { background-color: #fafafa; }
    .totals-table { width: 260px; margin-top: 14px; margin-inline-start: auto; border-collapse: collapse; }
    .totals-table td { padding: 5px 6px; font-size: 12px; }
    .totals-table .label { color: #6b7280; }
    .totals-table .grand-row td {
        border-top: 2px solid #0F1B4C;
        font-weight: bold;
        color: #0F1B4C;
        font-size: 14px;
        padding-top: 8px;
    }
    .footer-note { margin-top: 26px; font-size: 10px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>

    <table class="header-table" dir="rtl">
        <tr>
            <td style="width: 60%;">
                <div class="brand">{{ config('app.name', 'ERP') }}</div>
                <div class="muted">{{ $invoice->branch?->name ?? '' }}</div>
            </td>
            <td style="width: 40%; text-align: left;">
                <span class="invoice-badge">
                    {{ __('invoices.invoice_no') }} #{{ $invoice->invoice_number ?? $invoice->id }}
                </span>
                <div class="muted" style="margin-top: 6px;">
                    {{ __('invoices.date') }}:
                    {{ $invoice->issue_date?->format('Y-m-d') ?? $invoice->created_at->format('Y-m-d') }}
                </div>
                <div class="muted">
                    {{ $invoice->is_finalized ? __('invoices.final') : __('invoices.draft') }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;" dir="rtl">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="box">
                    <div class="box-title">{{ __('invoices.customer') }}</div>
                    <div class="box-value">{{ $invoice->customer?->name ?? '-' }}</div>
                    @if ($invoice->customer?->phone)
                        <div class="muted" dir="ltr">{{ $invoice->customer->phone }}</div>
                    @endif
                    @if ($invoice->customer?->tax_number)
                        <div class="muted">{{ __('invoices.tax_number') }}: {{ $invoice->customer->tax_number }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="box">
                    <div class="box-title">{{ __('invoices.payment_method') }}</div>
                    <div class="box-value">{{ __('invoices.' . $invoice->payment_method) }}</div>
                    @if ($invoice->purchase_order_number)
                        <div class="muted">{{ __('invoices.po_number') }}: {{ $invoice->purchase_order_number }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- جدول الأصناف: زي translation.blade.php بالظبط، بيتلف جوه div
         dir="ltr" لإن الأعمدة أغلبها أرقام، وده بيخلي شكل الجدول
         مضبوط، مع keep النص العربي (اسم الصنف) واضح جوه كل خلية. --}}
    <div dir="ltr">
        <table class="items">
            <thead>
                <tr dir="rtl">
                    <td>{{ __('invoices.product') }}</td>
                    <td>{{ __('invoices.unit_price') }}</td>
                    <td>{{ __('invoices.quantity') }}</td>
                    <td>{{ __('invoices.discount') }}</td>
                    <td>{{ __('invoices.tax') }}</td>
                    <td>{{ __('invoices.total') }}</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    @php
                        $lineSubtotal = ($item->unit_price * $item->quantity) - ($item->discount_amount ?? 0);
                        $lineTotal = $lineSubtotal + ($item->tax_amount ?? 0);
                    @endphp
                    <tr dir="rtl">
                        <td>{{ $item->product_name_snapshot ?? $item->product?->name ?? '-' }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                        <td>{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                        <td>{{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="totals-table" dir="rtl">
        <tr>
            <td class="label">{{ __('invoices.subtotal') }}</td>
            <td>{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('invoices.discount_total') }}</td>
            <td>{{ number_format(($invoice->discount_amount ?? 0) + ($invoice->invoice_level_discount ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('invoices.tax_total') }}</td>
            <td>{{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr class="grand-row">
            <td>{{ __('invoices.grand_total') }}</td>
            <td>
                {{ number_format($invoice->subtotal + $invoice->tax_amount - ($invoice->invoice_level_discount ?? 0), 2) }}
            </td>
        </tr>
    </table>

    @if ($invoice->note)
        <div class="box" style="margin-top: 16px;" dir="rtl">
            <div class="box-title">{{ __('invoices.note') }}</div>
            <div>{{ $invoice->note }}</div>
        </div>
    @endif

    <div class="footer-note" dir="rtl">
        {{ __('invoices.pdf_footer_note') }}
    </div>

</body>
</html>
