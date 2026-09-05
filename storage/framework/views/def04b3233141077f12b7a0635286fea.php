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
              <?php echo e(__('invoices.print')); ?>

              <i class="mdi mdi-printer ml-1"></i>
          </button>
        </div>

        
        <div class="invoice-topbar" id="zatca_button_wrap" style="padding-top:0;">
          <?php if(!$data['invoiceData']->is_sent_to_zatca): ?>
            <button type="button" id="sendzatca"
                    style="background:#F5811E; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                <?php echo e(__('zatca.send')); ?>

            </button>
          <?php elseif($data['invoiceData']->zatca_status === 'PASS'): ?>
            <a href="<?php echo e(route('zatca.download-xml', $data['invoiceData']->id)); ?>"
               style="display:inline-block; background:#059669; color:#fff; text-decoration:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px;">
                <?php echo e(__('zatca.download_xml')); ?>

            </a>
          <?php else: ?>
            <span style="color:#DC2626; font-weight:600; font-size:13px; margin-inline-end:10px;"><?php echo e(__('zatca.failed')); ?></span>
            <button type="button" id="sendzatca"
                    style="background:#F5811E; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                <?php echo e(__('zatca.retry')); ?>

            </button>
          <?php endif; ?>
        </div>

        
        <div class="invoice-header" dir="rtl">
          <div class="company-block">
            <div class="name"><?php echo e(Namear); ?></div>
            <p><?php echo e(describtionar); ?></p>
            <p><?php echo e(STar); ?></p>
            <p><?php echo e(Taxar); ?></p>
          </div>

          <div>
            <?php $logo = camplogo; ?>
              <img src="<?php echo e(asset('assets/img/brand').'/'.$logo); ?>" alt="logo">
          </div>

          <div class="company-block">
            <div class="name"><?php echo e(Nameen); ?></div>
            <p><?php echo e(describtionen); ?></p>
            <p><?php echo e(STen); ?></p>
            <p><?php echo e(Taxen); ?></p>
          </div>
        </div>

        
        <div class="invoice-type-badge">
          <?php if(strlen($data['invoiceData']->customer->tax_no)==15): ?>
            <span>Tax Invoice - فاتورة ضريبية</span>
          <?php else: ?>
            <span>Simplified Tax Invoice - فاتورة ضريبية مبسطة</span>
          <?php endif; ?>

          <input type="number" name="show_invoice_number" id="show_invoice_number"
                 value="<?php echo e($data['invoiceData']->id); ?>" hidden>
        </div>

        
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
            <div class="value"><?php echo e($pay); ?></div>
          </div>
          <div>
            <div class="label">تاريخ الفاتورة INVOICE DATE</div>
            <div class="value"><?php echo e($data['invoiceData']->created_at); ?></div>
          </div>
          <div>
            <div class="label">رقم الفاتورة INVOICE NUMBER</div>
            <div class="value"><?php echo e($data['invoiceData']->id); ?></div>
          </div>
          <div>
            <div class="label">اسم الفرع BRANCH NAME</div>
            <div class="value"><?php echo e($data['invoiceData']->branch->name); ?></div>
          </div>
        </div>

        
        <div class="parties-grid" dir="rtl">
          <div class="party-card">
            <div class="title">اسم العميل - CLIENT NAME</div>
            <div class="row"><span class="k">الاسم</span><span class="v"><?php echo e($data['invoiceData']->customer->name); ?></span></div>
            <div class="row">
              <span class="k">الرقم الضريبي</span>
              <span class="v">
                <?php echo e($data['invoiceData']->customer->tax_no==0?'-':$data['invoiceData']->customer->tax_no); ?>

              </span>
            </div>
            <div class="row">
              <span class="k">السجل التجاري</span>
              <span class="v"><?php echo e($data['invoiceData']->customer->CRN==NULL?'-':$data['invoiceData']->customer->CRN); ?></span>
            </div>
            <div class="row">
              <?php if($data['invoiceData']->customer->address == '-'): ?>
                <span class="k">رقم الجوال</span>
                <span class="v"><?php echo e($data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->phone); ?></span>
              <?php else: ?>
                <span class="k">المدينة</span>
                <span class="v"><?php echo e($data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->address); ?></span>
              <?php endif; ?>
            </div>
            <div class="row">
              <span class="k">المنطقة</span>
              <span class="v"><?php echo e($data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->sub_city); ?></span>
            </div>
            <div class="row">
              <span class="k">اسم الشارع</span>
              <span class="v"><?php echo e($data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->street_name); ?></span>
            </div>
            <div class="row">
              <span class="k">الرمز البريدي</span>
              <span class="v"><?php echo e($data['invoiceData']->customer->postcode); ?></span>
            </div>
            <div class="row">
              <span class="k">رقم المبنى</span>
              <span class="v"><?php echo e($data['invoiceData']->customer->id == 1 ? '-' : $data['invoiceData']->customer->building_number); ?></span>
            </div>
          </div>

          <div class="party-card">
            <div class="title">اسم البائع - SELLER NAME</div>
            <div class="row"><span class="k">الاسم</span><span class="v"><?php echo e(Namear); ?></span></div>
            <div class="row"><span class="k">الرقم الضريبي</span><span class="v"><?php echo e(Taxen); ?></span></div>
            <div class="row"><span class="k">المدينة</span><span class="v"><?php echo e(defined('city') ? city : '-'); ?></span></div>
            <div class="row"><span class="k">المنطقة</span><span class="v"><?php echo e(defined('region') ? region : '-'); ?></span></div>
            <div class="row"><span class="k">اسم الشارع</span><span class="v"><?php echo e(defined('street_name') ? street_name : '-'); ?></span></div>
            <div class="row"><span class="k">الرمز البريدي</span><span class="v"><?php echo e(defined('postal_number') ? postal_number : '-'); ?></span></div>
            <div class="row"><span class="k">رقم المبنى</span><span class="v"><?php echo e(defined('building_number') ? building_number : '-'); ?></span></div>
          </div>
        </div>

        
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
              <?php $__currentLoopData = App\Models\InvoiceItem::where('invoice_id', $data['invoiceData']->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($product->quantity!=0): ?>
                  <?php $i++ ?>
                  <tr>
                    <td><?php echo e($i); ?></td>
                    <td dir="rtl"><?php echo e(optional($product->productData)->code ?? '-'); ?></td>
                    <td><?php echo e(optional($product->productData)->name ?? 'منتج محذوف'); ?></td>
                    <td><?php echo e($product->quantity); ?></td>
                    <td><?php echo e(number_format($product->unit_price, 2, '.', '')); ?></td>
                    <td><?php echo e(number_format($product->unit_price*$product->quantity, 2, '.', '')); ?></td>
                    <td><?php echo e(number_format($product->discount_amount, 2, '.', '')); ?></td>
                    <td><?php echo e(number_format(($product->unit_price*$product->quantity)-$product->Discount_Value, 2, '.', '')); ?></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>

        <div class="notice-box">
          يمكن ارجاع القطع المباعة او استبدالها خلال 7 ايام من تاريخ الشراء وتكون بحالتها المباعة و القطع الكهربائية لا ترد ولا تستبدل
        </div>

        
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
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?php echo e(urlencode($dataforQRcode)); ?>" alt="QR Code">
            </div>

            <?php if(Auth()->user()->branchs_id==1): ?>
              <div class="bank-box" style="margin-top:14px;">
                <?php echo e(bankname); ?> <br>
                Account Number: <?php echo e(bank_acount_number); ?> <br>
                IBAN Number: <?php echo e(bank_acount_iban); ?>

              </div>
            <?php endif; ?>
          </div>

          <div class="totals-box">
            <div class="row">
              <span>الاجمالي - SUB TOTAL</span>
              <span><?php echo e(number_format((float)(round($invoicetotal_price,2)+round($invoicetotal_discount,2)), 2, '.', '')); ?></span>
            </div>
            <div class="row">
              <span>الخصم - DISCOUNT</span>
              <span><?php echo e(number_format(round($invoicetotal_discount,2), 2, '.', '')); ?></span>
            </div>
            <div class="row">
              <span>الاجمالي بعد الخصم - SUB TOTAL AFTER DISCOUNT</span>
              <span><?php echo e(number_format(round($invoicetotal_price,2), 2, '.', '')); ?></span>
            </div>
            <div class="row">
              <span>ضريبة القيمة المضافة (<?php echo e($taxRate * 100); ?>%)</span>
              <span><?php echo e(number_format(round($invoicetotal_addedvalue,2), 2, '.', '')); ?></span>
            </div>
            <div class="row grand">
              <span>الاجمالي الكلي - NET TOTAL</span>
              <span>
                <?php echo e(number_format(round($invoicetotal_addedvalue+$invoicetotal_price,2), 2, '.', '')); ?>

                <small><?php echo e($data['totatextlriyales']); ?> Riyals <?php echo e($data['totatextlrihalala']); ?></small>
              </span>
            </div>
          </div>
        </div>

        <div class="invoice-note" dir="rtl">
          <?php echo e(__('invoices.note')); ?>: <?php echo e($data['invoiceData']->note); ?>

        </div>

    

        <input type="hidden" id="token_search" value="<?php echo e(csrf_token()); ?>">

      </div>
    </div>
  </div>
</div>


<?php if(request('saved') && !$data['invoiceData']->is_sent_to_zatca): ?>
    <div id="save-choice-modal" style="position:fixed;inset:0;background:rgba(15,27,76,.55);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
        <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:380px;width:100%;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,.25);">
            <h3 style="margin:0 0 8px;font-size:16px;font-weight:700;color:#1F2937;"><?php echo e(__('invoices.created_successfully')); ?></h3>
            <p style="margin:0 0 22px;font-size:13px;color:#6B7280;"><?php echo e(__('invoices.save_choice_prompt')); ?></p>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('zatca.send')): ?>
                    <button type="button" onclick="modalSendZatca()"
                            style="background:#F5811E;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;">
                        <?php echo e(__('zatca.send')); ?>

                    </button>
                <?php endif; ?>
                <button type="button" onclick="modalPrint()"
                        style="background:linear-gradient(90deg, var(--brand-blue), var(--brand-purple));color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;">
                    <?php echo e(__('invoices.print')); ?>

                </button>
                <button type="button" onclick="closeSaveModal()"
                        style="background:#F3F4F6;color:#374151;border:none;padding:9px 20px;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;">
                    <?php echo e(__('invoices.close')); ?>

                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

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
                url: "<?php echo e(url('zatca')); ?>/" + invoiceId + "/send",
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': token },
                success: function (data) {
                    if (data && data.success) {
                        alert(<?php echo json_encode(__('zatca.sent_successfully'), 15, 512) ?>);
                        window.location.reload();
                    } else {
                        alert((data && data.message) || <?php echo json_encode(__('zatca.send_failed'), 15, 512) ?>);
                        $btn.prop('disabled', false).css('opacity', '1');
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : <?php echo json_encode(__('zatca.send_failed'), 15, 512) ?>;
                    alert(msg);
                    $btn.prop('disabled', false).css('opacity', '1');
                }
            });
        });
    });
</script>
</body>
</html><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/invoices/show.blade.php ENDPATH**/ ?>