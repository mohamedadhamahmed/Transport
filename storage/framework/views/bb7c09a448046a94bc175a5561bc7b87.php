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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('reports.sales.by_employee')); ?></h2>
                </div>
                <a href="<?php echo e(route('reports.sales.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('reports.sales.title')); ?>

                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center"><?php echo e(__('reports.sales.by_employee')); ?> (<?php echo e($dateFrom); ?> → <?php echo e($dateTo); ?>)</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                <?php echo $__env->make('reports._filters', [
                    'hasDateRange' => true,
                    'hasEntitySelect' => true,
                    'entityParam' => 'user_id',
                    'entityId' => $userId,
                    'entityLabel' => __('reports.employee'),
                    'entityAllLabel' => __('reports.all_employees'),
                    'entityOptions' => $users->pluck('name', 'id'),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.employee')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.invoices_count')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.quantity')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.net_total')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]"><?php echo e($row->user_name); ?></td>
                                    <td class="px-4 py-2.5 text-end text-gray-600"><?php echo e((int) $row->invoices_count); ?></td>
                                    <td class="px-4 py-2.5 text-end text-gray-600"><?php echo e(number_format((float) $row->total_quantity, 2)); ?></td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]"><?php echo e(number_format((float) $row->net_total, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400"><?php echo e(__('reports.no_invoices_found')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if($rows->isNotEmpty()): ?>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3"><?php echo e(__('reports.total')); ?></td>
                                    <td class="px-4 py-3 text-end"><?php echo e($totalInvoices); ?></td>
                                    <td class="px-4 py-3 text-end"><?php echo e(number_format($totalQuantity, 2)); ?></td>
                                    <td class="px-4 py-3 text-end"><?php echo e(number_format($totalNet, 2)); ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/reports/sales/by-employee.blade.php ENDPATH**/ ?>