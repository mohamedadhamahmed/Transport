<x-app-layout>

    <div class="py-6" x-data="{ activeTab: 'sales' }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ترويسة الصفحة --}}
            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 14l6-6m-5.5.5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm6 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3 6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.tax_report_title') }}</h2>
                        <p class="text-white/60 text-xs sm:text-sm mt-0.5">{{ __('reports.tax_report_subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            {{-- عنوان مخصص للطباعة فقط --}}
            <div class="dc-print-only text-center my-4">
                <h2 class="text-2xl font-bold">{{ __('reports.tax_report_title') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('reports.date_from') }}: {{ $dateFrom }} - {{ __('reports.date_to') }}: {{ $dateTo }}</p>
            </div>

            {{-- فلاتر التقرير --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-hide">
                <form id="tax-filter-form" method="GET" action="{{ url()->current() }}" class="p-4 space-y-4">
                    <div class="flex flex-wrap items-end gap-3">
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
                            <input type="date" id="filter_date_from" name="date_from" value="{{ $dateFrom }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                        </div>
                        <div class="w-full sm:w-44">
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_to') }}</label>
                            <input type="date" id="filter_date_to" name="date_to" value="{{ $dateTo }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                        </div>

                        <div class="flex items-center gap-2 flex-wrap pt-2 sm:pt-0">
                            <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition">
                                {{ __('reports.apply_filters') }}
                            </button>
                            <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z"/></svg>
                                {{ __('reports.print') }}
                            </button>
                            <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h8"/></svg>
                                {{ __('reports.export_excel') }}
                            </button>
                        </div>
                    </div>

                    {{-- أزرار الفترات الضريبية السريعة --}}
                    <div class="flex items-center gap-2 flex-wrap pt-2 border-t border-gray-100 text-xs">
                        <span class="text-gray-400 font-medium">{{ __('reports.fast_periods') }}:</span>
                        @php
                            $currentYear = date('Y');
                        @endphp
                        <button type="button" onclick="setPeriod('{{ $currentYear }}-01-01', '{{ $currentYear }}-03-31')"
                                class="px-2.5 py-1 rounded-md bg-gray-50 hover:bg-[#1456E8]/10 hover:text-[#1456E8] text-gray-600 transition border border-gray-200">
                            {{ __('reports.quarter_1') }}
                        </button>
                        <button type="button" onclick="setPeriod('{{ $currentYear }}-04-01', '{{ $currentYear }}-06-30')"
                                class="px-2.5 py-1 rounded-md bg-gray-50 hover:bg-[#1456E8]/10 hover:text-[#1456E8] text-gray-600 transition border border-gray-200">
                            {{ __('reports.quarter_2') }}
                        </button>
                        <button type="button" onclick="setPeriod('{{ $currentYear }}-07-01', '{{ $currentYear }}-09-30')"
                                class="px-2.5 py-1 rounded-md bg-gray-50 hover:bg-[#1456E8]/10 hover:text-[#1456E8] text-gray-600 transition border border-gray-200">
                            {{ __('reports.quarter_3') }}
                        </button>
                        <button type="button" onclick="setPeriod('{{ $currentYear }}-10-01', '{{ $currentYear }}-12-31')"
                                class="px-2.5 py-1 rounded-md bg-gray-50 hover:bg-[#1456E8]/10 hover:text-[#1456E8] text-gray-600 transition border border-gray-200">
                            {{ __('reports.quarter_4') }}
                        </button>
                        <button type="button" onclick="setPeriod('{{ date('Y-m-01') }}', '{{ date('Y-m-d') }}')"
                                class="px-2.5 py-1 rounded-md bg-gray-50 hover:bg-[#1456E8]/10 hover:text-[#1456E8] text-gray-600 transition border border-gray-200">
                            {{ __('reports.this_month') }}
                        </button>
                        <button type="button" onclick="setPeriod('{{ $currentYear }}-01-01', '{{ $currentYear }}-12-31')"
                                class="px-2.5 py-1 rounded-md bg-gray-50 hover:bg-[#1456E8]/10 hover:text-[#1456E8] text-gray-600 transition border border-gray-200">
                            {{ __('reports.this_year') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- 4 بطاقات إحصائية رئيسية (KPI Cards) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- بطاقة 1: صافي ضريبة المخرجات (المبيعات) --}}
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">{{ __('reports.net_sales_tax') }}</span>
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#1456E8] flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-[#0F1B4C] mt-2">{{ number_format($netSalesTax, 2) }}</p>
                    <div class="mt-2 pt-2 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-400">
                        <span>{{ __('reports.sales_taxable') }}: {{ number_format($netSalesTaxable, 2) }}</span>
                        <span class="bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-medium">{{ $salesCount }} فاتورة</span>
                    </div>
                </div>

                {{-- بطاقة 2: صافي ضريبة المشتريات --}}
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">{{ __('reports.net_purchases_tax') }}</span>
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.5 9h-12ZM6 6 5 3H2"/></svg>
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-[#0F1B4C] mt-2">{{ number_format($netPurchasesTax, 2) }}</p>
                    <div class="mt-2 pt-2 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-400">
                        <span>{{ __('reports.purchases_taxable') }}: {{ number_format($netPurchasesTaxable, 2) }}</span>
                        <span class="bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-medium">{{ $purchasesCount }} فاتورة</span>
                    </div>
                </div>

                {{-- بطاقة 3: ضريبة المصروفات الخاضعة للضريبة --}}
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">{{ __('reports.expenses_tax') }}</span>
                        <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-[#0F1B4C] mt-2">{{ number_format($expensesTax, 2) }}</p>
                    <div class="mt-2 pt-2 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-400">
                        <span>{{ __('reports.expenses_taxable') }}: {{ number_format($expensesTaxable, 2) }}</span>
                        <span class="bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded font-medium">{{ $taxableExpensesCount }} سند صرف</span>
                    </div>
                </div>

                {{-- بطاقة 4: صافي الضريبة المستحقة للسداد أو المستردة --}}
                @php
                    $isPayable = $netTaxDue > 0;
                    $isRefundable = $netTaxDue < 0;
                @endphp
                <div class="rounded-xl p-4 shadow-sm border {{ $isPayable ? 'bg-red-50/70 border-red-200' : ($isRefundable ? 'bg-emerald-50/70 border-emerald-200' : 'bg-gray-50 border-gray-200') }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold {{ $isPayable ? 'text-red-700' : ($isRefundable ? 'text-emerald-700' : 'text-gray-700') }}">
                            {{ $isPayable ? __('reports.net_tax_due') : ($isRefundable ? __('reports.net_tax_refund') : __('reports.net_tax_zero')) }}
                        </span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $isPayable ? 'bg-red-200 text-red-800' : ($isRefundable ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-800') }}">
                            {{ $isPayable ? __('reports.tax_status_payable') : ($isRefundable ? __('reports.tax_status_refundable') : '0.00') }}
                        </span>
                    </div>
                    <p class="text-2xl font-black mt-2 {{ $isPayable ? 'text-red-700' : ($isRefundable ? 'text-emerald-700' : 'text-gray-800') }}">
                        {{ $isPayable ? '+' : '' }}{{ number_format($netTaxDue, 2) }}
                    </p>
                    <div class="mt-2 pt-2 border-t {{ $isPayable ? 'border-red-200/60' : ($isRefundable ? 'border-emerald-200/60' : 'border-gray-200') }} flex items-center justify-between text-[11px] {{ $isPayable ? 'text-red-600' : ($isRefundable ? 'text-emerald-600' : 'text-gray-500') }}">
                        <span>{{ __('reports.total_input_tax') }}:</span>
                        <span class="font-bold">{{ number_format($totalInputTax, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- جدول الإقرار الضريبي الرسمي (ZATCA VAT Summary) --}}
            <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden dc-print-plain">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-[#0F1B4C] text-base">{{ __('reports.tax_net_declaration') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">جدول ملخص الإقرار الضريبي الرسمي لضريبة القيمة المضافة</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-white border border-gray-200 rounded-lg text-gray-600">
                        {{ $dateFrom }} إلى {{ $dateTo }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-start">
                        <thead>
                            <tr class="bg-gray-100/75 text-gray-700 font-semibold border-b border-gray-200 text-xs">
                                <th class="p-3 text-start">{{ __('reports.item') }}</th>
                                <th class="p-3 text-end">{{ __('reports.taxable_amount') }}</th>
                                <th class="p-3 text-center">{{ __('reports.vat_rate') }}</th>
                                <th class="p-3 text-end">{{ __('reports.tax_amount') }}</th>
                                <th class="p-3 text-end">{{ __('reports.total_with_tax') }}</th>
                                <th class="p-3 text-center">{{ __('reports.invoices_count') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- القسم الأول: ضريبة المخرجات --}}
                            <tr class="bg-blue-50/40 font-bold text-xs text-[#0F1B4C]">
                                <td colspan="6" class="p-2.5 px-3 uppercase tracking-wider text-start">
                                    {{ __('reports.tax_sales_section') }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-800">{{ __('reports.sales_taxable') }}</td>
                                <td class="p-3 text-end text-gray-700 font-mono">{{ number_format($salesTaxable, 2) }}</td>
                                <td class="p-3 text-center text-gray-500 font-mono">15%</td>
                                <td class="p-3 text-end text-[#1456E8] font-bold font-mono">{{ number_format($salesTax, 2) }}</td>
                                <td class="p-3 text-end text-gray-700 font-mono">{{ number_format($salesTotalWithTax, 2) }}</td>
                                <td class="p-3 text-center text-gray-600">{{ $salesCount }}</td>
                            </tr>
                            <tr class="bg-blue-50/60 font-bold border-t border-b border-blue-100 text-[#0F1B4C]">
                                <td class="p-3 text-start">{{ __('reports.net_sales_tax') }}</td>
                                <td class="p-3 text-end font-mono">{{ number_format($netSalesTaxable, 2) }}</td>
                                <td class="p-3 text-center">-</td>
                                <td class="p-3 text-end font-mono text-[#1456E8] text-base">{{ number_format($netSalesTax, 2) }}</td>
                                <td class="p-3 text-end font-mono">{{ number_format($netSalesTotalWithTax, 2) }}</td>
                                <td class="p-3 text-center">{{ $salesCount + $salesReturnsCount }}</td>
                            </tr>

                            {{-- القسم الثاني: ضريبة المدخلات --}}
                            <tr class="bg-emerald-50/40 font-bold text-xs text-[#0F1B4C]">
                                <td colspan="6" class="p-2.5 px-3 uppercase tracking-wider text-start">
                                    {{ __('reports.tax_purchases_section') }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-800">{{ __('reports.purchases_taxable') }}</td>
                                <td class="p-3 text-end text-gray-700 font-mono">{{ number_format($purchasesTaxable, 2) }}</td>
                                <td class="p-3 text-center text-gray-500 font-mono">15%</td>
                                <td class="p-3 text-end text-emerald-600 font-bold font-mono">{{ number_format($purchasesTax, 2) }}</td>
                                <td class="p-3 text-end text-gray-700 font-mono">{{ number_format($purchasesTotalWithTax, 2) }}</td>
                                <td class="p-3 text-center text-gray-600">{{ $purchasesCount }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50 text-red-600">
                                <td class="p-3 font-medium">{{ __('reports.purchases_returns_taxable') }}</td>
                                <td class="p-3 text-end font-mono">({{ number_format($purchaseReturnsTaxable, 2) }})</td>
                                <td class="p-3 text-center font-mono">15%</td>
                                <td class="p-3 text-end font-bold font-mono">({{ number_format($purchaseReturnsTax, 2) }})</td>
                                <td class="p-3 text-end font-mono">({{ number_format($purchaseReturnsTotalWithTax, 2) }})</td>
                                <td class="p-3 text-center">{{ $purchaseReturnsCount }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 font-semibold border-t border-b border-emerald-100 text-emerald-950">
                                <td class="p-3 text-start">{{ __('reports.net_purchases_tax') }}</td>
                                <td class="p-3 text-end font-mono">{{ number_format($netPurchasesTaxable, 2) }}</td>
                                <td class="p-3 text-center">-</td>
                                <td class="p-3 text-end font-mono text-emerald-700 font-bold">{{ number_format($netPurchasesTax, 2) }}</td>
                                <td class="p-3 text-end font-mono">{{ number_format($netPurchasesTotalWithTax, 2) }}</td>
                                <td class="p-3 text-center">{{ $purchasesCount + $purchaseReturnsCount }}</td>
                            </tr>

                            {{-- القسم الثالث: ضريبة المصروفات الخاضعة للضريبة --}}
                            <tr class="bg-amber-50/40 font-bold text-xs text-[#0F1B4C]">
                                <td colspan="6" class="p-2.5 px-3 uppercase tracking-wider text-start">
                                    {{ __('reports.tax_expenses_section') }}
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-800">{{ __('reports.expenses_taxable') }}</td>
                                <td class="p-3 text-end text-gray-700 font-mono">{{ number_format($expensesTaxable, 2) }}</td>
                                <td class="p-3 text-center text-gray-500 font-mono">15%</td>
                                <td class="p-3 text-end text-amber-600 font-bold font-mono">{{ number_format($expensesTax, 2) }}</td>
                                <td class="p-3 text-end text-gray-700 font-mono">{{ number_format($expensesTotalWithTax, 2) }}</td>
                                <td class="p-3 text-center text-gray-600">{{ $taxableExpensesCount }}</td>
                            </tr>

                            {{-- إجمالي ضريبة المدخلات القابلة للخصم --}}
                            <tr class="bg-amber-50/80 font-bold border-t-2 border-amber-200 text-amber-950">
                                <td class="p-3 text-start">{{ __('reports.total_input_tax') }}</td>
                                <td class="p-3 text-end font-mono">{{ number_format($netPurchasesTaxable + $expensesTaxable, 2) }}</td>
                                <td class="p-3 text-center">-</td>
                                <td class="p-3 text-end font-mono text-amber-800 text-base font-bold">{{ number_format($totalInputTax, 2) }}</td>
                                <td class="p-3 text-end font-mono">{{ number_format($netPurchasesTotalWithTax + $expensesTotalWithTax, 2) }}</td>
                                <td class="p-3 text-center">{{ $purchasesCount + $purchaseReturnsCount + $taxableExpensesCount }}</td>
                            </tr>

                            {{-- النتيجة النهائية للإقرار الضريبي --}}
                            <tr class="font-black text-base {{ $isPayable ? 'bg-red-100 text-red-900 border-t-4 border-red-400' : ($isRefundable ? 'bg-emerald-100 text-emerald-900 border-t-4 border-emerald-400' : 'bg-gray-100 text-gray-900') }}">
                                <td class="p-4 text-start">
                                    {{ $isPayable ? __('reports.net_tax_due') : ($isRefundable ? __('reports.net_tax_refund') : __('reports.net_tax_zero')) }}
                                </td>
                                <td class="p-4 text-end font-mono">{{ number_format($netSalesTaxable - ($netPurchasesTaxable + $expensesTaxable), 2) }}</td>
                                <td class="p-4 text-center">-</td>
                                <td class="p-4 text-end font-mono text-xl">{{ $isPayable ? '+' : '' }}{{ number_format($netTaxDue, 2) }}</td>
                                <td class="p-4 text-end font-mono">{{ number_format($netSalesTotalWithTax - ($netPurchasesTotalWithTax + $expensesTotalWithTax), 2) }}</td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isPayable ? 'bg-red-200 text-red-900' : ($isRefundable ? 'bg-emerald-200 text-emerald-900' : 'bg-gray-200 text-gray-900') }}">
                                        {{ $isPayable ? __('reports.tax_status_payable') : ($isRefundable ? __('reports.tax_status_refundable') : '0.00') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- الجداول التفصيلية التفاعلية (Tabs) --}}
            <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden dc-print-hide">
                <div class="border-b border-gray-100 bg-gray-50 px-4 pt-3 flex items-center gap-2 overflow-x-auto">
                    <button type="button" @click="activeTab = 'sales'"
                            class="px-4 py-2.5 text-xs font-bold rounded-t-lg transition border-b-2"
                            :class="activeTab === 'sales' ? 'bg-white text-[#1456E8] border-[#1456E8] shadow-sm' : 'text-gray-500 border-transparent hover:text-gray-700'">
                        {{ __('reports.sales_invoices_tab') }} ({{ $salesCount }})
                    </button>
                    <button type="button" @click="activeTab = 'purchases'"
                            class="px-4 py-2.5 text-xs font-bold rounded-t-lg transition border-b-2"
                            :class="activeTab === 'purchases' ? 'bg-white text-emerald-600 border-emerald-600 shadow-sm' : 'text-gray-500 border-transparent hover:text-gray-700'">
                        {{ __('reports.purchases_invoices_tab') }} ({{ $purchasesCount }})
                    </button>
                    <button type="button" @click="activeTab = 'purchase_returns'"
                            class="px-4 py-2.5 text-xs font-bold rounded-t-lg transition border-b-2"
                            :class="activeTab === 'purchase_returns' ? 'bg-white text-red-600 border-red-600 shadow-sm' : 'text-gray-500 border-transparent hover:text-gray-700'">
                        {{ __('reports.purchases_returns_tab') }} ({{ $purchaseReturnsCount }})
                    </button>
                    <button type="button" @click="activeTab = 'expenses'"
                            class="px-4 py-2.5 text-xs font-bold rounded-t-lg transition border-b-2"
                            :class="activeTab === 'expenses' ? 'bg-white text-amber-600 border-amber-600 shadow-sm' : 'text-gray-500 border-transparent hover:text-gray-700'">
                        {{ __('reports.expenses_tab') }} ({{ $taxableExpensesCount }})
                    </button>
                </div>

                {{-- تبويب 1: فواتير المبيعات --}}
                <div x-show="activeTab === 'sales'" class="p-4">
                    @if ($salesInvoices->isEmpty())
                        <p class="text-center text-gray-400 py-8 text-sm">لا توجد فواتير مبيعات في هذه الفترة</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-start">
                                <thead class="bg-gray-50 text-gray-500 border-b">
                                    <tr>
                                        <th class="p-2.5 text-start">{{ __('reports.invoice_number') }}</th>
                                        <th class="p-2.5 text-start">{{ __('reports.customer') }}</th>
                                        <th class="p-2.5 text-center">{{ __('reports.date') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.taxable_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.tax_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.total_with_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($salesInvoices as $inv)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2.5 font-medium text-[#1456E8]"><a href="{{ route('transport.invoices.show', $inv->id) }}" class="hover:underline">{{ $inv->invoice_number }}</a></td>
                                            <td class="p-2.5">{{ $inv->customer?->name ?? '-' }}</td>
                                            <td class="p-2.5 text-center text-gray-500">{{ \Illuminate\Support\Carbon::parse($inv->issue_date)->format('Y-m-d') }}</td>
                                            <td class="p-2.5 text-end font-mono">{{ number_format($inv->subtotal, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono text-blue-600 font-bold">{{ number_format($inv->tax_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono font-bold">{{ number_format($inv->subtotal + $inv->tax_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- تبويب 2: مرتجع المبيعات --}}
                <div x-show="activeTab === 'sales_returns'" class="p-4">
                    @if ($salesReturnsList->isEmpty())
                        <p class="text-center text-gray-400 py-8 text-sm">لا توجد مرتجعات مبيعات في هذه الفترة</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-start">
                                <thead class="bg-gray-50 text-gray-500 border-b">
                                    <tr>
                                        <th class="p-2.5 text-start">{{ __('reports.invoice_number') }}</th>
                                        <th class="p-2.5 text-start">{{ __('reports.product') }}</th>
                                        <th class="p-2.5 text-center">{{ __('reports.date') }}</th>
                                        <th class="p-2.5 text-center">{{ __('reports.quantity') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.taxable_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.tax_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.total_with_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($salesReturnsList as $ret)
                                        @php
                                            $bAmount = ($ret->unit_price * $ret->quantity) - $ret->discount_amount;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2.5 font-medium text-red-600">{{ $ret->invoice?->invoice_number ?? '-' }}</td>
                                            <td class="p-2.5">{{ $ret->product?->name ?? '-' }}</td>
                                            <td class="p-2.5 text-center text-gray-500">{{ $ret->created_at->format('Y-m-d') }}</td>
                                            <td class="p-2.5 text-center font-mono">{{ $ret->quantity }}</td>
                                            <td class="p-2.5 text-end font-mono">{{ number_format($bAmount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono text-red-600 font-bold">{{ number_format($ret->tax_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono font-bold">{{ number_format($bAmount + $ret->tax_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- تبويب 3: فواتير المشتريات --}}
                <div x-show="activeTab === 'purchases'" class="p-4">
                    @if ($purchasesList->isEmpty())
                        <p class="text-center text-gray-400 py-8 text-sm">لا توجد فواتير مشتريات في هذه الفترة</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-start">
                                <thead class="bg-gray-50 text-gray-500 border-b">
                                    <tr>
                                        <th class="p-2.5 text-start">رقم فاتورة الشراء</th>
                                        <th class="p-2.5 text-start">{{ __('reports.supplier') }}</th>
                                        <th class="p-2.5 text-center">{{ __('reports.date') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.taxable_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.tax_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.total_with_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($purchasesList as $pur)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2.5 font-medium text-emerald-700">{{ $pur->purchase_number }}</td>
                                            <td class="p-2.5">{{ $pur->supplier?->name ?? '-' }}</td>
                                            <td class="p-2.5 text-center text-gray-500">{{ $pur->issue_date }}</td>
                                            <td class="p-2.5 text-end font-mono">{{ number_format($pur->subtotal - $pur->discount_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono text-emerald-600 font-bold">{{ number_format($pur->tax_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono font-bold">{{ number_format($pur->grand_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- تبويب 4: مرتجع المشتريات --}}
                <div x-show="activeTab === 'purchase_returns'" class="p-4">
                    @if ($purchaseReturnsList->isEmpty())
                        <p class="text-center text-gray-400 py-8 text-sm">لا توجد مرتجعات مشتريات في هذه الفترة</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-start">
                                <thead class="bg-gray-50 text-gray-500 border-b">
                                    <tr>
                                        <th class="p-2.5 text-start">رقم إشعار المرتجع</th>
                                        <th class="p-2.5 text-start">{{ __('reports.supplier') }}</th>
                                        <th class="p-2.5 text-center">{{ __('reports.date') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.taxable_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.tax_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.total_with_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($purchaseReturnsList as $pret)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2.5 font-medium text-red-600">{{ $pret->return_number }}</td>
                                            <td class="p-2.5">{{ $pret->supplier?->name ?? '-' }}</td>
                                            <td class="p-2.5 text-center text-gray-500">{{ $pret->return_date }}</td>
                                            <td class="p-2.5 text-end font-mono">{{ number_format($pret->subtotal - $pret->discount_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono text-red-600 font-bold">{{ number_format($pret->tax_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono font-bold">{{ number_format($pret->grand_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- تبويب 5: المصروفات الخاضعة للضريبة --}}
                <div x-show="activeTab === 'expenses'" class="p-4">
                    @if ($taxableExpensesList->isEmpty())
                        <p class="text-center text-gray-400 py-8 text-sm">لا توجد سندات صرف لمصروفات خاضعة للضريبة في هذه الفترة</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-start">
                                <thead class="bg-gray-50 text-gray-500 border-b">
                                    <tr>
                                        <th class="p-2.5 text-start">رقم السند</th>
                                        <th class="p-2.5 text-start">{{ __('reports.expense_account') }}</th>
                                        <th class="p-2.5 text-start">البيان</th>
                                        <th class="p-2.5 text-center">{{ __('reports.date') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.taxable_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.tax_amount') }}</th>
                                        <th class="p-2.5 text-end">{{ __('reports.total_with_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($taxableExpensesList as $exp)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2.5 font-medium text-amber-700">{{ $exp->voucher_number }}</td>
                                            <td class="p-2.5 font-semibold text-gray-800">{{ $exp->expense_account_name ?? '-' }}</td>
                                            <td class="p-2.5 text-gray-500">{{ $exp->description ?? '-' }}</td>
                                            <td class="p-2.5 text-center text-gray-500">{{ $exp->voucher_date }}</td>
                                            <td class="p-2.5 text-end font-mono">{{ number_format($exp->net_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono text-amber-600 font-bold">{{ number_format($exp->tax_amount, 2) }}</td>
                                            <td class="p-2.5 text-end font-mono font-bold">{{ number_format($exp->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
    function setPeriod(from, to) {
        document.getElementById('filter_date_from').value = from;
        document.getElementById('filter_date_to').value = to;
        document.getElementById('tax-filter-form').submit();
    }
    </script>
</x-app-layout>
