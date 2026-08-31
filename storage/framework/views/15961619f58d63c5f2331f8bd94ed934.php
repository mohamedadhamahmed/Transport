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
                            <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 8H6"/>
                            <circle cx="9" cy="21" r="1"/><circle cx="17" cy="21" r="1"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('purchases.purchase_no')); ?> #<?php echo e($purchase->purchase_number ?? $purchase->id); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(optional($purchase->issue_date)->format('Y-m-d') ?? $purchase->created_at->format('Y-m-d')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('purchases.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('purchases.back_to_list')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.supplier')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchase->supplier?->name ?? '-'); ?></div>
                        <?php if($purchase->supplier?->phone): ?>
                            <div class="text-xs text-gray-400 mt-0.5"><?php echo e($purchase->supplier->phone); ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.branch')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchase->branch?->name ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.payment_method')); ?></div>
                        <div class="font-medium text-gray-800">
                            <?php if($purchase->isCredit()): ?>
                                <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700"><?php echo e(__('purchases.credit')); ?></span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700"><?php echo e($purchase->paymentAccount?->name ?? __('purchases.payment_immediate')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.supplier_invoice_number')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchase->supplier_invoice_number ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.warehouse_name')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchase->warehouse_name ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.cost_center')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchase->costCenter?->cost_center_ar ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.created_by')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($purchase->creator?->name ?? '-'); ?></div>
                    </div>
                    <?php if($purchase->supplier): ?>
                        <div>
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.supplier_balance')); ?></div>
                            <div class="font-medium text-gray-800"><?php echo e(number_format($purchase->supplier->balance, 2)); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if($purchase->note): ?>
                        <div class="md:col-span-3">
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchases.note')); ?></div>
                            <div class="text-gray-700"><?php echo e($purchase->note); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.code')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.product')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.quantity')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.unit_price')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.discount')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.tax')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchases.total')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__currentLoopData = $purchase->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchases.subtotal')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchase->subtotal, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchases.discount_total')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchase->discount_amount + $purchase->invoice_level_discount, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchases.tax_total')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchase->tax_amount, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchases.shipping')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($purchase->shipping_fee, 2)); ?></div>
                    </div>
                    <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                        <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                        <div class="text-xs text-white/50 mb-1"><?php echo e(__('purchases.grand_total')); ?></div>
                        <div class="font-bold text-lg"><?php echo e(number_format($purchase->grand_total, 2)); ?></div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="font-semibold text-gray-800 mb-3"><?php echo e(__('purchases.attachments')); ?></h3>
                <?php if($purchase->attachments->isEmpty()): ?>
                    <div class="text-sm text-gray-400"><?php echo e(__('purchases.no_attachments')); ?></div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <?php $__currentLoopData = $purchase->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($attachment->url()); ?>" target="_blank"
                               class="flex items-center gap-3 px-4 py-3 rounded-lg border border-gray-100 hover:border-[#1456E8]/30 hover:bg-[#1456E8]/5 transition">
                                <span class="w-9 h-9 shrink-0 rounded-lg bg-[#0F1B4C]/5 flex items-center justify-center text-[#0F1B4C]">
                                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                                </span>
                                <span class="text-sm text-gray-700 truncate"><?php echo e($attachment->original_name ?? __('purchases.download_attachment')); ?></span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/purchases/show.blade.php ENDPATH**/ ?>