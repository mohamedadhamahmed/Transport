<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <?php if($employee->exists): ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.employee_number')); ?></label>
        <input type="text" value="<?php echo e($employee->employee_number); ?>" disabled
               class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-500 shadow-sm">
    </div>
    <?php endif; ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.name')); ?> *</label>
        <input type="text" name="name" value="<?php echo e(old('name', $employee->name ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.name_en')); ?></label>
        <input type="text" name="name_en" value="<?php echo e(old('name_en', $employee->name_en ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.national_id')); ?></label>
        <input type="text" name="national_id" value="<?php echo e(old('national_id', $employee->national_id ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.phone')); ?></label>
        <input type="text" name="phone" value="<?php echo e(old('phone', $employee->phone ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.email')); ?></label>
        <input type="email" name="email" value="<?php echo e(old('email', $employee->email ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('employees.job_title')); ?> / <?php echo e(__('employees.department')); ?></p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.job_title')); ?></label>
        <input type="text" name="job_title" value="<?php echo e(old('job_title', $employee->job_title ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.department')); ?></label>
        <input type="text" name="department" value="<?php echo e(old('department', $employee->department ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.branch')); ?></label>
        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <option value="">-</option>
            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($branch->id); ?>" <?php echo e((string) old('branch_id', $employee->branch_id ?? '') === (string) $branch->id ? 'selected' : ''); ?>>
                <?php echo e($branch->name); ?>

            </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.hire_date')); ?></label>
        <input type="date" name="hire_date" value="<?php echo e(old('hire_date', optional($employee->hire_date ?? null)->format('Y-m-d'))); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('employees.basic_salary')); ?> / <?php echo e(__('employees.pay_method')); ?></p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.basic_salary')); ?></label>
        <input type="number" step="0.01" min="0" name="basic_salary" value="<?php echo e(old('basic_salary', $employee->basic_salary ?? 0)); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.allowances')); ?></label>
        <input type="number" step="0.01" min="0" name="allowances" value="<?php echo e(old('allowances', $employee->allowances ?? 0)); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.pay_method')); ?></label>
        <select name="pay_method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <option value="Cash" <?php echo e(old('pay_method', $employee->pay_method ?? 'Cash') === 'Cash' ? 'selected' : ''); ?>><?php echo e(__('employees.pay_method_cash')); ?></option>
            <option value="Bank" <?php echo e(old('pay_method', $employee->pay_method ?? 'Cash') === 'Bank' ? 'selected' : ''); ?>><?php echo e(__('employees.pay_method_bank')); ?></option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.bank_name')); ?></label>
        <input type="text" name="bank_name" value="<?php echo e(old('bank_name', $employee->bank_name ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.iban')); ?></label>
        <input type="text" name="iban" value="<?php echo e(old('iban', $employee->iban ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.national_address')); ?></label>
        <input type="text" name="national_address" value="<?php echo e(old('national_address', $employee->national_address ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('employees.notes')); ?></label>
        <input type="text" name="notes" value="<?php echo e(old('notes', $employee->notes ?? '')); ?>"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
        <?php echo e(__('employees.save')); ?>

    </button>
    <a href="<?php echo e(route('employees.index')); ?>" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        <?php echo e(__('employees.cancel')); ?>

    </a>
</div>
<?php /**PATH C:\xampp\htdocs\factory\resources\views/employees/_form.blade.php ENDPATH**/ ?>