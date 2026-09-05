<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ __('purchases.purchase_no') }} #{{ $purchase->purchase_number ?? $purchase->id }}</title>
<style>
    /* نفس تقنية الخط المستخدمة في invoices/pdf.blade.php بالظبط - سيلكتور
       شامل، اسم الخط بحروف كبيرة من غير quotes، و !important، عشان
       dompdf يعرض العربي صح. */
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
                <div class="muted">{{ $purchase->branch?->name ?? '' }}</div>
            </td>
            <td style="width: 40%; text-align: left;">
                <span class="invoice-badge">
                    {{ __('purchases.purchase_no') }} #{{ $purchase->purchase_number ?? $purchase->id }}
                </span>
                <div class="muted" style="margin-top: 6px;">
                    {{ __('purchases.date') }}:
                    {{ optional($purchase->issue_date)->format('Y-m-d') ?? $purchase->created_at->format('Y-m-d') }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;" dir="rtl">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="box">
                    <div class="box-title">{{ __('purchases.supplier') }}</div>
                    <div class="box-value">{{ $purchase->supplier?->name ?? '-' }}</div>
                    @if ($purchase->supplier?->phone)
                        <div class="muted" dir="ltr">{{ $purchase->supplier->phone }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="box">
                    <div class="box-title">{{ __('purchases.payment_method') }}</div>
                    <div class="box-value">
                        @if ($purchase->isCredit())
                            {{ __('purchases.credit') }}
                        @else
                            {{ $purchase->paymentAccount?->name ?? __('purchases.payment_immediate') }}
                        @endif
                    </div>
                    @if ($purchase->supplier_invoice_number)
                        <div class="muted">{{ __('purchases.supplier_invoice_number') }}: {{ $purchase->supplier_invoice_number }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- جدول الأصناف: نفس تقنية invoices/pdf.blade.php (اتجاه ltr للجدول
         نفسه عشان الأعمدة الرقمية تترتب صح، مع الحفاظ على وضوح النص
         العربي جوه كل خلية). --}}
    <div dir="ltr">
        <table class="items">
            <thead>
                <tr dir="rtl">
                    <td>{{ __('purchases.code') }}</td>
                    <td>{{ __('purchases.product') }}</td>
                    <td>{{ __('purchases.quantity') }}</td>
                    <td>{{ __('purchases.unit_price') }}</td>
                    <td>{{ __('purchases.discount') }}</td>
                    <td>{{ __('purchases.tax') }}</td>
                    <td>{{ __('purchases.total') }}</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    @php
                        $lineTotal = ($item->unit_price * $item->quantity) - ($item->discount_amount ?? 0) + ($item->tax_amount ?? 0);
                    @endphp
                    <tr dir="rtl">
                        <td>{{ $item->product_code_snapshot ?? $item->product?->code ?? '-' }}</td>
                        <td>{{ $item->product_name_snapshot ?? $item->product?->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
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
            <td class="label">{{ __('purchases.subtotal') }}</td>
            <td>{{ number_format($purchase->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('purchases.discount_total') }}</td>
            <td>{{ number_format(($purchase->discount_amount ?? 0) + ($purchase->invoice_level_discount ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('purchases.tax_total') }}</td>
            <td>{{ number_format($purchase->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('purchases.shipping') }}</td>
            <td>{{ number_format($purchase->shipping_fee ?? 0, 2) }}</td>
        </tr>
        <tr class="grand-row">
            <td>{{ __('purchases.grand_total') }}</td>
            <td>{{ number_format($purchase->grand_total, 2) }}</td>
        </tr>
    </table>

    @if ($purchase->note)
        <div class="box" style="margin-top: 16px;" dir="rtl">
            <div class="box-title">{{ __('purchases.note') }}</div>
            <div>{{ $purchase->note }}</div>
        </div>
    @endif

</body>
</html>
