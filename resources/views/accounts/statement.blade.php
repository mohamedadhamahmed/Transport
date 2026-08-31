<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 3v18M3 9h18M3 15h18"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('accounts.statement_of') }}{{ $account->name }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('accounts.account_number') }}: {{ $account->account_number ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('accounts.back_to_list') }}
                </a>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.operation_type') }}</label>
                        <select name="operation_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('accounts.all_operation_types') }}</option>
                            @foreach (\App\Support\OperationType::LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(request('operation_type') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                            {{ __('accounts.filter') }}
                        </button>
                        @if (request()->hasAny(['date_from', 'date_to', 'operation_type']))
                            <a href="{{ route('accounts.statement', $account) }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                {{ __('accounts.cancel') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                    <div class="text-xs text-gray-500 mb-1">{{ __('accounts.opening_balance_label') }}</div>
                    <div class="font-semibold text-[#0F1B4C]">{{ number_format($openingBalance, 2) }}</div>
                </div>
                <div class="bg-[#0F1B4C]/5 border border-[#0F1B4C]/10 rounded-lg p-4 text-center">
                    <div class="text-xs text-gray-500 mb-1">{{ __('accounts.total_debtor') }} / {{ __('accounts.total_creditor') }}</div>
                    <div class="font-semibold text-[#0F1B4C]">{{ number_format($transactions->sum('debtor'), 2) }} / {{ number_format($transactions->sum('creditor'), 2) }}</div>
                </div>
                <div class="rounded-lg p-4 text-center text-white bg-[#0F1B4C] relative overflow-hidden">
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-[#F5811E]"></span>
                    <div class="text-xs text-white/50 mb-1">{{ __('accounts.closing_balance_label') }}</div>
                    <div class="font-bold text-lg">{{ number_format($transactions->last()->running_balance ?? $openingBalance, 2) }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.date') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.operation_type') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.reference') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.description') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.debtor') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.creditor') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.running_balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($transactions as $t)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ \App\Support\OperationType::label($t->operation_type) ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $t->invoice_number ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $t->note ?? '-' }}</td>
                                    <td class="px-3 py-2 text-emerald-700">{{ $t->debtor > 0 ? number_format($t->debtor, 2) : '-' }}</td>
                                    <td class="px-3 py-2 text-red-600">{{ $t->creditor > 0 ? number_format($t->creditor, 2) : '-' }}</td>
                                    <td class="px-3 py-2 font-semibold text-[#0F1B4C]">{{ number_format($t->running_balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('accounts.no_transactions_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
