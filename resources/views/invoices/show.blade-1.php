@php
    $brand = [
        'name_ar' => defined('Namear') ? constant('Namear') : config('app.name'),
        'name_en' => defined('Nameen') ? constant('Nameen') : null,
        'desc_ar' => defined('describtionar') ? constant('describtionar') : null,
        'desc_en' => defined('describtionen') ? constant('describtionen') : null,
        'cr_ar' => defined('STar') ? constant('STar') : null,
        'cr_en' => defined('STen') ? constant('STen') : null,
        'tax_ar' => defined('Taxar') ? constant('Taxar') : null,
        'tax_en' => defined('Taxen') ? constant('Taxen') : null,
        'addr_ar' => defined('addressar') ? constant('addressar') : null,
        'logo' => defined('camplogo') ? constant('camplogo') : null,
        'bank' => defined('bankname') ? constant('bankname') : null,
        'iban' => defined('bank_acount_iban') ? constant('bank_acount_iban') : null,
        'acc' => defined('bank_acount_number') ? constant('bank_acount_number') : null,
    ];

    $currency = function_exists('currency_name') ? currency_name() : 'ريال سعودي';
    $amountWords = class_exists(\App\Support\ArabicNumberWords::class)
        ? \App\Support\ArabicNumberWords::amountToWords((float) $invoice->total, $currency)
        : '';

    $qrSvg = null;
    if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
        try {
            $qrSvg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(120)->margin(0)->generate($qrData);
            $qrSvg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $qrSvg);
        } catch (\Throwable $e) {
            $qrSvg = null;
        }
    }
    $c = $invoice->customer;
    $hasTransfers = $invoice->items->contains('has_transfer', true);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('transport.transport_invoice') }} {{ $invoice->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
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
</head>
<body>

@if (session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif

<div class="toolbar">
    <button type="button" onclick="window.print()">{{ __('transport.print') }}</button>
    @can('transport_invoices.edit')
        <a class="light" href="{{ route('transport.invoices.edit', $invoice) }}">{{ __('transport.edit') }}</a>
    @endcan
    @can('transport_invoices.create')
        <a class="light" href="{{ route('transport.invoices.create') }}">{{ __('transport.new_invoice') }}</a>
    @endcan
    <a class="light" href="{{ route('transport.invoices.index') }}">{{ __('transport.back_to_list') }}</a>
</div>

<div class="doc">
    <div class="head">
        <div class="co">
            <div class="name">{{ $brand['name_ar'] }}</div>
            @if ($brand['desc_ar'])<p>{{ $brand['desc_ar'] }}</p>@endif
            @if ($brand['cr_ar'])<p>{{ $brand['cr_ar'] }}</p>@endif
            @if ($brand['tax_ar'])<p>{{ $brand['tax_ar'] }}</p>@endif
            @if ($brand['addr_ar'])<p>{{ $brand['addr_ar'] }}</p>@endif
        </div>
        @if ($brand['logo'])
            <img src="{{ asset('assets/img/brand/' . $brand['logo']) }}" alt="logo">
        @endif
        @if ($brand['name_en'])
            <div class="co en">
                <div class="name">{{ $brand['name_en'] }}</div>
                @if ($brand['desc_en'])<p>{{ $brand['desc_en'] }}</p>@endif
                @if ($brand['cr_en'])<p>{{ $brand['cr_en'] }}</p>@endif
                @if ($brand['tax_en'])<p>{{ $brand['tax_en'] }}</p>@endif
            </div>
        @endif
    </div>

    <div class="title">
        {{ (float) $invoice->tax_amount > 0 ? 'فاتورة ضريبية - نقليات' : 'فاتورة نقليات' }}
        &nbsp;|&nbsp; {{ (float) $invoice->tax_amount > 0 ? 'TAX INVOICE - TRANSPORTATION' : 'TRANSPORTATION INVOICE' }}
    </div>

    <div class="meta">
        <div class="box">
            <h4>بيانات الفاتورة - Invoice</h4>
            <div class="kv"><span>رقم الفاتورة - No.</span><span>{{ $invoice->invoice_number }}</span></div>
            <div class="kv"><span>تاريخ الفاتورة - Date</span><span>{{ $invoice->issue_date->format('Y-m-d') }}</span></div>
            <div class="kv"><span>طريقة الدفع - Payment</span><span>{{ __('transport.pay_' . $invoice->payment_method) }}</span></div>
            @if ($invoice->po_number)
                <div class="kv"><span>رقم أمر الشراء - PO</span><span>{{ $invoice->po_number }}</span></div>
            @endif
            <div class="kv"><span>الفرع - Branch</span><span>{{ $invoice->branch?->name ?? '-' }}</span></div>
        </div>
        <div class="box">
            <h4>بيانات العميل - Customer</h4>
            <div class="kv"><span>الاسم - Name</span><span>{{ $c?->name ?? '-' }}</span></div>
            @if ($c?->tax_number)
                <div class="kv"><span>الرقم الضريبي - VAT</span><span>{{ $c->tax_number }}</span></div>
            @endif
            @if ($c?->commercial_registration_number)
                <div class="kv"><span>السجل التجاري - C.R</span><span>{{ $c->commercial_registration_number }}</span></div>
            @endif
            @if ($c?->phone)
                <div class="kv"><span>الجوال - Phone</span><span dir="ltr">{{ $c->phone }}</span></div>
            @endif
            @if ($c?->address || $c?->city)
                <div class="kv"><span>العنوان - Address</span><span>{{ trim(($c->city ?? '') . ' ' . ($c->address ?? '')) }}</span></div>
            @endif
        </div>
    </div>

    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>تاريخ الحمل<br>Load Date</th>
                    <th>الشاحنة<br>Truck</th>
                    <th>من<br>From</th>
                    <th>إلى<br>To</th>
                    <th>رقم البوليصة<br>Waybill</th>
                    <th>سعر النقلة<br>Trip Price</th>
                    @if ($hasTransfers)
                        <th>التحويلة<br>Transfer</th>
                    @endif
                    <th>الإجمالي<br>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->trip_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="plate">{{ $item->truck_snapshot ?? $item->truck?->display_name }}</td>
                        <td>{{ $item->from_location ?? '-' }}</td>
                        <td>{{ $item->to_location ?? '-' }}</td>
                        <td>{{ $item->waybill_number ?? '-' }}</td>
                        <td>{{ number_format((float) $item->trip_price, 2) }}</td>
                        @if ($hasTransfers)
                            <td class="transfer">
                                @if ($item->has_transfer)
                                    {{ number_format((float) $item->transfer_price, 2) }}
                                    @if ($item->transfer_location)<br>{{ $item->transfer_location }}@endif
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        <td><strong>{{ number_format((float) $item->line_total, 2) }}</strong></td>
                    </tr>
                    @if ($item->note)
                        <tr><td></td><td colspan="{{ $hasTransfers ? 8 : 7 }}" style="text-align:start;color:#6B7280;font-size:11.5px">{{ $item->note }}</td></tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bottom">
        <div>
            <div class="qr">
                @if ($qrSvg)
                    {!! $qrSvg !!}
                @else
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrData) }}" alt="QR">
                @endif
                @if ($brand['bank'] || $brand['iban'])
                    <div class="bank">
                        @if ($brand['bank']){{ $brand['bank'] }}<br>@endif
                        @if ($brand['acc'])Account: {{ $brand['acc'] }}<br>@endif
                        @if ($brand['iban'])IBAN: {{ $brand['iban'] }}@endif
                    </div>
                @endif
            </div>
        </div>
        <div class="totals">
            <div class="row"><span>مجموع النقلات <small>Trips</small> ({{ $invoice->trips_count }})</span><span>{{ number_format((float) $invoice->trips_total, 2) }}</span></div>
            @if ((float) $invoice->transfers_total > 0)
                <div class="row"><span>مجموع التحويلات <small>Transfers</small></span><span>{{ number_format((float) $invoice->transfers_total, 2) }}</span></div>
            @endif
            @if ((float) $invoice->discount_amount > 0)
                <div class="row"><span>الخصم <small>Discount</small></span><span>- {{ number_format((float) $invoice->discount_amount, 2) }}</span></div>
            @endif
            <div class="row"><span>الإجمالي قبل الضريبة <small>Subtotal</small></span><span>{{ number_format((float) $invoice->subtotal, 2) }}</span></div>
            <div class="row"><span>ضريبة القيمة المضافة <small>VAT</small> {{ rtrim(rtrim(number_format($invoice->tax_rate * 100, 2), '0'), '.') }}%</span><span>{{ number_format((float) $invoice->tax_amount, 2) }}</span></div>
            <div class="row grand"><span>الإجمالي <small style="color:#fff">Total</small></span><span>{{ number_format((float) $invoice->total, 2) }} {{ $currencySymbol ?? '' }}</span></div>
            @if ($invoice->payment_method === 'split')
                <div class="row"><span>كاش</span><span>{{ number_format((float) $invoice->cash_amount, 2) }}</span></div>
                <div class="row"><span>بنك</span><span>{{ number_format((float) $invoice->bank_amount, 2) }}</span></div>
                <div class="row"><span>آجل</span><span>{{ number_format((float) $invoice->credit_amount, 2) }}</span></div>
            @endif
        </div>
    </div>

    @if ($amountWords)
        <div class="words">المبلغ بالحروف: {{ $amountWords }}</div>
    @endif

    @if ($invoice->note)
        <div class="notes">ملاحظات: {{ $invoice->note }}</div>
    @endif

    <div class="sign">
        <div>المحاسب - Accountant<br>{{ $invoice->creator?->name }}</div>
        <div>المستلم - Receiver</div>
        <div>الختم - Stamp</div>
    </div>
</div>

@if (request('saved'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
@endif
</body>
</html>
