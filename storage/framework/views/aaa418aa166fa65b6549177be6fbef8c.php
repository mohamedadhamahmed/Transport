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
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                            <path d="M14 4v4a1 1 0 0 0 1 1h4" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('delivery.delivery_history')); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(__('delivery.delivery_product')); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delivery.create')): ?>
                    <a href="<?php echo e(route('delivery.create')); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <?php echo e(__('delivery.new_product')); ?>

                    </a>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" action="<?php echo e(route('delivery.history')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('delivery.from')); ?></label>
                        <input type="date" name="start_at" value="<?php echo e(request('start_at', date('Y-m-01'))); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('delivery.to')); ?></label>
                        <input type="date" name="end_at" value="<?php echo e(request('end_at', date('Y-m-d'))); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('delivery.chooseclient')); ?></label>
                        <select name="customer_id" data-ajax-select data-ajax-url="<?php echo e(route('customers.search')); ?>"
                                data-ajax-placeholder="<?php echo e(__('delivery.all')); ?>"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value=""><?php echo e(__('delivery.all')); ?></option>
                            <?php $__currentLoopData = $Customer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($name): ?>
                                    <option value="<?php echo e($id); ?>" selected><?php echo e($name); ?></option>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <?php echo e(__('delivery.search')); ?>

                        </button>
                    </div>
                </form>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.decoumentNo')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.date')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.chooseclient')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.employee')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.total')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.status_active')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('delivery.operations')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-400"><?php echo e($loop->iteration); ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($invoice->id); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($invoice->created_at->format('Y-m-d H:i')); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($invoice->customer->name ?? '-'); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($invoice->user->name ?? '-'); ?></td>
                                <td class="px-4 py-3 font-semibold text-[#0F1B4C]"><?php echo e(number_format($invoice->Price, 2)); ?></td>
                                <td class="px-4 py-3">
                                    <?php if($invoice->status == 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100"><?php echo e(__('delivery.confirmed')); ?></span>
                                    <?php elseif($invoice->status == 1): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100"><?php echo e(__('delivery.returned')); ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100"><?php echo e(__('delivery.partially_returned')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delivery.view')): ?>
                                        <a href="<?php echo e(route('delivery.show', $invoice->id)); ?>" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <?php echo e(__('delivery.view')); ?>

                                        </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delivery.view')): ?>
                                        <a href="<?php echo e(route('delivery.return.create', $invoice->id)); ?>"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#F5811E] bg-[#F5811E]/10 hover:bg-[#F5811E]/20 transition">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 7v6h6" /><path d="M3 13a9 9 0 1 0 3-6.7L3 9" />
                                            </svg>
                                            <?php echo e(__('delivery.delivery_return')); ?>

                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400"><?php echo e(__('delivery.no_data')); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($invoices->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100">
                    <?php echo e($invoices->appends(request()->all())->links()); ?>

                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php echo $__env->make('partials.ajax-select-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/delivery/history.blade.php ENDPATH**/ ?>