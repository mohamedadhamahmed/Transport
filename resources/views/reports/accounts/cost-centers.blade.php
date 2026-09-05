<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M2 12h20M12 2a10 10 0 0 1 0 20 10 10 0 0 1 0-20Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.accounts.cost_centers') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.accounts.cost_centers') }}</h2>

            {{-- فلاتر: فرع + فترة --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <form method="GET" action="{{ url()->current() }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-56">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.branch') }}</label>
                        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="">{{ __('reports.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-44">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>
                    <div class="w-full sm:w-44">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition">
                            {{ __('reports.apply_filters') }}
                        </button>
                        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
                            {{ __('reports.print') }}
                        </button>
                        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition">
                            {{ __('reports.export_excel') }}
                        </button>
                    </div>
                </form>

                @if ($branchId)
                    <div class="dc-print-hide px-4 py-2 bg-amber-50 text-amber-700 text-xs border-b dc-border-amber-soft">
                        {{ __('reports.branch_filter_note') }}
                    </div>
                @endif

                <div class="px-4 py-2 text-xs text-gray-400">{{ __('reports.period_note') }}</div>
            </div>

            {{-- بطاقة الإجمالي العام --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.cost_center') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ $rows->count() }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.net_total') }}</p>
                    <p class="text-lg font-bold text-[#F5811E] mt-0.5">{{ number_format($rows->sum('net_total'), 2) }}</p>
                </div>
            </div>

            {{-- جدول مراكز التكلفة --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.cost_center') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.purchases_total') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.payment_vouchers') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.receipt_vouchers') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.journal_entries') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C] font-medium">{{ $row->name }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($row->purchases, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($row->payments, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($row->receipts, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($row->journal, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold text-[#0F1B4C]">{{ number_format($row->net_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_cost_centers_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('purchases'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('payments'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('receipts'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('journal'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('net_total'), 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
