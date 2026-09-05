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
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('accounts.list_title')); ?></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('accounts.tree')); ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        <?php echo e(__('accounts.tree_view')); ?>

                    </a>
                    <a href="<?php echo e(route('accounts.create')); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        + <?php echo e(__('accounts.new_account')); ?>

                    </a>
                </div>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="<?php echo e(route('accounts.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('accounts.name')); ?></label>
                            <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(__('accounts.search_placeholder')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('accounts.branch')); ?></label>
                            <select name="branchs_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value=""><?php echo e(__('accounts.all_branches')); ?></option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php if(request('branchs_id') == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('accounts.status')); ?></label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value=""><?php echo e(__('accounts.all_statuses')); ?></option>
                                <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>><?php echo e(__('accounts.active')); ?></option>
                                <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>><?php echo e(__('accounts.inactive')); ?></option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                <?php echo e(__('accounts.filter')); ?>

                            </button>
                            <?php if(request()->hasAny(['q', 'branchs_id', 'status'])): ?>
                                <a href="<?php echo e(route('accounts.index')); ?>"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    <?php echo e(__('accounts.cancel')); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.account_number')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.name')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.parent_account')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.debtor')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.creditor')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.current_balance')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.status')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('accounts.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($account->account_number ?? '-'); ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($account->name); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($account->parentAccount?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e(number_format($account->debtor_current ?? 0, 2)); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e(number_format($account->creditor_current ?? 0, 2)); ?></td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]"><?php echo e(number_format($account->current_balance, 2)); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if($account->active): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700"><?php echo e(__('accounts.active')); ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-500"><?php echo e(__('accounts.inactive')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <a href="<?php echo e(route('accounts.statement', $account)); ?>" title="<?php echo e(__('accounts.statement')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v18M3 9h18M3 15h18"/></svg>
                                            </a>
                                            <a href="<?php echo e(route('accounts.edit', $account)); ?>" title="<?php echo e(__('accounts.edit_account')); ?>"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                            <form action="<?php echo e(route('accounts.toggle', $account)); ?>" method="POST"
                                                  onsubmit="return confirm(<?php echo json_encode(__('accounts.confirm_toggle'), 15, 512) ?>);">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" title="<?php echo e($account->active ? __('accounts.deactivate') : __('accounts.activate')); ?>"
                                                        class="w-7 h-7 inline-flex items-center justify-center rounded-md <?php echo e($account->active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'); ?> transition">
                                                    <?php if($account->active): ?>
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                                                    <?php else: ?>
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/></svg>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                        <?php echo e(__('accounts.no_accounts_found')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    <?php if($accounts->hasPages()): ?>
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            <?php if($accounts->onFirstPage()): ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('accounts.previous')); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($accounts->previousPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('accounts.previous')); ?></a>
                            <?php endif; ?>

                            <?php $__currentLoopData = range(1, $accounts->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $accounts->currentPage()): ?>
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]"><?php echo e($page); ?></span>
                                <?php else: ?>
                                    <a href="<?php echo e($accounts->url($page)); ?>"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e($page); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($accounts->hasMorePages()): ?>
                                <a href="<?php echo e($accounts->nextPageUrl()); ?>"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition"><?php echo e(__('accounts.next')); ?></a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed"><?php echo e(__('accounts.next')); ?></span>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/accounts/index.blade.php ENDPATH**/ ?>