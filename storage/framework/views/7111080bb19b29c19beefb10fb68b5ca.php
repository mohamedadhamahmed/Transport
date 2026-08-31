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
                            <path d="M4 21V10l8-6 8 6v11" /><path d="M9 21v-6h6v6" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('suppliers.title')); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(__('suppliers.subtitle')); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('suppliers.create')); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    <?php echo e(__('suppliers.new_supplier')); ?>

                </a>
            </div>

            <?php if(session('success')): ?>
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex gap-3 mb-4">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                           placeholder="<?php echo e(__('suppliers.search_placeholder')); ?>"
                           class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        <?php echo e(__('suppliers.search')); ?>

                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('suppliers.name')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('suppliers.phone')); ?></th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($supplier->name); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($supplier->phone); ?></td>
                                <td class="px-4 py-3">
                                    <a href="<?php echo e(route('suppliers.edit', $supplier->id)); ?>"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                        <?php echo e(__('suppliers.edit')); ?>

                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3" class="px-4 py-10 text-center text-gray-400"><?php echo e(__('suppliers.no_data')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($suppliers->hasPages()): ?>
                <div class="mt-4"><?php echo e($suppliers->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/suppliers/index.blade.php ENDPATH**/ ?>