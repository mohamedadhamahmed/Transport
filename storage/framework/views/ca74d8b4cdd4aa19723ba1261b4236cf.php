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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 7h6M9 11h6M9 15h3"/>
                            <path d="M5 4h14a1 1 0 0 1 1 1v15l-3-2-3 2-3-2-3 2-3-2-3 2V5a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('quotations.quotation_no')); ?> #<?php echo e($quotation->id); ?></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('quotations.pdf', $quotation)); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>
                        </svg>
                        <?php echo e(__('quotations.download_pdf')); ?>

                    </a>
                    <a href="<?php echo e(route('quotations.index')); ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        <?php echo e(__('quotations.back_to_list')); ?>

                    </a>
                </div>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <?php if($quotation->status === 'approved'): ?>
                        <span class="px-3 py-1.5 rounded-full text-sm bg-emerald-50 text-emerald-700 font-medium"><?php echo e(__('quotations.status_approved')); ?></span>
                        <?php if($quotation->invoice): ?>
                            <a href="<?php echo e(route('invoices.show', $quotation->invoice)); ?>" class="ms-2 text-sm text-[#1456E8] hover:underline">
                                <?php echo e(__('quotations.view_invoice')); ?> #<?php echo e($quotation->invoice->invoice_number ?? $quotation->invoice->id); ?>

                            </a>
                        <?php endif; ?>
                        <div class="text-xs text-gray-400 mt-1">
                            <?php echo e(__('quotations.approved_by')); ?>: <?php echo e($quotation->approver?->name ?? '-'); ?> — <?php echo e($quotation->approved_at?->format('Y-m-d H:i')); ?>

                        </div>
                    <?php elseif($quotation->status === 'rejected'): ?>
                        <span class="px-3 py-1.5 rounded-full text-sm bg-red-50 text-red-700 font-medium"><?php echo e(__('quotations.status_rejected')); ?></span>
                    <?php else: ?>
                        <span class="px-3 py-1.5 rounded-full text-sm bg-amber-50 text-amber-700 font-medium"><?php echo e(__('quotations.status_pending')); ?></span>
                    <?php endif; ?>
                </div>
                <?php if($quotation->isPending()): ?>
                    <div class="flex items-center gap-2" x-data>
                        <form method="POST" action="<?php echo e(route('quotations.approve', $quotation)); ?>"
                              @submit="if (!confirm(<?php echo \Illuminate\Support\Js::from(__('quotations.approve_confirm'))->toHtml() ?>)) $event.preventDefault();">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition shadow-sm">
                                <?php echo e(__('quotations.approve')); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('quotations.reject', $quotation)); ?>"
                              @submit="if (!confirm(<?php echo \Illuminate\Support\Js::from(__('quotations.reject_confirm'))->toHtml() ?>)) $event.preventDefault();">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg text-red-600 text-sm font-medium bg-red-50 hover:bg-red-100 transition">
                                <?php echo e(__('quotations.reject')); ?>

                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('quotations.customer')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($quotation->customer?->name ?? '-'); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('quotations.date')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($quotation->created_at->format('Y-m-d')); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('quotations.payment_method')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e(__('quotations.' . $quotation->payment_method)); ?></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1"><?php echo e(__('quotations.created_by')); ?></div>
                        <div class="font-medium text-gray-800"><?php echo e($quotation->creator?->name ?? '-'); ?></div>
                    </div>
                    <?php if($quotation->note): ?>
                        <div class="md:col-span-4">
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('quotations.note')); ?></div>
                            <div class="text-gray-700"><?php echo e($quotation->note); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.code')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.product')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.quantity')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.unit_price')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.discount')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.tax')); ?></th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.total')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $lineSubtotal = ($item->unit_price * $item->quantity) - ($item->discount_amount ?? 0);
                                    $lineTotal = $lineSubtotal + ($item->tax_amount ?? 0);
                                ?>
                                <tr>
                                    <td class="px-3 py-2 text-gray-400 text-xs"><?php echo e($item->product_code_snapshot ?? $item->product?->code ?? '-'); ?></td>
                                    <td class="px-3 py-2 font-medium text-gray-800"><?php echo e($item->product_name_snapshot ?? $item->product?->name ?? '-'); ?></td>
                                    <td class="px-3 py-2"><?php echo e($item->quantity); ?></td>
                                    <td class="px-3 py-2"><?php echo e(number_format($item->unit_price, 2)); ?></td>
                                    <td class="px-3 py-2"><?php echo e(number_format($item->discount_amount ?? 0, 2)); ?></td>
                                    <td class="px-3 py-2"><?php echo e(number_format($item->tax_amount ?? 0, 2)); ?></td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]"><?php echo e(number_format($lineTotal, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('quotations.subtotal')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($quotation->subtotal, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('quotations.discount_total')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($quotation->discount_amount + $quotation->invoice_level_discount, 2)); ?></div>
                    </div>
                    <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                        <div class="text-xs text-gray-500 mb-1"><?php echo e(__('quotations.tax_total')); ?></div>
                        <div class="font-semibold text-[#0F1B4C]"><?php echo e(number_format($quotation->tax_amount, 2)); ?></div>
                    </div>
                    <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                        <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                        <div class="text-xs text-white/50 mb-1"><?php echo e(__('quotations.grand_total')); ?></div>
                        <div class="font-bold text-lg"><?php echo e(number_format($quotation->grand_total, 2)); ?></div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="font-semibold text-gray-800 mb-3"><?php echo e(__('quotations.previous_quotations_for_customer')); ?></h3>
                <?php if($previousQuotations->isEmpty()): ?>
                    <div class="text-sm text-gray-400 text-center py-4"><?php echo e(__('quotations.no_previous_quotations')); ?></div>
                <?php else: ?>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500">
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.quotation_no')); ?></th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.date')); ?></th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.status')); ?></th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.items_count')); ?></th>
                                    <th class="px-3 py-2 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('quotations.grand_total')); ?></th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $previousQuotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-[#1456E8]/5 transition">
                                        <td class="px-3 py-2 font-medium text-gray-800">#<?php echo e($prev->id); ?></td>
                                        <td class="px-3 py-2 text-gray-500"><?php echo e($prev->created_at->format('Y-m-d')); ?></td>
                                        <td class="px-3 py-2">
                                            <?php if($prev->status === 'approved'): ?>
                                                <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700"><?php echo e(__('quotations.status_approved')); ?></span>
                                            <?php elseif($prev->status === 'rejected'): ?>
                                                <span class="px-2 py-1 rounded-full text-xs bg-red-50 text-red-700"><?php echo e(__('quotations.status_rejected')); ?></span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700"><?php echo e(__('quotations.status_pending')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500"><?php echo e($prev->items->count()); ?></td>
                                        <td class="px-3 py-2 font-semibold text-[#0F1B4C]"><?php echo e(number_format($prev->grand_total, 2)); ?></td>
                                        <td class="px-3 py-2">
                                            <a href="<?php echo e(route('quotations.show', $prev)); ?>" class="text-[#1456E8] text-xs font-medium hover:underline"><?php echo e(__('quotations.view')); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/quotations/show.blade.php ENDPATH**/ ?>