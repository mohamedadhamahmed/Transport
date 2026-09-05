<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 3v4M15 3v4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.daily_closing_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.daily_closing_title') }} ({{ $date }})</h2>

            {{-- فلاتر: فرع + تاريخ اليوم --}}
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
                    <div class="w-full sm:w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date') }}</label>
                        <input type="date" name="date" value="{{ $date }}"
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

                @if ($hasSplitInvoices)
                    <div class="dc-print-hide px-4 py-2 bg-amber-50 text-amber-700 text-xs">
                        {{ __('reports.daily_closing_split_note') }}
                    </div>
                @endif
            </div>

            {{-- بطاقات المبيعات حسب طريقة الدفع --}}
            <div>
                <h3 class="font-bold text-[#0F1B4C] text-sm mb-3">{{ __('reports.sales.title') }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <p class="text-[11px] text-gray-400">{{ __('reports.sales_by_cash') }}</p>
                        <p class="text-lg font-bold text-[#0d9488] mt-0.5">{{ number_format($salesCash, 2) }}</p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <p class="text-[11px] text-gray-400">{{ __('reports.sales_by_card') }}</p>
                        <p class="text-lg font-bold text-[#1456E8] mt-0.5">{{ number_format($salesCard, 2) }}</p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <p class="text-[11px] text-gray-400">{{ __('reports.sales_by_bank_transfer') }}</p>
                        <p class="text-lg font-bold text-[#6B2FD6] mt-0.5">{{ number_format($salesBankTransfer, 2) }}</p>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <p class="text-[11px] text-gray-400">{{ __('reports.sales_by_credit') }}</p>
                        <p class="text-lg font-bold text-[#F5811E] mt-0.5">{{ number_format($salesCredit, 2) }}</p>
                    </div>
                    <div class="bg-[#0F1B4C] rounded-xl p-4 shadow-sm">
                        <p class="text-[11px] text-white/50">{{ __('reports.total_sales') }} ({{ $invoicesCount }})</p>
                        <p class="text-lg font-bold text-white mt-0.5">{{ number_format($totalSales, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- بطاقات المشتريات والسندات والقيود --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#e11d48]/10 text-[#e11d48] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6h15l-1.5 9h-12ZM6 6 5 3H2"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.total_purchases') }} ({{ $purchasesCount }})</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ number_format($totalPurchases, 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#0d9488]/10 text-[#0d9488] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.receipt_vouchers_total') }} ({{ $receiptVouchersCount }})</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ number_format($totalReceipts, 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#e11d48]/10 text-[#e11d48] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.payment_vouchers_total') }} ({{ $paymentVouchersCount }})</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ number_format($totalPayments, 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#F5811E]/10 text-[#F5811E] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.daily_entries_count') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ $journalEntriesCount }}</p>
                </div>
            </div>

            {{-- جدول سندات القبض والصرف --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('reports.receipt_vouchers') }} / {{ __('reports.payment_vouchers') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('vouchers.voucher_no') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.voucher_type') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.counterpart_account') }}</th>
                                <th class="text-end px-4 py-3">{{ __('vouchers.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receiptLines->concat($paymentLines) as $line)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $line->voucher?->voucher_number }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($line->voucher?->type === \App\Models\AccountVoucher::TYPE_RECEIPT)
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#0d9488]/10 text-[#0d9488]">{{ __('vouchers.receipt') }}</span>
                                        @else
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#e11d48]/10 text-[#e11d48]">{{ __('vouchers.payment') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ optional($line->counterpartAccount)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $line->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_vouchers_found_day') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- جدول القيود اليومية --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('reports.journal_entries') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">#</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.description') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.debtor') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.creditor') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($journalEntries as $entry)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $entry->entry_number }}</td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ optional($entry->entry_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $entry->description ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $entry->total_debit, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $entry->total_credit, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_journal_entries_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($journalEntries->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalJournalDebit, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalJournalCredit, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
