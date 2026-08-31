<x-app-layout>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 10h18M6 15h4M3 6h18v12H3z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ $type === 'receipt' ? __('vouchers.new_receipt') : __('vouchers.new_payment') }}</h2>
                </div>
                <a href="{{ route('vouchers.index', ['type' => $type]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('vouchers.back_to_list') }}
                </a>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl p-4">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('vouchers.store') }}" id="voucher-form" class="space-y-6">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="treasury_account_id" id="treasury_account_id" value="{{ old('treasury_account_id') }}">
                <input type="hidden" name="counterpart_account_id" id="counterpart_account_id" value="{{ old('counterpart_account_id') }}">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('vouchers.treasury_account') }}</label>
                            <div class="relative">
                                <input type="text" id="treasury-search" autocomplete="off" placeholder="{{ __('vouchers.select_treasury_placeholder') }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <div id="treasury-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $type === 'receipt' ? __('vouchers.counterpart_account_receipt') : __('vouchers.counterpart_account_payment') }}
                            </label>
                            <div class="relative">
                                <input type="text" id="counterpart-search" autocomplete="off" placeholder="{{ __('vouchers.search_account_placeholder') }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <div id="counterpart-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('vouchers.amount') }}</label>
                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('vouchers.voucher_date') }}</label>
                            <input type="date" name="voucher_date" value="{{ old('voucher_date', now()->toDateString()) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('vouchers.branch') }}</label>
                            <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('vouchers.cost_center') }}</label>
                            <select name="cost_center_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">-</option>
                                @foreach ($costCenters as $cc)
                                    <option value="{{ $cc->id }}" @selected(old('cost_center_id') == $cc->id)>{{ $cc->cost_center_ar }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('vouchers.description') }}</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('vouchers.index', ['type' => $type]) }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                        {{ __('vouchers.cancel') }}
                    </a>
                    <button type="submit" id="submit-btn"
                            class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                        {{ __('vouchers.save') }}
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
                const res = await fetch(`{{ route('accounts.search') }}?q=${encodeURIComponent(q)}${scopeParam}`, {
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
                alert(__('vouchers.select_both_accounts'));
                return;
            }
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.textContent = __('vouchers.saving_please_wait');
        });
    })();
    </script>
</x-app-layout>
