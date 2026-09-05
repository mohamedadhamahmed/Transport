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
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10h18M6 15h4M3 6h18v12H3z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e($type === 'receipt' ? __('vouchers.receipt_title') : __('vouchers.payment_title')); ?></h2>
                </div>
                <a href="<?php echo e(route('vouchers.create', ['type' => $type])); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + <?php echo e($type === 'receipt' ? __('vouchers.new_receipt') : __('vouchers.new_payment')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="flex gap-2">
                <a href="<?php echo e(route('vouchers.index', ['type' => 'receipt'])); ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition <?php echo e($type === 'receipt' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'); ?>">
                    <?php echo e(__('vouchers.receipt_title')); ?>

                </a>
                <a href="<?php echo e(route('vouchers.index', ['type' => 'payment'])); ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition <?php echo e($type === 'payment' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'); ?>">
                    <?php echo e(__('vouchers.payment_title')); ?>

                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                <div class="p-4 border-b border-gray-100">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <input type="hidden" name="type" value="<?php echo e($type); ?>">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('vouchers.date_from')); ?></label>
                            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('vouchers.date_to')); ?></label>
                            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                <?php echo e(__('vouchers.filter')); ?>

                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('vouchers.voucher_no')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('vouchers.voucher_date')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('vouchers.treasury_account')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('vouchers.items_count')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('vouchers.amount')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('vouchers.view')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#<?php echo e($voucher->voucher_number); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($voucher->voucher_date->format('Y-m-d')); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($voucher->treasuryAccount?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($voucher->lines_count); ?></td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]"><?php echo e(number_format($voucher->lines_total ?? 0, 2)); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <a href="<?php echo e(route('vouchers.show', $voucher)); ?>" title="<?php echo e(__('vouchers.view')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('vouchers.edit')): ?>
                                            <a href="<?php echo e(route('vouchers.edit', $voucher)); ?>" title="<?php echo e(__('vouchers.edit_voucher')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('vouchers.no_vouchers_found')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    <?php if($vouchers->hasPages()): ?>
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            <?php if($vouchers->onFirstPage()): ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('vouchers.previous')); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($vouchers->previousPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('vouchers.previous')); ?></a>
                            <?php endif; ?>

                            <?php $__currentLoopData = range(1, $vouchers->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $vouchers->currentPage()): ?>
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]"><?php echo e($page); ?></span>
                                <?php else: ?>
                                    <a href="<?php echo e($vouchers->url($page)); ?>"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e($page); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($vouchers->hasMorePages()): ?>
                                <a href="<?php echo e($vouchers->nextPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('vouchers.next')); ?></a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('vouchers.next')); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/vouchers/index.blade.php ENDPATH**/ ?>