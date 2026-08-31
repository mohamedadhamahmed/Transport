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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16M4 12h16M4 18h10"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e($type === 'opening' ? __('journal_entries.new_opening_entry') : __('journal_entries.new_daily_entry')); ?></h2>
                </div>
                <a href="<?php echo e(route('journal-entries.index', ['type' => $type])); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('journal_entries.back_to_list')); ?>

                </a>
            </div>

            <?php if($type === 'opening'): ?>
                <div class="bg-amber-50 border border-amber-100 text-amber-800 text-sm rounded-xl p-4">
                    <?php echo e(__('journal_entries.opening_hint')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl p-4">
                    <ul class="list-disc ps-5 space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('journal-entries.store')); ?>" id="entry-form" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="entry_type" value="<?php echo e($type); ?>">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('journal_entries.entry_date')); ?></label>
                            <input type="date" name="entry_date" value="<?php echo e(old('entry_date', now()->toDateString())); ?>" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('journal_entries.branch')); ?></label>
                            <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php if(old('branch_id') == $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('journal_entries.cost_center')); ?></label>
                            <select name="cost_center_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                <?php $__currentLoopData = $costCenters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cc->id); ?>" <?php if(old('cost_center_id') == $cc->id): echo 'selected'; endif; ?>><?php echo e($cc->cost_center_ar); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('journal_entries.description')); ?></label>
                            <input type="text" name="description" value="<?php echo e(old('description')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800"><?php echo e(__('journal_entries.lines')); ?></h3>
                        <button type="button" id="add-line-btn"
                                class="px-3 py-1.5 rounded-lg bg-[#0F1B4C]/5 text-[#0F1B4C] text-sm font-medium hover:bg-[#0F1B4C]/10 transition">
                            <?php echo e(__('journal_entries.add_line')); ?>

                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide w-1/3"><?php echo e(__('journal_entries.account')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.debit')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.credit')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('journal_entries.note')); ?></th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body" class="divide-y divide-gray-100 bg-white"></tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-3 py-2.5 text-gray-600"><?php echo e(__('journal_entries.total_debit')); ?> / <?php echo e(__('journal_entries.total_credit')); ?></td>
                                    <td class="px-3 py-2.5 text-emerald-700" id="total-debit">0.00</td>
                                    <td class="px-3 py-2.5 text-red-600" id="total-credit">0.00</td>
                                    <td colspan="2" class="px-3 py-2.5">
                                        <span id="balance-indicator" class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500"><?php echo e(__('journal_entries.not_balanced_hint')); ?></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('journal-entries.index', ['type' => $type])); ?>" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                        <?php echo e(__('journal_entries.cancel')); ?>

                    </a>
                    <button type="submit" id="submit-btn" disabled
                            class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        <?php echo e(__('journal_entries.save')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const linesBody = document.getElementById('lines-body');
        const addLineBtn = document.getElementById('add-line-btn');
        const submitBtn = document.getElementById('submit-btn');
        const form = document.getElementById('entry-form');
        let rowCounter = 0;

        function money(n) {
            return (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
        }

        function addLine() {
            const index = rowCounter++;
            const tr = document.createElement('tr');
            tr.className = 'line-row';
            tr.dataset.index = index;
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="hidden" name="lines[${index}][account_id]" class="account-id-input">
                    <div class="relative">
                        <input type="text" autocomplete="off" placeholder="<?php echo e(__('journal_entries.search_account_placeholder')); ?>"
                               class="account-search-input w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                        <div class="account-results absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                    </div>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" min="0" name="lines[${index}][debit]" class="debit-input w-28 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" min="0" name="lines[${index}][credit]" class="credit-input w-28 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="lines[${index}][note]" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="remove-line-btn text-red-500 hover:text-red-700 text-xs font-medium"><?php echo e(__('journal_entries.remove_line')); ?></button>
                </td>
            `;
            linesBody.appendChild(tr);
            wireRow(tr);
            return tr;
        }

        function wireRow(tr) {
            const searchInput = tr.querySelector('.account-search-input');
            const resultsBox = tr.querySelector('.account-results');
            const hiddenInput = tr.querySelector('.account-id-input');
            const debitInput = tr.querySelector('.debit-input');
            const creditInput = tr.querySelector('.credit-input');
            const removeBtn = tr.querySelector('.remove-line-btn');
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

            debitInput.addEventListener('input', () => {
                if (parseFloat(debitInput.value) > 0) creditInput.value = '';
                recalc();
            });
            creditInput.addEventListener('input', () => {
                if (parseFloat(creditInput.value) > 0) debitInput.value = '';
                recalc();
            });

            removeBtn.addEventListener('click', () => {
                if (linesBody.querySelectorAll('.line-row').length <= 2) {
                    alert(<?php echo json_encode(__('journal_entries.need_at_least_two_lines'), 15, 512) ?>);
                    return;
                }
                tr.remove();
                recalc();
            });
        }

        function recalc() {
            let totalDebit = 0;
            let totalCredit = 0;
            linesBody.querySelectorAll('.line-row').forEach((tr) => {
                totalDebit += parseFloat(tr.querySelector('.debit-input').value) || 0;
                totalCredit += parseFloat(tr.querySelector('.credit-input').value) || 0;
            });

            document.getElementById('total-debit').textContent = money(totalDebit);
            document.getElementById('total-credit').textContent = money(totalCredit);

            const indicator = document.getElementById('balance-indicator');
            const balanced = totalDebit > 0 && Math.abs(totalDebit - totalCredit) < 0.01;

            if (balanced) {
                indicator.textContent = <?php echo json_encode(__('journal_entries.balanced'), 15, 512) ?>;
                indicator.className = 'text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700';
                submitBtn.disabled = false;
            } else {
                indicator.textContent = <?php echo json_encode(__('journal_entries.not_balanced_hint'), 15, 512) ?>;
                indicator.className = 'text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700';
                submitBtn.disabled = true;
            }
        }

        addLineBtn.addEventListener('click', addLine);

        // ابدأ بسطرين فاضيين (الحد الأدنى لأي قيد)
        addLine();
        addLine();
        recalc();

        let isSubmitting = false;
        form.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.textContent = <?php echo json_encode(__('journal_entries.saving_please_wait'), 15, 512) ?>;
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
<?php /**PATH C:\xampp\htdocs\my-erp\resources\views/journal-entries/create.blade.php ENDPATH**/ ?>