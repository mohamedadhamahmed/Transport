<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.name')); ?> *</label>
        <input type="text" name="name" value="<?php echo e(old('name', $customer->name ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.phone')); ?> *</label>
        <input type="text" name="phone" value="<?php echo e(old('phone', $customer->phone ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.email')); ?></label>
        <input type="email" name="email" value="<?php echo e(old('email', $customer->email ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.company_name')); ?></label>
        <input type="text" name="company_name" value="<?php echo e(old('company_name', $customer->company_name ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.tax_number')); ?></label>
        <input type="text" name="tax_number" value="<?php echo e(old('tax_number', $customer->tax_number ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.crn')); ?></label>
        <input type="text" name="commercial_registration_number" value="<?php echo e(old('commercial_registration_number', $customer->commercial_registration_number ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.credit_limit')); ?></label>
        <input type="number" step="0.01" min="0" name="credit_limit" value="<?php echo e(old('credit_limit', $customer->credit_limit ?? 10000)); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.grace_period_days')); ?></label>
        <input type="number" step="1" min="0" name="grace_period_days" value="<?php echo e(old('grace_period_days', $customer->grace_period_days ?? 30)); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <?php if(!($customer->exists ?? false)): ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.opening_balance')); ?></label>
        <input type="number" step="0.01" name="opening_balance" value="<?php echo e(old('opening_balance', 0)); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <?php endif; ?>
    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.notes')); ?></label>
        <input type="text" name="notes" value="<?php echo e(old('notes', $customer->notes ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('customers.national_address')); ?></p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.city')); ?></label>
        <input type="text" name="city" value="<?php echo e(old('city', $customer->city ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.district')); ?></label>
        <input type="text" name="district" value="<?php echo e(old('district', $customer->district ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.street_name')); ?></label>
        <input type="text" name="street_name" value="<?php echo e(old('street_name', $customer->street_name ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.building_number')); ?></label>
        <input type="text" name="building_number" value="<?php echo e(old('building_number', $customer->building_number ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.plot_identification')); ?></label>
        <input type="text" name="plot_identification" value="<?php echo e(old('plot_identification', $customer->plot_identification ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('customers.postal_code')); ?></label>
        <input type="text" name="postal_code" value="<?php echo e(old('postal_code', $customer->postal_code ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
        <?php echo e(__('customers.save')); ?>

    </button>
    <a href="<?php echo e(route('customers.index')); ?>" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        <?php echo e(__('customers.cancel')); ?>

    </a>
</div>
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/customers/_form.blade.php ENDPATH**/ ?>