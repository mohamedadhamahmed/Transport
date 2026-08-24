<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('invoices.credit_note_preview_title')); ?></title>
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
              <?php echo e(__('invoices.print')); ?>

              <i class="mdi mdi-printer ml-1"></i>
          </button>
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
          <?php if(strlen((string) $invoice->customer->tax_no) == 15): ?>
            <span>Tax Credit Note - إشعار دائن ضريبي</span>
          <?php else: ?>
            <span>Simplified Tax Credit Note - إشعار دائن ضريبي مبسط</span>
          <?php endif; ?>

          <input type="text" name="show_reference_value" id="show_reference_value"
                 value="<?php echo e($referenceValue); ?>" hidden>
        </div>

        
        <div class="meta-grid" dir="rtl">
          <div>
            <div class="label">رقم الإشعار NOTICE NUMBER</div>
            <div class="value"><?php echo e($referenceValue); ?></div>
          </div>
          <div>
            <div class="label">تاريخ الإشعار NOTICE DATE</div>
            <div class="value"><?php echo e(optional($returns->first()->created_at)->format('Y-m-d H:i')); ?></div>
          </div>
          <div>
            <div class="label">الفاتورة الأصلية ORIGINAL INVOICE</div>
            <div class="value">#<?php echo e($invoice->invoice_number); ?></div>
          </div>
          <div>
            <div class="label">طريقة الاسترداد REFUND METHOD</div>
            <div class="value"><?php echo e($refundMethodLabel); ?></div>
          </div>
          <div>
            <div class="label">اسم الفرع BRANCH NAME</div>
            <div class="value"><?php echo e(optional($invoice->branch)->name); ?></div>
          </div>
        </div>

        
        <div class="parties-grid" dir="rtl">
          <div class="party-card">
            <div class="title">اسم العميل - CLIENT NAME</div>
            <div class="row"><span class="k">الاسم</span><span class="v"><?php echo e($invoice->customer->name); ?></span></div>
            <div class="row">
              <span class="k">الرقم الضريبي</span>
              <span class="v"><?php echo e($invoice->customer->tax_no == 0 ? '-' : $invoice->customer->tax_no); ?></span>
            </div>
            <div class="row">
              <span class="k">السجل التجاري</span>
              <span class="v"><?php echo e($invoice->customer->CRN == null ? '-' : $invoice->customer->CRN); ?></span>
            </div>
            <div class="row">
              <?php if($invoice->customer->address == '-'): ?>
                <span class="k">رقم الجوال</span>
                <span class="v"><?php echo e($invoice->customer->id == 1 ? '-' : $invoice->customer->phone); ?></span>
              <?php else: ?>
                <span class="k">المدينة</span>
                <span class="v"><?php echo e($invoice->customer->id == 1 ? '-' : $invoice->customer->address); ?></span>
              <?php endif; ?>
            </div>
            <div class="row">
              <span class="k">المنطقة</span>
              <span class="v"><?php echo e($invoice->customer->id == 1 ? '-' : $invoice->customer->sub_city); ?></span>
            </div>
            <div class="row">
              <span class="k">اسم الشارع</span>
              <span class="v"><?php echo e($invoice->customer->id == 1 ? '-' : $invoice->customer->street_name); ?></span>
            </div>
            <div class="row">
              <span class="k">الرمز البريدي</span>
              <span class="v"><?php echo e($invoice->customer->postcode); ?></span>
            </div>
            <div class="row">
              <span class="k">رقم المبنى</span>
              <span class="v"><?php echo e($invoice->customer->id == 1 ? '-' : $invoice->customer->building_number); ?></span>
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
                <th>QUANTITY<br>الكمية المرتجعة</th>
                <th>PRODUCT PRICE<br>سعر القطعة</th>
                <th>Total<br>الاجمالي</th>
                <th>DISCOUNT<br>الخصم</th>
                <th>Total AFTER DISCOUNT<br>الاجمالي بعد الخصم</th>
              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $lineTotal = $row->unit_price * $row->quantity;
                    $lineDiscount = $row->discount_amount + $row->invoice_discount_amount;
                ?>
                <tr>
                  <td><?php echo e($index + 1); ?></td>
                  <td dir="rtl"><?php echo e(optional($row->product)->code ?? '-'); ?></td>
                  <td><?php echo e(optional($row->product)->name ?? 'منتج محذوف'); ?></td>
                  <td><?php echo e($row->quantity); ?></td>
                  <td><?php echo e(number_format($row->unit_price, 2, '.', '')); ?></td>
                  <td><?php echo e(number_format($lineTotal, 2, '.', '')); ?></td>
                  <td><?php echo e(number_format($lineDiscount, 2, '.', '')); ?></td>
                  <td><?php echo e(number_format($lineTotal - $lineDiscount, 2, '.', '')); ?></td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>

        <div class="notice-box">
          إشعار دائن صادر بخصوص مرتجع من الفاتورة رقم #<?php echo e($invoice->invoice_number); ?>

          بتاريخ <?php echo e(optional($invoice->created_at)->format('Y-m-d')); ?>

        </div>

        
        <div class="bottom-grid" dir="rtl">
          <div>
            <div class="qr-box">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?php echo e(urlencode($qrCodeData)); ?>" alt="QR Code">
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
              <span><?php echo e(number_format($subtotal, 2, '.', '')); ?></span>
            </div>
            <div class="row">
              <span>الخصم - DISCOUNT</span>
              <span><?php echo e(number_format($totalDiscount, 2, '.', '')); ?></span>
            </div>
            <div class="row">
              <span>الاجمالي بعد الخصم - SUB TOTAL AFTER DISCOUNT</span>
              <span><?php echo e(number_format($netBeforeTax, 2, '.', '')); ?></span>
            </div>
            <div class="row">
              <span>ضريبة القيمة المضافة (<?php echo e($taxRate * 100); ?>%)</span>
              <span><?php echo e(number_format($taxTotal, 2, '.', '')); ?></span>
            </div>
            <div class="row grand">
              <span>إجمالي المرتجع - NET REFUND TOTAL</span>
              <span><?php echo e(number_format($netTotal, 2, '.', '')); ?></span>
            </div>
          </div>
        </div>

        <input type="hidden" id="token_search" value="<?php echo e(csrf_token()); ?>">

      </div>
    </div>
  </div>
</div>

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

    $(document).ready(function() {
        var printContents = document.getElementById('print').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        setTimeout(() => { window.print(); }, 500);
        setTimeout(() => { window.close(); }, 10000);
    });
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/invoices/returns/print.blade.php ENDPATH**/ ?>