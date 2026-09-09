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
    <div class="p-6" dir="rtl">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-bold text-[#0F1B4C]"><?php echo e(__('notifications.title')); ?></h1>
            <span class="text-xs text-gray-400"><?php echo e(__('notifications.range_note', ['days' => $days])); ?></span>
        </div>

        <div class="bg-white rounded-xl shadow divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full <?php echo e($alert['days_left'] <= 15 ? 'bg-red-500' : 'bg-amber-500'); ?>"></span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?php echo e($alert['employee']->name ?? '-'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($alert['type']); ?></p>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-medium text-gray-700"><?php echo e($alert['date']->format('Y-m-d')); ?></p>
                        <p class="text-xs <?php echo e($alert['days_left'] <= 15 ? 'text-red-500' : 'text-amber-500'); ?>">
                            <?php echo e($alert['days_left']); ?> <?php echo e(__('notifications.days')); ?>

                        </p>
                    </div>
                    <a href="<?php echo e(route('contracts.edit', $alert['contract'])); ?>" class="text-blue-600 hover:underline text-xs font-medium">
                        <?php echo e(__('contracts.edit')); ?>

                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-5 py-10 text-center text-gray-400 text-sm"><?php echo e(__('notifications.empty')); ?></div>
            <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\factory\resources\views/notifications/index.blade.php ENDPATH**/ ?>