<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($voucher->isReceipt() ? __('vouchers.receipt') : __('vouchers.payment')); ?> #<?php echo e($voucher->voucher_number); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php echo $__env->make('partials.print-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>

<div class="doc-wrap" id="print-area">

    <div class="doc-topbar">
        <button onclick="window.print()">
            <?php echo e(__('vouchers.print')); ?>

        </button>
    </div>

    <?php echo $__env->make('partials.print-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="doc-type-badge">
        <span><?php echo e($voucher->isReceipt() ? __('vouchers.receipt_title') : __('vouchers.payment_title')); ?></span>
        <span class="doc-no"><?php echo e(__('vouchers.voucher_no')); ?>: #<?php echo e($voucher->voucher_number); ?></span>
    </div>

    <div class="meta-grid">
        <div>
            <div class="label"><?php echo e(__('vouchers.voucher_date')); ?></div>
            <div class="value"><?php echo e($voucher->voucher_date->format('Y-m-d')); ?></div>
        </div>
        <div>
            <div class="label"><?php echo e(__('vouchers.branch')); ?></div>
            <div class="value"><?php echo e($voucher->branch?->name ?? '-'); ?></div>
        </div>
        <div>
            <div class="label"><?php echo e(__('vouchers.cost_center')); ?></div>
            <div class="value"><?php echo e($voucher->costCenter?->cost_center_ar ?? '-'); ?></div>
        </div>
        <div>
            <div class="label"><?php echo e(__('vouchers.created_by')); ?></div>
            <div class="value"><?php echo e($voucher->creator?->name ?? '-'); ?></div>
        </div>
    </div>

    <div class="parties-grid">
        <div class="party-card">
            <div class="title"><?php echo e(__('vouchers.treasury_account')); ?></div>
            <div class="name"><?php echo e($voucher->treasuryAccount?->name ?? '-'); ?></div>
        </div>
        <div class="party-card">
            <div class="title">
                <?php echo e($voucher->isReceipt() ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment')); ?>

            </div>
            <div class="name"><?php echo e($voucher->counterpartAccount?->name ?? '-'); ?></div>
        </div>
    </div>

    <div class="amount-box">
        <div class="label"><?php echo e(__('vouchers.amount')); ?></div>
        <div class="value"><?php echo e(number_format($voucher->amount, 2)); ?></div>
    </div>

    <div class="amount-words-box">
        <?php echo e(__('vouchers.amount_in_words')); ?>: <?php echo e($amountInWords); ?>

    </div>

    <?php if($voucher->description): ?>
        <div class="note-box">
            <div class="label"><?php echo e(__('vouchers.description')); ?></div>
            <div><?php echo e($voucher->description); ?></div>
        </div>
    <?php endif; ?>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line"><?php echo e(__('vouchers.signature_receiver_or_payer')); ?></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"><?php echo e(__('vouchers.signature_cashier')); ?></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"><?php echo e(__('vouchers.signature_manager')); ?></div>
        </div>
    </div>

    <div class="doc-footer">
        <?php echo e(__('vouchers.print_footer_note', ['date' => now()->format('Y-m-d H:i')])); ?>

    </div>

</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/vouchers/print.blade.php ENDPATH**/ ?>