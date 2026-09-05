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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10h18M6 15h4M3 6h18v12H3z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e($type === 'receipt' ? __('vouchers.new_receipt') : __('vouchers.new_payment')); ?></h2>
                </div>
                <a href="<?php echo e(route('vouchers.index', ['type' => $type])); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('vouchers.back_to_list')); ?>

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

            <form method="POST" action="<?php echo e(route('vouchers.store')); ?>" id="voucher-form" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="type" value="<?php echo e($type); ?>">
                <input type="hidden" name="treasury_account_id" id="treasury_account_id" value="<?php echo e(old('treasury_account_id')); ?>">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.treasury_account')); ?></label>
                            <div class="relative">
                                <input type="text" id="treasury-search" autocomplete="off" placeholder="<?php echo e(__('vouchers.select_treasury_placeholder')); ?>"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <div id="treasury-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.voucher_date')); ?></label>
                            <input type="date" name="voucher_date" value="<?php echo e(old('voucher_date', now()->toDateString())); ?>" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.branch')); ?></label>
                            <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id') == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('vouchers.description')); ?></label>
                            <input type="text" name="description" value="<?php echo e(old('description')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800"><?php echo e(__('vouchers.lines')); ?></h3>
                        <button type="button" id="add-line-btn"
                                class="px-3 py-1.5 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] text-sm font-medium hover:bg-[#0F1B4C]/10 transition">
                            + <?php echo e(__('vouchers.add_line')); ?>

                        </button>
                    </div>

                    <div id="lines-container" class="space-y-4"></div>

                    <div class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                        <span class="text-sm text-gray-500"><?php echo e(__('vouchers.grand_total')); ?></span>
                        <span id="grand-total" class="text-xl font-bold text-[#0F1B4C]">0.00</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('vouchers.index', ['type' => $type])); ?>" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
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
        const COST_CENTERS = <?php echo json_encode($costCenters->map(fn ($cc) => ['id' => $cc->id, 'name' => $cc->cost_center_ar]), 512) ?>;
        const TAXES = <?php echo json_encode($taxes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'rate' => (float) $t->rate])) ?>;

        const I18N = {
            counterpartLabel: <?php echo json_encode($type === 'receipt' ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment'), 15, 512) ?>,
            searchPlaceholder: <?php echo json_encode(__('vouchers.search_account_placeholder'), 15, 512) ?>,
            amount: <?php echo json_encode(__('vouchers.amount'), 15, 512) ?>,
            costCenter: <?php echo json_encode(__('vouchers.line_cost_center'), 15, 512) ?>,
            description: <?php echo json_encode(__('vouchers.line_description'), 15, 512) ?>,
            removeLine: <?php echo json_encode(__('vouchers.remove_line'), 15, 512) ?>,
            lineNo: <?php echo json_encode(__('vouchers.line_no'), 15, 512) ?>,
            subjectToTax: <?php echo json_encode(__('vouchers.subject_to_tax'), 15, 512) ?>,
            selectTax: <?php echo json_encode(__('vouchers.select_tax'), 15, 512) ?>,
            netAmount: <?php echo json_encode(__('vouchers.net_amount'), 15, 512) ?>,
            taxAmount: <?php echo json_encode(__('vouchers.tax_amount'), 15, 512) ?>,
            noActiveTaxes: <?php echo json_encode(__('vouchers.no_active_taxes'), 15, 512) ?>,
            needAtLeastOneLine: <?php echo json_encode(__('vouchers.need_at_least_one_line'), 15, 512) ?>,
            selectBothAccounts: <?php echo json_encode(__('vouchers.select_both_accounts'), 15, 512) ?>,
            savingPleaseWait: <?php echo json_encode(__('vouchers.saving_please_wait'), 15, 512) ?>,
        };

        const linesContainer = document.getElementById('lines-container');
        const addLineBtn = document.getElementById('add-line-btn');
        let rowCounter = 0;

        function money(n) {
            return (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
        }

        function buildSelectOptions(items, valueKey, labelFn, placeholder) {
            let html = `<option value="">${placeholder}</option>`;
            items.forEach((item) => {
                html += `<option value="${item[valueKey]}">${labelFn(item)}</option>`;
            });
            return html;
        }

        function addLine(prefill) {
            const index = rowCounter++;
            const card = document.createElement('div');
            card.className = 'line-card border border-gray-200 rounded-xl p-4 space-y-3';
            card.dataset.index = index;

            const costCenterOptions = buildSelectOptions(COST_CENTERS, 'id', (c) => c.name, '-');
            const taxOptions = TAXES.length
                ? buildSelectOptions(TAXES, 'id', (t) => `${t.name} (${(Math.round(t.rate * 100) / 100).toString().replace(/\.?0+$/, '')}%)`, '')
                : `<option value="">${I18N.noActiveTaxes}</option>`;

            card.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-400">${I18N.lineNo} #${index + 1}</span>
                    <button type="button" class="remove-line-btn text-red-500 hover:text-red-700 text-xs font-medium">${I18N.removeLine}</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${I18N.counterpartLabel}</label>
                        <input type="hidden" name="lines[${index}][counterpart_account_id]" class="counterpart-id-input">
                        <div class="relative">
                            <input type="text" autocomplete="off" placeholder="${I18N.searchPlaceholder}"
                                   class="counterpart-search-input w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <div class="counterpart-results absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${I18N.amount}</label>
                        <input type="number" step="0.01" min="0.01" name="lines[${index}][amount]" class="amount-input w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${I18N.costCenter}</label>
                        <select name="lines[${index}][cost_center_id]" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">${costCenterOptions}</select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${I18N.description}</label>
                        <input type="text" name="lines[${index}][description]" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>
                <div class="border-t border-gray-100 pt-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="lines[${index}][is_taxable]" value="1" class="is-taxable-checkbox rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                        <span class="text-sm font-medium text-gray-700">${I18N.subjectToTax}</span>
                    </label>
                    <div class="tax-fields hidden mt-3 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">${I18N.selectTax}</label>
                                <select name="lines[${index}][tax_id]" class="tax-select w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">${taxOptions}</select>
                            </div>
                            <div class="bg-gray-50 rounded-lg px-3 py-2">
                                <div class="text-xs text-gray-400">${I18N.netAmount}</div>
                                <div class="net-amount-display font-semibold text-gray-700 mt-0.5">0.00</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg px-3 py-2">
                                <div class="text-xs text-gray-400">${I18N.taxAmount}</div>
                                <div class="tax-amount-display font-semibold text-gray-700 mt-0.5">0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            linesContainer.appendChild(card);
            wireLine(card);

            if (prefill) {
                const counterpartIdInput = card.querySelector('.counterpart-id-input');
                const counterpartSearchInput = card.querySelector('.counterpart-search-input');
                const amountInput = card.querySelector('.amount-input');
                const costCenterSelect = card.querySelector('select[name$="[cost_center_id]"]');
                const descriptionInput = card.querySelector('input[name$="[description]"]');
                const taxableCheckbox = card.querySelector('.is-taxable-checkbox');
                const taxSelect = card.querySelector('.tax-select');

                if (prefill.counterpart_account_id) {
                    counterpartIdInput.value = prefill.counterpart_account_id;
                    counterpartSearchInput.value = prefill.counterpart_account_name || '';
                }
                if (prefill.amount !== undefined && prefill.amount !== null) {
                    amountInput.value = prefill.amount;
                }
                if (prefill.cost_center_id) {
                    costCenterSelect.value = prefill.cost_center_id;
                }
                if (prefill.description) {
                    descriptionInput.value = prefill.description;
                }
                if (prefill.is_taxable) {
                    taxableCheckbox.checked = true;
                    if (prefill.tax_id) {
                        taxSelect.value = prefill.tax_id;
                    }
                    taxableCheckbox.dispatchEvent(new Event('change'));
                }
            }

            recalcGrandTotal();
            return card;
        }

        function wireLine(card) {
            const searchInput = card.querySelector('.counterpart-search-input');
            const resultsBox = card.querySelector('.counterpart-results');
            const hiddenInput = card.querySelector('.counterpart-id-input');
            const amountInput = card.querySelector('.amount-input');
            const removeBtn = card.querySelector('.remove-line-btn');
            const taxableCheckbox = card.querySelector('.is-taxable-checkbox');
            const taxFields = card.querySelector('.tax-fields');
            const taxSelect = card.querySelector('.tax-select');
            const netDisplay = card.querySelector('.net-amount-display');
            const taxDisplay = card.querySelector('.tax-amount-display');
            let debounceTimer = null;

            async function runSearch(q) {
                const res = await fetch(`<?php echo e(route('accounts.search')); ?>?q=${encodeURIComponent(q)}`, {
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

            document.addEventListener('click', (e) => {
                if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                    resultsBox.classList.add('hidden');
                }
            });

            function recalcTax() {
                if (!taxableCheckbox.checked) { return; }
                const amount = parseFloat(amountInput.value) || 0;
                const rate = parseFloat((taxSelect.selectedOptions[0] && taxSelect.selectedOptions[0].dataset.rate) || 0);
                const selectedTax = TAXES.find((t) => String(t.id) === taxSelect.value);
                const effectiveRate = selectedTax ? selectedTax.rate : rate;
                const net = effectiveRate > 0 ? (amount * 100) / (100 + effectiveRate) : amount;
                const tax = amount - net;
                netDisplay.textContent = money(net);
                taxDisplay.textContent = money(tax);
            }

            function toggleTaxFields() {
                taxFields.classList.toggle('hidden', !taxableCheckbox.checked);
                recalcTax();
            }

            taxableCheckbox.addEventListener('change', toggleTaxFields);
            taxSelect.addEventListener('change', recalcTax);
            amountInput.addEventListener('input', () => {
                recalcTax();
                recalcGrandTotal();
            });

            removeBtn.addEventListener('click', () => {
                if (linesContainer.querySelectorAll('.line-card').length <= 1) {
                    alert(I18N.needAtLeastOneLine);
                    return;
                }
                card.remove();
                recalcGrandTotal();
            });
        }

        function recalcGrandTotal() {
            let total = 0;
            linesContainer.querySelectorAll('.amount-input').forEach((input) => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('grand-total').textContent = money(total);
        }

        addLineBtn.addEventListener('click', () => addLine());

        // ابدأ ببند واحد فاضي (الحد الأدنى لأي سند)
        addLine();

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
            // كل حسابات الخزينة/البنوك فورًا من غير ما تكتب حاجة.
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

        const form = document.getElementById('voucher-form');
        const submitBtn = document.getElementById('submit-btn');
        let isSubmitting = false;

        form.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            const treasuryFilled = !!document.getElementById('treasury_account_id').value;
            const allLinesFilled = Array.from(linesContainer.querySelectorAll('.line-card')).every(
                (card) => !!card.querySelector('.counterpart-id-input').value
            );

            if (!treasuryFilled || !allLinesFilled || !linesContainer.querySelectorAll('.line-card').length) {
                e.preventDefault();
                alert(I18N.selectBothAccounts);
                return;
            }

            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.textContent = I18N.savingPleaseWait;
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/vouchers/create.blade.php ENDPATH**/ ?>