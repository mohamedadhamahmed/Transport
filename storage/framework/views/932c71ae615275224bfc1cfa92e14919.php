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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                
                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6"><?php echo e(__('taxes.title')); ?></h2>

                <?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <!-- نموذج إضافة ضريبة جديدة -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('settings.taxes')): ?>
                <form action="<?php echo e(route('taxes.store')); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('taxes.tax_name')); ?></label>
                        <input type="text" name="name" placeholder="<?php echo e(__('taxes.tax_name_placeholder')); ?>" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('taxes.rate')); ?></label>
                        <input type="number" step="0.01" name="rate" placeholder="<?php echo e(__('taxes.rate_placeholder')); ?>" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('taxes.priority')); ?></label>
                        <input type="number" name="priority" placeholder="<?php echo e(__('taxes.priority_placeholder')); ?>" value="0" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-4 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            <?php echo e(__('taxes.add_new')); ?>

                        </button>
                    </div>
                </form>
                <?php endif; ?>

                <!-- جدول عرض الضرائب -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4"><?php echo e(__('taxes.table_priority')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('taxes.table_name')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('taxes.table_rate')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('taxes.table_status')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('taxes.table_actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-3 px-4 font-bold text-[#1456E8]"><?php echo e($tax->priority); ?></td>
                                <td class="py-3 px-4 font-medium text-gray-800"><?php echo e($tax->name); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e($tax->rate); ?>%</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?php echo e($tax->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'); ?>">
                                        <?php echo e($tax->is_active ? __('taxes.active') : __('taxes.inactive')); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('settings.taxes')): ?>
                                    <form action="<?php echo e(route('taxes.destroy', $tax->id)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('taxes.confirm_delete')); ?>');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium"><?php echo e(__('taxes.delete')); ?></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-sm"><?php echo e(__('taxes.empty')); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/taxes/index.blade.php ENDPATH**/ ?>