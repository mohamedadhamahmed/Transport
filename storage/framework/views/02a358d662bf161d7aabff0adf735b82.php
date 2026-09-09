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
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#0F1B4C]"><?php echo e(__('manufacturing.bom_title')); ?></h2>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manufacturing.bom')): ?>
                    <a href="<?php echo e(route('manufacturing.bom.create')); ?>" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-5 rounded-lg font-medium hover:opacity-90 transition shadow-sm text-sm">
                        <?php echo e(__('manufacturing.bom_add')); ?>

                    </a>
                    <?php endif; ?>
                </div>

                <?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.code')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.name')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.bom_product')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.bom_production_quantity')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.bom_total_cost')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.status')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.bom_is_default')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $boms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-3 px-4 text-gray-500 text-sm">#<?php echo e($bom->code); ?></td>
                                <td class="py-3 px-4 font-medium text-gray-800"><?php echo e($bom->name); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e($bom->product->name ?? '-'); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e($bom->production_quantity); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e(number_format($bom->total_cost, 2)); ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?php echo e($bom->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'); ?>">
                                        <?php echo e($bom->status === 'active' ? __('manufacturing.active') : __('manufacturing.inactive')); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-500 text-sm"><?php echo e($bom->is_default ? '✓' : '-'); ?></td>
                                <td class="py-3 px-4 space-x-2 space-x-reverse">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manufacturing.bom')): ?>
                                    <a href="<?php echo e(route('manufacturing.bom.edit', $bom)); ?>" class="text-[#1456E8] hover:underline text-sm font-medium"><?php echo e(__('manufacturing.edit')); ?></a>
                                    <form action="<?php echo e(route('manufacturing.bom.destroy', $bom)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('manufacturing.confirm_delete')); ?>');" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium"><?php echo e(__('manufacturing.delete')); ?></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="py-6 text-center text-gray-400 text-sm"><?php echo e(__('manufacturing.empty')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4"><?php echo e($boms->links()); ?></div>

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
<?php /**PATH C:\xampp\htdocs\factory\resources\views/manufacturing/bom/index.blade.php ENDPATH**/ ?>