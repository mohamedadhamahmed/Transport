<?php
    $account = $node['account'];
    $children = $node['children'];
    $hasChildren = $children->isNotEmpty();
    $indent = 10 + $depth * 22;
?>

<div class="tree-node" data-name="<?php echo e(\Illuminate\Support\Str::lower($account->name)); ?>" data-number="<?php echo e(\Illuminate\Support\Str::lower((string) $account->account_number)); ?>">
    <div class="tree-row flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-[#1456E8]/5 transition" style="padding-inline-start: <?php echo e($indent); ?>px;">
        <?php if($hasChildren): ?>
            <button type="button" class="toggle-btn w-5 h-5 flex items-center justify-center text-gray-400 hover:text-[#0F1B4C] transition shrink-0" aria-expanded="true">
                <svg class="w-3.5 h-3.5 toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 6 6 6-6 6"/></svg>
            </button>
        <?php else: ?>
            <span class="w-5 h-5 shrink-0"></span>
        <?php endif; ?>

        <span class="text-xs text-gray-400 w-16 shrink-0"><?php echo e($account->account_number ?? '-'); ?></span>
        <span class="font-medium text-gray-800 flex-1 truncate"><?php echo e($account->name); ?></span>

        <?php if (! ($account->active)): ?>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-500 shrink-0"><?php echo e(__('accounts.inactive')); ?></span>
        <?php endif; ?>

        <span class="text-xs text-gray-500 w-24 text-end shrink-0"><?php echo e(number_format($account->debtor_current ?? 0, 2)); ?></span>
        <span class="text-xs text-gray-500 w-24 text-end shrink-0"><?php echo e(number_format($account->creditor_current ?? 0, 2)); ?></span>
        <span class="text-xs font-semibold text-[#0F1B4C] w-24 text-end shrink-0"><?php echo e(number_format($account->current_balance, 2)); ?></span>

        <div class="flex items-center gap-1 shrink-0">
            <a href="<?php echo e(route('accounts.statement', $account)); ?>" title="<?php echo e(__('accounts.statement')); ?>"
               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v18M3 9h18M3 15h18"/></svg>
            </a>
            <a href="<?php echo e(route('accounts.edit', $account)); ?>" title="<?php echo e(__('accounts.edit_account')); ?>"
               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </a>
        </div>
    </div>

    <?php if($hasChildren): ?>
        <div class="children-wrap">
            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('accounts.tree-node', ['node' => $child, 'depth' => $depth + 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/accounts/tree-node.blade.php ENDPATH**/ ?>