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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('settings.general_settings')); ?></h2>
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
                <div class="font-semibold text-gray-700 mb-4"><?php echo e(__('settings.company_general_info')); ?></div>

                <form method="POST" action="<?php echo e(route('settings.system.update')); ?>" enctype="multipart/form-data"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="branchs_id" value="<?php echo e($selectedBranchId); ?>">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.name_ar')); ?></label>
                        <input type="text" name="name_ar" value="<?php echo e(old('name_ar', $systemSetting->name_ar)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.name_en')); ?></label>
                        <input type="text" name="name_en" value="<?php echo e(old('name_en', $systemSetting->name_en)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.commercial_record')); ?> (SR)</label>
                        <input type="text" name="SR" value="<?php echo e(old('SR', $systemSetting->SR)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.tax_number')); ?> (Tax)</label>
                        <input type="text" name="Tax" value="<?php echo e(old('Tax', $systemSetting->Tax)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.address_ar')); ?></label>
                        <input type="text" name="address_ar" value="<?php echo e(old('address_ar', $systemSetting->address_ar)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.address_en')); ?></label>
                        <input type="text" name="address_en" value="<?php echo e(old('address_en', $systemSetting->address_en)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.service_cost')); ?></label>
                        <input type="number" step="0.01" name="serviceCost" value="<?php echo e(old('serviceCost', $systemSetting->serviceCost)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.delivery_cost')); ?></label>
                        <input type="number" step="0.01" name="deliveryCost" value="<?php echo e(old('deliveryCost', $systemSetting->deliveryCost)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.bank_name')); ?></label>
                        <input type="text" name="bankname" value="<?php echo e(old('bankname', $systemSetting->bankname)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.bank_account_number')); ?></label>
                        <input type="text" name="bank_acount_number" value="<?php echo e(old('bank_acount_number', $systemSetting->bank_acount_number)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.iban')); ?></label>
                        <input type="text" name="bank_acount_iban" value="<?php echo e(old('bank_acount_iban', $systemSetting->bank_acount_iban)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.company_logo')); ?></label>
                        <input type="file" name="logo" class="w-full text-sm">
                        <?php if($systemSetting->logo && $systemSetting->logo !== 'empty'): ?>
                            <img src="<?php echo e(asset('assets/img/brand/' . $systemSetting->logo)); ?>" class="h-16 mt-2 rounded">
                        <?php endif; ?>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="rounded-lg py-2 px-6 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            <?php echo e(__('settings.save_company_info')); ?>

                        </button>
                    </div>
                </form>
            </div>

            
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-semibold text-gray-700"><?php echo e(__('settings.zakat_zatca_settings')); ?></div>
                    <a href="<?php echo e(route('settings.onboarding', ['branch_id' => $selectedBranchId])); ?>"
                       class="rounded-lg py-2 px-4 text-white text-xs font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                        <?php echo e(__('settings.go_to_onboarding')); ?>

                    </a>
                </div>

                <form method="POST" action="<?php echo e(route('settings.zakat.update')); ?>"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="branchs_id" value="<?php echo e($selectedBranchId); ?>">
                    <input type="hidden" name="company_id" value="<?php echo e(old('company_id', $zakatSetting->company_id ?? 1)); ?>">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.facility_name')); ?></label>
                        <input type="text" name="name" value="<?php echo e(old('name', $zakatSetting->name)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.mobile')); ?></label>
                        <input type="text" name="mobile" value="<?php echo e(old('mobile', $zakatSetting->mobile)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.trn')); ?></label>
                        <input type="number" name="trn" value="<?php echo e(old('trn', $zakatSetting->trn)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.crn')); ?></label>
                        <input type="number" name="crn" value="<?php echo e(old('crn', $zakatSetting->crn)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.street_name')); ?></label>
                        <input type="text" name="street_name" value="<?php echo e(old('street_name', $zakatSetting->street_name)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.building_number')); ?></label>
                        <input type="number" name="building_number" value="<?php echo e(old('building_number', $zakatSetting->building_number)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.plot_identification')); ?></label>
                        <input type="number" name="plot_identification" value="<?php echo e(old('plot_identification', $zakatSetting->plot_identification)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.postal_number')); ?></label>
                        <input type="number" name="postal_number" value="<?php echo e(old('postal_number', $zakatSetting->postal_number)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.region')); ?></label>
                        <input type="text" name="region" value="<?php echo e(old('region', $zakatSetting->region)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.city')); ?></label>
                        <input type="text" name="city" value="<?php echo e(old('city', $zakatSetting->city)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.business_category')); ?></label>
                        <select name="business_category" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            <?php $__currentLoopData = ['IT', 'Food', 'Film Festivals']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat); ?>" <?php if(old('business_category', $zakatSetting->business_category) == $cat): echo 'selected'; endif; ?>><?php echo e($cat); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.invoice_type')); ?></label>
                        <select name="invoice_type" class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                            <?php $__currentLoopData = ['1100', '0100', '1000']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>" <?php if(old('invoice_type', $zakatSetting->invoice_type) == $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.email_address')); ?></label>
                        <input type="email" name="email_address" value="<?php echo e(old('email_address', $zakatSetting->email_address)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="is_production" value="1"
                               <?php if(old('is_production', $zakatSetting->is_production)): echo 'checked'; endif; ?>
                               class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                        <label class="text-sm text-gray-600"><?php echo e(__('settings.production_mode')); ?></label>
                    </div>

                    <div class="md:col-span-2 border-t pt-4 mt-2 text-xs text-gray-500">
                        <?php echo e(__('settings.certificate_fields_hint')); ?>

                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.common_name')); ?></label>
                        <input type="text" name="common_name" value="<?php echo e(old('common_name', $zakatSetting->common_name)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.organization_name')); ?></label>
                        <input type="text" name="organization_name" value="<?php echo e(old('organization_name', $zakatSetting->organization_name)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.organization_unit_name')); ?></label>
                        <input type="text" name="organization_unit_name" value="<?php echo e(old('organization_unit_name', $zakatSetting->organization_unit_name)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.country_name')); ?></label>
                        <input type="text" name="country_name" maxlength="5" value="<?php echo e(old('country_name', $zakatSetting->country_name ?? 'SA')); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.egs_serial_number')); ?></label>
                        <input type="text" name="egs_serial_number" value="<?php echo e(old('egs_serial_number', $zakatSetting->egs_serial_number)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block"><?php echo e(__('settings.registered_address')); ?></label>
                        <input type="text" name="registered_address" value="<?php echo e(old('registered_address', $zakatSetting->registered_address)); ?>"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="rounded-lg py-2 px-6 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            <?php echo e(__('settings.save_zakat_info')); ?>

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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/settings/index.blade.php ENDPATH**/ ?>