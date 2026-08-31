<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($entry->isOpening() ? __('journal_entries.opening_badge') : __('journal_entries.daily_badge')); ?> #<?php echo e($entry->entry_number); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php echo $__env->make('partials.print-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>

<div class="doc-wrap" id="print-area">

    <div class="doc-topbar">
        <button onclick="window.print()">
            <?php echo e(__('journal_entries.print')); ?>

        </button>
    </div>

    <?php echo $__env->make('partials.print-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="doc-type-badge">
        <span><?php echo e($entry->isOpening() ? __('journal_entries.opening_badge') : __('journal_entries.daily_badge')); ?></span>
        <span class="doc-no"><?php echo e(__('journal_entries.entry_no')); ?>: #<?php echo e($entry->entry_number); ?></span>
    </div>

    <div class="meta-grid">
        <div>
            <div class="label"><?php echo e(__('journal_entries.entry_date')); ?></div>
            <div class="value"><?php echo e($entry->entry_date->format('Y-m-d')); ?></div>
        </div>
        <div>
            <div class="label"><?php echo e(__('journal_entries.branch')); ?></div>
            <div class="value"><?php echo e($entry->branch?->name ?? '-'); ?></div>
        </div>
        <div>
            <div class="label"><?php echo e(__('journal_entries.cost_center')); ?></div>
            <div class="value"><?php echo e($entry->costCenter?->cost_center_ar ?? '-'); ?></div>
        </div>
        <div>
            <div class="label"><?php echo e(__('journal_entries.created_by')); ?></div>
            <div class="value"><?php echo e($entry->creator?->name ?? '-'); ?></div>
        </div>
    </div>

    <div class="items-table-wrap">
        <table class="items-table">
            <thead>
                <tr>
                    <th><?php echo e(__('journal_entries.account')); ?></th>
                    <th><?php echo e(__('journal_entries.note')); ?></th>
                    <th><?php echo e(__('journal_entries.debit')); ?></th>
                    <th><?php echo e(__('journal_entries.credit')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $entry->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($line->account?->name ?? '#' . $line->account_id); ?></td>
                        <td><?php echo e($line->note ?? '-'); ?></td>
                        <td><?php echo e($line->debit > 0 ? number_format($line->debit, 2) : '-'); ?></td>
                        <td><?php echo e($line->credit > 0 ? number_format($line->credit, 2) : '-'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><?php echo e(__('journal_entries.total_debit')); ?> / <?php echo e(__('journal_entries.total_credit')); ?></td>
                    <td><?php echo e(number_format($entry->total_debit, 2)); ?></td>
                    <td><?php echo e(number_format($entry->total_credit, 2)); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if($entry->description): ?>
        <div class="note-box">
            <div class="label"><?php echo e(__('journal_entries.description')); ?></div>
            <div><?php echo e($entry->description); ?></div>
        </div>
    <?php endif; ?>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line"><?php echo e(__('journal_entries.signature_preparer')); ?></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"><?php echo e(__('journal_entries.signature_reviewer')); ?></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"><?php echo e(__('journal_entries.signature_manager')); ?></div>
        </div>
    </div>

    <div class="doc-footer">
        <?php echo e(__('journal_entries.print_footer_note', ['date' => now()->format('Y-m-d H:i')])); ?>

    </div>

</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/journal-entries/print.blade.php ENDPATH**/ ?>