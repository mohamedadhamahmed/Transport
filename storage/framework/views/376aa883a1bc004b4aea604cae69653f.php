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
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21a9 9 0 1 1 9-9"/><path d="M9 8v5h5"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('purchase_returns.return_no')); ?> #<?php echo e($purchaseReturn->return_number ?? $purchaseReturn->id); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(optional($purchaseReturn->return_date)->format('Y-m-d') ?? $purchaseReturn->created_at->format('Y-m-d')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('purchases.returns.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('purchase_returns.back_to_list')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.linked_purchase')); ?></div>
                        <div class="font-medium text-gray-800">
                            <?php if($purchaseReturn->purchase): ?>
                                <a href="<?php echo e(route('purchases.show', $purchaseReturn->purchase)); ?>" class="text-[#1456E8] hover:underline">
                                    #<?php echo e($purchaseReturn->purchase->purchase_number ?? $purchaseReturn->purchase->id); ?>

                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.supplier')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchaseReturn->supplier?->name ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.branch')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchaseReturn->branch?->name ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.refund_account')); ?></div>
                        <div class="font-medium text-gray-800">
                            <?php if($purchaseReturn->isCredit()): ?>
                                <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700"><?php echo e(__('purchase_returns.credit')); ?></span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700"><?php echo e($purchaseReturn->refundAccount?->name ?? __('purchase_returns.payment_immediate')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.cost_center')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchaseReturn->costCenter?->cost_center_ar ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.created_by')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchaseReturn->creator?->name ?? '-'); ?></div>
                    </div>
                    <?php if($purchaseReturn->reason): ?>
                        <div class="md:col-span-3">
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.reason')); ?></div>
                            <div class="text-gray-700"><?php echo e($purchaseReturn->reason); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.code')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.product')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.return_qty')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.unit_price')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.discount')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.tax')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.total')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__currentLoopData = $purchaseReturn->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-400 text-xs"><?php echo e($item->product_code_snapshot ?? $item->product?->code ?? '-'); ?></td>
                                    <td class="px-3 py-2 font-medium text-gray-800"><?php echo e($item->product_name_snapshot ?? $item->product?->name ?? '-'); ?></td>
                                    <td class="px-3 py-2 text-gray-600"><?php echo e($item->quantity); ?></td>
                                    <td class="px-3 py-2 text-gray-600"><?php echo e(number_format($item->unit_price, 2)); ?></td>
                                    <td class="px-3 py-2 text-gray-600"><?php echo e(number_format($item->discount_amount, 2)); ?></td>
                                    <td class="px-3 py-2 text-gray-600"><?php echo e(number_format($item->tax_amount, 2)); ?></td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]">
                                        <?php echo e(number_format(($item->unit_price * $item->quantity) - $item->discount_amount + $item->tax_amount, 2)); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchase_returns.subtotal')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchaseReturn->subtotal, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchase_returns.discount_total')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchaseReturn->discount_amount + $purchaseReturn->invoice_level_discount, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchase_returns.tax_total')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchaseReturn->tax_amount, 2)); ?></div>
                    </div>
                    <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                        <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                        <div class="text-xs text-white/50 mb-1"><?php echo e(__('purchase_returns.grand_total')); ?></div>
                        <div class="font-bold text-lg"><?php echo e(number_format($purchaseReturn->grand_total, 2)); ?></div>
                    </div>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/purchases/returns/show.blade.php ENDPATH**/ ?>