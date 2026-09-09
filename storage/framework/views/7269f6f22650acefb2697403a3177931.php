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
        <div class="max-w-[900px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg"><?php echo e(__('products.import_excel')); ?></h2>
                <p class="text-white/45 text-xs mt-0.5"><?php echo e(__('products.branch')); ?>: <?php echo e($branch->name); ?></p>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6" x-data="{ mode: 'adjustment' }">
                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 mb-6">
                    <?php echo e(__('products.import_format_note')); ?>

                </div>

                <form method="POST" action="<?php echo e(route('products.import.process', $branch->id)); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('products.import_mode')); ?></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="mode" value="opening_stock" x-model="mode" class="text-[#1456E8]">
                                <span class="text-sm"><?php echo e(__('products.opening_stock_mode')); ?></span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="mode" value="adjustment" x-model="mode" class="text-[#1456E8]" checked>
                                <span class="text-sm"><?php echo e(__('products.adjustment_mode')); ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('products.excel_file')); ?></label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <p class="text-xs text-gray-400 mt-2"><?php echo e(__('products.excel_columns_hint')); ?></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                            <?php echo e(__('products.upload_and_process')); ?>

                        </button>
                        <a href="<?php echo e(route('products.index', $branch->id)); ?>" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            <?php echo e(__('products.cancel')); ?>

                        </a>
                    </div>
                </form>
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
<?php /**PATH C:\xampp\htdocs\factory\resources\views/products/import.blade.php ENDPATH**/ ?>