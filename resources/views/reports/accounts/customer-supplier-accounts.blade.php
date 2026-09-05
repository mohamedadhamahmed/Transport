<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.accounts.customer_supplier_accounts') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.accounts.customer_supplier_accounts') }}</h2>

            {{-- فلاتر: نوع + بحث حي (Ajax) --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <form method="GET" action="{{ route('reports.accounts.customer-supplier-accounts') }}"
                      class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-52">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.aging_type') }}</label>
                        <select id="csa-type" name="type" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="" @selected(!$type)>{{ __('reports.aging_all_types') }}</option>
                            <option value="customer" @selected($type === 'customer')>{{ __('reports.customer') }}</option>
                            <option value="supplier" @selected($type === 'supplier')>{{ __('reports.supplier') }}</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-72 relative">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.search_placeholder') }}</label>
                        <input type="text" id="csa-search" autocomplete="off" value="{{ $q }}" placeholder="{{ __('reports.search_placeholder') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                        <input type="hidden" id="csa-search-hidden" name="q" value="{{ $q }}">
                        <span id="csa-loading" class="hidden absolute left-2 top-8 text-[11px] text-gray-400">...</span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
                            {{ __('reports.print') }}
                        </button>
                        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition">
                            {{ __('reports.export_excel') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- جدول الحسابات --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.account_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.account_name') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.aging_type') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_total') }}</th>
                                <th class="text-center px-4 py-3">{{ __('reports.active') }}</th>
                                <th class="text-center px-4 py-3 dc-print-hide">{{ __('reports.statement') }}</th>
                            </tr>
                        </thead>
                        <tbody id="csa-tbody">
                            @forelse ($accounts as $account)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-500">{{ $account->account_number ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C] font-medium">{{ $account->name }}</td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ (int) $account->orginal_type === 2 ? __('reports.supplier') : __('reports.customer') }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold text-[#0F1B4C]">{{ number_format($account->current_balance, 2) }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if ($account->active)
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">{{ __('reports.active') }}</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">{{ __('reports.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center dc-print-hide">
                                        <a href="{{ route('accounts.statement', $account->id) }}" class="text-[#1456E8] hover:underline text-xs font-medium">{{ __('reports.statement') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_accounts_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const searchInput = document.getElementById('csa-search');
            const searchHidden = document.getElementById('csa-search-hidden');
            const typeSelect = document.getElementById('csa-type');
            const tbody = document.getElementById('csa-tbody');
            const loading = document.getElementById('csa-loading');
            const searchUrl = @json(route('reports.accounts.customer-supplier-accounts.search'));
            const statementBaseUrl = @json(route('accounts.statement', ['account' => '__ID__']));

            const labels = {
                active: @json(__('reports.active')),
                inactive: @json(__('reports.inactive')),
                statement: @json(__('reports.statement')),
                noResults: @json(__('reports.no_accounts_found')),
            };

            let debounceTimer = null;
            let currentRequestId = 0;

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                })[c]);
            }

            function renderRows(accounts) {
                if (!accounts.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">' + escapeHtml(labels.noResults) + '</td></tr>';
                    return;
                }

                tbody.innerHTML = accounts.map((a) => {
                    const statementUrl = a.statement_url || statementBaseUrl.replace('__ID__', a.id);
                    const activeBadge = a.active
                        ? '<span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">' + escapeHtml(labels.active) + '</span>'
                        : '<span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">' + escapeHtml(labels.inactive) + '</span>';

                    return '<tr class="border-b border-gray-50 hover:bg-gray-50/60">'
                        + '<td class="px-4 py-2.5 text-gray-500">' + escapeHtml(a.account_number || '-') + '</td>'
                        + '<td class="px-4 py-2.5 text-[#0F1B4C] font-medium">' + escapeHtml(a.name) + '</td>'
                        + '<td class="px-4 py-2.5 text-gray-500 text-xs">' + escapeHtml(a.type_label) + '</td>'
                        + '<td class="px-4 py-2.5 text-end font-bold text-[#0F1B4C]">' + Number(a.current_balance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>'
                        + '<td class="px-4 py-2.5 text-center">' + activeBadge + '</td>'
                        + '<td class="px-4 py-2.5 text-center dc-print-hide"><a href="' + statementUrl + '" class="text-[#1456E8] hover:underline text-xs font-medium">' + escapeHtml(labels.statement) + '</a></td>'
                        + '</tr>';
                }).join('');
            }

            function runSearch() {
                const requestId = ++currentRequestId;
                const params = new URLSearchParams();
                if (searchInput.value.trim() !== '') params.set('q', searchInput.value.trim());
                if (typeSelect.value !== '') params.set('type', typeSelect.value);

                loading.classList.remove('hidden');

                fetch(searchUrl + '?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                })
                    .then((res) => res.json())
                    .then((data) => {
                        if (requestId !== currentRequestId) return; // نتيجة قديمة، اتجاهلها
                        renderRows(Array.isArray(data) ? data : []);
                    })
                    .catch(() => {
                        if (requestId !== currentRequestId) return;
                    })
                    .finally(() => {
                        if (requestId === currentRequestId) loading.classList.add('hidden');
                    });
            }

            searchInput.addEventListener('input', function () {
                searchHidden.value = searchInput.value;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(runSearch, 300);
            });

            typeSelect.addEventListener('change', runSearch);
        })();
    </script>
</x-app-layout>
