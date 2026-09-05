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
                            <path d="M9 2h6l1 4h4v2h-2l-1.6 9.6A2 2 0 0 1 14.4 20H9.6a2 2 0 0 1-2-1.4L6 8H4V6h4l1-4Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg"><?php echo e(__('reports.sales.title')); ?></h2>
                        <p class="text-white/60 text-sm"><?php echo e(__('reports.sales.subtitle')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('reports.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('reports.hub_title')); ?>

                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <?php $__currentLoopData = collect([
                    ['route' => 'reports.sales.summary', 'label' => __('reports.sales.summary'), 'desc' => __('reports.sales.summary_desc'), 'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 13h6M9 17h6', 'perm' => 'reports_sales.summary'],
                    ['route' => 'reports.sales.by-customer', 'label' => __('reports.sales.by_customer'), 'desc' => __('reports.sales.by_customer_desc'), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z', 'perm' => 'reports_sales.by_customer'],
                    ['route' => 'reports.sales.by-product', 'label' => __('reports.sales.by_product'), 'desc' => __('reports.sales.by_product_desc'), 'icon' => 'M21 8 12 3 3 8l9 5 9-5ZM3 8v8l9 5m0-13v13m9-13v8l-9 5', 'perm' => 'reports_sales.by_product'],
                    ['route' => 'reports.sales.returns', 'label' => __('reports.sales.returns'), 'desc' => __('reports.sales.returns_desc'), 'icon' => 'M3 12a9 9 0 1 0 3-6.7M3 4v5h5', 'perm' => 'reports_sales.returns'],
                    ['route' => 'reports.sales.by-employee', 'label' => __('reports.sales.by_employee'), 'desc' => __('reports.sales.by_employee_desc'), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'perm' => 'reports_sales.by_employee'],
                ])->filter(fn ($card) => auth()->user()?->hasPermission($card['perm'])); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/reports/sales/index.blade.php ENDPATH**/ ?>