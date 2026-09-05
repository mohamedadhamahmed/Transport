<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l9-5 9 5-9 5-9-5Z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('stock_transfers.dispatch_title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('stock_transfers.from_branch') }}: {{ $fromBranch->name }}</p>
                    </div>
                </div>
                <a href="{{ route('stock-transfers.index', ['box' => 'sent']) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('stock_transfers.back_to_list') }}
                </a>
            </div>

            @include('partials.sweet-alert-flash')

            @if ($errors->any())
                <div class="bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl p-4">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('stock-transfers.store') }}" id="dispatch-form" class="space-y-6">
                @csrf
                <input type="hidden" name="from_branch_id" value="{{ $fromBranch->id }}">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('stock_transfers.to_branch') }}</label>
                            <select name="to_branch_id" id="to_branch_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('stock_transfers.select_branch') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('to_branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('stock_transfers.receiver_employee') }}</label>
                            <select name="receiver_user_id" id="receiver_user_id" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('stock_transfers.select_branch_first') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('stock_transfers.transfer_date') }}</label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('stock_transfers.notes') }}</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                        <h3 class="font-semibold text-gray-800">{{ __('stock_transfers.items') }}</h3>
                        <div class="flex items-center gap-2">
                            <button type="button" id="pick-product-btn"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition whitespace-nowrap">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="16" rx="2" />
                                    <path d="M3 9h18M8 4v16" />
                                </svg>
                                {{ __('stock_transfers.pick_product') }}
                            </button>
                            <div class="relative w-72">
                                <input type="text" id="product-search" autocomplete="off" placeholder="{{ __('stock_transfers.search_product_placeholder') }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                                <div id="product-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.available_stock') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.quantity') }}</th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="lines-body" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>
                    <p id="empty-hint" class="text-sm text-gray-400 text-center py-6">{{ __('stock_transfers.no_items_yet') }}</p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('stock-transfers.index', ['box' => 'sent']) }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                        {{ __('stock_transfers.cancel') }}
                    </a>
                    <button type="submit" name="action" value="draft" id="draft-btn"
                            class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300 transition">
                        {{ __('stock_transfers.save_as_draft') }}
                    </button>
                    <button type="submit" name="action" value="send" id="send-btn"
                            class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                        {{ __('stock_transfers.confirm_dispatch') }}
                    </button>
                </div>
            </form>

            {{-- مودال اختيار منتج - نفس شكل المودال المستخدم في المبيعات
                 والمشتريات والتسليمات (بحث + صفحات 20 منتج)، لكن بيانات
                 هنا محصورة على مخزون $fromBranch فقط. --}}
            <div id="product-picker-modal" class="fixed inset-0 bg-black/50 z-50 p-4 hidden items-center justify-center">
                <div class="bg-white rounded-xl w-full max-w-3xl my-8 flex flex-col max-h-[90vh] shadow-2xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] rounded-t-xl">
                        <h3 class="font-semibold text-white">{{ __('stock_transfers.pick_product') }}</h3>
                        <button type="button" id="picker-close-btn" class="text-white/60 hover:text-white transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4 border-b border-gray-100">
                        <input type="text" id="picker-search-input" autocomplete="off"
                               placeholder="{{ __('stock_transfers.search_product_placeholder') }}"
                               class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0">
                                <tr class="bg-[#0F1B4C] text-white/80">
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.product') }}</th>
                                    <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('stock_transfers.available_stock') }}</th>
                                    <th class="px-3 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="picker-body" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 flex-wrap gap-2">
                        <span id="picker-page-info" class="text-xs text-gray-400"></span>
                        <div class="flex gap-2">
                            <button type="button" id="picker-prev-btn"
                                    class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                                {{ __('stock_transfers.previous') }}
                            </button>
                            <button type="button" id="picker-next-btn"
                                    class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white transition">
                                {{ __('stock_transfers.next') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const fromBranchId = {{ $fromBranch->id }};
        const linesBody = document.getElementById('lines-body');
        const emptyHint = document.getElementById('empty-hint');
        const toBranchSelect = document.getElementById('to_branch_id');
        const receiverSelect = document.getElementById('receiver_user_id');
        const productSearch = document.getElementById('product-search');
        const productResults = document.getElementById('product-results');
        const form = document.getElementById('dispatch-form');
        let rowCounter = 0;
        const addedProductIds = new Set();

        function updateEmptyHint() {
            const count = linesBody.querySelectorAll('.line-row').length;
            emptyHint.classList.toggle('hidden', count > 0);
        }

        function branchUsersUrl(branchId) {
            return `{{ route('stock-transfers.branch-users', ['branch' => '__ID__']) }}`.replace('__ID__', branchId);
        }

        toBranchSelect.addEventListener('change', async () => {
            const branchId = toBranchSelect.value;
            if (!branchId) {
                receiverSelect.innerHTML = `<option value="">{{ __('stock_transfers.select_branch_first') }}</option>`;
                return;
            }
            receiverSelect.innerHTML = `<option value="">{{ __('stock_transfers.loading') }}</option>`;
            const res = await fetch(branchUsersUrl(branchId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const users = await res.json();
            receiverSelect.innerHTML = `<option value="">{{ __('stock_transfers.select_employee') }}</option>`;
            users.forEach((user) => {
                const opt = document.createElement('option');
                opt.value = user.id;
                opt.textContent = user.name;
                receiverSelect.appendChild(opt);
            });
        });

        // ---- بحث سريع (اقتراحات فورية أثناء الكتابة) - جنب زرار
        // "اختيار منتج"، بنفس أسلوب صندوق البحث في شاشة الفاتورة. ----
        async function runProductSearch(q) {
            const res = await fetch(`{{ route('stock-transfers.products.search') }}?branch_id=${fromBranchId}&q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await res.json();
            const rows = json.data ?? [];

            productResults.innerHTML = '';
            if (!rows.length) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-sm text-gray-400';
                empty.textContent = q
                    ? @json(__('stock_transfers.no_products_match'))
                    : @json(__('stock_transfers.no_products_in_branch'));
                productResults.appendChild(empty);
                productResults.classList.remove('hidden');
                return;
            }
            rows.forEach((row) => {
                const already = addedProductIds.has(row.id);
                const div = document.createElement('div');
                div.className = 'px-3 py-2 text-sm hover:bg-[#1456E8]/5 cursor-pointer transition' + (already ? ' opacity-40' : '');
                div.textContent = `${row.name}${row.code ? ' (#' + row.code + ')' : ''} - {{ __('stock_transfers.available_stock') }}: ${row.stock_quantity}`;
                div.addEventListener('click', () => {
                    if (addedProductIds.has(row.id)) {
                        return;
                    }
                    addLine(row);
                    productResults.classList.add('hidden');
                    productSearch.value = '';
                });
                productResults.appendChild(div);
            });
            productResults.classList.remove('hidden');
        }

        let searchDebounce = null;
        productSearch.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            const q = productSearch.value.trim();
            searchDebounce = setTimeout(() => runProductSearch(q), 250);
        });
        productSearch.addEventListener('focus', () => {
            if (!productSearch.value.trim()) {
                runProductSearch('');
            }
        });
        document.addEventListener('click', (e) => {
            if (!productResults.contains(e.target) && e.target !== productSearch) {
                productResults.classList.add('hidden');
            }
        });

        // ---- مودال اختيار منتج (نفس شكل المودال المستخدم في المبيعات
        // والمشتريات والتسليمات) ----
        const pickerModal = document.getElementById('product-picker-modal');
        const pickerSearchInput = document.getElementById('picker-search-input');
        const pickerBody = document.getElementById('picker-body');
        const pickerPageInfo = document.getElementById('picker-page-info');
        const pickerPrevBtn = document.getElementById('picker-prev-btn');
        const pickerNextBtn = document.getElementById('picker-next-btn');
        const pickerCloseBtn = document.getElementById('picker-close-btn');
        let pickerPage = 1;
        let pickerLastPage = 1;

        function openPicker() {
            pickerModal.classList.remove('hidden');
            pickerModal.classList.add('flex');
            pickerSearchInput.value = '';
            loadPickerProducts(1);
        }

        function closePicker() {
            pickerModal.classList.add('hidden');
            pickerModal.classList.remove('flex');
        }

        async function loadPickerProducts(page) {
            if (page < 1) return;
            pickerBody.innerHTML = `<tr><td colspan="4" class="px-3 py-8 text-center text-gray-400">{{ __('stock_transfers.loading') }}</td></tr>`;
            const res = await fetch(`{{ route('stock-transfers.products.search') }}?branch_id=${fromBranchId}&q=${encodeURIComponent(pickerSearchInput.value.trim())}&page=${page}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            pickerPage = data.current_page;
            pickerLastPage = data.last_page;
            renderPickerRows(data.data, data.total);
        }

        function renderPickerRows(rows, total) {
            pickerBody.innerHTML = '';
            if (!rows.length) {
                const emptyText = pickerSearchInput.value.trim()
                    ? @json(__('stock_transfers.no_products_match'))
                    : @json(__('stock_transfers.no_products_in_branch'));
                pickerBody.innerHTML = `<tr><td colspan="4" class="px-3 py-8 text-center text-gray-400">${emptyText}</td></tr>`;
            } else {
                rows.forEach((row, idx) => {
                    const already = addedProductIds.has(row.id);
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-[#1456E8]/5 transition';
                    tr.innerHTML = `
                        <td class="px-3 py-2 text-gray-400">${(pickerPage - 1) * 20 + idx + 1}</td>
                        <td class="px-3 py-2 font-medium text-gray-800 min-w-[220px] whitespace-normal">${row.name}${row.code ? ` <span class="text-xs text-gray-400 font-normal">(#${row.code})</span>` : ''}</td>
                        <td class="px-3 py-2 text-gray-500">${row.stock_quantity} ${row.unit ?? ''}</td>
                        <td class="px-3 py-2"></td>
                    `;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'px-3 py-1.5 rounded-lg text-white text-xs font-medium transition whitespace-nowrap ' + (already ? 'bg-emerald-500' : 'bg-[#F5811E] hover:brightness-95');
                    btn.textContent = already ? ('✓ ' + @json(__('stock_transfers.added'))) : ('+ ' + @json(__('stock_transfers.add')));
                    btn.addEventListener('click', () => {
                        if (addedProductIds.has(row.id)) {
                            return;
                        }
                        addLine(row);
                        btn.textContent = '✓ ' + @json(__('stock_transfers.added'));
                        btn.classList.remove('bg-[#F5811E]', 'hover:brightness-95');
                        btn.classList.add('bg-emerald-500');
                    });
                    tr.lastElementChild.appendChild(btn);
                    pickerBody.appendChild(tr);
                });
            }
            pickerPageInfo.textContent = total > 0
                ? @json(__('stock_transfers.page_of_total')).replace(':current', pickerPage).replace(':last', pickerLastPage).replace(':total', total)
                : '';
            pickerPrevBtn.disabled = pickerPage <= 1;
            pickerNextBtn.disabled = pickerPage >= pickerLastPage;
        }

        document.getElementById('pick-product-btn').addEventListener('click', openPicker);
        pickerCloseBtn.addEventListener('click', closePicker);
        pickerModal.addEventListener('click', (e) => {
            if (e.target === pickerModal) {
                closePicker();
            }
        });
        pickerPrevBtn.addEventListener('click', () => loadPickerProducts(pickerPage - 1));
        pickerNextBtn.addEventListener('click', () => loadPickerProducts(pickerPage + 1));
        let pickerDebounce = null;
        pickerSearchInput.addEventListener('input', () => {
            clearTimeout(pickerDebounce);
            pickerDebounce = setTimeout(() => loadPickerProducts(1), 300);
        });

        function addLine(product) {
            const index = rowCounter++;
            addedProductIds.add(product.id);

            const tr = document.createElement('tr');
            tr.className = 'line-row';
            tr.dataset.productId = product.id;
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="hidden" name="items[${index}][product_id]" value="${product.id}">
                    <div class="font-medium text-gray-800">${product.name}</div>
                    ${product.code ? `<div class="text-xs text-gray-400">#${product.code}</div>` : ''}
                </td>
                <td class="px-3 py-2 text-gray-600">${product.stock_quantity} ${product.unit ?? ''}</td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" min="0.01" max="${product.stock_quantity}" name="items[${index}][quantity]"
                           class="quantity-input w-28 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="remove-line-btn text-red-500 hover:text-red-700 text-xs font-medium">{{ __('stock_transfers.remove_line') }}</button>
                </td>
            `;
            linesBody.appendChild(tr);

            tr.querySelector('.remove-line-btn').addEventListener('click', () => {
                addedProductIds.delete(product.id);
                tr.remove();
                updateEmptyHint();
            });

            updateEmptyHint();
        }

        form.addEventListener('submit', (e) => {
            if (!linesBody.querySelectorAll('.line-row').length) {
                e.preventDefault();
                alert(@json(__('stock_transfers.no_items_to_send')));
            }
        });
    })();
    </script>
</x-app-layout>
