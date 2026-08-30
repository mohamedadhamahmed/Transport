<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Quotation #{{ $quotation->id }}</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
    * {
        font-family: 'Cairo', 'Tahoma', sans-serif;
        box-sizing: border-box;
    }

    body {
        font-size: 13px;
        color: #1a202c;
        margin: 0;
        padding: 20px;
        background: #eef1f5;
        line-height: 1.45;
    }

    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 7px 9px; text-align: center; vertical-align: middle; }

    .bordered { border: 1px solid #cbd5e1; }
    .bordered th, .bordered td { border: 1px solid #cbd5e1; }

    /* ============ الصفحة/الورقة ============ */
    .page-sheet {
        max-width: 794px; /* عرض A4 تقريبًا بدقة الشاشة */
        margin: 0 auto;
        background: #ffffff;
        padding: 16px 22px 22px;
        box-shadow: 0 4px 18px rgba(15, 41, 66, 0.12);
        border-radius: 6px;
    }

    /* ============ شريط الأدوات (لا يُطبع) ============ */
    .toolbar {
        max-width: 794px;
        margin: 0 auto 14px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .toolbar button {
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: bold;
        cursor: pointer;
        font-family: inherit;
        transition: transform 0.05s ease, box-shadow 0.15s ease;
        box-shadow: 0 2px 6px rgba(15, 41, 66, 0.15);
    }
    .toolbar button:active { transform: translateY(1px); }
    .btn-download {
        background-color: #0f2942;
        background-image: linear-gradient(135deg, #1a365d 0%, #0f2942 100%);
        color: #ffffff;
    }
    .btn-download:hover { background-color: #1a365d; }
    .btn-print {
        background-color: #ffffff;
        color: #0f2942;
        border: 1px solid #cbd5e1 !important;
        box-shadow: none;
    }
    .btn-print:hover { background-color: #f4f7fb; }

    /* ============ الشريط العلوي الزخرفي ============ */
    .top-strip {
        width: 100%;
        height: 5px;
        background-color: #c9a227;
        background-image: linear-gradient(to left, #0f2942 0%, #1a365d 55%, #c9a227 100%);
        margin-bottom: 10px;
        border-radius: 3px;
    }

    /* ============ بطاقة الترويسة ============ */
    .header-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 18px;
        background-color: #ffffff;
    }

    .header-table td { border: none; vertical-align: middle; padding: 0 4px; }
    .header-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .company-block { flex: 1; }
    .company-block .name { font-size: 15px; font-weight: bold; color: #0f2942; letter-spacing: 0.3px; }
    .company-block p { font-size: 9.5px; color: #64748b; margin: 2px 0 0; }
    .company-block.ar { text-align: right; }
    .company-block.en { text-align: left; direction: ltr; }
    .company-name { font-size: 15px; font-weight: bold; color: #0f2942; letter-spacing: 0.3px; }
    .company-line { font-size: 9.5px; color: #64748b; display: block; margin-top: 2px; }

    .logo-frame {
        width: 78px;
        height: 78px;
        flex-shrink: 0;
        border: 2px solid #c9a227;
        border-radius: 50%;
        text-align: center;
        vertical-align: middle;
        margin: 0 auto;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .logo-frame img {
        max-width: 60px;
        max-height: 60px;
        object-fit: contain;
    }

    .header-divider { width: 100%; margin: 12px 0 6px; }
    .header-divider .thick { height: 2.5px; background-color: #0f2942; width: 100%; }
    .header-divider .thin { height: 1px; background-color: #c9a227; width: 100%; margin-top: 1.5px; }

    /* ============ بانر العنوان + رقم مرجعي ============ */
    .banner-wrap { width: 100%; margin: 14px 0; }
    .quote-banner {
        background-color: #0f2942;
        background-image: linear-gradient(to left, #0f2942 0%, #24466e 100%);
        color: #ffffff;
        font-size: 16px;
        font-weight: bold;
        padding: 12px 8px;
        text-align: center;
        letter-spacing: 0.5px;
        border-radius: 6px;
    }
    .quote-banner .sub {
        font-size: 10px;
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
        font-size: 10.5px;
        font-weight: bold;
        text-align: center;
        border-radius: 6px;
        padding: 8px 8px;
    }
    .ref-badge span {
        display: block;
        font-weight: normal;
        font-size: 9px;
        color: #64748b;
        margin-top: 1px;
    }

    /* ============ جدول بيانات العرض/العميل ============ */
    .meta-table { border-radius: 4px; overflow: hidden; }
    .meta-table th {
        background-color: #f4f7fb;
        font-size: 10.5px;
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
        font-size: 10.5px;
        font-weight: bold;
        color: #1a365d;
        margin-bottom: 4px;
        border-right: 3px solid #c9a227;
        padding-right: 6px;
    }

    /* ============ جدول الأصناف ============ */
    .items-table { margin-top: 6px; border-radius: 6px; overflow: hidden; }
    .items-table thead th {
        background-color: #0f2942;
        color: #ffffff;
        font-weight: bold;
        font-size: 10.5px;
        padding: 10px 6px;
        border-color: #0f2942;
        letter-spacing: 0.2px;
    }
    .items-table thead th span { color: #e9c766; }
    .items-table tbody td {
        border-color: #cbd5e1;
        color: #2d3748;
        font-size: 10.5px;
    }
    .items-table tbody tr:nth-child(even) { background-color: #f4f7fb; }
    .items-table tbody tr:last-child td { border-bottom: 2px solid #1a365d; }
    .item-name-cell {
        text-align: right;
        padding-right: 6px;
        font-weight: bold;
        color: #0f2942;
    }

    /* ============ ملخص الإجماليات ============ */
    .totals-card {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 41, 66, 0.08);
    }
    .totals-card table { width: 100%; }
    .totals-card td {
        padding: 8px 12px;
        border-bottom: 1px dashed #cbd5e1;
    }
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
        font-size: 14px;
        border-bottom: none !important;
        padding: 11px 12px !important;
    }

    /* ============ الصناديق الجانبية ============ */
    .words-box {
        border: 1px solid #cbd5e1;
        background-color: #f4f7fb;
        border-right: 3px solid #1a365d;
        padding: 10px 12px;
        margin-top: 0;
        font-size: 10.5px;
        text-align: left;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(15, 41, 66, 0.06);
    }
    .words-box strong { color: #0f2942; }

    .notes-box {
        border: 1px solid #c9a227;
        background-color: #fffaf0;
        border-right: 3px solid #c9a227;
        padding: 10px 12px;
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 10.5px;
        text-align: right;
        color: #744210;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(15, 41, 66, 0.06);
    }

    /* ============ صندوق التوقيعات ============ */
    .signature-wrap { width: 100%; margin-top: 26px; }
    .signature-box { border-top: 1.5px solid #64748b; padding-top: 6px; text-align: center; }
    .signature-role { font-size: 10.5px; font-weight: bold; color: #1a365d; }
    .signature-name { font-size: 10px; color: #2d3748; margin-top: 2px; }
    .signature-label { font-size: 9px; color: #64748b; margin-top: 12px; }

    /* ============ التذييل ============ */
    .footer-bar {
        margin-top: 18px;
        text-align: center;
        border-top: 1px solid #cbd5e1;
        padding-top: 9px;
        font-size: 9.5px;
        color: #64748b;
    }
    .footer-bar .footer-brand { color: #1a365d; font-weight: bold; font-size: 10px; margin-bottom: 3px; }

    /* ============ إعدادات الطباعة ============ */
    @media print {
        body { background: #ffffff; padding: 0; }
        .toolbar { display: none !important; }
        .page-sheet { box-shadow: none; border-radius: 0; max-width: 100%; padding: 10mm; }
        @page { size: a4; margin: 10mm 8mm; }
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
        $companyLogoUrl = $logoFile && file_exists(public_path('assets/img/brand/' . $logoFile))
            ? asset('assets/img/brand/' . $logoFile)
            : asset('images/sidebar-icon.png');

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

    <!-- شريط الأدوات: مش هيظهر في الطباعة/PDF -->
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">طباعة</button>
        <button class="btn-download" onclick="downloadPdf()">تحميل PDF</button>
    </div>

    <!-- الورقة القابلة للتحويل لـ PDF -->
    <div id="quotation-sheet" class="page-sheet">

        <div class="top-strip"></div>

        <!-- بطاقة الترويسة -->
        <div class="header-card">
            <div class="header-flex">
                <div class="company-block ar">
                    <div class="name">{{ Namear }}</div>
                    @if (describtionar) <p>{{ describtionar }}</p> @endif
                    @if (STar) <p>{{ STar }}</p> @endif
                    @if (Taxar) <p>{{ Taxar }}</p> @endif
                </div>

                <div class="logo-frame">
                    <img src="{{ $companyLogoUrl }}" crossorigin="anonymous" alt="logo">
                </div>

                <div class="company-block en">
                    <div class="name">{{ Nameen }}</div>
                    @if (describtionen) <p>{{ describtionen }}</p> @endif
                    @if (STen) <p>{{ STen }}</p> @endif
                    @if (Taxen) <p>{{ Taxen }}</p> @endif
                </div>
            </div>
        </div>

        <div class="header-divider">
            <div class="thick"></div>
            <div class="thin"></div>
        </div>

        <!-- البانر + الرقم المرجعي -->
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

        <!-- بيانات العرض والعميل -->
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

        <!-- جدول الأصناف -->
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

        <!-- الإجماليات -->
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

        <!-- التوقيعات -->
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

        <!-- التذييل -->
        <div class="footer-bar">
            <div class="footer-brand">{{ $companyNameAr }}</div>
            @if ($companyAddressAr) <div>{{ $companyAddressAr }}</div> @endif
            @if ($companyAddressEn) <div dir="ltr">{{ $companyAddressEn }}</div> @endif
            <div style="margin-top: 4px;">هذا المستند صادر إلكترونياً ولا يحتاج إلى ختم أو توقيع.</div>
        </div>

    </div>

    <script>
        function downloadPdf() {
            const element = document.getElementById('quotation-sheet');
            const opt = {
                margin: 0,
                filename: 'quotation-{{ $quotation->id }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['avoid-all'] }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>
</html>