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
                            <path d="M9 7h6M9 11h6M9 15h3"/>
                            <path d="M5 4h14a1 1 0 0 1 1 1v15l-3-2-3 2-3-2-3 2-3-2-3 2V5a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('quotations.title')); ?></h2>
                </div>
                <a href="<?php echo e(route('quotations.create')); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + <?php echo e(__('quotations.new_quotation')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="<?php echo e(route('quotations.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('quotations.filter_by_customer')); ?></label>
                            <select name="customer_id" data-ajax-select data-ajax-url="<?php echo e(route('customers.search')); ?>"
                                    data-ajax-placeholder="<?php echo e(__('quotations.all_customers')); ?>"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value=""><?php echo e(__('quotations.all_customers')); ?></option>
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($name): ?>
                                        <option value="<?php echo e($id); ?>" selected><?php echo e($name); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('quotations.filter_by_status')); ?></label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value=""><?php echo e(__('quotations.all_statuses')); ?></option>
                                <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>><?php echo e(__('quotations.status_pending')); ?></option>
                                <option value="approved" <?php if(request('status') === 'approved'): echo 'selected'; endif; ?>><?php echo e(__('quotations.status_approved')); ?></option>
                                <option value="rejected" <?php if(request('status') === 'rejected'): echo 'selected'; endif; ?>><?php echo e(__('quotations.status_rejected')); ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('quotations.filter_by_date')); ?></label>
                            <input type="date" name="date" value="<?php echo e(request('date')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                <?php echo e(__('quotations.filter')); ?>

                            </button>
                            <?php if(request()->hasAny(['customer_id', 'status', 'date'])): ?>
                                <a href="<?php echo e(route('quotations.index')); ?>"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    <?php echo e(__('quotations.cancel')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.quotation_no')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.customer')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.date')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.created_by')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.grand_total')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.status')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#<?php echo e($quotation->id); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($quotation->customer?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($quotation->created_at->format('Y-m-d')); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($quotation->creator?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]"><?php echo e(number_format($quotation->grand_total, 2)); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if($quotation->status === 'approved'): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700"><?php echo e(__('quotations.status_approved')); ?></span>
                                        <?php elseif($quotation->status === 'rejected'): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-50 text-red-700"><?php echo e(__('quotations.status_rejected')); ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700"><?php echo e(__('quotations.status_pending')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="<?php echo e(route('quotations.show', $quotation)); ?>" title="<?php echo e(__('quotations.view')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('quotations.edit')): ?>
                                                <?php if($quotation->isPending()): ?>
                                                    <a href="<?php echo e(route('quotations.edit', $quotation)); ?>" title="<?php echo e(__('quotations.edit')); ?>"
                                                       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                        </svg>
                                                    </a>
                                                <?php else: ?>
                                                    <span title="<?php echo e(__('quotations.already_processed')); ?>"
                                                          class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                        </svg>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <a href="<?php echo e(route('quotations.pdf', $quotation)); ?>" title="<?php echo e(__('quotations.download_pdf')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('quotations.no_quotations_found')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    <?php if($quotations->hasPages()): ?>
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            <?php if($quotations->onFirstPage()): ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('quotations.previous')); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($quotations->previousPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('quotations.previous')); ?></a>
                            <?php endif; ?>

                            <?php $__currentLoopData = range(1, $quotations->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $quotations->currentPage()): ?>
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]"><?php echo e($page); ?></span>
                                <?php else: ?>
                                    <a href="<?php echo e($quotations->url($page)); ?>"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e($page); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($quotations->hasMorePages()): ?>
                                <a href="<?php echo e($quotations->nextPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('quotations.next')); ?></a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('quotations.next')); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('partials.ajax-select-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/quotations/index.blade.php ENDPATH**/ ?>