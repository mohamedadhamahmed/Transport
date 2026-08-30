<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Quotation #{{ $quotation->id }}</title>
<style>
    * {
        font-family: 'DejaVu Sans', sans-serif !important;
        box-sizing: border-box;
    }

    @page {
        size: a4;
        margin: 10mm 8mm;
    }

    body {
        font-size: 11px;
        color: #1a202c;
        margin: 0;
        padding: 0;
        background: #ffffff;
        line-height: 1.45;
    }

    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 6px 8px; text-align: center; vertical-align: middle; }

    .bordered { border: 1px solid #cbd5e1; }
    .bordered th, .bordered td { border: 1px solid #cbd5e1; }

    /* ============ Top decorative strip ============ */
    .top-strip {
        width: 100%;
        height: 5px;
        background-color: #c9a227;
        background-image: linear-gradient(to left, #0f2942 0%, #1a365d 55%, #c9a227 100%);
        margin-bottom: 10px;
    }

    /* ============ Header card ============ */
    .header-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 16px;
        background-color: #ffffff;
        box-shadow: 0 1px 4px rgba(15, 41, 66, 0.08);
    }

    .header-table td { border: none; vertical-align: middle; padding: 0 4px; }
    .company-name { font-size: 14px; font-weight: bold; color: #0f2942; letter-spacing: 0.3px; }
    .company-line { font-size: 8.5px; color: #64748b; display: block; margin-top: 2px; }

    .logo-frame {
        width: 74px;
        height: 74px;
        border: 2px solid #c9a227;
        border-radius: 50%;
        text-align: center;
        vertical-align: middle;
        margin: 0 auto;
        padding: 6px;
    }
    .logo-frame img {
        max-width: 58px;
        max-height: 58px;
        object-fit: contain;
    }

    .header-divider {
        width: 100%;
        margin: 10px 0 4px;
    }
    .header-divider .thick { height: 2.5px; background-color: #0f2942; width: 100%; }
    .header-divider .thin { height: 1px; background-color: #c9a227; width: 100%; margin-top: 1.5px; }

    /* ============ Title banner + reference badge ============ */
    .banner-wrap { width: 100%; margin: 12px 0; }
    .quote-banner {
        background-color: #0f2942;
        background-image: linear-gradient(to left, #0f2942 0%, #24466e 100%);
        color: #ffffff;
        font-size: 15px;
        font-weight: bold;
        padding: 10px 8px;
        text-align: center;
        letter-spacing: 0.5px;
        border-radius: 5px;
        border: 1px solid #0f2942;
    }
    .quote-banner .sub {
        font-size: 9px;
        font-weight: normal;
        color: #e9c766;
        display: block;
        letter-spacing: 3px;
        margin-top: 3px;
    }
    .ref-badge {
        border: 1px solid #c9a227;
        background-color: #fffaf0;
        color: #0f2942;
        font-size: 9.5px;
        font-weight: bold;
        text-align: center;
        border-radius: 5px;
        padding: 6px 8px;
    }
    .ref-badge span {
        display: block;
        font-weight: normal;
        font-size: 8px;
        color: #64748b;
        margin-top: 1px;
    }

    /* ============ Quote/customer meta table ============ */
    .meta-table {
        border-radius: 4px;
        overflow: hidden;
    }
    .meta-table th {
        background-color: #f4f7fb;
        font-size: 9.5px;
        color: #1a365d;
        width: 25%;
        text-align: right;
        font-weight: bold;
        border-color: #cbd5e1;
    }
    .meta-table td {
        text-align: right;
        font-weight: bold;
        width: 25%;
        color: #1a202c;
        background-color: #ffffff;
    }

    .section-caption {
        font-size: 9.5px;
        font-weight: bold;
        color: #1a365d;
        margin-bottom: 4px;
        border-right: 3px solid #c9a227;
        padding-right: 6px;
    }

    /* ============ Items table ============ */
    .items-table { margin-top: 4px; }
    .items-table thead th {
        background-color: #0f2942;
        color: #ffffff;
        font-weight: bold;
        font-size: 9.5px;
        padding: 9px 6px;
        border-color: #0f2942;
        letter-spacing: 0.2px;
    }
    .items-table thead th span { color: #e9c766; }
    .items-table tbody td {
        border-color: #cbd5e1;
        color: #2d3748;
        font-size: 9.5px;
    }
    .items-table tbody tr:nth-child(even) { background-color: #f4f7fb; }
    .items-table tbody tr:last-child td { border-bottom: 2px solid #1a365d; }
    .item-name-cell {
        text-align: right;
        padding-right: 6px;
        font-weight: bold;
        color: #0f2942;
    }

    /* ============ Totals summary card ============ */
    .totals-card {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 41, 66, 0.06);
    }
    .totals-card table { width: 100%; }
    .totals-card td {
        padding: 7px 12px;
        border-bottom: 1px dashed #cbd5e1;
    }
    .totals-card tr:first-child td { border-top: none; }
    .totals-card tr td:first-child {
        text-align: right;
        color: #2d3748;
        font-weight: bold;
        width: 60%;
        background-color: #ffffff;
    }
    .totals-card tr td:last-child {
        text-align: left;
        direction: ltr;
        font-weight: bold;
        color: #1a202c;
        width: 40%;
        background-color: #f4f7fb;
    }
    .net-total-row td {
        background-color: #0f2942 !important;
        color: #ffffff !important;
        font-size: 13px;
        border-bottom: none !important;
        padding: 10px 12px !important;
    }
    .net-total-row td:last-child { background-color: #0f2942 !important; }

    /* ============ Side boxes ============ */
    .words-box {
        border: 1px solid #cbd5e1;
        background-color: #f4f7fb;
        border-right: 3px solid #1a365d;
        padding: 8px 10px;
        margin-top: 8px;
        font-size: 9.5px;
        text-align: left;
        border-radius: 4px;
    }
    .words-box strong { color: #0f2942; }

    .notes-box {
        border: 1px solid #c9a227;
        background-color: #fffaf0;
        border-right: 3px solid #c9a227;
        padding: 8px 10px;
        margin-top: 8px;
        font-size: 9.5px;
        text-align: right;
        color: #744210;
        border-radius: 4px;
    }

    /* ============ Signature box ============ */
    .signature-wrap { width: 100%; margin-top: 22px; }
    .signature-box {
        border-top: 1.5px solid #64748b;
        padding-top: 6px;
        text-align: center;
    }
    .signature-role {
        font-size: 9.5px;
        font-weight: bold;
        color: #1a365d;
    }
    .signature-name {
        font-size: 9px;
        color: #2d3748;
        margin-top: 2px;
    }
    .signature-label {
        font-size: 8px;
        color: #64748b;
        margin-top: 10px;
    }

    /* ============ Footer ============ */
    .footer-bar {
        margin-top: 16px;
        text-align: center;
        border-top: 1px solid #cbd5e1;
        padding-top: 8px;
        font-size: 8.5px;
        color: #64748b;
    }
    .footer-bar .footer-brand {
        color: #1a365d;
        font-weight: bold;
        font-size: 9px;
        margin-bottom: 3px;
    }
    .footer-note {
        margin-top: 4px;
        font-size: 8px;
        color: #64748b;
    }
</style>
</head>
<body>

    @php
        if (!function_exists('numberToWords')) {
            function numberToWords($num)
            {
                $num = (int) str_replace([',', ''], '', trim((string) $num));
                if (!$num) { return ''; }

                $list1 = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
                $list2 = ['', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred'];
                $list3 = ['', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion', 'octillion', 'nonillion', 'decillion'];

                $numLength = strlen((string) $num);
                $levels = (int) (($numLength + 2) / 3);
                $maxLength = $levels * 3;
                $numStr = substr('00' . $num, -$maxLength);
                $numLevels = str_split($numStr, 3);

                $words = [];
                for ($i = 0; $i < count($numLevels); $i++) {
                    $levels--;
                    $hundredsDigit = (int) ($numLevels[$i] / 100);
                    $hundreds = $hundredsDigit ? ' ' . $list1[$hundredsDigit] . ' hundred ' : '';
                    $tensVal = (int) ($numLevels[$i] % 100);
                    $singles = '';

                    if ($tensVal < 20) {
                        $tens = $tensVal ? ' and ' . $list1[$tensVal] . ' ' : '';
                    } else {
                        $tensDigit = (int) ($tensVal / 10);
                        $singlesDigit = (int) ($numLevels[$i] % 10);
                        $tens = ' and ' . $list2[$tensDigit] . ' ';
                        $singles = ' ' . $list1[$singlesDigit] . ' ';
                    }

                    $words[] = $hundreds . $tens . $singles . (($levels && (int) $numLevels[$i]) ? ' ' . $list3[$levels] . ' ' : '');
                }

                $wordsStr = implode(' ', $words);
                $wordsStr = preg_replace('/^\s\b(and)/', '', $wordsStr);

                return ucfirst(trim($wordsStr));
            }
        }

        $companyNameEn = isset($Nameen) ? $Nameen : ($quotation->branch?->name_en ?? config('app.name', ''));
        $companyNameAr = isset($Namear) ? $Namear : ($quotation->branch?->name ?? config('app.name', ''));
        $companyDescEn = isset($describtionen) ? $describtionen : '';
        $companyDescAr = isset($describtionar) ? $describtionar : '';
        $companySTEn = isset($STen) ? $STen : '';
        $companySTAr = isset($STar) ? $STar : '';
        $companyTaxEn = isset($Taxen) ? $Taxen : '';
        $companyTaxAr = isset($Taxar) ? $Taxar : '';
        $companyAddressAr = isset($addressar) ? $addressar : ($quotation->branch?->address ?? '');
        $companyAddressEn = isset($addressen) ? $addressen : ($quotation->branch?->address_en ?? '');

        $logoFile = isset($camplogo) ? $camplogo : null;
        $companyLogoPath = $logoFile && file_exists(public_path('assets/img/brand/' . $logoFile))
            ? public_path('assets/img/brand/' . $logoFile)
            : public_path('images/sidebar-icon.png');

        $grossSubtotal = $quotation->subtotal + $quotation->discount_amount;
        $totalDiscount = $quotation->discount_amount + $quotation->invoice_level_discount;
        $taxableAmount = $quotation->subtotal - $quotation->invoice_level_discount;
        $vatPercent = $taxableAmount > 0 ? round(($quotation->tax_amount / $taxableAmount) * 100) : 15;

        [$whole, $decimal] = explode('.', number_format((float) $quotation->grand_total, 2, '.', ''));
        $decimalDigits = str_split($decimal);
        $decimalValue = (isset($decimalDigits[0]) && $decimalDigits[0] === '0') ? (int) $decimalDigits[1] : (int) $decimal;
        $decimalWords = $decimalValue !== 0 ? numberToWords($decimalValue) : 'Zero';
        $wholeWords = numberToWords((int) $whole);
    @endphp

    <div class="top-strip"></div>

    <!-- Header card -->
    <div class="header-card">
        <table class="header-table">
            <tr>
                <td style="width: 35%; text-align: left;" dir="ltr">
                    <span class="company-name">{{ $companyNameEn }}</span><br>
                    @if ($companyDescEn) <span class="company-line">{{ $companyDescEn }}</span> @endif
                    @if ($companySTEn) <span class="company-line">{{ $companySTEn }}</span> @endif
                    @if ($companyTaxEn) <span class="company-line">{{ $companyTaxEn }}</span> @endif
                </td>
                <td style="width: 30%; text-align: center;">
                    @if (file_exists($companyLogoPath))
                        <div class="logo-frame">
                            <img src="{{ $companyLogoPath }}">
                        </div>
                    @endif
                </td>
                <td style="width: 35%; text-align: right;" dir="rtl">
                    <span class="company-name">{{ $companyNameAr }}</span><br>
                    @if ($companyDescAr) <span class="company-line">{{ $companyDescAr }}</span> @endif
                    @if ($companySTAr) <span class="company-line">{{ $companySTAr }}</span> @endif
                    @if ($companyTaxAr) <span class="company-line">{{ $companyTaxAr }}</span> @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="header-divider">
        <div class="thick"></div>
        <div class="thin"></div>
    </div>

    <!-- Banner + reference number -->
    <table class="banner-wrap">
        <tr>
            <td style="width: 74%; padding: 0 4px 0 0;">
                <div class="quote-banner">
                    عرض سعر (Quotation)
                    <span class="sub">QUOTATION TO CUSTOMER</span>
                </div>
            </td>
            <td style="width: 26%; padding: 0;">
                <div class="ref-badge">
                    #{{ $quotation->id }}
                    <span>{{ $quotation->created_at->format('Y-m-d') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Quote/customer data -->
    <div class="section-caption">بيانات العرض والعميل</div>
    <table class="bordered meta-table">
        <tr>
            <th>تاريخ العرض (Date)</th>
            <td dir="ltr">{{ $quotation->created_at->format('Y-m-d') }}</td>
            <th>اسم العميل (Client)</th>
            <td dir="rtl">{{ $quotation->customer?->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>رقم العرض (Quote No)</th>
            <td dir="ltr">#{{ $quotation->id }}</td>
            <th>الرقم الضريبي (Tax No)</th>
            <td dir="ltr">{{ $quotation->customer?->tax_no ?? '-' }}</td>
        </tr>
    </table>

    <br>

    <!-- Items table -->
    <table class="bordered items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">الكود<br><span>Code</span></th>
                <th style="width: 32%; text-align: right; padding-right: 6px;">اسم الصنف<br><span>Item Name</span></th>
                <th style="width: 11%;">السعر<br><span>Price</span></th>
                <th style="width: 8%;">الكمية<br><span>Qty</span></th>
                <th style="width: 11%;">الإجمالي<br><span>Total</span></th>
                <th style="width: 10%;">الخصم<br><span>Disc.</span></th>
                <th style="width: 11%;">الصافي<br><span>Net Total</span></th>
            </tr>
        </thead>
        <tbody>
            @php $i = 0; @endphp
            @foreach ($quotation->items as $item)
                @php
                    $i++;
                    $unitPrice = $item->unit_price ?? 0;
                    $qty = $item->quantity ?? 0;
                    $discount = $item->discount_amount ?? 0;
                    $lineTotal = $unitPrice * $qty;
                    $lineNet = $lineTotal - $discount;
                    $itemCode = $item->product_code_snapshot ?? $item->product?->code ?? '-';
                    $itemName = $item->product_name_snapshot ?? $item->product?->name ?? '-';
                @endphp
                <tr>
                    <td>{{ $i }}</td>
                    <td dir="ltr">{{ $itemCode }}</td>
                    <td class="item-name-cell">{{ $itemName }}</td>
                    <td dir="ltr">{{ number_format($unitPrice, 2) }}</td>
                    <td dir="ltr">{{ $qty }}</td>
                    <td dir="ltr">{{ number_format($lineTotal, 2) }}</td>
                    <td dir="ltr">{{ number_format($discount, 2) }}</td>
                    <td dir="ltr" style="font-weight: bold;">{{ number_format($lineNet, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <!-- Totals -->
    <table style="width: 100%;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 0;">
                @if ($quotation->note)
                    <div class="notes-box">
                        <strong>ملاحظات:</strong> {{ $quotation->note }}
                    </div>
                @endif
                <div class="words-box" dir="ltr">
                    <strong>Amount in Words:</strong> {{ $wholeWords }} Riyals and {{ $decimalWords }} Halala Only.
                </div>
            </td>
            <td style="width: 5%;"></td>
            <td style="width: 40%; vertical-align: top; padding: 0;">
                <div class="totals-card">
                    <table>
                        <tr>
                            <td>الإجمالي الفرعي (Sub Total)</td>
                            <td>{{ number_format($grossSubtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>إجمالي الخصم (Discount)</td>
                            <td>{{ number_format($totalDiscount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>الإجمالي قبل الضريبة (Taxable Amount)</td>
                            <td>{{ number_format($taxableAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>ضريبة القيمة المضافة (VAT {{ $vatPercent }}%)</td>
                            <td>{{ number_format($quotation->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="net-total-row">
                            <td>الإجمالي النهائي (Grand Total)</td>
                            <td>{{ number_format($quotation->grand_total, 2) }} SAR</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Signatures: preparer and approver -->
    <table class="signature-wrap">
        <tr>
            <td style="width: 45%; padding: 0 10px;">
                <div class="signature-box">
                    <div class="signature-role">أعد بواسطة (Prepared by)</div>
                    <div class="signature-name">{{ $quotation->creator?->name ?? '-' }}</div>
                    <div class="signature-label">التوقيع / Signature</div>
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; padding: 0 10px;">
                <div class="signature-box">
                    <div class="signature-role">اعتمد بواسطة (Approved by)</div>
                    <div class="signature-name">{{ $quotation->approver?->name ?? '-' }}</div>
                    <div class="signature-label">التوقيع / Signature</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-bar">
        <div class="footer-brand">{{ $companyNameAr }}</div>
        @if ($companyAddressAr) <div>{{ $companyAddressAr }}</div> @endif
        @if ($companyAddressEn) <div dir="ltr">{{ $companyAddressEn }}</div> @endif
        <div class="footer-note">هذا المستند صادر إلكترونياً ولا يحتاج إلى ختم أو توقيع.</div>
    </div>

</body>
</html>