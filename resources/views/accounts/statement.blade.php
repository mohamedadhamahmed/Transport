<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 3v18M3 9h18M3 15h18"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">
                            {{ __('accounts.statement_of') }}{{ $account?->name ?? __('accounts.statement') }}
                        </h2>
                        <p class="text-white/60 text-xs mt-0.5">
                            {{ __('accounts.account_number') }}: <span class="font-mono">{{ $account?->account_number ?? '-' }}</span>
                            @if ($account?->parentAccount)
                                <span class="mx-1.5 opacity-40">|</span>
                                <span>{{ __('accounts.parent_account') }}: {{ $account->parentAccount->name }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('reports.accounts.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        {{ __('reports.accounts.title') }}
                    </a>
                    <a href="{{ route('accounts.tree') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        {{ __('accounts.tree_title') }}
                    </a>
                    <a href="{{ route('accounts.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('accounts.back_to_list') }}
                    </a>
                </div>
            </div>

            <div class="dc-print-only text-center">
                <h2 class="text-xl font-bold">{{ __('accounts.statement_of') }}{{ $account?->name }}</h2>
                <p class="text-sm text-gray-500">{{ __('accounts.account_number') }}: {{ $account?->account_number ?? '-' }}</p>
                @if ($dateFrom || $dateTo)
                    <p class="text-xs text-gray-400 mt-1">
                        @if ($dateFrom) {{ __('accounts.date_from') }}: {{ $dateFrom }} @endif
                        @if ($dateTo) {{ __('accounts.date_to') }}: {{ $dateTo }} @endif
                    </p>
                @endif
            </div>

            {{-- فلاتر: اختيار الحساب بالبحث الفوري + فترة + نوع العملية --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <form id="statementFilterForm" method="GET" action="{{ route('reports.accounts.statement') }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    {{-- حقل اختيار الحساب - قائمة بحث تفاعلية ذكية (Searchable Select) --}}
                    <div class="w-full lg:w-80 sm:w-72">
                        <label for="account_select" class="block text-xs font-semibold text-gray-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#1456E8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                {{ __('accounts.account') }}
                            </span>
                            <span class="text-[11px] font-normal text-[#1456E8] bg-blue-50 px-2 py-0.5 rounded-full">
                                {{ __('accounts.search_any_account') }}
                            </span>
                        </label>
                        <select id="account_select" name="account_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="{{ __('accounts.search_account_placeholder') }}">
                            <option value="">{{ __('accounts.choose_account') }}</option>
                            @foreach ($allAccounts as $acc)
                                <option value="{{ $acc->id }}"
                                        data-number="{{ $acc->account_number }}"
                                        data-name="{{ $acc->name }}"
                                        @selected(isset($account) && $account->id === $acc->id)>
                                    {{ $acc->account_number }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-36">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>
                    <div class="w-full sm:w-36">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>
                    <div class="w-full sm:w-44">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.operation_type') }}</label>
                        <select name="operation_type" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="">{{ __('accounts.all_operation_types') }}</option>
                            @foreach ($operationTypes as $value => $label)
                                <option value="{{ $value }}" @selected((string) $operationType === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            {{ __('accounts.filter') }}
                        </button>
                        <a href="{{ route('reports.accounts.statement', ['account_id' => $account?->id]) }}" class="px-3.5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
                            {{ __('accounts.cancel') }}
                        </a>
                        <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            {{ __('reports.print') }}
                        </button>
                        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-3.5 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('reports.export_excel') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- بطاقات الملخص --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('accounts.opening_balance_label') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ number_format($openingBalance, 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('accounts.total_debtor') }}</p>
                    <p class="text-lg font-bold text-[#1456E8] mt-0.5">{{ number_format($transactions->sum('debtor'), 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('accounts.total_creditor') }}</p>
                    <p class="text-lg font-bold text-[#F5811E] mt-0.5">{{ number_format($transactions->sum('creditor'), 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('accounts.closing_balance_label') }}</p>
                    <p class="text-lg font-bold {{ ($transactions->last()->running_balance ?? $openingBalance) >= 0 ? 'text-[#0d9488]' : 'text-[#e11d48]' }} mt-0.5">
                        {{ number_format($transactions->last()->running_balance ?? $openingBalance, 2) }}
                    </p>
                </div>
            </div>

            {{-- جدول الحركات --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('accounts.date') }}</th>
                                <th class="text-start px-4 py-3">{{ __('accounts.operation_type') }}</th>
                                <th class="text-start px-4 py-3">{{ __('accounts.description') }}</th>
                                <th class="text-start px-4 py-3">{{ __('accounts.reference') }}</th>
                                <th class="text-end px-4 py-3">{{ __('accounts.debtor') }}</th>
                                <th class="text-end px-4 py-3">{{ __('accounts.creditor') }}</th>
                                <th class="text-end px-4 py-3">{{ __('accounts.running_balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-50 bg-gray-50/60">
                                <td class="px-4 py-2.5 text-gray-400 text-xs" colspan="6">{{ __('accounts.opening_balance_label') }}</td>
                                <td class="px-4 py-2.5 text-end font-semibold text-[#0F1B4C]">{{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            @forelse ($transactions as $t)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ optional($t->created_at)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#1456E8]/10 text-[#1456E8]">
                                            {{ $operationTypes[$t->operation_type] ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $t->note ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $t->invoice_number ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ $t->debtor > 0 ? number_format((float) $t->debtor, 2) : '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ $t->creditor > 0 ? number_format((float) $t->creditor, 2) : '-' }}</td>
                                    <td class="px-4 py-2.5 text-end font-semibold {{ $t->running_balance >= 0 ? 'text-[#0F1B4C]' : 'text-[#e11d48]' }}">
                                        {{ number_format($t->running_balance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('accounts.no_transactions_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($transactions->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="4">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($transactions->sum('debtor'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($transactions->sum('creditor'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($transactions->last()->running_balance ?? $openingBalance, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- تنسيق وتفعيل TomSelect التفاعلي الذكي --}}
    <style>
        .ts-wrapper {
            position: relative;
            width: 100%;
        }
        .ts-wrapper.single .ts-control {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 2.375rem;
            box-sizing: border-box;
            padding-inline-start: 0.75rem;
            padding-inline-end: 1.75rem;
            padding-block: 0.45rem;
            background-color: #fff;
            border: 1px solid rgb(209 213 219);
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: rgb(17 24 39);
            cursor: pointer;
            overflow: hidden;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .ts-wrapper.single.focus .ts-control,
        .ts-wrapper.single.dropdown-active .ts-control {
            border-color: #1456E8;
            box-shadow: 0 0 0 3px rgb(20 86 232 / 0.15);
        }
        .ts-wrapper.single .ts-control::after {
            content: "";
            position: absolute;
            inset-inline-end: 0.85rem;
            top: 50%;
            width: 0.4rem;
            height: 0.4rem;
            border-inline-end: 1.5px solid rgb(156 163 175);
            border-block-end: 1.5px solid rgb(156 163 175);
            transform: translateY(-70%) rotate(45deg);
            pointer-events: none;
        }
        .ts-control input {
            color: inherit;
            font-size: inherit;
            background: transparent;
            min-width: 2rem;
            cursor: pointer;
        }
        .ts-control input::placeholder {
            color: rgb(156 163 175);
        }
        .ts-wrapper.single .ts-control > .item {
            color: rgb(17 24 39);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ts-dropdown {
            margin-top: 0.25rem;
            background: #fff;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            overflow: hidden;
            z-index: 60;
            font-size: 0.875rem;
            text-align: start;
        }
        .ts-dropdown .ts-dropdown-content {
            max-height: 18rem;
            overflow-y: auto;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            color: rgb(55 65 81);
            border-bottom: 1px solid rgb(243 244 246);
        }
        .ts-dropdown .option.active,
        .ts-dropdown .option:hover {
            background-color: rgb(239 246 255);
            color: #1456E8;
        }
        .ts-dropdown .no-results {
            padding: 0.75rem 1rem;
            color: rgb(156 163 175);
            font-size: 0.8125rem;
            text-align: center;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectEl = document.getElementById('account_select');
            if (selectEl && window.TomSelect) {
                const tom = new TomSelect(selectEl, {
                    create: false,
                    allowEmptyOption: false,
                    maxOptions: 1000,
                    searchField: ['text'],
                    placeholder: '{{ __('accounts.search_account_placeholder') }}',
                    render: {
                        option: function(data, escape) {
                            const raw = data.text || '';
                            const parts = raw.split(' - ');
                            const num = parts.length > 1 ? parts[0].trim() : '';
                            const name = parts.length > 1 ? parts.slice(1).join(' - ').trim() : raw;

                            return '<div class="flex items-center justify-between py-1.5 px-2 hover:bg-blue-50/70 transition">' +
                                '<div class="font-medium text-gray-800">' + escape(name) + '</div>' +
                                (num ? '<span class="text-xs font-mono font-semibold bg-blue-100 text-[#1456E8] px-2 py-0.5 rounded">' + escape(num) + '</span>' : '') +
                            '</div>';
                        },
                        item: function(data, escape) {
                            const raw = data.text || '';
                            const parts = raw.split(' - ');
                            const num = parts.length > 1 ? parts[0].trim() : '';
                            const name = parts.length > 1 ? parts.slice(1).join(' - ').trim() : raw;

                            return '<div class="flex items-center gap-2 font-medium text-gray-800">' +
                                (num ? '<span class="text-xs font-mono font-semibold bg-blue-100 text-[#1456E8] px-1.5 py-0.5 rounded">' + escape(num) + '</span>' : '') +
                                '<span>' + escape(name) + '</span>' +
                            '</div>';
                        }
                    },
                    onChange: function(newAccountId) {
                        const currentAccountId = '{{ $account?->id }}';
                        if (newAccountId && String(newAccountId) !== String(currentAccountId)) {
                            // التبديل الفوري لكشف حساب الحساب المختار مع الاحتفاظ بباقي الفلاتر
                            document.getElementById('statementFilterForm').submit();
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
