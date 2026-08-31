<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 3v18M3 9h18M3 15h18"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('accounts.statement_of')); ?><?php echo e($account->name); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(__('accounts.account_number')); ?>: <?php echo e($account->account_number ?? '-'); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('accounts.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('accounts.back_to_list')); ?>

                </a>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('accounts.date_from')); ?></label>
                        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('accounts.date_to')); ?></label>
                        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('accounts.operation_type')); ?></label>
                        <select name="operation_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value=""><?php echo e(__('accounts.all_operation_types')); ?></option>
                            <?php $__currentLoopData = \App\Support\OperationType::LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(request('operation_type') == $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                            <?php echo e(__('accounts.filter')); ?>

                        </button>
                        <?php if(request()->hasAny(['date_from', 'date_to', 'operation_type'])): ?>
                            <a href="<?php echo e(route('accounts.statement', $account)); ?>" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                <?php echo e(__('accounts.cancel')); ?>

                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                    <div class="text-xs text-gray-500 mb-1"><?php echo e(__('accounts.opening_balance_label')); ?></div>
                    <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($openingBalance, 2)); ?></div>
                </div>
                <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                    <div class="text-xs text-gray-500 mb-1"><?php echo e(__('accounts.total_debtor')); ?> / <?php echo e(__('accounts.total_creditor')); ?></div>
                    <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($transactions->sum('debtor'), 2)); ?> / <?php echo e(number_format($transactions->sum('creditor'), 2)); ?></div>
                </div>
                <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                    <div class="text-xs text-white/50 mb-1"><?php echo e(__('accounts.closing_balance_label')); ?></div>
                    <div class="font-bold text-lg"><?php echo e(number_format($transactions->last()->running_balance ?? $openingBalance, 2)); ?></div>
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.date')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.operation_type')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.reference')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.description')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.debtor')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.creditor')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.running_balance')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-500 text-xs"><?php echo e($t->created_at?->format('Y-m-d H:i')); ?></td>
                                    <td class="px-3 py-2 text-gray-600"><?php echo e(\App\Support\OperationType::label($t->operation_type) ?? '-'); ?></td>
                                    <td class="px-3 py-2 text-gray-500 text-xs"><?php echo e($t->invoice_number ?? '-'); ?></td>
                                    <td class="px-3 py-2 text-gray-600"><?php echo e($t->note ?? '-'); ?></td>
                                    <td class="px-3 py-2 text-emerald-700"><?php echo e($t->debtor > 0 ? number_format($t->debtor, 2) : '-'); ?></td>
                                    <td class="px-3 py-2 text-red-600"><?php echo e($t->creditor > 0 ? number_format($t->creditor, 2) : '-'); ?></td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]"><?php echo e(number_format($t->running_balance, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('accounts.no_transactions_found')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/accounts/statement.blade.php ENDPATH**/ ?>