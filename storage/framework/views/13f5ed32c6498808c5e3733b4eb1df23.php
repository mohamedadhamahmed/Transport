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
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg"><?php echo e(__('products.edit')); ?> - <?php echo e($product->name); ?></h2>
            </div>
<?php if($errors->any()): ?>
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
            <form method="POST" action="<?php echo e(route('products.update', $product->id)); ?>" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    
    <!-- أضف هذا السطر هنا لتجنب خطأ فقدان الفرع -->
    <input type="hidden" name="branch_id" value="<?php echo e($product->branch_id); ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.name')); ?> *</label>
                        <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.name_en')); ?></label>
                        <input type="text" name="name_en" value="<?php echo e(old('name_en', $product->name_en)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.code')); ?></label>
                        <input type="text" name="code" value="<?php echo e(old('code', $product->code)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.location')); ?></label>
                        <input type="text" name="location" value="<?php echo e(old('location', $product->location)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.unit')); ?></label>
                        <input type="text" name="unit" value="<?php echo e(old('unit', $product->unit)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.purchase_price')); ?></label>
                        <input type="number" step="0.01" min="0" name="purchase_price" value="<?php echo e(old('purchase_price', $product->purchase_price)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.sale_price')); ?></label>
                        <input type="number" step="0.01" min="0" name="sale_price" value="<?php echo e(old('sale_price', $product->sale_price)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.wholesale_price')); ?></label>
                        <input type="number" step="0.01" min="0" name="wholesale_price" value="<?php echo e(old('wholesale_price', $product->wholesale_price)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.low_stock_alert_quantity')); ?></label>
                        <input type="number" step="1" min="0" name="low_stock_alert_quantity" value="<?php echo e(old('low_stock_alert_quantity', $product->low_stock_alert_quantity)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.tax_value')); ?></label>
                        <input type="number" step="0.01" min="0" name="tax_value" value="<?php echo e(old('tax_value', $product->tax_value)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.group')); ?> *</label>
                        <select name="product_group_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value=""><?php echo e(__('products.choose_group')); ?></option>
                            <?php $__currentLoopData = $productGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($group->id); ?>" <?php if(old('product_group_id', $product->product_group_id) == $group->id): echo 'selected'; endif; ?>><?php echo e($group->group_ar); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <!-- حقل الحالة المضاف حديثاً -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.status') ?? 'حالة المنتج'); ?> *</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="1" <?php if(old('status', $product->status) == 1): echo 'selected'; endif; ?>><?php echo e(__('products.active') ?? 'نشط'); ?></option>
                            <option value="0" <?php if(old('status', $product->status) == 0): echo 'selected'; endif; ?>><?php echo e(__('products.inactive') ?? 'غير نشط'); ?></option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('products.notes')); ?></label>
                        <input type="text" name="notes" value="<?php echo e(old('notes', $product->notes)); ?>"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>

                <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 mt-6 text-sm text-gray-600">
                    <?php echo e(__('products.stock_edit_note')); ?>

                    <span class="font-semibold text-[#0F1B4C]"><?php echo e($product->stock_quantity); ?></span> —
                    <a href="<?php echo e(route('products.index', $product->branch_id)); ?>" class="text-[#1456E8] underline"><?php echo e(__('products.go_edit_stock_from_list')); ?></a>
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        <?php echo e(__('products.save')); ?>

                    </button>
                    <a href="<?php echo e(route('products.index', $product->branch_id)); ?>" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        <?php echo e(__('products.cancel')); ?>

                    </a>
                </div>
            </form>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/products/edit.blade.php ENDPATH**/ ?>