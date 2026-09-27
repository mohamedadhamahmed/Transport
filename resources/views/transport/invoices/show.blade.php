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
    @include('transport.partials.print-doc-style')
</head>
<body>

@if (session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert" style="background:#fff1f2;color:#be123c;border-color:#fecdd3">{{ session('error') }}</div>
@endif
@if ($invoice->is_draft)
    <div class="alert" style="background:#fff7ed;color:#c2410c;border-color:#fed7aa">📝 {{ __('transport.draft_banner') }}</div>
@endif

@if ($invoice->creditNotes->isNotEmpty())
    <div class="alert" style="background:#fff1f2;color:#9f1239;border-color:#fecdd3">
        ↩ {{ __('transport.cn_on_invoice') }}:
        @foreach ($invoice->creditNotes as $cn)
            <a href="{{ route('transport.credit-notes.show', $cn) }}" style="color:#9f1239;font-weight:800;margin-inline-start:.4rem">{{ $cn->credit_note_number }} ({{ number_format((float) $cn->total, 2) }})</a>
        @endforeach
    </div>
@endif

<div class="toolbar">
    <button type="button" onclick="window.print()">{{ __('transport.print') }}</button>
    @if ($invoice->is_draft)
        @can('transport_invoices.create')
            <form method="POST" action="{{ route('transport.invoices.approve', $invoice) }}" style="display:inline" onsubmit="return confirm('{{ __('transport.confirm_convert_draft') }}')">
                @csrf
                <button type="submit" style="background:#059669">🧾 {{ __('transport.convert_draft_to_invoice') }}</button>
            </form>
        @endcan
    @endif
    @if ($invoice->isEditable())
        @can('transport_invoices.edit')
            <a class="light" href="{{ route('transport.invoices.edit', $invoice) }}">{{ __('transport.edit') }}</a>
        @endcan
    @endif
    @if (!$invoice->is_draft)
        @if ($invoice->is_sent_to_zatca)
            <span class="light" style="padding:9px 16px;border-radius:10px;background:#ecfdf5;color:#047857;font-weight:700">✓ {{ __('transport.zatca_sent') }}</span>
            @can('zatca.view')
                <a class="light" href="{{ route('transport.zatca.xml', $invoice) }}">XML</a>
            @endcan
        @else
            @can('zatca.send')
                <form method="POST" action="{{ route('transport.zatca.send', $invoice) }}" style="display:inline" onsubmit="return confirm('{{ __('transport.confirm_zatca_send') }}')">
                    @csrf
                    <button type="submit">🏛 {{ __('transport.send_to_zatca') }}</button>
                </form>
            @endcan
            @if ($invoice->zatca_status === 'FAIL' && $invoice->zatca_message)
                <span style="color:#be123c;font-size:12px;font-weight:700">✗ {{ \Illuminate\Support\Str::limit($invoice->zatca_message, 160) }}</span>
            @endif
        @endif
    @endif
    @if (!$invoice->is_draft)
        @can('transport_invoices.edit')
            <a class="light" href="{{ route('transport.credit-notes.create', $invoice) }}" style="color:#be123c;border-color:#fecdd3">↩ {{ __('transport.cn_new') }}</a>
        @endcan
    @endif
    @can('transport_invoices.create')
        <a class="light" href="{{ route('transport.invoices.create') }}">{{ __('transport.new_invoice') }}</a>
    @endcan
    <a class="light" href="{{ route('transport.invoices.index') }}">{{ __('transport.back_to_list') }}</a>
</div>

<div class="doc">
    @include('transport.partials.print-brand')

    @if ($invoice->is_draft)
        <div style="text-align:center;padding:6px;background:#fed7aa;color:#9a3412;font-weight:800;letter-spacing:2px">مسودة - DRAFT (غير معتمدة)</div>
    @endif
    <div class="title">
        {{-- الفاتورة ضريبية حتى لو نسبتها صفر (شحن خارج المملكة) - مبسطة لو العميل من غير رقم ضريبي --}}
        {{ $invoice->isSimplified() ? 'فاتورة ضريبية مبسطة - نقليات' : 'فاتورة ضريبية - نقليات' }}
        &nbsp;|&nbsp; {{ $invoice->isSimplified() ? 'SIMPLIFIED TAX INVOICE - TRANSPORTATION' : 'TAX INVOICE - TRANSPORTATION' }}
    </div>

    <div class="meta">
        <div class="box">
            <h4>بيانات الفاتورة - Invoice</h4>
            <div class="kv"><span>رقم الفاتورة - No.</span><span>{{ $invoice->invoice_number ?? ('مسودة #' . $invoice->id) }}</span></div>
            <div class="kv"><span>تاريخ الفاتورة - Date</span><span>{{ $invoice->issue_date->format('Y-m-d') }}</span></div>
            @if ($invoice->supply_from || $invoice->supply_to)
                <div class="kv"><span>فترة التوريد - Supply Period</span><span>{{ $invoice->supply_from?->format('Y-m-d') ?? '…' }} → {{ $invoice->supply_to?->format('Y-m-d') ?? '…' }}</span></div>
            @endif
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
                    @if ($item->description)
                        {{-- بند يدوي: وصف × كمية × سعر --}}
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td colspan="5" style="text-align:start">
                                {{ $item->description }}
                                @if ((float) $item->quantity != 1)
                                    <span style="color:#6B7280">({{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} × {{ number_format((float) $item->unit_price, 2) }})</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                            @if ($hasTransfers)<td>-</td>@endif
                            <td><strong>{{ number_format((float) $item->line_total, 2) }}</strong></td>
                        </tr>
                        @continue
                    @endif
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
            @if ($invoice->tax_type === 'international')
                <div class="row"><span>ضريبة القيمة المضافة <small>VAT</small> 0% — <b>معفاة: شحن خارج المملكة</b> <small>Exempt - International</small></span><span>0.00</span></div>
            @else
                <div class="row"><span>ضريبة القيمة المضافة <small>VAT</small> {{ rtrim(rtrim(number_format($invoice->tax_rate * 100, 2), '0'), '.') }}%</span><span>{{ number_format((float) $invoice->tax_amount, 2) }}</span></div>
            @endif
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
