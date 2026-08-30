<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('deliverynote.delivery_invoice') ?? 'معاينة سند تسليم' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
      :root {
        --brand-blue: #1456E8;
        --brand-purple: #6B2FD6;
        --ink: #1F2937;
        --muted: #6B7280;
        --border: #E5E7EB;
        --success: #16A34A;
        --danger: #DC2626;
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
        gap: 10px;
        padding: 14px 0;
      }
      .invoice-topbar button, .invoice-topbar a {
        background: linear-gradient(90deg, var(--brand-blue), var(--brand-purple));
        color: #fff;
        border: none;
        padding: 10px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .invoice-topbar a.secondary {
        background: #fff;
        color: var(--brand-blue);
        border: 2px solid var(--brand-blue);
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

      .status-pill {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        margin-right: 8px;
      }
      .status-confirmed { background: #DCFCE7; color: var(--success); }
      .status-returned { background: #FEE2E2; color: var(--danger); }
      .status-partial { background: #FEF3C7; color: #B45309; }

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

<div class="main-content-body-invoice" id="print">
  <div class="invoice-wrap">

    <div class="invoice-topbar" id="print_Button">
      <button onclick="printDiv()">
          {{ __('deliverynote.print') ?? 'طباعة' }}
          <i class="mdi mdi-printer ml-1"></i>
      </button>
      <a href="{{ route('deliverynote.history') }}" class="secondary">
          {{ __('deliverynote.back') ?? 'رجوع' }}
      </a>
      <a href="{{ route('deliverynote.return.create', $invoice->id) }}" class="secondary">
          {{ __('deliverynote.delivery_return') ?? 'مرتجع' }}
      </a>
    </div>

    {{-- الهيدر: بيانات الشركة عربي / شعار / بيانات الشركة إنجليزي --}}
    <div class="invoice-header">
      <div class="company-block">
        <div class="name">{{ Namear ?? '' }}</div>
        <p>{{ describtionar ?? '' }}</p>
        <p>{{ STar ?? '' }}</p>
        <p>{{ Taxar ?? '' }}</p>
      </div>

      <div>
        <?php $logo = defined('camplogo') ? camplogo : null; ?>
        @if($logo)
          <img src="{{ asset('assets/img/brand').'/'.$logo }}" alt="logo">
        @endif
      </div>

      <div class="company-block">
        <div class="name">{{ Nameen ?? '' }}</div>
        <p>{{ describtionen ?? '' }}</p>
        <p>{{ STen ?? '' }}</p>
        <p>{{ Taxen ?? '' }}</p>
      </div>
    </div>

    {{-- شارة نوع السند والحالة --}}
    <div class="invoice-type-badge">
      <span>{{ __('deliverynote.delivery_invoice') ?? 'Delivery Note - سند تسليم' }}</span>
      <div style="margin-top:10px;">
        @if($invoice->status == 0)
          <span class="status-pill status-confirmed">{{ __('deliverynote.confirmed') ?? 'مؤكدة' }}</span>
        @elseif($invoice->status == 1)
          <span class="status-pill status-returned">{{ __('deliverynote.returned') ?? 'مرتجعة بالكامل' }}</span>
        @else
          <span class="status-pill status-partial">{{ __('deliverynote.partially_returned') ?? 'مرتجعة جزئيًا' }}</span>
        @endif
      </div>
    </div>

    {{-- بيانات الفاتورة --}}
    <?php
        $payLabels = [
            'Cash' => __('report.cash') ?? 'نقدي',
            'Bank_transfer' => __('home.Bank_transfer') ?? 'تحويل بنكي',
            'Shabka' => __('report.shabka') ?? 'شبكة',
        ];
        $payLabel = $payLabels[$invoice->Pay] ?? $invoice->Pay;
    ?>
    <div class="meta-grid">
      <div>
        <div class="label">طريقة الدفع - PAYMENT METHOD</div>
        <div class="value">{{ $payLabel }}</div>
      </div>
      <div>
        <div class="label">تاريخ السند - DATE</div>
        <div class="value">{{ $invoice->created_at->format('Y-m-d H:i') }}</div>
      </div>
      <div>
        <div class="label">رقم السند - DOCUMENT No.</div>
        <div class="value">{{ $invoice->id }}</div>
      </div>
      <div>
        <div class="label">الموظف - EMPLOYEE</div>
        <div class="value">{{ $invoice->user->name ?? '-' }}</div>
      </div>
    </div>

    {{-- بيانات العميل --}}
    <div class="parties-grid" style="grid-template-columns: 1fr;">
      <div class="party-card">
        <div class="title">اسم العميل - CLIENT NAME</div>
        <div class="row"><span class="k">الاسم</span><span class="v">{{ $invoice->customer->name ?? '-' }}</span></div>
        <div class="row"><span class="k">رقم الجوال</span><span class="v">{{ $invoice->customer->phone ?? '-' }}</span></div>
        <div class="row"><span class="k">ملاحظات</span><span class="v">{{ $invoice->note ?? '-' }}</span></div>
      </div>
    </div>

    {{-- جدول الأصناف --}}
    <div class="items-table-wrap">
      <table class="items-table">
        <thead>
          <tr>
            <th>NO<br>رقم</th>
            <th>ITEM NAME<br>اسم الصنف</th>
            <th>QUANTITY<br>الكمية</th>
            <th>UNIT PRICE<br>سعر الوحدة</th>
            <th>DISCOUNT<br>الخصم</th>
            <th>RETURNED<br>مرتجع</th>
            <th>TOTAL<br>الإجمالي</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->product->name ?? 'منتج محذوف' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->Unit_Price, 2) }}</td>
            <td>{{ number_format($item->Discount_Value, 2) }}</td>
            <td>{{ $item->quantityreturn }}</td>
            <td>{{ number_format(($item->quantity * $item->Unit_Price) - $item->Discount_Value, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- الإجماليات (بدون ضريبة) --}}
    <?php
        $itemsSubtotal = $items->sum(fn($i) => $i->quantity * $i->Unit_Price);
        $itemsDiscount = $items->sum('Discount_Value');
        $invoiceDiscount = $invoice->discountOnInvoice ?? 0;
        $netTotal = $itemsSubtotal - $itemsDiscount - $invoiceDiscount;
    ?>
    <div class="bottom-grid" style="grid-template-columns: 1fr;">
      <div class="totals-box">
        <div class="row">
          <span>الإجمالي - SUB TOTAL</span>
          <span>{{ number_format($itemsSubtotal, 2) }}</span>
        </div>
        <div class="row">
          <span>خصم الأصناف - ITEMS DISCOUNT</span>
          <span>{{ number_format($itemsDiscount, 2) }}</span>
        </div>
        <div class="row">
          <span>خصم الفاتورة - INVOICE DISCOUNT</span>
          <span>{{ number_format($invoiceDiscount, 2) }}</span>
        </div>
        <div class="row grand">
          <span>الإجمالي الصافي - NET TOTAL</span>
          <span>{{ number_format($netTotal, 2) }} {{ __('home.SAR') ?? 'SAR' }}</span>
        </div>
      </div>
    </div>

    <div class="invoice-footer">
      {{ config('app.name') }} — {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}
    </div>

  </div>
</div>

<script type="text/javascript">
    function printDiv() {
        window.print();
    }
</script>
</body>
</html>
