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
                        <h2 class="text-white font-bold text-lg">{{ __('accounts.statement_of') }}{{ $account->name }}</h2>
                        <p class="text-white/60 text-xs mt-0.5">{{ __('accounts.account_number') }}: {{ $account->account_number ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('accounts.back_to_list') }}
                </a>
            </div>

            <div class="dc-print-only text-center">
                <h2 class="text-xl font-bold">{{ __('accounts.statement_of') }}{{ $account->name }}</h2>
                <p class="text-sm text-gray-500">{{ __('accounts.account_number') }}: {{ $account->account_number ?? '-' }}</p>
            </div>

            {{-- فلاتر: فترة + نوع العملية --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <form method="GET" action="{{ route('accounts.statement', $account) }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-44">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>
                    <div class="w-full sm:w-44">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>
                    <div class="w-full sm:w-56">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.operation_type') }}</label>
                        <select name="operation_type" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="">{{ __('accounts.all_operation_types') }}</option>
                            @foreach ($operationTypes as $value => $label)
                                <option value="{{ $value }}" @selected((string) $operationType === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition">
                            {{ __('accounts.filter') }}
                        </button>
                        <a href="{{ route('accounts.statement', $account) }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
                            {{ __('accounts.cancel') }}
                        </a>
                        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
                            {{ __('reports.print') }}
                        </button>
                        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition">
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
</x-app-layout>
