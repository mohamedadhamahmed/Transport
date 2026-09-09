<?php
    $branch = $branch ?? null;
?>

<?php if($errors->any()): ?>
    <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5 mb-4">
        <ul class="list-disc ps-5 space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('branches.name')); ?> *</label>
        <input type="text" name="name" value="<?php echo e(old('name', $branch->name ?? '')); ?>" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('branches.name_en')); ?></label>
        <input type="text" name="name_en" value="<?php echo e(old('name_en', $branch->name_en ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('branches.location')); ?></label>
        <input type="text" name="location" value="<?php echo e(old('location', $branch->location ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('branches.type')); ?> *</label>
        <select name="type"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <option value="main" <?php if(old('type', $branch->type ?? 'main') === 'main'): echo 'selected'; endif; ?>><?php echo e(__('branches.type_main')); ?></option>
            <option value="sub" <?php if(old('type', $branch->type ?? 'main') === 'sub'): echo 'selected'; endif; ?>><?php echo e(__('branches.type_sub')); ?></option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('branches.parent_branch')); ?></label>
        <select name="parent_branch_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <option value=""><?php echo e(__('branches.parent_branch_placeholder')); ?></option>
            <?php $__currentLoopData = $parentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($id); ?>" <?php if((string) old('parent_branch_id', $branch->parent_branch_id ?? '') === (string) $id): echo 'selected'; endif; ?>>
                    <?php echo e($name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
        <?php echo e(__('branches.save')); ?>

    </button>
    <a href="<?php echo e(route('branches.index')); ?>" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
        <?php echo e(__('branches.cancel')); ?>

    </a>
</div>
<?php /**PATH C:\xampp\htdocs\factory\resources\views/branches/_form.blade.php ENDPATH**/ ?>