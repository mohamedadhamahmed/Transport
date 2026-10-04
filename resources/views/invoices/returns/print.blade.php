<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('invoices.credit_note_preview_title') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
      :root {
        --brand-blue: #1456E8;
        --brand-purple: #6B2FD6;
        --ink: #1F2937;
        --muted: #6B7280;
        --border: #E5E7EB;
      }

      body {
        font-family: 'Cairo', sans-serif;
        background: #F3F4F6;
        margin: 0;
        padding: 20px;
      }

      .invoice-wrap {
        max-width: 950px;
        margin: 0 auto;
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
      }

      .invoice-topbar {
        display: flex;
        justify-content: center;
        padding: 14px 0;
      }
      .invoice-topbar button {
        background: linear-gradient(90deg, var(--brand-blue), var(--brand-purple));
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
      }

      .invoice-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 28px;
        border-bottom: 1px solid var(--border);
        gap: 16px;
      }
      .company-block { width: 33%; text-align: center; }
      .company-block .name { font-weight: 700; font-size: 16px; color: var(--ink); }
      .company-block p { font-size: 12px; color: var(--muted); margin: 2px 0; }
      .invoice-header img { width: 90px; height: 90px; object-fit: contain; }

      .invoice-type-badge {
        text-align: center;
        padding: 10px;
        margin: 0 28px;
      }
      .invoice-type-badge span {
        display: inline-block;
        padding: 6px 22px;
        border: 2px solid var(--brand-purple);
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        color: var(--brand-purple);
      }

      .meta-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        padding: 20px 28px;
        background: #F9FAFB;
        margin: 0 28px;
        border-radius: 10px;
      }
      .meta-grid .label { font-size: 11px; color: var(--muted); margin-bottom: 3px; }
      .meta-grid .value { font-size: 13px; font-weight: 600; color: var(--ink); }

      .parties-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        padding: 20px 28px;
      }
      .party-card {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
      }
      .party-card .title { font-size: 11px; color: var(--muted); margin-bottom: 8px; font-weight: 700; text-transform: uppercase; }
      .party-card .row { display: flex; justify-content: space-between; font-size: 12px; padding: 4px 0; border-bottom: 1px dashed var(--border); }
      .party-card .row:last-child { border-bottom: none; }
      .party-card .row .k { color: var(--muted); }
      .party-card .row .v { font-weight: 600; color: var(--ink); }

      .items-table-wrap { padding: 0 28px; margin-top: 8px; }
      table.items-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
      table.items-table thead th {
        background: #F3F4F6;
        color: var(--muted);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10.5px;
        padding: 10px 8px;
        text-align: center;
        border-bottom: 2px solid var(--border);
      }
      table.items-table tbody td {
        padding: 9px 8px;
        text-align: center;
        border-bottom: 1px solid var(--border);
        color: var(--ink);
      }
      table.items-table tbody tr:nth-child(even) { background: #FAFAFB; }

      .notice-box {
        margin: 18px 28px;
        padding: 10px 16px;
        border: 2px dashed var(--brand-purple);
        border-radius: 10px;
        text-align: center;
        font-size: 12px;
        color: var(--ink);
      }

      .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 10px 28px 24px;
        align-items: start;
      }

      .qr-box { display: flex; flex-direction: column; align-items: center; gap: 10px; }
      .bank-box {
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
        font-size: 12px;
        text-align: center;
        color: var(--ink);
      }

      .totals-box { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
      .totals-box .row {
        display: flex; justify-content: space-between;
        padding: 10px 16px; font-size: 13px;
        border-bottom: 1px solid var(--border);
        color: var(--ink);
      }
      .totals-box .row.grand {
        background: linear-gradient(90deg, var(--brand-blue), var(--brand-purple));
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        border-bottom: none;
      }
      .totals-box .row.grand small { display: block; font-size: 10px; opacity: .85; font-weight: 400; margin-top: 2px; }

      .invoice-note {
        padding: 0 28px 20px;
        font-size: 12px;
        color: var(--muted);
      }

      .invoice-footer {
        text-align: center;
        padding: 16px 28px 24px;
        border-top: 1px solid var(--border);
        font-size: 11px;
        color: var(--muted);
      }

 @media print {
  .invoice-topbar { display: none !important; }
  .hide-cell { display: none; }
  body {
    background: #fff !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .invoice-wrap {
    box-shadow: none;
    border-radius: 0;
    max-width: 100%;
    margin: 0;
  }
  @page {
    size: A4;
    margin: 5mm;
  }
}
    </style>
</head>
<body>

<div class="row row-sm table-responsive" dir="ltr">
  <div class="col-md-12 col-xl-12">
    <div class="main-content-body-invoice" id="print">
      <div class="invoice-wrap">

        <div class="invoice-topbar" id="print_Button">
          <button onclick="printDiv()">
              {{ __('invoices.print') }}
              <i class="mdi mdi-printer ml-1"></i>
          </button>
        </div>

        {{-- إرسال إشعار الدائن للزكاة - نفس فكرة زرار الإرسال في صفحة طباعة
        الفاتورة العادية (invoices/show.blade.php) بس على
        route('zatca.send-return') وبمرجع reference_value بدل رقم الفاتورة.
        .invoice-topbar بتتخفي وقت الطباعة تلقائيًا فمش هتظهر في الورقة. --}}
        <div class="invoice-topbar" id="zatca_button_wrap" style="padding-top:0;">
          @if(!($returns->first()->is_sent_to_zatca ?? false))
            <button type="button" id="sendzatca"
                    style="background:#F5811E; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                {{ __('zatca.send') }}
            </button>
          @elseif($invoice->credit_note_zatca_status === 'PASS')
            <a href="{{ route('zatca.download-credit-note', $referenceValue) }}"
               style="display:inline-block; background:#059669; color:#fff; text-decoration:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px;">
                {{ __('zatca.download_xml') }}
            </a>
          @else
            <span style="color:#DC2626; font-weight:600; font-size:13px; margin-inline-end:10px;">{{ __('zatca.failed') }}</span>
            <button type="button" id="sendzatca"
                    style="background:#F5811E; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                {{ __('zatca.retry') }}
            </button>
          @endif
        </div>

        {{-- الهيدر: بيانات الشركة عربي / شعار / بيانات الشركة إنجليزي - نفس بيانات
             صفحة طباعة الفاتورة العادية بالظبط (constants معرّفة في ServiceProvider) --}}
        @php
            $retBranchId = $invoice->branch_id ?? null;
            $headerSetting = null;
            if ($retBranchId) {
                $headerSetting = \App\Models\SystemSetting::where('branchs_id', $retBranchId)->first();
            }
            if (!$headerSetting) {
                $headerSetting = \App\Models\SystemSetting::orderBy('id')->first();
            }

            $hdrNameAr = $headerSetting?->name_ar ?: (defined('Namear') ? Namear : '');
            $hdrDescAr = $headerSetting ? $headerSetting->descriptionarbic : (defined('describtionar') ? describtionar : '');
            $hdrStAr = $headerSetting ? (' س . ت  :' . $headerSetting->SR) : (defined('STar') ? STar : '');
            $hdrTaxAr = $headerSetting ? ('  الرقم الضريبي : ' . $headerSetting->Tax) : (defined('Taxar') ? Taxar : '');

            $hdrLogo = $headerSetting?->logo ?: (defined('camplogo') ? camplogo : '');

            $hdrNameEn = $headerSetting?->name_en ?: (defined('Nameen') ? Nameen : '');
            $hdrDescEn = $headerSetting ? $headerSetting->descriptionenglish : (defined('describtionen') ? describtionen : '');
            $hdrStEn = $headerSetting ? ('  C.R : ' . $headerSetting->SR) : (defined('STen') ? STen : '');
            $hdrTaxEn = $headerSetting ? ('VAT Number : ' . $headerSetting->Tax) : (defined('Taxen') ? Taxen : '');
        @endphp

        {{-- الهيدر: بيانات الشركة عربي / شعار / بيانات الشركة إنجليزي --}}
        <div class="invoice-header" dir="rtl">
          <div class="company-block">
            <div class="name">{{ $hdrNameAr }}</div>
            @if(!empty(trim((string) $hdrDescAr)))
              <p>{{ $hdrDescAr }}</p>
            @endif
            @if(!empty(trim((string) $hdrStAr)))
              <p>{{ $hdrStAr }}</p>
            @endif
            @if(!empty(trim((string) $hdrTaxAr)))
              <p>{{ $hdrTaxAr }}</p>
            @endif
          </div>

          <div>
            @if(!empty($hdrLogo) && $hdrLogo !== 'empty')
              <img src="{{ asset('assets/img/brand/' . $hdrLogo) }}" alt="logo">
            @endif
          </div>

          <div class="company-block" dir="ltr">
            <div class="name">{{ $hdrNameEn }}</div>
            @if(!empty(trim((string) $hdrDescEn)))
              <p>{{ $hdrDescEn }}</p>
            @endif
            @if(!empty(trim((string) $hdrStEn)))
              <p>{{ $hdrStEn }}</p>
            @endif
            @if(!empty(trim((string) $hdrTaxEn)))
              <p>{{ $hdrTaxEn }}</p>
            @endif
          </div>
        </div>

        {{-- شارة نوع الإشعار: ضريبي / مبسط، بنفس منطق تصنيف الفاتورة الأصلية --}}
        <div class="invoice-type-badge">
          @if(strlen((string) $invoice->customer->tax_no) == 15)
            <span>Tax Credit Note - إشعار دائن ضريبي</span>
          @else
            <span>Simplified Tax Credit Note - إشعار دائن ضريبي مبسط</span>
          @endif

          <input type="text" name="show_reference_value" id="show_reference_value"
                 value="{{ $referenceValue }}" hidden>
        </div>

        {{-- بيانات الإشعار: رقمه، تاريخه، الفاتورة الأصلية، طريقة الاسترداد، الفرع --}}
        <div class="meta-grid" dir="rtl">
          <div>
            <div class="label">رقم الإشعار NOTICE NUMBER</div>
            <div class="value">{{ $referenceValue }}</div>
          </div>
          <div>
            <div class="label">تاريخ الإشعار NOTICE DATE</div>
            <div class="value">{{ optional($returns->first()->created_at)->format('Y-m-d H:i') }}</div>
          </div>
          <div>
            <div class="label">الفاتورة الأصلية ORIGINAL INVOICE</div>
            <div class="value">#{{ $invoice->invoice_number }}</div>
          </div>
          <div>
            <div class="label">طريقة الاسترداد REFUND METHOD</div>
            <div class="value">{{ $refundMethodLabel }}</div>
          </div>
          <div>
            <div class="label">اسم الفرع BRANCH NAME</div>
            <div class="value">{{ optional($invoice->branch)->name }}</div>
          </div>
        </div>

        {{-- بيانات العميل والبائع - نفس أعمدة جدول customers المستخدمة في طباعة
             الفاتورة الأصلية بالظبط (tax_no / CRN / address / sub_city / ...) --}}
        <div class="parties-grid" dir="rtl">
          <div class="party-card">
            <div class="title">اسم العميل - CLIENT NAME</div>
            <div class="row"><span class="k">الاسم</span><span class="v">{{ $invoice->customer->name }}</span></div>
            <div class="row">
              <span class="k">الرقم الضريبي</span>
              <span class="v">{{ $invoice->customer->tax_no == 0 ? '-' : $invoice->customer->tax_no }}</span>
            </div>
            <div class="row">
              <span class="k">السجل التجاري</span>
              <span class="v">{{ $invoice->customer->CRN == null ? '-' : $invoice->customer->CRN }}</span>
            </div>
            <div class="row">
              @if($invoice->customer->address == '-')
                <span class="k">رقم الجوال</span>
                <span class="v">{{ $invoice->customer->id == 1 ? '-' : $invoice->customer->phone }}</span>
              @else
                <span class="k">المدينة</span>
                <span class="v">{{ $invoice->customer->id == 1 ? '-' : $invoice->customer->address }}</span>
              @endif
            </div>
            <div class="row">
              <span class="k">المنطقة</span>
              <span class="v">{{ $invoice->customer->id == 1 ? '-' : $invoice->customer->sub_city }}</span>
            </div>
            <div class="row">
              <span class="k">اسم الشارع</span>
              <span class="v">{{ $invoice->customer->id == 1 ? '-' : $invoice->customer->street_name }}</span>
            </div>
            <div class="row">
              <span class="k">الرمز البريدي</span>
              <span class="v">{{ $invoice->customer->postcode }}</span>
            </div>
            <div class="row">
              <span class="k">رقم المبنى</span>
              <span class="v">{{ $invoice->customer->id == 1 ? '-' : $invoice->customer->building_number }}</span>
            </div>
          </div>

          <div class="party-card">
            <div class="title">اسم البائع - SELLER NAME</div>
            <div class="row"><span class="k">الاسم</span><span class="v">{{Namear}}</span></div>
            <div class="row"><span class="k">الرقم الضريبي</span><span class="v">{{Taxen}}</span></div>
            <div class="row"><span class="k">المدينة</span><span class="v">{{ defined('city') ? city : '-' }}</span></div>
            <div class="row"><span class="k">المنطقة</span><span class="v">{{ defined('region') ? region : '-' }}</span></div>
            <div class="row"><span class="k">اسم الشارع</span><span class="v">{{ defined('street_name') ? street_name : '-' }}</span></div>
            <div class="row"><span class="k">الرمز البريدي</span><span class="v">{{ defined('postal_number') ? postal_number : '-' }}</span></div>
            <div class="row"><span class="k">رقم المبنى</span><span class="v">{{ defined('building_number') ? building_number : '-' }}</span></div>
          </div>
        </div>

        {{-- جدول الأصناف المرتجعة --}}
        <div class="items-table-wrap">
          <table class="items-table">
            <thead>
              <tr>
                <th>NO<br>رقم</th>
                <th>Item NO<br>رقم منتج</th>
                <th>ITEM NAME<br>اسم الصنف</th>
                <th>QUANTITY<br>الكمية المرتجعة</th>
                <th>PRODUCT PRICE<br>سعر القطعة</th>
                <th>Total<br>الاجمالي</th>
                <th>DISCOUNT<br>الخصم</th>
                <th>Total AFTER DISCOUNT<br>الاجمالي بعد الخصم</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($returns as $index => $row)
                <?php
                    $lineTotal = $row->unit_price * $row->quantity;
                    $lineDiscount = $row->discount_amount + $row->invoice_discount_amount;
                ?>
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td dir="rtl">{{ optional($row->product)->code ?? '-' }}</td>
                  <td>{{ optional($row->product)->name ?? 'منتج محذوف' }}</td>
                  <td>{{ $row->quantity }}</td>
                  <td>{{ number_format($row->unit_price, 2, '.', '') }}</td>
                  <td>{{ number_format($lineTotal, 2, '.', '') }}</td>
                  <td>{{ number_format($lineDiscount, 2, '.', '') }}</td>
                  <td>{{ number_format($lineTotal - $lineDiscount, 2, '.', '') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="notice-box">
          إشعار دائن صادر بخصوص مرتجع من الفاتورة رقم #{{ $invoice->invoice_number }}
          بتاريخ {{ optional($invoice->created_at)->format('Y-m-d') }}
        </div>

        {{-- QR الضريبي + بيانات البنك + الإجماليات --}}
        <div class="bottom-grid" dir="rtl">
          <div>
            <div class="qr-box">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($qrCodeData) }}" alt="QR Code">
            </div>

            @if(Auth()->user()->branchs_id==1)
              <div class="bank-box" style="margin-top:14px;">
                {{bankname}} <br>
                Account Number: {{bank_acount_number}} <br>
                IBAN Number: {{bank_acount_iban}}
              </div>
            @endif
          </div>

          <div class="totals-box">
            <div class="row">
              <span>الاجمالي - SUB TOTAL</span>
              <span>{{ number_format($subtotal, 2, '.', '') }}</span>
            </div>
            <div class="row">
              <span>الخصم - DISCOUNT</span>
              <span>{{ number_format($totalDiscount, 2, '.', '') }}</span>
            </div>
            <div class="row">
              <span>الاجمالي بعد الخصم - SUB TOTAL AFTER DISCOUNT</span>
              <span>{{ number_format($netBeforeTax, 2, '.', '') }}</span>
            </div>
            <div class="row">
              <span>ضريبة القيمة المضافة ({{ $taxRate * 100 }}%)</span>
              <span>{{ number_format($taxTotal, 2, '.', '') }}</span>
            </div>
            <div class="row grand">
              <span>إجمالي المرتجع - NET REFUND TOTAL</span>
              <span>{{ number_format($netTotal, 2, '.', '') }}</span>
            </div>
          </div>
        </div>

        <input type="hidden" id="token_search" value="{{ csrf_token() }}">

      </div>
    </div>
  </div>
</div>

{{-- مودال "إرسال للزكاة / طباعة" - الصفحة دي مالهاش أي طريق تانية توصلها
     غير مباشرة بعد حفظ مرتجع مبيعات جديد (InvoiceReturnController::store())،
     يعني كل زيارة ليها أصلًا "فور الحفظ" - فالمودال بيظهر دايمًا هنا بدل
     ما تطبع الصفحة أوتوماتيك على طول زي الأول. زرار "إرسال للزكاة" بيظهر
     بس لو المستخدم عنده صلاحية zatca.send. --}}
@if(!($returns->first()->is_sent_to_zatca ?? false))
    <div id="save-choice-modal" style="position:fixed;inset:0;background:rgba(15,27,76,.55);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
        <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:380px;width:100%;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,.25);">
            <h3 style="margin:0 0 8px;font-size:16px;font-weight:700;color:#1F2937;">{{ __('invoices.return_created_successfully') }}</h3>
            <p style="margin:0 0 22px;font-size:13px;color:#6B7280;">{{ __('invoices.save_choice_prompt') }}</p>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @can('zatca.send')
                    <button type="button" onclick="modalSendZatca()"
                            style="background:#F5811E;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;">
                        {{ __('zatca.send') }}
                    </button>
                @endcan
                <button type="button" onclick="modalPrint()"
                        style="background:linear-gradient(90deg, var(--brand-blue), var(--brand-purple));color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;">
                    {{ __('invoices.print') }}
                </button>
                <button type="button" onclick="closeSaveModal()"
                        style="background:#F3F4F6;color:#374151;border:none;padding:9px 20px;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;">
                    {{ __('invoices.close') }}
                </button>
            </div>
        </div>
    </div>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    function printDiv() {
        var printContents = document.getElementById('print').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }

    // مودال الاختيار بعد الحفظ - مش أوتوماتيك، المستخدم هو اللي يختار
    // "إرسال للزكاة" أو "طباعة" أو يقفل المودال من غير ما يعمل أي حاجة.
    function closeSaveModal() {
        var el = document.getElementById('save-choice-modal');
        if (el) { el.remove(); }
    }
    function modalPrint() {
        closeSaveModal();
        printDiv();
    }
    function modalSendZatca() {
        $('#sendzatca').trigger('click');
    }

    // إرسال إشعار الدائن للزكاة - نفس منطق زرار الفاتورة العادية
    // (invoices/show.blade.php) بس بمرجع reference_value. لازم يترتبط
    // في $(document).ready منفصل زي هناك بالظبط عشان #sendzatca يكون
    // موجود فعليًا بعد استبدال document.body.innerHTML فوق.
    $(document).ready(function() {
        $(document).on('click', '#sendzatca', function () {
            var $btn = $(this);
            var referenceValue = $('#show_reference_value').val();
            var token = $('#token_search').val();

            $btn.prop('disabled', true).css('opacity', '0.6');

            $.ajax({
                url: "{{ url('zatca/returns') }}/" + referenceValue + "/send",
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': token },
                success: function (data) {
                    if (data && data.success) {
                        alert(@json(__('zatca.sent_successfully')));
                        window.location.reload();
                    } else {
                        alert((data && data.message) || @json(__('zatca.send_failed')));
                        $btn.prop('disabled', false).css('opacity', '1');
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : @json(__('zatca.send_failed'));
                    alert(msg);
                    $btn.prop('disabled', false).css('opacity', '1');
                }
            });
        });
    });
</script>
</body>
</html>
