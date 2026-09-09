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
                            <path d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('reports.products.stock')); ?></h2>
                </div>
                <a href="<?php echo e(route('reports.products.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('reports.products.title')); ?>

                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center"><?php echo e(__('reports.products.stock')); ?> - <?php echo e(__('reports.as_of')); ?> <?php echo e(now()->format('Y-m-d')); ?></h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                <?php echo $__env->make('reports._filters', ['searchPlaceholder' => __('reports.product')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.product_code')); ?></th>
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.product')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.stock_quantity')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.unit_cost')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.stock_value')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.sale_price')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs"><?php echo e($product->code ?? '-'); ?></td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">
                                        <?php echo e($product->name); ?>

                                        <?php if($product->is_low_stock): ?>
                                            <span class="ms-1 text-[10px] font-medium px-1.5 py-0.5 rounded-full dc-bg-red-soft dc-text-red-strong"><?php echo e(__('reports.low_stock_badge')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-600"><?php echo e(number_format((float) $product->stock_quantity, 2)); ?> <?php echo e($product->unit); ?></td>
                                    <td class="px-4 py-2.5 text-end text-gray-600"><?php echo e(number_format((float) ($product->average_cost ?: $product->purchase_price), 2)); ?></td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]"><?php echo e(number_format($product->stock_value, 2)); ?></td>
                                    <td class="px-4 py-2.5 text-end text-gray-600"><?php echo e(number_format((float) $product->sale_price, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400"><?php echo e(__('reports.no_products_found')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if($products->isNotEmpty()): ?>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="2"><?php echo e(__('reports.total')); ?></td>
                                    <td class="px-4 py-3 text-end"><?php echo e(number_format($totalQuantity, 2)); ?></td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3 text-end"><?php echo e(number_format($totalValue, 2)); ?></td>
                                    <td class="px-4 py-3"></td>
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
<?php /**PATH C:\xampp\htdocs\factory\resources\views/reports/products/stock.blade.php ENDPATH**/ ?>