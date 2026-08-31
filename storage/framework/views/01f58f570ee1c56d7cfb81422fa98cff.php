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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10h18M6 15h4M3 6h18v12H3z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight"><?php echo e(__('vouchers.edit_voucher')); ?></h2>
                        <p class="text-white/45 text-xs mt-0.5">
                            #<?php echo e($voucher->voucher_number); ?> -
                            <?php echo e($voucher->isReceipt() ? __('vouchers.receipt_title') : __('vouchers.payment_title')); ?>

                        </p>
                    </div>
                </div>
                <a href="<?php echo e(route('vouchers.show', $voucher)); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('vouchers.cancel')); ?>

                </a>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl p-4">
                    <ul class="list-disc ps-5 space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('vouchers.update', $voucher)); ?>" id="voucher-form" class="space-y-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="treasury_account_id" id="treasury_account_id" value="<?php echo e(old('treasury_account_id', $voucher->treasury_account_id)); ?>">
                <input type="hidden" name="counterpart_account_id" id="counterpart_account_id" value="<?php echo e(old('counterpart_account_id', $voucher->counterpart_account_id)); ?>">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.treasury_account')); ?></label>
                            <div class="relative">
                                <input type="text" id="treasury-search" autocomplete="off" placeholder="<?php echo e(__('vouchers.select_treasury_placeholder')); ?>"
                                       value="<?php echo e($voucher->treasuryAccount?->name); ?>"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <div id="treasury-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo e($voucher->isReceipt() ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment')); ?>

                            </label>
                            <div class="relative">
                                <input type="text" id="counterpart-search" autocomplete="off" placeholder="<?php echo e(__('vouchers.search_account_placeholder')); ?>"
                                       value="<?php echo e($voucher->counterpartAccount?->name); ?>"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <div id="counterpart-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.amount')); ?></label>
                            <input type="number" step="0.01" min="0.01" name="amount" value="<?php echo e(old('amount', $voucher->amount)); ?>" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.voucher_date')); ?></label>
                            <input type="date" name="voucher_date" value="<?php echo e(old('voucher_date', $voucher->voucher_date->toDateString())); ?>" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.branch')); ?></label>
                            <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id', $voucher->branch_id) == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.cost_center')); ?></label>
                            <select name="cost_center_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                <?php $__currentLoopData = $costCenters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cc->id); ?>" <?php if(old('cost_center_id', $voucher->cost_center_id) == $cc->id): echo 'selected'; endif; ?>><?php echo e($cc->cost_center_ar); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.description')); ?></label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]"><?php echo e(old('description', $voucher->description)); ?></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('vouchers.show', $voucher)); ?>" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                        <?php echo e(__('vouchers.cancel')); ?>

                    </a>
                    <button type="submit" id="submit-btn"
                            class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                        <?php echo e(__('vouchers.save')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        function wirePicker(searchId, resultsId, hiddenId, scope) {
            const searchInput = document.getElementById(searchId);
            const resultsBox = document.getElementById(resultsId);
            const hiddenInput = document.getElementById(hiddenId);
            let debounceTimer = null;

            async function runSearch(q) {
                const scopeParam = scope ? `&scope=${encodeURIComponent(scope)}` : '';
                const res = await fetch(`<?php echo e(route('accounts.search')); ?>?q=${encodeURIComponent(q)}${scopeParam}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const rows = await res.json();

                resultsBox.innerHTML = '';
                if (!rows.length) {
                    resultsBox.classList.add('hidden');
                    return;
                }
                rows.forEach((row) => {
                    const div = document.createElement('div');
                    div.className = 'px-3 py-2 text-sm hover:bg-[#1456E8]/5 cursor-pointer transition';
                    div.textContent = `${row.name}${row.account_number ? ' (#' + row.account_number + ')' : ''}`;
                    div.addEventListener('click', () => {
                        hiddenInput.value = row.id;
                        searchInput.value = div.textContent;
                        resultsBox.classList.add('hidden');
                    });
                    resultsBox.appendChild(div);
                });
                resultsBox.classList.remove('hidden');
            }

            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                hiddenInput.value = '';
                const q = searchInput.value.trim();
                debounceTimer = setTimeout(() => runSearch(q), 250);
            });

            // مقصود: عند التركيز على حقل الخزينة (نطاق محدود مسبقًا) بيظهر
            // كل حسابات الخزينة/البنوك فورًا من غير ما تكتبي حاجة.
            if (scope) {
                searchInput.addEventListener('focus', () => {
                    if (!searchInput.value.trim()) {
                        runSearch('');
                    }
                });
            }

            document.addEventListener('click', (e) => {
                if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                    resultsBox.classList.add('hidden');
                }
            });
        }

        wirePicker('treasury-search', 'treasury-results', 'treasury_account_id', 'treasury');
        wirePicker('counterpart-search', 'counterpart-results', 'counterpart_account_id');

        const form = document.getElementById('voucher-form');
        const submitBtn = document.getElementById('submit-btn');
        let isSubmitting = false;

        form.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            if (!document.getElementById('treasury_account_id').value || !document.getElementById('counterpart_account_id').value) {
                e.preventDefault();
                alert(<?php echo json_encode(__('vouchers.select_both_accounts'), 15, 512) ?>);
                return;
            }
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.textContent = <?php echo json_encode(__('vouchers.saving_please_wait'), 15, 512) ?>;
        });
    })();
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/vouchers/edit.blade.php ENDPATH**/ ?>