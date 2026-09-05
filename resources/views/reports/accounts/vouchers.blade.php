<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16v16H4V4Zm4 4h8M8 12h8M8 16h4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.vouchers_report_title') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.vouchers_report_title') }}</h2>

            {{-- فلاتر: فرع + نوع السند + فترة (فلتر مخصص لأن التقرير ده الوحيد
                 المحتاج فلتر "نوع" بجانب الفرع والفترة المشتركين) --}}
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
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.voucher_type') }}</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="" @selected(!$type)>{{ __('reports.all') }}</option>
                            <option value="receipt" @selected($type === 'receipt')>{{ __('vouchers.receipt_title') }}</option>
                            <option value="payment" @selected($type === 'payment')>{{ __('vouchers.payment_title') }}</option>
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

            {{-- بطاقات الملخص: قبض/صرف/صافي الحركة + عدد القيود اليومية والافتتاحية --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#0d9488]/10 text-[#0d9488] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.receipt_vouchers_count') }}: {{ $receiptCount }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ number_format($totalReceipts, 2) }}</p>
                    <p class="text-[10px] text-gray-400">{{ __('reports.receipt_vouchers_total') }}</p>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#e11d48]/10 text-[#e11d48] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.payment_vouchers_count') }}: {{ $paymentCount }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ number_format($totalPayments, 2) }}</p>
                    <p class="text-[10px] text-gray-400">{{ __('reports.payment_vouchers_total') }}</p>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h18M13 6l6 6-6 6"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.net_cash_movement') }}</p>
                    <p class="text-lg font-bold {{ $netCashMovement >= 0 ? 'text-[#0d9488]' : 'text-[#e11d48]' }} mt-0.5">{{ number_format($netCashMovement, 2) }}</p>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#6B2FD6]/10 text-[#6B2FD6] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16M4 19V9l4-3 4 3 4-5 4 4v11"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.opening_entries_count') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ $openingEntriesCount }}</p>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <span class="w-9 h-9 rounded-lg bg-[#F5811E]/10 text-[#F5811E] flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                    </span>
                    <p class="text-[11px] text-gray-400">{{ __('reports.daily_entries_count') }}</p>
                    <p class="text-lg font-bold text-[#0F1B4C] mt-0.5">{{ $dailyEntriesCount }}</p>
                </div>
            </div>

            {{-- مخطط مقارنة القبض والصرف يوميًا --}}
            <div class="dc-print-hide bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <h3 class="font-bold text-[#0F1B4C] text-sm">{{ __('reports.vouchers_chart_title') }}</h3>
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#0d9488"></span>{{ __('vouchers.receipt_title') }}</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#e11d48"></span>{{ __('vouchers.payment_title') }}</span>
                    </div>
                </div>
                <div style="height:260px">
                    <canvas id="vouchers-trend-chart"></canvas>
                </div>
            </div>

            {{-- جدول السندات --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('vouchers.voucher_no') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.voucher_type') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-start px-4 py-3">{{ __('vouchers.treasury_account') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.counterpart_account') }}</th>
                                <th class="text-end px-4 py-3">{{ __('vouchers.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lines as $line)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $line->voucher?->voucher_number }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($line->voucher?->type === \App\Models\AccountVoucher::TYPE_RECEIPT)
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#0d9488]/10 text-[#0d9488]">{{ __('vouchers.receipt') }}</span>
                                        @else
                                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#e11d48]/10 text-[#e11d48]">{{ __('vouchers.payment') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ optional($line->voucher?->voucher_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ optional($line->voucher?->treasuryAccount)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ optional($line->counterpartAccount)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $line->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('vouchers.no_vouchers_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($lines->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="5">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalFilteredAmount, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var trend = @json($chartTrend);
            var canvas = document.getElementById('vouchers-trend-chart');
            if (!canvas || typeof Chart === 'undefined' || !trend || !trend.length) { return; }

            var labels = trend.map(function (d) {
                return new Date(d.date + 'T00:00:00').toLocaleDateString(
                    @json(app()->getLocale()) === 'ar' ? 'ar-SA' : 'en-US',
                    { month: 'short', day: 'numeric' }
                );
            });

            var ctx = canvas.getContext('2d');
            var receiptsGradient = ctx.createLinearGradient(0, 0, 0, 240);
            receiptsGradient.addColorStop(0, 'rgba(13,148,136,.26)');
            receiptsGradient.addColorStop(1, 'rgba(13,148,136,0)');
            var paymentsGradient = ctx.createLinearGradient(0, 0, 0, 240);
            paymentsGradient.addColorStop(0, 'rgba(225,29,72,.22)');
            paymentsGradient.addColorStop(1, 'rgba(225,29,72,0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: @json(__('vouchers.receipt_title')),
                            data: trend.map(function (d) { return d.receipts; }),
                            borderColor: '#0d9488',
                            backgroundColor: receiptsGradient,
                            pointBackgroundColor: '#0d9488',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: true,
                        },
                        {
                            label: @json(__('vouchers.payment_title')),
                            data: trend.map(function (d) { return d.payments; }),
                            borderColor: '#e11d48',
                            backgroundColor: paymentsGradient,
                            pointBackgroundColor: '#e11d48',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F1B4C',
                            padding: 10,
                            cornerRadius: 8,
                            titleFont: { weight: 'bold' },
                        },
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#F1F3F8' } },
                        x: { grid: { display: false } },
                    },
                },
            });
        })();
    </script>
</x-app-layout>
