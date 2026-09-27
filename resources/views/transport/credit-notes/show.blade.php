@php
    $brand = [
        'bank' => defined('bankname') ? constant('bankname') : null,
        'iban' => defined('bank_acount_iban') ? constant('bank_acount_iban') : null,
        'acc' => defined('bank_acount_number') ? constant('bank_acount_number') : null,
    ];
    $currency = function_exists('currency_name') ? currency_name() : 'ريال سعودي';
    $amountWords = class_exists(\App\Support\ArabicNumberWords::class)
        ? \App\Support\ArabicNumberWords::amountToWords((float) $note->total, $currency)
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
    $c = $note->customer;
    $inv = $note->invoice;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('transport.cn_title') }} {{ $note->credit_note_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @include('transport.partials.print-doc-style')
    <style>.title.cn { background: #9f1239; }</style>
</head>
<body>

@if (session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert" style="background:#fff1f2;color:#be123c;border-color:#fecdd3">{{ session('error') }}</div>
@endif

<div class="toolbar">
    <button type="button" onclick="window.print()">{{ __('transport.print') }}</button>
    @if ($note->is_sent_to_zatca)
        <span class="light" style="padding:9px 16px;border-radius:10px;background:#ecfdf5;color:#047857;font-weight:700">✓ {{ __('transport.zatca_sent') }}</span>
        @can('zatca.view')
            <a class="light" href="{{ route('transport.credit-notes.xml', $note) }}">XML</a>
        @endcan
    @else
        @can('zatca.send')
            @if ($inv?->is_sent_to_zatca)
                <form method="POST" action="{{ route('transport.credit-notes.zatca', $note) }}" style="display:inline" onsubmit="return confirm('{{ __('transport.cn_confirm_zatca') }}')">
                    @csrf
                    <button type="submit">🏛 {{ __('transport.send_to_zatca') }}</button>
                </form>
            @else
                <span style="color:#b45309;font-size:12px;font-weight:700">⚠ {{ __('transport.cn_invoice_not_sent') }}</span>
            @endif
        @endcan
        @if ($note->zatca_status === 'FAIL' && $note->zatca_message)
            <span style="color:#be123c;font-size:12px;font-weight:700">✗ {{ \Illuminate\Support\Str::limit($note->zatca_message, 160) }}</span>
        @endif
        @can('transport_invoices.delete')
            <form method="POST" action="{{ route('transport.credit-notes.destroy', $note) }}" style="display:inline" onsubmit="return confirm('{{ __('transport.cn_confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:#be123c">{{ __('transport.delete') }}</button>
            </form>
        @endcan
    @endif
    @if ($inv)
        <a class="light" href="{{ route('transport.invoices.show', $inv) }}">{{ __('transport.cn_original_invoice') }} {{ $inv->invoice_number }}</a>
    @endif
    <a class="light" href="{{ route('transport.credit-notes.index') }}">{{ __('transport.back_to_list') }}</a>
</div>

<div class="doc">
    @include('transport.partials.print-brand')

    <div class="title cn">
        {{ $note->isSimplified() ? 'إشعار دائن ضريبي مبسط' : 'إشعار دائن ضريبي' }}
        &nbsp;|&nbsp; {{ $note->isSimplified() ? 'SIMPLIFIED TAX CREDIT NOTE' : 'TAX CREDIT NOTE' }}
    </div>

    <div class="meta">
        <div class="box">
            <h4>بيانات الإشعار - Credit Note</h4>
            <div class="kv"><span>رقم الإشعار - No.</span><span>{{ $note->credit_note_number }}</span></div>
            <div class="kv"><span>التاريخ - Date</span><span>{{ $note->issue_date->format('Y-m-d') }} {{ substr((string) $note->issue_time, 0, 5) }}</span></div>
            <div class="kv"><span>الفاتورة الأصلية - Invoice Ref.</span><span>{{ $inv?->invoice_number }} ({{ $inv?->issue_date?->format('Y-m-d') }})</span></div>
            <div class="kv"><span>السبب - Reason</span><span>{{ $note->reason }}</span></div>
            <div class="kv"><span>الفرع - Branch</span><span>{{ $note->branch?->name ?? '-' }}</span></div>
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
                    <th>البيان<br>Description</th>
                    <th>المبلغ قبل الضريبة<br>Amount</th>
                    <th>الضريبة<br>VAT</th>
                    <th>الإجمالي<br>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($note->items as $i => $item)
                    @php $lineTax = round((float) $item->amount * (float) $note->tax_rate, 2); @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="text-align:start">{{ $item->description }}</td>
                        <td>{{ number_format((float) $item->amount, 2) }}</td>
                        <td>{{ number_format($lineTax, 2) }}</td>
                        <td><strong>{{ number_format((float) $item->amount + $lineTax, 2) }}</strong></td>
                    </tr>
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
            </div>
        </div>
        <div class="totals">
            <div class="row"><span>الإجمالي قبل الضريبة <small>Subtotal</small></span><span>{{ number_format((float) $note->subtotal, 2) }}</span></div>
            <div class="row"><span>ضريبة القيمة المضافة <small>VAT</small> {{ rtrim(rtrim(number_format($note->tax_rate * 100, 2), '0'), '.') }}%</span><span>{{ number_format((float) $note->tax_amount, 2) }}</span></div>
            <div class="row grand" style="background:#9f1239"><span>إجمالي الإشعار <small style="color:#fff">Total</small></span><span>{{ number_format((float) $note->total, 2) }}</span></div>
        </div>
    </div>

    @if ($amountWords)
        <div class="words">المبلغ بالحروف: {{ $amountWords }}</div>
    @endif

    @if ($note->release_loads)
        <div class="notes">{{ __('transport.cn_loads_released') }}</div>
    @endif

    <div class="sign">
        <div>المحاسب - Accountant<br>{{ $note->creator?->name }}</div>
        <div>المستلم - Receiver</div>
        <div>الختم - Stamp</div>
    </div>
</div>

@if (request('saved'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
@endif
</body>
</html>
