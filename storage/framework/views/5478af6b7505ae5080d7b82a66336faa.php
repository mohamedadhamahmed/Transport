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
                    <h2 class="text-xl font-bold text-[#0F1B4C]"><?php echo e(__('manufacturing.orders_title')); ?></h2>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manufacturing.orders')): ?>
                    <a href="<?php echo e(route('manufacturing.orders.create')); ?>" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-5 rounded-lg font-medium hover:opacity-90 transition shadow-sm text-sm">
                        <?php echo e(__('manufacturing.order_add')); ?>

                    </a>
                    <?php endif; ?>
                </div>

                <?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm font-medium"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <form method="GET" class="mb-4 flex gap-3">
                    <select name="status_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">كل الحالات</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status->id); ?>" <?php if(request('status_id') == $status->id): echo 'selected'; endif; ?>><?php echo e($status->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.code')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.name')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.bom_product')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.order_quantity')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.total_cost')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.status')); ?></th>
                                <th class="py-3 px-4"><?php echo e(__('manufacturing.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-3 px-4 text-gray-500 text-sm">#<?php echo e($order->code); ?></td>
                                <td class="py-3 px-4 font-medium text-gray-800"><?php echo e($order->name); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e($order->product->name ?? '-'); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e($order->quantity); ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo e(number_format($order->total_cost, 2)); ?></td>
                                <td class="py-3 px-4">
                                    <?php if($order->status): ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:<?php echo e($order->status->color); ?>22; color:<?php echo e($order->status->color); ?>">
                                            <?php echo e($order->status->name); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if($order->completed_at): ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">مكتمل</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="<?php echo e(route('manufacturing.orders.edit', $order)); ?>" class="text-[#1456E8] hover:underline text-sm font-medium"><?php echo e(__('manufacturing.edit')); ?></a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="py-6 text-center text-gray-400 text-sm"><?php echo e(__('manufacturing.empty')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4"><?php echo e($orders->links()); ?></div>

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
<?php /**PATH C:\xampp\htdocs\factory\resources\views/manufacturing/orders/index.blade.php ENDPATH**/ ?>