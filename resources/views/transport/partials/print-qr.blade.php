{{--
    QR الفاتورة/الإشعار. 3 طبقات عشان يظهر في كل الحالات:
    1) SVG من السيرفر (simplesoftwareio/simple-qrcode)
    2) لو المكتبة مش متسطبة/فشلت: رسم في المتصفح بمكتبة qrcode-generator
    3) لو السكريبت اتمنع: صورة من api.qrserver.com
--}}
@php
    $qrPayload = (string) ($qrData ?? '');
    $qrSvgOut = null;
    if ($qrPayload !== '' && class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
        try {
            $qrSvgOut = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(130)->margin(0)->errorCorrection('M')->generate($qrPayload);
            $qrSvgOut = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $qrSvgOut);
        } catch (\Throwable $e) {
            report($e);
            $qrSvgOut = null;
        }
    }
    $qrId = 'qr-' . substr(md5($qrPayload . microtime()), 0, 8);
@endphp
<style>.qr-code svg, .qr-code img { width:130px !important; height:130px !important; display:block; }</style>
<div class="qr-code" id="{{ $qrId }}" data-qr="{{ $qrPayload }}" style="width:130px;height:130px;flex:0 0 130px;background:#fff">
    @if ($qrSvgOut)
        {!! $qrSvgOut !!}
    @endif
</div>
@if (!$qrSvgOut && $qrPayload !== '')
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        (function () {
            var box = document.getElementById(@json($qrId));
            var data = box.getAttribute('data-qr');
            try {
                var qr = qrcode(0, 'M');
                qr.addData(data);
                qr.make();
                box.innerHTML = qr.createSvgTag({ cellSize: 3, margin: 0, scalable: true });
            } catch (e) {
                box.innerHTML = '<img alt="QR" style="width:130px;height:130px" src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=' + encodeURIComponent(data) + '">';
            }
        })();
    </script>
@endif
