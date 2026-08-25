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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4v5h5M20 20v-5h-5"/>
                            <path d="M4.6 15a8 8 0 0 0 14.9 2M19.4 9A8 8 0 0 0 4.5 7"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('invoices.title')); ?></h2>
                </div>
                <a href="<?php echo e(route('invoices.create')); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + <?php echo e(__('invoices.new_invoice')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="<?php echo e(route('invoices.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('invoices.invoice_no')); ?></label>
                            <input type="text" name="invoice_number" value="<?php echo e(request('invoice_number')); ?>"
                                   placeholder="<?php echo e(__('invoices.invoice_no_placeholder')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('invoices.customer')); ?></label>
                            <select name="customer_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value=""><?php echo e(__('invoices.all_customers')); ?></option>
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($customer->id); ?>" <?php if(request('customer_id') == $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('invoices.filter_by_date')); ?></label>
                            <input type="date" name="date" value="<?php echo e(request('date')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                <?php echo e(__('messages.search')); ?>

                            </button>
                            <?php if(request()->hasAny(['invoice_number', 'customer_id', 'date'])): ?>
                                <a href="<?php echo e(route('invoices.index')); ?>"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    <?php echo e(__('invoices.cancel')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.invoice_no')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.seller')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.customer')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.date')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.branch')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.grand_total')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.payment_method')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.status')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('invoices.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#<?php echo e($invoice->invoice_number ?? $invoice->id); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($invoice->creator?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($invoice->customer?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($invoice->issue_date?->format('Y-m-d') ?? $invoice->created_at->format('Y-m-d')); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($invoice->branch?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]"><?php echo e(number_format($invoice->subtotal + $invoice->tax_amount - ($invoice->invoice_level_discount ?? 0), 2)); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e(__('invoices.' . $invoice->payment_method)); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if($invoice->is_finalized): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-50 text-green-700"><?php echo e(__('invoices.final')); ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                                                <?php echo e(__('invoices.draft')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </td>
                           <td class="px-4 py-3">
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" title="<?php echo e(__('invoices.view')); ?>"
           class="w-7 h-7 flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#11111] hover:bg-[#1456E8]/20 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
 
        <?php
            $comingSoonIcons = [
                'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
                'pdf' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
                'whatsapp' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>',
                'print' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
            ];
        ?>
 
        <?php $__currentLoopData = $comingSoonIcons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $soon => $iconPath): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span title="<?php echo e(__('invoices.' . $soon)); ?> - <?php echo e(__('invoices.coming_soon')); ?>"
                  class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 text-gray-500 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?php echo $iconPath; ?></svg>
            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('invoices.no_invoices')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

       <div class="p-4 border-t border-gray-100">
    <?php if($invoices->hasPages()): ?>
        <div class="flex items-center justify-center gap-1 flex-wrap">
            <?php if($invoices->onFirstPage()): ?>
                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('invoices.previous')); ?></span>
            <?php else: ?>
                <a href="<?php echo e($invoices->previousPageUrl()); ?>"
                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('invoices.previous')); ?></a>
            <?php endif; ?>

            <?php $__currentLoopData = range(1, $invoices->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($page == $invoices->currentPage()): ?>
                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a href="<?php echo e($invoices->url($page)); ?>"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e($page); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($invoices->hasMorePages()): ?>
                <a href="<?php echo e($invoices->nextPageUrl()); ?>"
                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('invoices.next')); ?></a>
            <?php else: ?>
                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('invoices.next')); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/invoices/index.blade.php ENDPATH**/ ?>