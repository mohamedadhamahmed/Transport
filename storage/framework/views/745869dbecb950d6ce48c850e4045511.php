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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6"><?php echo e(__('manufacturing.statuses_title')); ?></h2>

                <?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm font-medium"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manufacturing.statuses')): ?>
                <form action="<?php echo e(route('manufacturing.statuses.store')); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.status_name')); ?></label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.status_color')); ?></label>
                        <input type="color" name="color" value="#1456E8" class="w-full h-10 rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.status_type')); ?></label>
                        <select name="type" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="open">مفتوحة</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="closed">مغلقة</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-4 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            <?php echo e(__('manufacturing.add_new')); ?>

                        </button>
                    </div>
                </form>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.status_name')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.status_type')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full inline-block" style="background:<?php echo e($status->color); ?>"></span>
                                        <?php echo e($status->name); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 text-sm"><?php echo e($status->type); ?></td>
                                <td class="py-3 px-4">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manufacturing.statuses')): ?>
                                    <?php if (! ($status->is_default)): ?>
                                    <form action="<?php echo e(route('manufacturing.statuses.destroy', $status->id)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('manufacturing.confirm_delete')); ?>');" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium"><?php echo e(__('manufacturing.delete')); ?></button>
                                    </form>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3" class="py-6 text-center text-gray-400 text-sm"><?php echo e(__('manufacturing.empty')); ?></td></tr>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\factory\resources\views/manufacturing/statuses/index.blade.php ENDPATH**/ ?>