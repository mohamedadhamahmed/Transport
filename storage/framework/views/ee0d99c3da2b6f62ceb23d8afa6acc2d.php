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
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('settings.employee_discounts_title')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <form method="GET" class="flex items-center gap-3">
                    <label class="text-sm text-gray-600"><?php echo e(__('settings.branch')); ?>:</label>
                    <select name="branch_id" onchange="this.form.submit()"
                        class="rounded-lg border-gray-300 text-sm focus:ring-[#1456E8] focus:border-[#1456E8]">
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>" <?php if($branch->id == $selectedBranchId): echo 'selected'; endif; ?>>
                                <?php echo e($branch->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
            </div>

            
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <div class="font-semibold text-gray-700 mb-4"><?php echo e(__('settings.branch_default_discount')); ?></div>

                <form method="POST" action="<?php echo e(route('employee-discounts.branch-default')); ?>"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="branchs_id" value="<?php echo e($selectedBranchId); ?>">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.max_default_discount')); ?></label>
                        <input type="number" step="0.01" min="0" max="100" name="max_employee_discount"
                               value="<?php echo e(old('max_employee_discount', $branchDefault)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.requires_approval_above')); ?></label>
                        <input type="number" step="0.01" min="0" max="100" name="requires_approval_above"
                               value="<?php echo e(old('requires_approval_above', $requiresApprovalAbove)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full rounded-lg py-2 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            <?php echo e(__('settings.save_default_discount')); ?>

                        </button>
                    </div>
                </form>
            </div>

            
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">
                    <?php echo e(__('settings.custom_employee_discounts')); ?>

                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-start"><?php echo e(__('settings.employee')); ?></th>
                                <th class="px-4 py-3 text-start"><?php echo e(__('settings.current_rate')); ?></th>
                                <th class="px-4 py-3 text-start"><?php echo e(__('settings.action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($user->name); ?></td>
                                    <td class="px-4 py-3 text-gray-500">
                                        <?php if(isset($overrides[$user->id])): ?>
                                            <?php echo e($overrides[$user->id]); ?>% <span class="text-xs text-[#1456E8]">(<?php echo e(__('settings.custom_label')); ?>)</span>
                                        <?php else: ?>
                                            <?php echo e($branchDefault); ?>% <span class="text-xs text-gray-400">(<?php echo e(__('settings.default_label')); ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="<?php echo e(route('employee-discounts.user-override')); ?>"
                                              class="flex items-center gap-2">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="user_id" value="<?php echo e($user->id); ?>">
                                            <input type="hidden" name="branchs_id" value="<?php echo e($selectedBranchId); ?>">

                                            <input type="number" step="0.01" min="0" max="100"
                                                   name="max_discount"
                                                   value="<?php echo e($overrides[$user->id] ?? ''); ?>"
                                                   placeholder="<?php echo e(__('settings.leave_empty_for_default')); ?>"
                                                   class="w-32 rounded-lg border-gray-300 text-sm focus:ring-[#1456E8] focus:border-[#1456E8]">

                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-white text-xs bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                                                <?php echo e(__('settings.save')); ?>

                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/settings/employee-discounts/index.blade.php ENDPATH**/ ?>