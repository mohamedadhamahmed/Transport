<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $voucher->isReceipt() ? __('vouchers.receipt') : __('vouchers.payment') }} #{{ $voucher->voucher_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @include('partials.print-styles')
</head>
<body>

<div class="doc-wrap" id="print-area">

    <div class="doc-topbar">
        <button onclick="window.print()">
            {{ __('vouchers.print') }}
        </button>
    </div>

    @include('partials.print-header')

    <div class="doc-type-badge">
        <span>{{ $voucher->isReceipt() ? __('vouchers.receipt_title') : __('vouchers.payment_title') }}</span>
        <span class="doc-no">{{ __('vouchers.voucher_no') }}: #{{ $voucher->voucher_number }}</span>
    </div>

    <div class="meta-grid">
        <div>
            <div class="label">{{ __('vouchers.voucher_date') }}</div>
            <div class="value">{{ $voucher->voucher_date->format('Y-m-d') }}</div>
        </div>
        <div>
            <div class="label">{{ __('vouchers.branch') }}</div>
            <div class="value">{{ $voucher->branch?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('vouchers.cost_center') }}</div>
            <div class="value">{{ $voucher->costCenter?->cost_center_ar ?? '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('vouchers.created_by') }}</div>
            <div class="value">{{ $voucher->creator?->name ?? '-' }}</div>
        </div>
    </div>

    <div class="parties-grid">
        <div class="party-card">
            <div class="title">{{ __('vouchers.treasury_account') }}</div>
            <div class="name">{{ $voucher->treasuryAccount?->name ?? '-' }}</div>
        </div>
        <div class="party-card">
            <div class="title">
                {{ $voucher->isReceipt() ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment') }}
            </div>
            <div class="name">{{ $voucher->counterpartAccount?->name ?? '-' }}</div>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">{{ __('vouchers.amount') }}</div>
        <div class="value">{{ number_format($voucher->amount, 2) }}</div>
    </div>

    <div class="amount-words-box">
        {{ __('vouchers.amount_in_words') }}: {{ $amountInWords }}
    </div>

    @if ($voucher->description)
        <div class="note-box">
            <div class="label">{{ __('vouchers.description') }}</div>
            <div>{{ $voucher->description }}</div>
        </div>
    @endif

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line">{{ __('vouchers.signature_receiver_or_payer') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('vouchers.signature_cashier') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('vouchers.signature_manager') }}</div>
        </div>
    </div>

    <div class="doc-footer">
        {{ __('vouchers.print_footer_note', ['date' => now()->format('Y-m-d H:i')]) }}
    </div>

</div>

</body>
</html>
