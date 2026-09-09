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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- رأس الصفحة والعنوان -->
            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('reports.stock_adjustments_title', [], 'تقرير تعديلات المخزون')); ?></h2>
                </div>
                <a href="<?php echo e(route('reports.products.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('reports.products.title', [], 'المنتجات والمخزون')); ?>

                </a>
            </div>

            <!-- الفلتر الخاص بالمنتج إن وجد -->
            <?php if($productFilter ?? null): ?>
                <div class="dc-print-hide rounded-xl border border-[#1456E8]/20 bg-[#1456E8]/5 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sm text-[#0F1B4C]">
                        <?php echo e(__('reports.filtered_by_product', ['name' => $productFilter->name, 'code' => $productFilter->code ?? '-'])); ?>

                    </span>
                    <a href="<?php echo e(route('reports.stock_adjustments', request()->except('product_id'))); ?>"
                       class="text-xs font-medium text-[#1456E8] hover:underline">
                        <?php echo e(__('reports.clear_product_filter', [], 'إلغاء فلتر المنتج')); ?>

                    </a>
                </div>
            <?php endif; ?>

            <!-- عنوان للطباعة فقط -->
            <h2 class="dc-print-only text-xl font-bold text-center">
                <?php echo e(__('reports.stock_adjustments_title', [], 'تقرير تعديلات المخزون')); ?> 
                <?php if(isset($dateFrom) && isset($dateTo)): ?>
                    (<?php echo e($dateFrom); ?> → <?php echo e($dateTo); ?>)
                <?php endif; ?>
            </h2>

            <!-- حاوية الجدول -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                <!-- الفلاتر والبحث (إن رغبت في دمجها عبر ملف الفلاتر الجزئي) -->
                <?php echo $__env->make('reports._filters', ['hasDateRange' => true, 'searchPlaceholder' => __('reports.search_product_or_reason', [], 'ابحث باسم المنتج أو السبب...')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.product_code', [], 'كود المنتج')); ?></th>
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.product_name', [], 'اسم المنتج')); ?></th>
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.user', [], 'المستخدم')); ?></th>
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.date', [], 'التاريخ والوقت')); ?></th>
                                <th class="text-start px-4 py-3"><?php echo e(__('reports.reason', [], 'السبب')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.old_quantity', [], 'الكمية القديمة')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.new_quantity', [], 'الكمية الجديدة')); ?></th>
                                <th class="text-end px-4 py-3"><?php echo e(__('reports.difference', [], 'الفارق')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C] font-medium"><?php echo e(optional($adj->product)->code ?? '-'); ?></td>
                                    <td class="px-4 py-2.5 text-gray-800"><?php echo e(optional($adj->product)->name ?? '-'); ?></td>
                                    <td class="px-4 py-2.5 text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo e(optional($adj->user)->name ?? '---'); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs"><?php echo e($adj->created_at->format('Y-m-d H:i')); ?></td>
                                    <td class="px-4 py-2.5 text-gray-600 max-w-xs truncate" title="<?php echo e($adj->reason); ?>">
                                        <?php echo e($adj->reason ?? '-'); ?>

                                    </td>
                                    <td class="px-4 py-2.5 text-end text-gray-600"><?php echo e(number_format($adj->old_quantity, 2)); ?></td>
                                    <td class="px-4 py-2.5 text-end font-semibold text-[#0F1B4C]"><?php echo e(number_format($adj->new_quantity, 2)); ?></td>
                                    <td class="px-4 py-2.5 text-end font-bold">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo e($adj->difference >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'); ?>">
                                            <?php echo e($adj->difference > 0 ? '+' . number_format($adj->difference, 2) : number_format($adj->difference, 2)); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('reports.no_adjustments_found', [], 'لا توجد سجلات تعديل مخزون مطابقة')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        
                        <?php if($adjustments->hasPages() || $adjustments->isNotEmpty()): ?>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="5"><?php echo e(__('reports.total_records', [], 'إجمالي السجلات')); ?></td>
                                    <td class="px-4 py-3 text-end" colspan="3"><?php echo e($adjustments->total() ?? count($adjustments)); ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- روابط التصفح (Pagination) إن وجدت -->
                <?php if(method_exists($adjustments, 'links')): ?>
                    <div class="px-4 py-3 border-t border-gray-100">
                        <?php echo e($adjustments->links()); ?>

                    </div>
                <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\factory\resources\views/reports/products/stock_adjustments.blade.php ENDPATH**/ ?>