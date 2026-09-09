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
                            <circle cx="12" cy="8" r="3.5" /><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('employees.title')); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5"><?php echo e(__('employees.subtitle')); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employees.create')): ?>
                    <a href="<?php echo e(route('employees.import.form')); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition whitespace-nowrap">
                        <?php echo e(__('employees.import_title')); ?>

                    </a>
                    <a href="<?php echo e(route('employees.create')); ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <?php echo e(__('employees.new_employee')); ?>

                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(session('success')): ?>
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                           placeholder="<?php echo e(__('employees.search_placeholder')); ?>"
                           class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value=""><?php echo e(__('employees.all_statuses')); ?></option>
                        <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>><?php echo e(__('employees.status_active')); ?></option>
                        <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>><?php echo e(__('employees.status_inactive')); ?></option>
                        <option value="terminated" <?php echo e(request('status') === 'terminated' ? 'selected' : ''); ?>><?php echo e(__('employees.status_terminated')); ?></option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        <?php echo e(__('employees.search')); ?>

                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('employees.employee_number')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('employees.name')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('employees.job_title')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('employees.branch')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('employees.phone')); ?></th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase"><?php echo e(__('employees.status')); ?></th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs"><?php echo e($employee->employee_number); ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($employee->name); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($employee->job_title); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($employee->branch->name ?? '-'); ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo e($employee->phone); ?></td>
                                <td class="px-4 py-3">
                                    <?php if($employee->status === 'active'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700"><?php echo e(__('employees.status_active')); ?></span>
                                    <?php elseif($employee->status === 'terminated'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"><?php echo e(__('employees.status_terminated')); ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700"><?php echo e(__('employees.status_inactive')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employees.edit')): ?>
                                        <a href="<?php echo e(route('employees.edit', $employee->id)); ?>"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                            <?php echo e(__('employees.edit')); ?>

                                        </a>
                                        <form method="POST" action="<?php echo e(route('employees.toggle-status', $employee->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium <?php echo e($employee->status === 'active' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100'); ?> transition">
                                                <?php echo e($employee->status === 'active' ? __('employees.deactivate') : __('employees.activate')); ?>

                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400"><?php echo e(__('employees.no_data')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($employees->hasPages()): ?>
                <div class="mt-4"><?php echo e($employees->links()); ?></div>
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
<?php /**PATH C:\xampp\htdocs\factory\resources\views/employees/index.blade.php ENDPATH**/ ?>