<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة طباعة الفاتورة</title>
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
        border: 2px solid var(--brand-blue);
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        color: var(--brand-blue);
      }

      .meta-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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
    margin: 5mm; /* تقليل الهوامش الخارجية لإعطاء مساحة إضافية للمحتوى */
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

        {{-- إرسال الفاتورة للزكاة - بنفس حالات زرار شاشة "الفواتير
        الضريبية (ZATCA)" (zatca/index.blade.php): لسه ما اترسلتش/
        اترسلت بنجاح/فشلت. الصفحة دي مش صفحة Tailwind عادية (هيدر
        وسكريبت مستقلين بالكامل زي النظام القديم) فالزرار هنا بـ jQuery
        عادي بدل Alpine، وبيستخدم نفس route('zatca.send') الحقيقي بدل
        أي endpoint قديم غير موجود في المشروع ده. .invoice-topbar
        بتتخفي تلقائيًا وقت الطباعة (@media print في الأعلى) فمش هتظهر
        في الورقة المطبوعة. --}}
        <div class="invoice-topbar" id="zatca_button_wrap" style="padding-top:0;">
          @if(!$data['invoiceData']->is_sent_to_zatca)
            <button type="button" id="sendzatca"
                    style="background:#F5811E; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                {{ __('zatca.send') }}
            </button>
          @elseif($data['invoiceData']->zatca_status === 'PASS')
            <a href="{{ route('zatca.download-xml', $data['invoiceData']->id) }}"
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

        {{-- الهيدر: بيانات الشركة عربي / شعار / بيانات الشركة إنجليزي --}}
        <div class="invoice-header" dir="rtl">
          <div class="company-block">
            <div class="name">{{Namear}}</div>
            <p>{{describtionar}}</p>
            <p>{{STar}}</p>
            <p>{{Taxar}}</p>
          </div>

          <div>
            <?php $logo = camplogo; ?>
              <img src="{{ asset('assets/img/brand').'/'.$logo }}" alt="logo">
          </div>

          <div class="company-block">
            <div class="name">{{Nameen}}</div>
            <p>{{describtionen}}</p>
            <p>{{STen}}</p>
            <p>{{Taxen}}</p>
          </div>
        </div>

        {{-- شارة نوع الفاتورة --}}
        <div class="invoice-type-badge">
          @if(strlen($data['invoiceData']->customer->tax_no)==15)
            <span>Tax Invoice - فاتورة ضريبية</span>
          @else
            <span>Simplified Tax Invoice - فاتورة ضريبية مبسطة</span>
          @endif

          <input type="number" name="show_invoice_number" id="show_invoice_number"
                 value="{{$data['invoiceData']->id}}" hidden>
        </div>

        {{-- بيانات الدفع والفاتورة --}}
        <?php
            $pay = '';
            if ($data['invoiceData']->payment_method == "cash") { $pay = __('report.cash'); }
            elseif ($data['invoiceData']->payment_method == "card") { $pay = __('report.shabka'); }
            elseif ($data['invoiceData']->payment_method == "credit") { $pay = __('report.credit'); }
            elseif ($data['invoiceData']->payment_method == "bank_transfer") { $pay = __('home.Bank_transfer'); }
            else { $pay = __('home.Partition of the amount'); }
        ?>
        <div class="meta-grid" dir="rtl">
          <div>
            <div class="label">طريقة الدفع PAYMENT METHOD</div>
            <div class="value">{{$pay}}</div>
          </div>
          <div>
            <div class="label">تاريخ الفاتورة INVOICE DATE</div>
            <div class="value">{{ $data['invoiceData']->created_at}}</div>
          </div>
          <div>
            <div class="label">رقم الفاتورة INVOICE NUMBER</div>
            <div class="value">{{ $data['invoiceData']->id}}</div>
          </div>
          <div>
            <div class="label">اسم الفرع BRANCH NAME</div>
            <div class="value">{{$data['invoiceData']->branch->name}}</div>
          </div>
        </div>

        {{-- بيانات العميل والبائع --}}
        <div class="parties-grid" dir="rtl">
          <div class="party-card">
            <div class="title">اسم العميل - CLIENT NAME</div>
            <div class="row"><span class="k">الاسم</span><span class="v">{{$data['invoiceData']->customer->name}}</span></div>
            <div class="row">
              <span class="k">الرقم الضريبي</span>
              <span class="v">
                {{$data['invoiceData']->customer->tax_no==0?'-':$data['invoiceData']->customer->tax_no}}
              </span>
            </div>
            <div class="row">
              <span class="k">السجل التجاري</span>
              <span class="v">{{$data['invoiceData']->customer->CRN==NULL?'-':$data['invoiceData']->customer->CRN}}</span>
            </div>
            <div class="row">
              @if($data['invoiceData']->customer->address == '-')
                <span class="k">رقم الجوال</span>
                <span class="v">{{$data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->phone}}</span>
              @else
                <span class="k">المدينة</span>
                <span class="v">{{$data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->address}}</span>
              @endif
            </div>
            <div class="row">
              <span class="k">المنطقة</span>
              <span class="v">{{$data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->sub_city}}</span>
            </div>
            <div class="row">
              <span class="k">اسم الشارع</span>
              <span class="v">{{$data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->street_name}}</span>
            </div>
            <div class="row">
              <span class="k">الرمز البريدي</span>
              <span class="v">{{$data['invoiceData']->customer->postcode}}</span>
            </div>
            <div class="row">
              <span class="k">رقم المبنى</span>
              <span class="v">{{$data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->building_number}}</span>
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

        {{-- جدول الأصناف --}}
        <div class="items-table-wrap">
          <table class="items-table">
            <thead>
              <tr>
                <th>NO<br>رقم</th>
                <th>Item NO<br>رقم منتج</th>
                <th>ITEM NAME<br>اسم الصنف</th>
                <th>QUANTITY<br>الكمية</th>
                <th>PRODUCT PRICE<br>سعر القطعة</th>
                <th>Total<br>الاجمالي</th>
                <th>DISCOUNT<br>الخصم</th>
                <th>Total AFTER DISCOUNT<br>الاجمالي بعد الخصم</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 0; $discountreturn = 0; ?>
              @foreach (App\Models\InvoiceItem::where('invoice_id', $data['invoiceData']->id)->get() as $product)
                @if($product->quantity!=0)
                  <?php $i++ ?>
                  <tr>
                    <td>{{$i}}</td>
                    <td dir="rtl">{{ optional($product->productData)->code ?? '-' }}</td>
                    <td>{{ $product->product_name_snapshot ?? optional($product->productData)->name ?? 'منتج محذوف' }}</td>
                    <td>{{$product->quantity}}</td>
                    <td>{{ number_format($product->unit_price, 2, '.', '')}}</td>
                    <td>{{ number_format($product->unit_price*$product->quantity, 2, '.', '')}}</td>
                    <td>{{ number_format($product->discount_amount, 2, '.', '')}}</td>
                    <td>{{ number_format(($product->unit_price*$product->quantity)-$product->Discount_Value, 2, '.', '')}}</td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="notice-box">
          يمكن ارجاع القطع المباعة او استبدالها خلال 7 ايام من تاريخ الشراء وتكون بحالتها المباعة و القطع الكهربائية لا ترد ولا تستبدل
        </div>

        {{-- QR الضريبي + بيانات البنك + الإجماليات --}}
        <?php
            function ConvertToHEX($value) {
                return pack("H*", sprintf("%02X", $value));
            }
            $invoice = App\Models\Invoice::find($data['invoiceData']->id);
            $price = $invoice->cash_amount + $invoice->wire_transfer_amount + $invoice->bank_amount + $invoice->credit_amount;
            
            $rawTaxRate = $invoice->tax_rate ?? 0.15;
            $taxRate = $rawTaxRate > 1 ? $rawTaxRate / 100 : $rawTaxRate;

            $price_befor_tax = $price / (1 + $taxRate);
            $invoicetotal_addedvalue = $price_befor_tax * $taxRate;
            $invoicetotal_price = $price_befor_tax;
            $invoicetotal_discount = $invoice->invoice_level_discount ;

            $sellerName = sallerQrCode;
            $varNumber = TaxQrCode;
            $time = $invoice->created_at;
            $issue_time = substr($time, 11);
            $issue_date = substr($time, 0, 10);
            $time = (string)$issue_date . 'T' . (string)$issue_time;

            $total = number_format((round($invoicetotal_addedvalue + $invoicetotal_price, 2)),2,'.','');
            $tax = number_format(round($invoicetotal_addedvalue, 2),2,'.','');
            $HexSeller = ConvertToHEX(1) . ConvertToHEX(strlen($sellerName));
            $seller  =  $HexSeller . $sellerName;
            $HexVAT  = ConvertToHEX(2) . ConvertToHEX(strlen($varNumber));
            $vat  = $HexVAT . $varNumber;
            $HexTime = ConvertToHEX(3) . ConvertToHEX(strlen($time));
            $time  = $HexTime . $time;
            $HexTotal = ConvertToHEX(4) . ConvertToHEX(strlen($total));
            $total  = $HexTotal . $total;
            $HexVATN = ConvertToHEX(5) . ConvertToHEX(strlen($tax));
            $VATN  = $HexVATN . $tax;
            $empty='';
            $Hexempty = ConvertToHEX(6) . ConvertToHEX(strlen($empty)); $empty6 = $Hexempty . $empty;
            $Hexempty = ConvertToHEX(7) . ConvertToHEX(strlen($empty)); $empty7 = $Hexempty . $empty;
            $Hexempty = ConvertToHEX(8) . ConvertToHEX(strlen($empty)); $empty8 = $Hexempty . $empty;
            $Hexempty = ConvertToHEX(9) . ConvertToHEX(strlen($empty)); $empty9 = $Hexempty . $empty;
            $tobase = $seller . $vat . $time . $total . $VATN . $empty6 . $empty7 . $empty8 . $empty9;
            $dataforQRcode = base64_encode($tobase);
        ?>

        <div class="bottom-grid" dir="rtl">
          <div>
            <div class="qr-box">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($dataforQRcode) }}" alt="QR Code">
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
              <span>{{number_format((float)(round($invoicetotal_price,2)+round($invoicetotal_discount,2)), 2, '.', '')}}</span>
            </div>
            <div class="row">
              <span>الخصم - DISCOUNT</span>
              <span>{{number_format(round($invoicetotal_discount,2), 2, '.', '')}}</span>
            </div>
            <div class="row">
              <span>الاجمالي بعد الخصم - SUB TOTAL AFTER DISCOUNT</span>
              <span>{{number_format(round($invoicetotal_price,2), 2, '.', '')}}</span>
            </div>
            <div class="row">
              <span>ضريبة القيمة المضافة ({{ $taxRate * 100 }}%)</span>
              <span>{{number_format(round($invoicetotal_addedvalue,2), 2, '.', '')}}</span>
            </div>
            <div class="row grand">
              <span>الاجمالي الكلي - NET TOTAL</span>
              <span>
                {{number_format(round($invoicetotal_addedvalue+$invoicetotal_price,2), 2, '.', '')}}
                <small>{{ $data['totatextlriyales'] }} Riyals {{ $data['totatextlrihalala'] }}</small>
              </span>
            </div>
          </div>
        </div>

        <div class="invoice-note" dir="rtl">
          {{__('invoices.note')}}: {{$data['invoiceData']->note}}
        </div>

    

        <input type="hidden" id="token_search" value="{{ csrf_token() }}">

      </div>
    </div>
  </div>
</div>

{{-- مودال "إرسال للزكاة / طباعة" - بيظهر مرة واحدة بس فور إنشاء
     الفاتورة (?saved=1 من InvoiceController::store()/approveDraft())
     بدل ما الصفحة تطبع أوتوماتيك على طول زي ما كان بيحصل قبل كده.
     زرار "إرسال للزكاة" بيظهر بس لو المستخدم عنده صلاحية zatca.send
     (بعض الشركات مش رابطة مع زكاة أصلاً) ولو الفاتورة لسه ما اترسلتش. --}}
@if(request('saved') && !$data['invoiceData']->is_sent_to_zatca)
    <div id="save-choice-modal" style="position:fixed;inset:0;background:rgba(15,27,76,.55);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
        <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:380px;width:100%;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,.25);">
            <h3 style="margin:0 0 8px;font-size:16px;font-weight:700;color:#1F2937;">{{ __('invoices.created_successfully') }}</h3>
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
        // بنستخدم نفس زرار #sendzatca وعملية الإرسال الحقيقية اللي في
        // شريط الفاتورة (تحت) بدل تكرار كود الـ AJAX هنا.
        $('#sendzatca').trigger('click');
    }

    // إرسال الفاتورة للزكاة - نفس منطق sendToZatca() في
    // zatca/index.blade.php بس بـ jQuery/$.ajax عادي (الصفحة دي مالهاش
    // Alpine/SweetAlert محملين). #sendzatca هنا موجود بعد استبدال
    // document.body.innerHTML فوق (نفس المحتوى اتنسخ بالظبط)، فربط
    // الحدث لازم يحصل في $(document).ready منفصل بعد الاستبدال ده -
    // ترتيب الـ <script> في الصفحة بيضمن إن ready() ده بيتنفذ بعد اللي
    // فوقه.
    $(document).ready(function() {
        $(document).on('click', '#sendzatca', function () {
            var $btn = $(this);
            var invoiceId = $('#show_invoice_number').val();
            var token = $('#token_search').val();

            $btn.prop('disabled', true).css('opacity', '0.6');

            $.ajax({
                url: "{{ url('zatca') }}/" + invoiceId + "/send",
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