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
        <div class="max-w-[1280px] mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-6.7L3 9"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('invoices.previous_returns')); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(__('invoices.sales_return_subtitle')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('invoices.returns.create')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    <?php echo e(__('invoices.sales_return')); ?>

                </a>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" action="<?php echo e(route('invoices.returns.index')); ?>" class="flex gap-2 max-w-md">
                    <div class="relative flex-1">
                        <input type="text" name="q" value="<?php echo e($q); ?>"
                               placeholder="<?php echo e(__('invoices.search_invoice_placeholder')); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <?php echo e(__('invoices.search')); ?>

                    </button>
                    <?php if($q !== ''): ?>
                        <a href="<?php echo e(route('invoices.returns.index')); ?>"
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition whitespace-nowrap">
                            <?php echo e(__('invoices.clear')); ?>

                        </a>
                    <?php endif; ?>
                </form>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.return_reference')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.invoice_number')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.customer')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.branch')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.date')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.items_count')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.net_refund_total')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.print')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 font-medium text-gray-800 whitespace-nowrap"><?php echo e($group->reference_value); ?></td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">
                                        <?php echo e($group->invoice_number ? '#' . $group->invoice_number : '-'); ?>

                                    </td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap"><?php echo e($group->customer_name); ?></td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap"><?php echo e($group->branch_name); ?></td>
                                    <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                                        <?php echo e(optional($group->created_at)->format('Y-m-d H:i')); ?>

                                    </td>
                                    <td class="px-3 py-2 text-gray-500"><?php echo e($group->items_count); ?></td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C] whitespace-nowrap">
                                        <?php echo e(number_format($group->net_total, 2)); ?>

                                    </td>
                                    <td class="px-3 py-2">
                               <a href="<?php echo e(route('invoices.returns.print', $group->reference_value)); ?>"
   target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
    </svg>
    <?php echo e(__('invoices.print')); ?>

</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-400">
                                        <?php echo e(__('invoices.no_previous_returns')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <?php echo e($returns->links()); ?>

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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/invoices/returns/index.blade.php ENDPATH**/ ?>