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
                            <path d="M9 21a9 9 0 1 1 9-9"/><path d="M9 8v5h5"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg"><?php echo e(__('purchase_returns.new_return')); ?></h2>
                </div>
                <a href="<?php echo e(route('purchases.returns.index')); ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    <?php echo e(__('purchase_returns.back_to_list')); ?>

                </a>
            </div>

            <?php echo $__env->make('partials.sweet-alert-flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if($errors->any()): ?>
                <div class="bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl p-4">
                    <ul class="list-disc ps-5 space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <div id="search-step" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('purchase_returns.search_purchase')); ?></label>
                <div class="flex gap-2">
                    <input type="text" id="search-input" placeholder="<?php echo e(__('purchase_returns.search_purchase_placeholder')); ?>"
                           class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <button type="button" id="search-btn"
                            class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                        <?php echo e(__('purchase_returns.search')); ?>

                    </button>
                </div>

                <div id="search-results" class="mt-4 divide-y divide-gray-100 border border-gray-100 rounded-lg hidden"></div>
                <div id="search-empty" class="mt-4 text-sm text-gray-400 hidden"><?php echo e(__('purchase_returns.no_purchases_found')); ?></div>
            </div>

            
            <form id="return-form" method="POST" action="<?php echo e(route('purchases.returns.store')); ?>" class="hidden space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="purchase_id" id="purchase_id">
                <input type="hidden" name="items_json" id="items_json">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <h3 class="font-semibold text-gray-800 mb-4"><?php echo e(__('purchase_returns.purchase_info')); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.purchase_no')); ?></div>
                            <div id="info-purchase-no" class="font-medium text-gray-800">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.supplier')); ?></div>
                            <div id="info-supplier" class="font-medium text-gray-800">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1"><?php echo e(__('purchase_returns.payment_method')); ?></div>
                            <div id="info-payment-method" class="font-medium text-gray-800">-</div>
                        </div>
                    </div>

                    <div id="refund-account-wrapper" class="mt-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('purchase_returns.refund_account')); ?></label>
                        <select name="refund_account_id" id="refund_account_id"
                                class="w-full md:w-1/2 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value=""><?php echo e(__('purchase_returns.choose_refund_account')); ?></option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1"><?php echo e(__('purchase_returns.refund_account_hint')); ?></p>
                    </div>
                    <div id="refund-credit-note" class="mt-4 hidden text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-4 py-2">
                        <?php echo e(__('purchase_returns.refund_credit_note')); ?>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('purchase_returns.return_date')); ?></label>
                            <input type="date" name="return_date" value="<?php echo e(now()->toDateString()); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('purchase_returns.reason')); ?></label>
                            <input type="text" name="reason" placeholder="<?php echo e(__('purchase_returns.reason_placeholder')); ?>"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <h3 class="font-semibold text-gray-800 mb-4"><?php echo e(__('purchase_returns.items')); ?></h3>

                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.code')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.product')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.original_qty')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.already_returned')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.remaining_qty')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide"><?php echo e(__('purchase_returns.unit_price')); ?></th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide w-32"><?php echo e(__('purchase_returns.return_qty')); ?></th>
                                </tr>
                            </thead>
                            <tbody id="items-body" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchase_returns.subtotal')); ?></div>
                            <div id="totals-subtotal" class="font-semibold text-[#0F1B4C]">0.00</div>
                        </div>
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1"><?php echo e(__('purchase_returns.tax_total')); ?></div>
                            <div id="totals-tax" class="font-semibold text-[#0F1B4C]">0.00</div>
                        </div>
                        <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden md:col-span-2">
                            <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                            <div class="text-xs text-white/50 mb-1"><?php echo e(__('purchase_returns.grand_total')); ?></div>
                            <div id="totals-grand" class="font-bold text-lg">0.00</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <a href="<?php echo e(route('purchases.returns.index')); ?>" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                            <?php echo e(__('purchase_returns.cancel')); ?>

                        </a>
                        <button type="submit" id="submit-btn"
                                class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                            <?php echo e(__('purchase_returns.save_return')); ?>

                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <script>
    (function () {
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const searchResults = document.getElementById('search-results');
        const searchEmpty = document.getElementById('search-empty');
        const searchStep = document.getElementById('search-step');
        const form = document.getElementById('return-form');
        const itemsBody = document.getElementById('items-body');
        const purchaseIdInput = document.getElementById('purchase_id');
        const itemsJsonInput = document.getElementById('items_json');
        const refundWrapper = document.getElementById('refund-account-wrapper');
        const refundSelect = document.getElementById('refund_account_id');
        const refundCreditNote = document.getElementById('refund-credit-note');

        let currentItems = [];

        function money(n) {
            return (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
        }

        async function runSearch() {
            const q = searchInput.value.trim();
            const res = await fetch(`<?php echo e(route('purchases.returns.search')); ?>?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const rows = await res.json();

            searchResults.innerHTML = '';
            if (!rows.length) {
                searchResults.classList.add('hidden');
                searchEmpty.classList.remove('hidden');
                return;
            }
            searchEmpty.classList.add('hidden');
            searchResults.classList.remove('hidden');

            rows.forEach((row) => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between px-4 py-3 hover:bg-[#1456E8]/5 cursor-pointer transition';
                div.innerHTML = `
                    <div>
                        <div class="font-medium text-gray-800">#${row.purchase_number ?? row.id}</div>
                        <div class="text-xs text-gray-400">${row.supplier_name} - ${row.created_at ?? ''}</div>
                    </div>
                    <div class="text-sm font-semibold text-[#0F1B4C]">${money(row.grand_total)}</div>
                `;
                div.addEventListener('click', () => selectPurchase(row.id));
                searchResults.appendChild(div);
            });
        }

        async function selectPurchase(purchaseId) {
            const res = await fetch(`<?php echo e(url('/purchases/returns')); ?>/${purchaseId}/items`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();

            purchaseIdInput.value = data.purchase.id;
            document.getElementById('info-purchase-no').textContent = '#' + (data.purchase.purchase_number ?? data.purchase.id);
            document.getElementById('info-supplier').textContent = data.purchase.supplier_name ?? '-';
            document.getElementById('info-payment-method').textContent = data.purchase.is_credit
                ? <?php echo json_encode(__('purchase_returns.credit'), 15, 512) ?>
                : (data.purchase.payment_account_name ?? <?php echo json_encode(__('purchase_returns.payment_immediate'), 15, 512) ?>);

            if (data.purchase.is_credit) {
                refundWrapper.classList.add('hidden');
                refundCreditNote.classList.remove('hidden');
                refundSelect.removeAttribute('name');
            } else {
                refundCreditNote.classList.add('hidden');
                refundWrapper.classList.remove('hidden');
                refundSelect.setAttribute('name', 'refund_account_id');
                await loadRefundAccounts(data.purchase.branch_id, data.purchase.payment_account_id);
            }

            currentItems = data.items;
            renderItems();

            searchStep.classList.add('hidden');
            form.classList.remove('hidden');
        }

        async function loadRefundAccounts(branchId, defaultAccountId) {
            refundSelect.innerHTML = `<option value=""><?php echo e(__('purchase_returns.choose_refund_account')); ?></option>`;
            const res = await fetch(`<?php echo e(url('/purchases/returns/refund-accounts')); ?>/${branchId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const accounts = await res.json();
            accounts.forEach((acc) => {
                const opt = document.createElement('option');
                opt.value = acc.id;
                opt.textContent = acc.name;
                if (defaultAccountId && Number(defaultAccountId) === Number(acc.id)) {
                    opt.selected = true;
                }
                refundSelect.appendChild(opt);
            });
        }

        function renderItems() {
            itemsBody.innerHTML = '';
            currentItems.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-[#1456E8]/5 transition';
                tr.innerHTML = `
                    <td class="px-3 py-2 text-gray-400 text-xs">${item.code ?? '-'}</td>
                    <td class="px-3 py-2 font-medium text-gray-800">${item.name ?? '-'}</td>
                    <td class="px-3 py-2 text-gray-600">${item.quantity}</td>
                    <td class="px-3 py-2 text-gray-600">${item.returned_quantity}</td>
                    <td class="px-3 py-2 text-gray-600">${item.remaining_quantity}</td>
                    <td class="px-3 py-2 text-gray-600">${money(item.unit_price)}</td>
                    <td class="px-3 py-2">
                        <input type="number" min="0" max="${item.remaining_quantity}" step="0.01" value="0"
                               data-index="${index}"
                               class="return-qty-input w-24 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </td>
                `;
                itemsBody.appendChild(tr);
            });

            itemsBody.querySelectorAll('.return-qty-input').forEach((input) => {
                input.addEventListener('input', recalcTotals);
            });
            recalcTotals();
        }

        function recalcTotals() {
            let subtotal = 0;
            let tax = 0;

            itemsBody.querySelectorAll('.return-qty-input').forEach((input) => {
                const item = currentItems[Number(input.dataset.index)];
                const qty = Math.min(Math.max(parseFloat(input.value) || 0, 0), item.remaining_quantity);

                const itemQty = item.quantity || 1;
                const unitNet = ((item.unit_price * itemQty) - item.discount_amount) / itemQty;
                const lineSubtotal = unitNet * qty;
                const lineTax = lineSubtotal * item.tax_rate;

                subtotal += lineSubtotal;
                tax += lineTax;
            });

            document.getElementById('totals-subtotal').textContent = money(subtotal);
            document.getElementById('totals-tax').textContent = money(tax);
            document.getElementById('totals-grand').textContent = money(subtotal + tax);
        }

        searchBtn.addEventListener('click', runSearch);
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                runSearch();
            }
        });

        // أول تحميل للصفحة: نجيب كل الفواتير القابلة للإرجاع (بدون فلترة)
        runSearch();

        // منع الحفظ المتكرر: لو ضغطتِ "حفظ" أكتر من مرة بسرعة (دبل كليك، أو
        // ضغط تاني قبل ما الصفحة تحمّل صفحة النتيجة)، كانت بتتبعت أكتر من
        // request بنفس الكميات - الطلب التاني كان بيوصل بعد ما الأول خلاص
        // نقص "المتاح للإرجاع" فعليًا، فكان بيظهر خطأ "الكمية أكبر من
        // المتاح" (مش باج في الحساب - ده تحديدًا اللي كان بيحصل معاكي).
        // الحل: تعطيل الزرار فورًا بعد أول ضغطة ومنع أي submit تاني تمامًا.
        const submitBtn = document.getElementById('submit-btn');
        let isSubmitting = false;

        form.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            const rows = [];
            itemsBody.querySelectorAll('.return-qty-input').forEach((input) => {
                const qty = parseFloat(input.value) || 0;
                if (qty > 0) {
                    const item = currentItems[Number(input.dataset.index)];
                    rows.push({ purchase_item_id: item.id, quantity: qty });
                }
            });

            if (!rows.length) {
                e.preventDefault();
                alert(<?php echo json_encode(__('purchase_returns.no_items_to_return'), 15, 512) ?>);
                return;
            }

            itemsJsonInput.value = JSON.stringify(rows);

            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.textContent = <?php echo json_encode(__('purchase_returns.saving_please_wait'), 15, 512) ?>;
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\my-erp\resources\views/purchases/returns/create.blade.php ENDPATH**/ ?>