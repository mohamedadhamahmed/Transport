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
                            <path d="M4 6h16M4 12h16M4 18h10"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e($type === 'opening' ? __('journal_entries.opening_title') : __('journal_entries.daily_title')); ?></h2>
                </div>
                <a href="<?php echo e(route('journal-entries.create', ['type' => $type])); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    + <?php echo e($type === 'opening' ? __('journal_entries.new_opening_entry') : __('journal_entries.new_daily_entry')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="flex gap-2">
                <a href="<?php echo e(route('journal-entries.index', ['type' => 'daily'])); ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition <?php echo e($type === 'daily' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'); ?>">
                    <?php echo e(__('journal_entries.daily_title')); ?>

                </a>
                <a href="<?php echo e(route('journal-entries.index', ['type' => 'opening'])); ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition <?php echo e($type === 'opening' ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'); ?>">
                    <?php echo e(__('journal_entries.opening_title')); ?>

                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                <div class="p-4 border-b border-gray-100">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <input type="hidden" name="type" value="<?php echo e($type); ?>">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('journal_entries.date_from')); ?></label>
                            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('journal_entries.date_to')); ?></label>
                            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                <?php echo e(__('journal_entries.filter')); ?>

                            </button>
                            <?php if(request()->hasAny(['date_from', 'date_to'])): ?>
                                <a href="<?php echo e(route('journal-entries.index', ['type' => $type])); ?>" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    <?php echo e(__('journal_entries.cancel')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.entry_no')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.entry_date')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.description')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.created_by')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.total_debit')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">#<?php echo e($entry->entry_number); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($entry->entry_date->format('Y-m-d')); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e(\Illuminate\Support\Str::limit($entry->description, 60) ?: '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($entry->creator?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]"><?php echo e(number_format($entry->total_debit, 2)); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <a href="<?php echo e(route('journal-entries.show', $entry)); ?>" title="<?php echo e(__('journal_entries.view')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <a href="<?php echo e(route('journal-entries.edit', $entry)); ?>" title="<?php echo e(__('journal_entries.edit_entry')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('journal_entries.no_entries_found')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    <?php if($entries->hasPages()): ?>
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            <?php if($entries->onFirstPage()): ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('journal_entries.previous')); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($entries->previousPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('journal_entries.previous')); ?></a>
                            <?php endif; ?>

                            <?php $__currentLoopData = range(1, $entries->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $entries->currentPage()): ?>
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]"><?php echo e($page); ?></span>
                                <?php else: ?>
                                    <a href="<?php echo e($entries->url($page)); ?>"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e($page); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($entries->hasMorePages()): ?>
                                <a href="<?php echo e($entries->nextPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('journal_entries.next')); ?></a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('journal_entries.next')); ?></span>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/journal-entries/index.blade.php ENDPATH**/ ?>