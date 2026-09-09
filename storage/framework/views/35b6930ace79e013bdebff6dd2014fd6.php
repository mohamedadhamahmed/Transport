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

        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('contracts.create')); ?>"
                   class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    + <?php echo e(__('contracts.add_new')); ?>

                </a>
                <a href="<?php echo e(route('notifications.index')); ?>"
                    class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    🔔 <?php echo e(__('notifications.title')); ?>

                </a>
            </div>
            <h1 class="text-lg font-bold text-[#0F1B4C]"><?php echo e(__('contracts.title')); ?></h1>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg px-4 py-2 text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.employee_name')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.contract_type')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.start_date')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.end_date')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.residency_expiry')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.work_permit_expiry')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('contracts.actions')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($contract->employee->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-gray-600"><?php echo e($contract->contract_type); ?></td>
                            <td class="px-4 py-3 text-gray-600"><?php echo e($contract->start_date->format('Y-m-d')); ?></td>
                            <td class="px-4 py-3"><?php if (isset($component)) { $__componentOriginalf5c4e41b1d27a9267575135abac12a92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5c4e41b1d27a9267575135abac12a92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.date-badge','data' => ['date' => $contract->end_date]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('date-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contract->end_date)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5c4e41b1d27a9267575135abac12a92)): ?>
<?php $attributes = $__attributesOriginalf5c4e41b1d27a9267575135abac12a92; ?>
<?php unset($__attributesOriginalf5c4e41b1d27a9267575135abac12a92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5c4e41b1d27a9267575135abac12a92)): ?>
<?php $component = $__componentOriginalf5c4e41b1d27a9267575135abac12a92; ?>
<?php unset($__componentOriginalf5c4e41b1d27a9267575135abac12a92); ?>
<?php endif; ?></td>
                            <td class="px-4 py-3"><?php if (isset($component)) { $__componentOriginalf5c4e41b1d27a9267575135abac12a92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5c4e41b1d27a9267575135abac12a92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.date-badge','data' => ['date' => $contract->residency_expiry]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('date-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contract->residency_expiry)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5c4e41b1d27a9267575135abac12a92)): ?>
<?php $attributes = $__attributesOriginalf5c4e41b1d27a9267575135abac12a92; ?>
<?php unset($__attributesOriginalf5c4e41b1d27a9267575135abac12a92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5c4e41b1d27a9267575135abac12a92)): ?>
<?php $component = $__componentOriginalf5c4e41b1d27a9267575135abac12a92; ?>
<?php unset($__componentOriginalf5c4e41b1d27a9267575135abac12a92); ?>
<?php endif; ?></td>
                            <td class="px-4 py-3"><?php if (isset($component)) { $__componentOriginalf5c4e41b1d27a9267575135abac12a92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5c4e41b1d27a9267575135abac12a92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.date-badge','data' => ['date' => $contract->work_permit_expiry]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('date-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contract->work_permit_expiry)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5c4e41b1d27a9267575135abac12a92)): ?>
<?php $attributes = $__attributesOriginalf5c4e41b1d27a9267575135abac12a92; ?>
<?php unset($__attributesOriginalf5c4e41b1d27a9267575135abac12a92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5c4e41b1d27a9267575135abac12a92)): ?>
<?php $component = $__componentOriginalf5c4e41b1d27a9267575135abac12a92; ?>
<?php unset($__componentOriginalf5c4e41b1d27a9267575135abac12a92); ?>
<?php endif; ?></td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="<?php echo e(route('contracts.edit', $contract)); ?>"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500 hover:bg-blue-600 text-white">✎</a>
                                <form action="<?php echo e(route('contracts.destroy', $contract)); ?>" method="POST"
                                      onsubmit="return confirm('<?php echo e(__('contracts.confirm_delete')); ?>')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-500 hover:bg-red-600 text-white">🗑</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400"><?php echo e(__('contracts.empty')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4"><?php echo e($contracts->links()); ?></div>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\factory\resources\views/contracts/index.blade.php ENDPATH**/ ?>