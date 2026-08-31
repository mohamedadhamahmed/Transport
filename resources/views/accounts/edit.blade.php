<x-app-layout>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('accounts.edit_account') }}: {{ $account->name }}</h2>
                </div>
                <a href="{{ route('accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('accounts.back_to_list') }}
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

            <form method="POST" action="{{ route('accounts.update', $account) }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="parent_account_number" id="parent_account_number" value="{{ old('parent_account_number', $account->parent_account_number) }}">

                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $account->name) }}" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.parent_account') }}</label>
                            <div class="relative">
                                <input type="text" id="parent-search" autocomplete="off"
                                       value="{{ $account->parentAccount?->name }}"
                                       placeholder="{{ __('accounts.search_placeholder') }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <div id="parent-results" class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg divide-y divide-gray-50 max-h-56 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ __('accounts.no_parent') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.account_number') }}</label>
                            <input type="text" name="account_number" value="{{ old('account_number', $account->account_number) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.branch') }}</label>
                        <select name="branchs_id" class="w-full md:w-1/2 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('accounts.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branchs_id', $account->branchs_id) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4">
                            <div class="text-xs text-gray-500 mb-1">{{ __('accounts.current_balance') }}</div>
                            <div class="font-semibold text-[#0F1B4C]">{{ number_format($account->current_balance, 2) }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_parent" value="1" @checked(old('is_parent', $account->is_parent))
                                   class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                            {{ __('accounts.is_parent') }}
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('accounts.notes') }}</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">{{ old('notes', $account->notes) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('accounts.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                        {{ __('accounts.cancel') }}
                    </a>
                    <button type="submit" id="submit-btn"
                            class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                        {{ __('accounts.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const searchInput = document.getElementById('parent-search');
        const resultsBox = document.getElementById('parent-results');
        const hiddenInput = document.getElementById('parent_account_number');
        let debounceTimer = null;

        async function runSearch(q) {
            const res = await fetch(`{{ route('accounts.search') }}?q=${encodeURIComponent(q)}`, {
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

        const form = searchInput.closest('form');
        const submitBtn = document.getElementById('submit-btn');
        let isSubmitting = false;
        form.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.textContent = @json(__('accounts.saving_please_wait'));
        });
    })();
    </script>
</x-app-layout>
