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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#0F1B4C]">
                        <?php echo e($order->name); ?>

                        <span class="text-sm font-normal text-gray-400">#<?php echo e($order->code); ?></span>
                    </h2>

                    <?php if($order->status): ?>
                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold" style="background:<?php echo e($order->status->color); ?>22; color:<?php echo e($order->status->color); ?>">
                            <?php echo e($order->status->name); ?>

                        </span>
                    <?php endif; ?>
                </div>

                <?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error') || $errors->any()): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm font-medium">
                        <?php echo e(session('error')); ?>

                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('manufacturing.orders.update', $order)); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.name')); ?></label>
                        <input type="text" name="name" value="<?php echo e($order->name); ?>" required <?php echo e($order->completed_at ? 'disabled' : ''); ?> class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.plan_starts')); ?></label>
                        <input type="date" name="date_start" value="<?php echo e($order->date_start->format('Y-m-d')); ?>" required <?php echo e($order->completed_at ? 'disabled' : ''); ?> class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.plan_ends')); ?></label>
                        <input type="date" name="date_end" value="<?php echo e($order->date_end->format('Y-m-d')); ?>" required <?php echo e($order->completed_at ? 'disabled' : ''); ?> class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.order_workstation')); ?></label>
                        <select name="workstation_id" <?php echo e($order->completed_at ? 'disabled' : ''); ?> class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">—</option>
                            <?php $__currentLoopData = $workstations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ws): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ws->id); ?>" <?php if($ws->id === $order->workstation_id): echo 'selected'; endif; ?>><?php echo e($ws->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.status')); ?></label>
                        <select name="status_id" <?php echo e($order->completed_at ? 'disabled' : ''); ?> class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->id); ?>" <?php if($status->id === $order->status_id): echo 'selected'; endif; ?>><?php echo e($status->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <?php if (! ($order->completed_at)): ?>
                    <div class="md:col-span-4">
                        <button type="submit" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-6 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            <?php echo e(__('manufacturing.save')); ?>

                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-[#0F1B4C] mb-4"><?php echo e(__('manufacturing.items_title')); ?></h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-2 px-3">المادة</th>
                                <th class="py-2 px-3"><?php echo e(__('manufacturing.item_required_quantity')); ?></th>
                                <th class="py-2 px-3"><?php echo e(__('manufacturing.item_consumed_quantity')); ?></th>
                                <th class="py-2 px-3">تكلفة الوحدة</th>
                                <th class="py-2 px-3">الإجمالي</th>
                                <?php if (! ($order->completed_at)): ?>
                                <th class="py-2 px-3"><?php echo e(__('manufacturing.actions')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-2 px-3 font-medium text-gray-800"><?php echo e($item->product->name ?? '-'); ?></td>
                                <td class="py-2 px-3 text-gray-600"><?php echo e($item->required_quantity); ?></td>
                                <td class="py-2 px-3 text-gray-600"><?php echo e($item->consumed_quantity ?: $item->required_quantity); ?></td>
                                <td class="py-2 px-3 text-gray-600"><?php echo e(number_format($item->unit_cost, 2)); ?></td>
                                <td class="py-2 px-3 text-gray-600"><?php echo e(number_format($item->total_cost, 2)); ?></td>
                                <?php if (! ($order->completed_at)): ?>
                                <td class="py-2 px-3">
                                    <form action="<?php echo e(route('manufacturing.orders.items.update', [$order, $item->id])); ?>" method="POST" class="flex gap-2 items-center">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="number" step="0.001" name="consumed_quantity" value="<?php echo e($item->consumed_quantity ?: $item->required_quantity); ?>" class="w-24 rounded-lg border-gray-300 text-sm">
                                        <button type="submit" class="text-[#1456E8] hover:underline text-sm"><?php echo e(__('manufacturing.save')); ?></button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="py-6 text-center text-gray-400 text-sm"><?php echo e(__('manufacturing.empty')); ?> — اربط الأمر بقائمة مواد إنتاج (BOM) عشان تتحسب تلقائيًا</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-[#0F1B4C] mb-4"><?php echo e(__('manufacturing.indirect_costs_title')); ?></h3>

                <?php if (! ($order->completed_at)): ?>
                <form action="<?php echo e(route('manufacturing.orders.indirect-costs.store', $order)); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4 p-4 bg-gray-50 rounded-xl">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="name" placeholder="<?php echo e(__('manufacturing.indirect_cost_name')); ?>" required class="rounded-lg border-gray-300 text-sm">
                    <input type="number" step="0.01" name="amount" placeholder="<?php echo e(__('manufacturing.indirect_cost_amount')); ?>" required class="rounded-lg border-gray-300 text-sm">
                    <input type="text" name="notes" placeholder="ملاحظات" class="rounded-lg border-gray-300 text-sm">
                    <button type="submit" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white rounded-lg text-sm font-medium hover:opacity-90 transition">
                        <?php echo e(__('manufacturing.indirect_cost_add')); ?>

                    </button>
                </form>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-2 px-3"><?php echo e(__('manufacturing.indirect_cost_name')); ?></th>
                                <th class="py-2 px-3"><?php echo e(__('manufacturing.indirect_cost_amount')); ?></th>
                                <th class="py-2 px-3">ملاحظات</th>
                                <?php if (! ($order->completed_at)): ?>
                                <th class="py-2 px-3"><?php echo e(__('manufacturing.actions')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $order->indirectCosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-2 px-3 font-medium text-gray-800"><?php echo e($cost->name); ?></td>
                                <td class="py-2 px-3 text-gray-600"><?php echo e(number_format($cost->amount, 2)); ?></td>
                                <td class="py-2 px-3 text-gray-500 text-sm"><?php echo e($cost->notes); ?></td>
                                <?php if (! ($order->completed_at)): ?>
                                <td class="py-2 px-3">
                                    <form action="<?php echo e(route('manufacturing.orders.indirect-costs.destroy', [$order, $cost->id])); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium"><?php echo e(__('manufacturing.delete')); ?></button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm"><?php echo e(__('manufacturing.empty')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-[#0F1B4C] mb-4"><?php echo e(__('manufacturing.cost_summary')); ?></h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('manufacturing.direct_materials_cost')); ?></div>
                        <div class="text-lg font-bold text-[#0F1B4C]"><?php echo e(number_format($order->direct_materials_cost, 2)); ?></div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('manufacturing.indirect_costs_total')); ?></div>
                        <div class="text-lg font-bold text-[#0F1B4C]"><?php echo e(number_format($order->indirect_costs_total, 2)); ?></div>
                    </div>
                    <div class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] rounded-xl p-4 text-white">
                        <div class="text-xs opacity-90 mb-1"><?php echo e(__('manufacturing.total_cost')); ?></div>
                        <div class="text-lg font-bold"><?php echo e(number_format($order->total_cost, 2)); ?></div>
                    </div>
                </div>

                <?php if($order->completed_at): ?>
                    <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">
                        تم إتمام الأمر بتاريخ <?php echo e($order->completed_at->format('d/m/Y H:i')); ?> — تم سحب المواد الخام وإضافة <?php echo e($order->quantity); ?> من <?php echo e($order->product->name); ?> للمخزون.
                    </div>
                <?php else: ?>
                    <form action="<?php echo e(route('manufacturing.orders.complete', $order)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('manufacturing.confirm_complete')); ?>');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="bg-emerald-600 text-white py-2.5 px-6 rounded-lg font-medium hover:bg-emerald-700 transition shadow-sm">
                            <?php echo e(__('manufacturing.complete_order')); ?>

                        </button>
                    </form>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\factory\resources\views/manufacturing/orders/edit.blade.php ENDPATH**/ ?>