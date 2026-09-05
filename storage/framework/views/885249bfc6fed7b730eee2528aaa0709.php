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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('settings.onboarding_title')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                <div class="font-semibold text-gray-700 mb-1"><?php echo e(__('settings.onboarding_title')); ?></div>
                <p class="text-xs text-gray-500 mb-4"><?php echo e(__('settings.onboarding_subtitle')); ?></p>

                <div class="rounded-lg bg-gray-50 p-4 mb-4 text-sm text-gray-600 space-y-1">
                    <div class="font-medium text-gray-700 mb-1"><?php echo e(__('settings.onboarding_branch_info')); ?></div>
                    <div><?php echo e(__('settings.facility_name')); ?>: <?php echo e($zakatSetting->name ?? '—'); ?></div>
                    <div><?php echo e(__('settings.common_name')); ?>: <?php echo e($zakatSetting->common_name ?? '—'); ?></div>
                    <div><?php echo e(__('settings.trn')); ?>: <?php echo e($zakatSetting->trn ?? '—'); ?></div>
                </div>

                <div class="rounded-lg bg-blue-50 p-4 mb-4 text-sm text-gray-700 flex items-center justify-between gap-3">
                    <span><?php echo e(__('settings.otp_hint')); ?></span>
                    <a href="https://fatoora.zatca.gov.sa/" target="_blank" rel="noopener"
                       class="whitespace-nowrap rounded-lg py-2 px-4 text-white text-xs font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                        <?php echo e(__('settings.get_otp_from_fatoora')); ?>

                    </a>
                </div>

                <form method="POST" action="<?php echo e(route('settings.onboarding.store')); ?>"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="branch_id" value="<?php echo e($selectedBranchId); ?>">
                    <input type="hidden" name="branchs_id" value="<?php echo e($selectedBranchId); ?>">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.invoice_type')); ?></label>
                        <select name="invoice_type" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            <option value="1100" <?php if(old('invoice_type', $zakatSetting->invoice_type) == '1100'): echo 'selected'; endif; ?>><?php echo e(__('settings.invoice_type_both')); ?></option>
                            <option value="1000" <?php if(old('invoice_type', $zakatSetting->invoice_type) == '1000'): echo 'selected'; endif; ?>><?php echo e(__('settings.invoice_type_standard')); ?></option>
                            <option value="0100" <?php if(old('invoice_type', $zakatSetting->invoice_type) == '0100'): echo 'selected'; endif; ?>><?php echo e(__('settings.invoice_type_simplified')); ?></option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.connection_type')); ?></label>
                        <select name="is_production" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            <option value="0" <?php if(!old('is_production', $zakatSetting->is_production)): echo 'selected'; endif; ?>><?php echo e(__('settings.connection_type_simulation')); ?></option>
                            <option value="1" <?php if(old('is_production', $zakatSetting->is_production)): echo 'selected'; endif; ?>><?php echo e(__('settings.connection_type_production')); ?></option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.otp_label')); ?></label>
                        <input type="text" name="otp" value="<?php echo e(old('otp')); ?>" required
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div class="md:col-span-2 flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            <?php echo e(__('settings.certificate_status')); ?>:
                            <?php if($zakatSetting->production_certificate): ?>
                                <span class="text-green-600 font-medium"><?php echo e(__('settings.certificate_issued')); ?></span>
                            <?php else: ?>
                                <span class="text-gray-500"><?php echo e(__('settings.certificate_not_issued')); ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="submit"
                            class="rounded-lg py-2 px-6 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            <?php echo e(__('settings.connect_now')); ?>

                        </button>
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/settings/onboarding.blade.php ENDPATH**/ ?>