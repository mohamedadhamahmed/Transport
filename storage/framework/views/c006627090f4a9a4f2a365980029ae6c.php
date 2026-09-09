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

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8 12 3 3 8l9 5 9-5ZM3 8v8l9 5m0-13v13m9-13v8l-9 5"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg"><?php echo e(__('reports.products.title')); ?></h2>
                        <p class="text-white/60 text-sm"><?php echo e(__('reports.products.subtitle')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('reports.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('reports.hub_title')); ?>

                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <?php $__currentLoopData = collect([
                    ['route' => 'reports.products.stock', 'label' => __('reports.products.stock'), 'desc' => __('reports.products.stock_desc'), 'icon' => 'M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'perm' => 'reports_products.stock'],
                    ['route' => 'reports.products.low-stock', 'label' => __('reports.products.low_stock'), 'desc' => __('reports.products.low_stock_desc'), 'icon' => 'M12 9v4m0 4h.01M10.3 3.9 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z', 'perm' => 'reports_products.low_stock'],
                    ['route' => 'reports.products.transfers', 'label' => __('reports.products.transfers'), 'desc' => __('reports.products.transfers_desc'), 'icon' => 'M17 3v4a1 1 0 0 1-1 1H4M7 21v-4a1 1 0 0 1 1-1h12M7 7 3 3M20 21l-4-4', 'perm' => 'reports_products.stock_transfers'],
                    
                    // أضفنا كارت تقرير تعديلات المخزون هنا:
                    ['route' => 'reports.stock_adjustments', 'label' => __('reports.stock_adjustments_title', [], 'تعديلات المخزون'), 'desc' => __('reports.stock_adjustments_desc', [], 'سجل كل التعديلات اليدوية على كميات المخزون والفوارق'), 'icon' => 'M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z', 'perm' => 'products.edit'],

                    ['route' => 'reports.products.movement', 'label' => __('reports.products.movement'), 'desc' => __('reports.products.movement_desc'), 'icon' => 'M3 12h4l3 8 4-16 3 8h4', 'perm' => null],
                ])->filter(fn ($card) => $card['perm'] === null
                    ? (auth()->user()?->hasPermission('reports_sales.by_product')
                        || auth()->user()?->hasPermission('reports_purchases.by_product')
                        || auth()->user()?->hasPermission('reports_products.stock_transfers'))
                    : auth()->user()?->hasPermission($card['perm'])); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route($card['route'])); ?>"
                       class="dc-card-link bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex items-start gap-4">
                        <span class="w-10 h-10 shrink-0 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="<?php echo e($card['icon']); ?>"/></svg>
                        </span>
                        <div>
                            <h3 class="dc-card-link-title font-bold text-[#0F1B4C] transition"><?php echo e($card['label']); ?></h3>
                            <p class="text-xs text-gray-400 mt-1"><?php echo e($card['desc']); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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

<?php /**PATH C:\xampp\htdocs\factory\resources\views/reports/products/index.blade.php ENDPATH**/ ?>