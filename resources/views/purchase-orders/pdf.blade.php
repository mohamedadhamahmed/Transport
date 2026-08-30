<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ __('purchase_orders.order_no') }} #{{ $purchaseOrder->id }}</title>
<style>
    /* نفس تقنية العربي المستخدمة في التسعيرات والفواتير بالظبط:
       * { font-family: DejaVu Sans !important; } - سيلكتور شامل، اسم
       الخط بحروف كبيرة من غير quotes، و !important - بالإضافة لـ
       dir="rtl"/dir="ltr" صريح على كل جدول (مش خاصية CSS direction) -
       ده اللي بيخلي dompdf يطلع العربي سليم بدون أي مكتبة تشكيل
       إضافية. نفس تصميم التسعيرات بالظبط، بس موجّه للمورد بدل العميل. */
    * {
        font-family: DejaVu Sans !important;
    }
    @page { margin: 18px 24px; }
    body {
        font-size: 12px;
        color: #212529;
    }
    table { border-collapse: collapse; }
    .header-table { width: 100%; margin-bottom: 10px; }
    .header-table td { vertical-align: top; padding: 0; font-size: 11px; line-height: 1.5; }
    .company-name { font-size: 15px; font-weight: bold; color: #111; }
    .header-rule { border-bottom: 2px solid #2b2b2b; padding-bottom: 10px; margin-bottom: 10px; }
    .badge-quote {
        border: 2px solid #2b2b2b;
        border-radius: 8px;
        width: 320px;
        margin: 10px auto 16px auto;
        text-align: center;
        background-color: #f1f3f5;
        font-weight: bold;
        color: #333;
        font-size: 15px;
        padding: 8px;
    }
    .badge-quote .sub { font-size: 12px; color: #666; font-weight: normal; }
    .info-table { width: 100%; font-size: 12px; }
    .info-table th, .info-table td {
        border: 1px solid #ced4da;
        padding: 7px 10px;
        text-align: center;
    }
    .info-table th { background-color: #f1f3f5; color: #333; font-weight: bold; width: 40%; }
    table.items { width: 100%; margin-top: 4px; }
    table.items th, table.items td {
        border: 1px solid #dee2e6;
        padding: 7px 6px;
        text-align: center;
        font-size: 11px;
    }
    table.items thead th {
        background-color: #343a40;
        color: #ffffff;
        font-size: 11px;
    }
    table.items thead .en { font-size: 9px; font-weight: normal; }
    table.items tbody tr:nth-child(even) { background-color: #fafbfc; }
    .totals-table { width: 100%; margin-top: 10px; }
    .totals-table th, .totals-table td {
        border: 1px solid #dee2e6;
        padding: 8px 6px;
        text-align: center;
    }
    .totals-table th { background-color: #f1f3f5; color: #333; font-size: 11px; }
    .totals-table .en { font-size: 9px; font-weight: normal; }
    .totals-table .value { font-size: 14px; font-weight: bold; }
    .totals-table .discount-value { color: #d9534f; }
    .totals-table .grand-cell { background-color: #e8f5e9; color: #28a745; font-size: 16px; font-weight: bold; }
    .notes-box {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 12px;
        color: #856404;
        margin-top: 14px;
    }
    .footer-note { margin-top: 16px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 6px; }
</style>
</head>
<body>

    @php
        // بيانات الشركة ثنائية اللغة + الشعار - نفس أسلوب التسعيرات
        // بالظبط. عدّل الأسطر دي لو حابب تربطها بجدول إعدادات حقيقي.
        $companyNameAr = $purchaseOrder->branch?->name ?? config('app.name', '');
        $companyNameEn = $purchaseOrder->branch?->name_en ?? config('app.name', '');
        $companyAddressAr = $purchaseOrder->branch?->address ?? '';
        $companyAddressEn = $purchaseOrder->branch?->address_en ?? '';
        $companyTaxNo = $purchaseOrder->branch?->tax_no ?? '';
        $companyLogo = public_path('images/sidebar-icon.png');

        $grossSubtotal = $purchaseOrder->subtotal + $purchaseOrder->discount_amount;
        $totalDiscount = $purchaseOrder->discount_amount + $purchaseOrder->invoice_level_discount;
        $taxableAmount = $purchaseOrder->subtotal - $purchaseOrder->invoice_level_discount;
        $vatPercent = $taxableAmount > 0 ? round(($purchaseOrder->tax_amount / $taxableAmount) * 100) : 15;
    @endphp

    {{-- هيدر ثنائي اللغة: بيانات إنجليزي - شعار في النص - بيانات عربي --}}
    <div class="header-rule">
        <table class="header-table" dir="ltr">
            <tr>
                <td style="width: 34%; text-align: left;">
                    <span class="company-name">{{ $companyNameEn }}</span><br>
                    @if ($companyAddressEn)
                        {{ $companyAddressEn }}<br>
                    @endif
                    @if ($companyTaxNo)
                        <strong>Tax No: {{ $companyTaxNo }}</strong>
                    @endif
                </td>
                <td style="width: 32%; text-align: center;">
                    @if (file_exists($companyLogo))
                        <img src="{{ $companyLogo }}" style="width: 80px; height: 70px; object-fit: contain;">
                    @endif
                </td>
                <td style="width: 34%; text-align: right;" dir="rtl">
                    <span class="company-name">{{ $companyNameAr }}</span><br>
                    @if ($companyAddressAr)
                        {{ $companyAddressAr }}<br>
                    @endif
                    @if ($companyTaxNo)
                        <strong>الرقم الضريبي: {{ $companyTaxNo }}</strong>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="badge-quote" dir="rtl">
        {{ __('purchase_orders.title_singular') }}
        <div class="sub">PURCHASE ORDER TO SUPPLIER</div>
    </div>

    {{-- بيانات المورد + بيانات الأمر - جنب بعض في صف واحد --}}
    <table style="width: 100%; margin-bottom: 14px;" dir="rtl">
        <tr>
            <td style="width: 49%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <th>{{ __('purchase_orders.supplier') }} / Supplier</th>
                        <td style="font-weight: bold;">{{ $purchaseOrder->supplier?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>الرقم الضريبي / Tax No</th>
                        <td>{{ $purchaseOrder->supplier?->tax_no ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <th>{{ __('purchase_orders.date') }} / Date</th>
                        <td>{{ optional($purchaseOrder->issue_date)->format('Y-m-d') ?? $purchaseOrder->created_at->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('purchase_orders.order_no') }} / Order No</th>
                        <td style="font-weight: bold; color: #d9534f;">#{{ $purchaseOrder->id }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- جدول الأصناف --}}
    <div dir="ltr">
        <table class="items">
            <thead>
                <tr dir="rtl">
                    <th>#</th>
                    <th>{{ __('purchase_orders.code') }}<br><span class="en">Item Code</span></th>
                    <th>{{ __('purchase_orders.product') }}<br><span class="en">Item Name</span></th>
                    <th>{{ __('purchase_orders.unit_price') }}<br><span class="en">Price</span></th>
                    <th>{{ __('purchase_orders.quantity') }}<br><span class="en">Qty</span></th>
                    <th>{{ __('purchase_orders.total') }}<br><span class="en">Total</span></th>
                    <th>{{ __('purchase_orders.discount') }}<br><span class="en">Discount</span></th>
                    <th>الصافي<br><span class="en">Net Total</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseOrder->items as $index => $item)
                    @php
                        $grossLineTotal = $item->unit_price * $item->quantity;
                        $netLineTotal = $grossLineTotal - ($item->discount_amount ?? 0);
                    @endphp
                    <tr dir="rtl">
                        <td>{{ $index + 1 }}</td>
                        <td dir="ltr" style="font-family: monospace;">{{ $item->product_code_snapshot ?? $item->product?->code ?? '-' }}</td>
                        <td style="text-align: right; white-space: normal;">{{ $item->product_name_snapshot ?? $item->product?->name ?? '-' }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($grossLineTotal, 2) }}</td>
                        <td>{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                        <td style="font-weight: bold;">{{ number_format($netLineTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- إجمالي قبل الخصم + إجمالي الخصم --}}
    <table class="totals-table" dir="rtl">
        <tr>
            <th>{{ __('purchase_orders.subtotal') }}<br><span class="en">Sub Total</span></th>
            <th>{{ __('purchase_orders.discount_total') }}<br><span class="en">Total Discount</span></th>
        </tr>
        <tr>
            <td class="value">{{ number_format($grossSubtotal, 2) }}</td>
            <td class="value discount-value">{{ number_format($totalDiscount, 2) }}</td>
        </tr>
    </table>

    {{-- المبلغ الخاضع للضريبة + الضريبة + الإجمالي التقديري --}}
    <table class="totals-table" style="margin-top: -1px;" dir="rtl">
        <tr>
            <th>المبلغ الخاضع للضريبة<br><span class="en">Taxable Amount</span></th>
            <th>ضريبة القيمة المضافة ({{ $vatPercent }}%)<br><span class="en">VAT Amount</span></th>
            <th class="grand-cell">{{ __('purchase_orders.grand_total') }}<br><span class="en">Grand Total</span></th>
        </tr>
        <tr>
            <td class="value">{{ number_format($taxableAmount, 2) }}</td>
            <td class="value">{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
            <td class="grand-cell">{{ number_format($purchaseOrder->grand_total, 2) }} SAR</td>
        </tr>
    </table>

    @if ($purchaseOrder->note)
        <div class="notes-box" dir="rtl">
            <strong>{{ __('purchase_orders.note') }}:</strong> {{ $purchaseOrder->note }}
        </div>
    @endif

    <div class="footer-note" dir="rtl">
        @if ($companyAddressAr || $companyAddressEn)
            <span>{{ $companyAddressAr }}</span>
            @if ($companyAddressAr && $companyAddressEn) | @endif
            <span dir="ltr">{{ $companyAddressEn }}</span>
            <br>
        @endif
        {{ __('purchase_orders.pdf_disclaimer') }}
    </div>

</body>
</html>
