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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6"><?php echo e(__('manufacturing.edit')); ?> — <?php echo e($bom->name); ?></h2>

                <?php if($errors->any()): ?>
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc pr-4">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('manufacturing.bom.update', $bom)); ?>" method="POST" id="bom-form">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.name')); ?></label>
                            <input type="text" name="name" value="<?php echo e($bom->name); ?>" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.bom_product')); ?></label>
                            <select name="product_id" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($product->id); ?>" <?php if($product->id === $bom->product_id): echo 'selected'; endif; ?>><?php echo e($product->name); ?> (<?php echo e($product->code); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.bom_production_quantity')); ?></label>
                            <input type="number" step="0.001" name="production_quantity" value="<?php echo e($bom->production_quantity); ?>" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('manufacturing.status')); ?></label>
                            <select name="status" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="active" <?php if($bom->status === 'active'): echo 'selected'; endif; ?>><?php echo e(__('manufacturing.active')); ?></option>
                                <option value="inactive" <?php if($bom->status === 'inactive'): echo 'selected'; endif; ?>><?php echo e(__('manufacturing.inactive')); ?></option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 mt-6">
                            <input type="checkbox" name="is_default" id="is_default" value="1" <?php if($bom->is_default): echo 'checked'; endif; ?> class="rounded border-gray-300 text-[#1456E8]">
                            <label for="is_default" class="text-sm text-gray-700"><?php echo e(__('manufacturing.bom_is_default')); ?></label>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]"><?php echo e($bom->notes); ?></textarea>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-[#0F1B4C]"><?php echo e(__('manufacturing.bom_items')); ?></h3>
                            <button type="button" onclick="addBomItemRow()" class="text-sm font-medium text-[#1456E8] hover:underline">+ <?php echo e(__('manufacturing.bom_item_add')); ?></button>
                        </div>

                        <div id="bom-items-wrapper" class="space-y-3"></div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-6 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            <?php echo e(__('manufacturing.save')); ?>

                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <template id="bom-item-row-template">
        <div class="grid grid-cols-12 gap-3 items-end bg-gray-50 rounded-xl p-3 bom-item-row">
            <div class="col-span-5">
                <label class="block text-xs font-medium text-gray-600 mb-1">المادة الخام</label>
                <select name="items[__INDEX__][product_id]" required class="w-full rounded-lg border-gray-300 text-sm focus:border-[#1456E8] focus:ring-[#1456E8] js-product-select">
                    <option value="">—</option>
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (<?php echo e($product->code); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-span-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">الكمية</label>
                <input type="number" step="0.001" name="items[__INDEX__][quantity]" required class="w-full rounded-lg border-gray-300 text-sm focus:border-[#1456E8] focus:ring-[#1456E8] js-quantity">
            </div>
            <div class="col-span-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">تكلفة الوحدة</label>
                <input type="number" step="0.01" name="items[__INDEX__][unit_cost]" value="0" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#1456E8] focus:ring-[#1456E8] js-unit-cost">
            </div>
            <div class="col-span-1">
                <button type="button" onclick="this.closest('.bom-item-row').remove()" class="text-red-500 hover:text-red-700 text-sm">✕</button>
            </div>
        </div>
    </template>

    <script>
        let bomItemIndex = 0;
        const existingItems = <?php echo json_encode($bom->items->map(fn($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity, 'unit_cost' => $i->unit_cost])) ?>;

        function addBomItemRow(prefill = null) {
            const template = document.getElementById('bom-item-row-template').innerHTML;
            const html = template.replaceAll('__INDEX__', bomItemIndex);
            const wrapper = document.getElementById('bom-items-wrapper');
            const div = document.createElement('div');
            div.innerHTML = html;
            const row = div.firstElementChild;

            if (prefill) {
                row.querySelector('.js-product-select').value = prefill.product_id;
                row.querySelector('.js-quantity').value = prefill.quantity;
                row.querySelector('.js-unit-cost').value = prefill.unit_cost;
            }

            wrapper.appendChild(row);
            bomItemIndex++;
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (existingItems.length) {
                existingItems.forEach(item => addBomItemRow(item));
            } else {
                addBomItemRow();
            }
        });
    </script>
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
<?php /**PATH C:\xampp\htdocs\factory\resources\views/manufacturing/bom/edit.blade.php ENDPATH**/ ?>