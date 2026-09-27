@php
    $w = $waybill;
    $drv = $w->driver ?? $w->truck?->driver;
    $qrText = implode("\n", array_filter([
        'Waybill: ' . $w->waybill_number,
        'Truck: ' . $w->truck?->plate_number,
        'From: ' . \App\Support\SaudiRegions::name($w->from_region) . ($w->from_city ? ' - ' . $w->from_city : ''),
        'To: ' . \App\Support\SaudiRegions::name($w->to_region) . ($w->to_city ? ' - ' . $w->to_city : ''),
        'Date: ' . $w->loaded_at?->format('Y-m-d H:i'),
    ]));
    $qrSvg = null;
    if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
        try {
            $qrSvg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->encoding('UTF-8')->size(110)->margin(0)->generate($qrText));
        } catch (\Throwable $e) {
            $qrSvg = null;
        }
    }
    $stClass = ['open' => '#b45309', 'delivered' => '#047857', 'cancelled' => '#6b7280'][$w->status] ?? '#6b7280';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('transport.waybill') }} {{ $w->waybill_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @include('transport.partials.print-doc-style')
    <style>
        .toolbar form { display:inline; }
        .toolbar input[type=datetime-local] { font-family:inherit; padding:7px 8px; border-radius:10px; border:1px solid var(--border); }
        .alert.err { background:#fff1f2; color:#be123c; border-color:#fecdd3; }
        .contact-bar { display:flex; justify-content:center; gap:26px; flex-wrap:wrap; padding:8px 26px; background:#F1F4FB; font-weight:700; color:var(--navy); font-size:12.5px; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid var(--border); }
        .grid2 > div { padding:14px 26px; }
        .grid2 > div + div { border-inline-start:1px solid var(--border); }
        .grid2 h4 { margin:0 0 8px; color:var(--blue); font-size:13px; }
        .route { display:flex; align-items:center; justify-content:center; gap:14px; padding:14px 26px; border-bottom:1px solid var(--border); font-size:16px; font-weight:800; color:var(--navy); flex-wrap:wrap; }
        .route .arrow { color:var(--orange); font-size:22px; }
        .route small { display:block; font-size:11px; color:var(--muted); font-weight:600; text-align:center; }
        .goods { padding:16px 26px; }
        .goods td, .goods th { border:1px solid var(--border); padding:8px; text-align:center; font-size:12.5px; }
        .goods th { background:#F1F4FB; color:var(--navy); font-size:11.5px; }
        .qr-row { display:flex; gap:18px; align-items:center; padding:0 26px 16px; }
        .qr-row svg, .qr-row img { width:110px; height:110px; }
        .status-pill { display:inline-block; padding:2px 10px; border-radius:99px; color:#fff; font-size:11px; }
        .sign { grid-template-columns:1fr 1fr 1fr 1fr; }
    </style>
</head>
<body>

@if (session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert err">{{ session('error') }}</div>@endif

<div class="toolbar">
    <button type="button" onclick="window.print()">{{ __('transport.print') }}</button>
    @if ($w->status === 'open')
        @can('waybills.edit')
            <form method="POST" action="{{ route('transport.waybills.deliver', $w) }}" onsubmit="return confirm('{{ __('transport.confirm_deliver') }}')">
                @csrf
                <input type="datetime-local" name="delivered_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                <button type="submit" style="background:#059669">✅ {{ __('transport.mark_delivered') }}</button>
            </form>
            <a class="light" href="{{ route('transport.waybills.edit', $w) }}">{{ __('transport.edit') }}</a>
            <form method="POST" action="{{ route('transport.waybills.cancel', $w) }}" onsubmit="return confirm('{{ __('transport.confirm_cancel_waybill') }}')">
                @csrf
                <button type="submit" class="light" style="background:#fff;color:#be123c;border:1px solid #fecdd3">✕ {{ __('transport.cancel_waybill') }}</button>
            </form>
        @endcan
    @endif
    @if ($w->invoice)
        <a class="light" href="{{ route('transport.invoices.show', $w->invoice) }}">🧾 {{ $w->invoice->invoice_number }}</a>
    @elseif ($w->status !== 'cancelled')
        @can('transport_invoices.create')
            <a href="{{ route('transport.waybills.invoice', $w) }}">🧾 {{ __('transport.create_invoice') }}</a>
        @endcan
    @endif
    @if ($drv?->hasValidPhone())
        <a class="light" href="{{ $drv->telUrl() }}">📞 {{ $drv->name }}</a>
        <a class="light" target="_blank" rel="noopener" style="color:#15803d" href="{{ $drv->whatsappUrl('السلام عليكم ' . $drv->name . '، بوليصة ' . $w->waybill_number . ' - الشاحنة ' . $w->truck?->plate_number . ' من ' . $w->from_label . ' إلى ' . $w->to_label) }}">💬 واتساب السائق</a>
    @endif
    <a class="light" href="{{ route('transport.waybills.index') }}">{{ __('transport.back_to_list') }}</a>
</div>

<div class="doc">
    @include('transport.partials.print-brand')

    @if ($contact['email'] || $contact['phone'])
        <div class="contact-bar">
            @if ($contact['phone'])<span>📞 <span dir="ltr">{{ $contact['phone'] }}</span></span>@endif
            @if ($contact['email'])<span>✉ <span dir="ltr">{{ $contact['email'] }}</span></span>@endif
        </div>
    @endif

    <div class="title">
        بوليصة شحن &nbsp;|&nbsp; WAYBILL &nbsp; — &nbsp; {{ $w->waybill_number }}
    </div>

    <div class="meta">
        <div class="box">
            <h4>بيانات البوليصة - Waybill</h4>
            <div class="kv"><span>رقم البوليصة - No.</span><span>{{ $w->waybill_number }}</span></div>
            <div class="kv"><span>التاريخ - Date</span><span>{{ $w->issue_date->format('Y-m-d') }}</span></div>
            <div class="kv"><span>الحالة - Status</span><span><span class="status-pill" style="background:{{ $stClass }}">{{ __('transport.wb_' . $w->status) }}</span></span></div>
            @if ($w->customer)<div class="kv"><span>العميل - Account</span><span>{{ $w->customer->name }}</span></div>@endif
        </div>
        <div class="box">
            <h4>الشاحنة والسائق - Truck & Driver</h4>
            <div class="kv"><span>رقم اللوحة - Plate</span><span>{{ $w->truck?->plate_number }}</span></div>
            @if ($w->truck?->type)<div class="kv"><span>النوع - Type</span><span>{{ $w->truck->type }}</span></div>@endif
            <div class="kv"><span>السائق - Driver</span><span>{{ $drv?->name ?? '-' }}</span></div>
            <div class="kv"><span>جوال السائق - Mobile</span><span dir="ltr">{{ $drv?->phone ?? '-' }}</span></div>
            @if ($drv?->id_number)<div class="kv"><span>الهوية / الإقامة - ID</span><span>{{ $drv->id_number }}</span></div>@endif
        </div>
    </div>

    <div class="route">
        <div>{{ \App\Support\SaudiRegions::name($w->from_region) }}<small>{{ $w->from_city }}</small></div>
        <span class="arrow">←</span>
        <div>{{ \App\Support\SaudiRegions::name($w->to_region) }}<small>{{ $w->to_city }}</small></div>
    </div>

    <div class="grid2">
        <div>
            <h4>📤 الشاحن - Shipper</h4>
            <div class="kv"><span>الاسم</span><span>{{ $w->shipper_name }}</span></div>
            @if ($w->shipper_phone)<div class="kv"><span>الجوال</span><span dir="ltr">{{ $w->shipper_phone }}</span></div>@endif
            <div class="kv"><span>المكان</span><span>{{ trim($w->from_label . ' ' . ($w->from_address ?? '')) }}</span></div>
        </div>
        <div>
            <h4>📥 المستلم - Consignee</h4>
            <div class="kv"><span>الاسم</span><span>{{ $w->consignee_name }}</span></div>
            @if ($w->consignee_phone)<div class="kv"><span>الجوال</span><span dir="ltr">{{ $w->consignee_phone }}</span></div>@endif
            <div class="kv"><span>المكان</span><span>{{ trim($w->to_label . ' ' . ($w->to_address ?? '')) }}</span></div>
        </div>
    </div>

    <div class="goods">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th>وصف البضاعة<br>Goods</th>
                    <th>عدد الطرود<br>Packages</th>
                    <th>الوزن (طن)<br>Weight</th>
                    <th>معاد التحميل<br>Loading</th>
                    <th>التنزيل المتوقع<br>Expected</th>
                    <th>التسليم الفعلي<br>Delivered</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>{{ $w->goods_description }}</b></td>
                    <td>{{ $w->packages_count ?? '-' }}</td>
                    <td>{{ $w->weight ? rtrim(rtrim(number_format($w->weight, 2), '0'), '.') : '-' }}</td>
                    <td>{{ $w->loaded_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $w->expected_unload_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $w->delivered_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="qr-row">
        @if ($qrSvg)
            {!! $qrSvg !!}
        @else
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($qrText) }}" alt="QR">
        @endif
        <div class="totals" style="flex:1">
            @if ((float) $w->freight_amount > 0)
                <div class="row grand"><span>أجرة النقل <small style="color:#fff">Freight</small></span><span>{{ number_format((float) $w->freight_amount, 2) }} {{ $currencySymbol ?? '' }}</span></div>
            @endif
            <div class="row"><span>الأجرة على - Paid by</span><span>{{ __('transport.payer_' . $w->freight_payer) }}</span></div>
        </div>
    </div>

    @if ($w->notes)<div class="notes">ملاحظات: {{ $w->notes }}</div>@endif

    <div class="sign">
        <div>الشاحن - Shipper</div>
        <div>السائق - Driver<br>{{ $drv?->name }}</div>
        <div>المستلم - Consignee</div>
        <div>الختم - Stamp</div>
    </div>
</div>
</body>
</html>
