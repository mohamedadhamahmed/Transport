@php
    $c = $quotation->customer;
    $currency = function_exists('currency_name') ? currency_name() : 'ريال سعودي';
    $amountWords = class_exists(\App\Support\ArabicNumberWords::class)
        ? \App\Support\ArabicNumberWords::amountToWords((float) $quotation->total, $currency)
        : '';
    $hasTransfers = $quotation->items->contains('has_transfer', true);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('transport.quotation') }} {{ $quotation->quotation_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @include('transport.partials.print-doc-style')
    <style>
        .toolbar form { display:inline; }
        .toolbar select { font-family:inherit; padding:8px 10px; border-radius:10px; border:1px solid var(--border); }
        .terms { margin:0 26px 18px; white-space:pre-line; font-size:12.5px; color:var(--ink); border:1px solid var(--border); border-radius:8px; padding:10px 14px; }
        .terms h4 { margin:0 0 6px; color:var(--blue); }
        .alert.err { background:#fff1f2; color:#be123c; border-color:#fecdd3; }
    </style>
</head>
<body>

@if (session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="toolbar">
    <button type="button" onclick="window.print()">{{ __('transport.print') }}</button>
    @if ($quotation->status !== 'converted')
        @can('transport_invoices.create')
            <a href="{{ route('transport.quotations.convert', $quotation) }}">🧾 {{ __('transport.convert_to_invoice') }}</a>
        @endcan
        @can('transport_quotations.edit')
            <a class="light" href="{{ route('transport.quotations.edit', $quotation) }}">{{ __('transport.edit') }}</a>
            <form method="POST" action="{{ route('transport.quotations.status', $quotation) }}">
                @csrf
                <select name="status" onchange="this.form.submit()">
                    @foreach (['draft', 'sent', 'accepted', 'rejected'] as $st)
                        <option value="{{ $st }}" @selected($quotation->status === $st)>{{ __('transport.status') }}: {{ __('transport.q_' . $st) }}</option>
                    @endforeach
                </select>
            </form>
        @endcan
    @elseif ($quotation->invoice)
        <a class="light" href="{{ route('transport.invoices.show', $quotation->invoice) }}">🧾 {{ $quotation->invoice->invoice_number }}</a>
    @endif
    @if ($c?->phone && \App\Support\SaudiPhone::isValid($c->phone))
        <a class="light" target="_blank" rel="noopener" href="{{ \App\Support\SaudiPhone::whatsappUrl($c->phone, 'السلام عليكم، مرفق عرض سعر النقل رقم ' . $quotation->quotation_number . ' بإجمالي ' . number_format((float) $quotation->total, 2)) }}">💬 واتساب العميل</a>
    @endif
    <a class="light" href="{{ route('transport.quotations.index') }}">{{ __('transport.back_to_list') }}</a>
</div>

<div class="doc">
    @include('transport.partials.print-brand')

    <div class="title">عرض سعر نقل &nbsp;|&nbsp; TRANSPORTATION QUOTATION</div>

    <div class="meta">
        <div class="box">
            <h4>بيانات العرض - Quotation</h4>
            <div class="kv"><span>رقم العرض - No.</span><span>{{ $quotation->quotation_number }}</span></div>
            <div class="kv"><span>التاريخ - Date</span><span>{{ $quotation->issue_date->format('Y-m-d') }}</span></div>
            @if ($quotation->valid_until)
                <div class="kv"><span>صالح حتى - Valid until</span><span>{{ $quotation->valid_until->format('Y-m-d') }}</span></div>
            @endif
            <div class="kv"><span>الحالة - Status</span><span>{{ __('transport.q_' . $quotation->status) }}</span></div>
        </div>
        <div class="box">
            <h4>بيانات العميل - Customer</h4>
            <div class="kv"><span>الاسم - Name</span><span>{{ $c?->name ?? '-' }}</span></div>
            @if ($c?->tax_number)<div class="kv"><span>الرقم الضريبي - VAT</span><span>{{ $c->tax_number }}</span></div>@endif
            @if ($c?->phone)<div class="kv"><span>الجوال - Phone</span><span dir="ltr">{{ $c->phone }}</span></div>@endif
            @if ($c?->city || $c?->address)<div class="kv"><span>العنوان - Address</span><span>{{ trim(($c->city ?? '') . ' ' . ($c->address ?? '')) }}</span></div>@endif
        </div>
    </div>

    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>من<br>From</th>
                    <th>إلى<br>To</th>
                    <th>نوع الشاحنة<br>Truck Type</th>
                    <th>نوع الحمولة<br>Load</th>
                    <th>عدد النقلات<br>Trips</th>
                    <th>سعر النقلة<br>Price</th>
                    @if ($hasTransfers)<th>التحويلة<br>Transfer</th>@endif
                    <th>الإجمالي<br>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $i => $it)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $it->from_label }}</td>
                        <td>{{ $it->to_label }}</td>
                        <td>{{ $it->truck_type ?? '-' }}</td>
                        <td>{{ $it->load_type ?? '-' }}</td>
                        <td>{{ $it->trips_count }}</td>
                        <td>{{ number_format((float) $it->trip_price, 2) }}</td>
                        @if ($hasTransfers)
                            <td class="transfer">
                                @if ($it->has_transfer)
                                    {{ number_format((float) $it->transfer_price, 2) }}@if ($it->transfer_location)<br>{{ $it->transfer_location }}@endif
                                @else - @endif
                            </td>
                        @endif
                        <td><strong>{{ number_format((float) $it->line_total, 2) }}</strong></td>
                    </tr>
                    @if ($it->note)
                        <tr><td></td><td colspan="{{ $hasTransfers ? 8 : 7 }}" style="text-align:start;color:#6B7280;font-size:11.5px">{{ $it->note }}</td></tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bottom">
        <div></div>
        <div class="totals">
            <div class="row"><span>مجموع النقلات <small>Trips</small></span><span>{{ number_format((float) $quotation->trips_total, 2) }}</span></div>
            @if ((float) $quotation->transfers_total > 0)
                <div class="row"><span>مجموع التحويلات <small>Transfers</small></span><span>{{ number_format((float) $quotation->transfers_total, 2) }}</span></div>
            @endif
            @if ((float) $quotation->discount_amount > 0)
                <div class="row"><span>الخصم <small>Discount</small></span><span>- {{ number_format((float) $quotation->discount_amount, 2) }}</span></div>
            @endif
            <div class="row"><span>الإجمالي قبل الضريبة <small>Subtotal</small></span><span>{{ number_format((float) $quotation->subtotal, 2) }}</span></div>
            @if ($quotation->tax_type === 'international')
                <div class="row"><span>ضريبة القيمة المضافة <small>VAT</small> 0% — <b>معفاة: شحن خارج المملكة</b> <small>Exempt - International</small></span><span>0.00</span></div>
            @else
                <div class="row"><span>ضريبة القيمة المضافة <small>VAT</small> {{ rtrim(rtrim(number_format($quotation->tax_rate * 100, 2), '0'), '.') }}%</span><span>{{ number_format((float) $quotation->tax_amount, 2) }}</span></div>
            @endif
            <div class="row grand"><span>الإجمالي <small style="color:#fff">Total</small></span><span>{{ number_format((float) $quotation->total, 2) }} {{ $currencySymbol ?? '' }}</span></div>
        </div>
    </div>

    @if ($amountWords)<div class="words">المبلغ بالحروف: {{ $amountWords }}</div>@endif

    @if ($quotation->terms)
        <div class="terms"><h4>الشروط والأحكام - Terms</h4>{{ $quotation->terms }}</div>
    @endif
    @if ($quotation->note)<div class="notes">ملاحظات: {{ $quotation->note }}</div>@endif

    <div class="sign">
        <div>إعداد - Prepared by<br>{{ $quotation->creator?->name }}</div>
        <div>موافقة العميل - Customer approval</div>
        <div>الختم - Stamp</div>
    </div>
</div>
</body>
</html>
