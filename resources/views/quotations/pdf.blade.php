<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ __('quotations.quotation_no') }} #{{ $quotation->id }}</title>
<style>
    /* نفس تقنية العربي المستخدمة في invoices/pdf.blade.php بالظبط:
       * { font-family: DejaVu Sans !important; } - سيلكتور شامل، اسم
       الخط بحروف كبيرة من غير quotes، و !important - بالإضافة لـ
       dir="rtl"/dir="ltr" صريح على كل جدول (مش خاصية CSS direction) -
       ده اللي بيخلي dompdf يطلع العربي سليم بدون أي مكتبة تشكيل
       إضافية. الشكل هنا بقى نسخة قريبة جدًا من تصميم شاشة الطباعة
       القديمة اللي بعتّها (print_order_perice_to_customer.blade.php)،
       بس معمول بجداول HTML بحتة (مفيش flexbox) عشان dompdf يقدر
       يرندرها صح - flexbox مش مدعوم بشكل موثوق في dompdf. */
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
    .bank-box {
        border: 2px dashed #6c757d;
        border-radius: 8px;
        background-color: #fafbfc;
        padding: 10px;
        text-align: center;
        font-size: 12px;
        color: #495057;
        margin-top: 14px;
    }
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
        // ================================================================
        // بيانات الشركة ثنائية اللغة + الشعار + الحساب البنكي - في
        // ملفك القديم كانت جايه من جدول إعدادات عام (Nameen/Namear/
        // camplogo/bankname...إلخ). عندنا دلوقتي مفيش جدول إعدادات
        // بنفس الأعمدة دي، فحطيت قيم افتراضية معقولة من بيانات الفرع
        // + config('app.name'). عدّلي الأسطر السبعة دي بس لو حابة
        // تربطيها بجدول الإعدادات الحقيقي عندك (أو قوليلي شكل الجدول
        // وأظبطها بنفسي).
        $companyNameAr = $quotation->branch?->name ?? config('app.name', '');
        $companyNameEn = $quotation->branch?->name_en ?? config('app.name', '');
        $companyAddressAr = $quotation->branch?->address ?? '';
        $companyAddressEn = $quotation->branch?->address_en ?? '';
        $companyTaxNo = $quotation->branch?->tax_no ?? '';
        $companyLogo = public_path('images/sidebar-icon.png');
        // لو عايزة تظهري صندوق بيانات الحساب البنكي في أسفل التسعيرة،
        // احطي القيم هنا (مثال: 'البنك الأهلي السعودي') - لو سبتيها
        // null الصندوق مش هيظهر خالص.
        $bankName = null;
        $bankAccountNumber = null;
        $bankIban = null;

        $grossSubtotal = $quotation->subtotal + $quotation->discount_amount;
        $totalDiscount = $quotation->discount_amount + $quotation->invoice_level_discount;
        $taxableAmount = $quotation->subtotal - $quotation->invoice_level_discount;
        $vatPercent = $taxableAmount > 0 ? round(($quotation->tax_amount / $taxableAmount) * 100) : 15;
    @endphp

    {{-- هيدر ثنائي اللغة: بيانات إنجليزي (يمين الشاشة بصريًا لإنه جوه
         dir="ltr") - شعار في النص - بيانات عربي (شمال الشاشة بصريًا) -
         بجدول HTML عادي بدل الـ flexbox اللي كانت مستخدمة في النسخة
         الأصلية (dompdf مبيدعمش flexbox بشكل موثوق). --}}
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
        {{ __('quotations.title_singular') }}
        <div class="sub">QUOTATION TO CUSTOMER</div>
    </div>

    {{-- بيانات العميل + بيانات التسعيرة - جنب بعض في صف واحد --}}
    <table style="width: 100%; margin-bottom: 14px;" dir="rtl">
        <tr>
            <td style="width: 49%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <th>{{ __('quotations.customer') }} / Client</th>
                        <td style="font-weight: bold;">{{ $quotation->customer?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('quotations.tax_number') }} / Tax No</th>
                        <td>{{ $quotation->customer?->tax_no ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <th>{{ __('quotations.date') }} / Date</th>
                        <td>{{ $quotation->created_at->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('quotations.quotation_no') }} / Quote No</th>
                        <td style="font-weight: bold; color: #d9534f;">#{{ $quotation->id }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- جدول الأصناف - نفس أعمدة الملف القديم بالظبط (الإجمالي هنا
         قبل الخصم، من غير عمود ضريبة لكل صنف - الضريبة بتتحسب مجمّعة
         تحت في جدول الإجماليات، زي بالظبط النسخة القديمة). --}}
    <div dir="ltr">
        <table class="items">
            <thead>
                <tr dir="rtl">
                    <th>#</th>
                    <th>{{ __('quotations.code') }}<br><span class="en">Item Code</span></th>
                    <th>{{ __('quotations.product') }}<br><span class="en">Item Name</span></th>
                    <th>{{ __('quotations.unit_price') }}<br><span class="en">Price</span></th>
                    <th>{{ __('quotations.quantity') }}<br><span class="en">Qty</span></th>
                    <th>{{ __('quotations.total') }}<br><span class="en">Total</span></th>
                    <th>{{ __('quotations.discount') }}<br><span class="en">Discount</span></th>
                    <th>{{ __('quotations.net_total') }}<br><span class="en">Net Total</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $index => $item)
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
            <th>{{ __('quotations.subtotal') }}<br><span class="en">Sub Total</span></th>
            <th>{{ __('quotations.discount_total') }}<br><span class="en">Total Discount</span></th>
        </tr>
        <tr>
            <td class="value">{{ number_format($grossSubtotal, 2) }}</td>
            <td class="value discount-value">{{ number_format($totalDiscount, 2) }}</td>
        </tr>
    </table>

    {{-- المبلغ الخاضع للضريبة + الضريبة + الإجمالي الكلي --}}
    <table class="totals-table" style="margin-top: -1px;" dir="rtl">
        <tr>
            <th>{{ __('quotations.taxable_amount') }}<br><span class="en">Taxable Amount</span></th>
            <th>{{ __('quotations.vat_amount') }} ({{ $vatPercent }}%)<br><span class="en">VAT Amount</span></th>
            <th class="grand-cell">{{ __('quotations.grand_total') }}<br><span class="en">Grand Total</span></th>
        </tr>
        <tr>
            <td class="value">{{ number_format($taxableAmount, 2) }}</td>
            <td class="value">{{ number_format($quotation->tax_amount, 2) }}</td>
            <td class="grand-cell">{{ number_format($quotation->grand_total, 2) }} SAR</td>
        </tr>
    </table>

    @if ($bankName || $bankAccountNumber || $bankIban)
        <div class="bank-box" dir="rtl">
            @if ($bankName)
                <strong>{{ __('quotations.bank') }}:</strong> {{ $bankName }}
            @endif
            @if ($bankAccountNumber)
                &nbsp;|&nbsp; <strong>{{ __('quotations.bank_account_number') }}:</strong> <span dir="ltr">{{ $bankAccountNumber }}</span>
            @endif
            @if ($bankIban)
                &nbsp;|&nbsp; <strong>IBAN:</strong> <span dir="ltr">{{ $bankIban }}</span>
            @endif
        </div>
    @endif

    @if ($quotation->note)
        <div class="notes-box" dir="rtl">
            <strong>{{ __('quotations.note') }}:</strong> {{ $quotation->note }}
        </div>
    @endif

    <div class="footer-note" dir="rtl">
        @if ($companyAddressAr || $companyAddressEn)
            <span>{{ $companyAddressAr }}</span>
            @if ($companyAddressAr && $companyAddressEn) | @endif
            <span dir="ltr">{{ $companyAddressEn }}</span>
            <br>
        @endif
        {{ __('quotations.pdf_disclaimer') }}
    </div>

</body>
</html>
