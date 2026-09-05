<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.hr.payroll') }}</h2>
                </div>
                <a href="{{ route('reports.hr.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hr.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.hr.payroll') }}</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasPostingSelect' => true,
                    'hasEntitySelect' => true,
                    'entityParam' => 'employee_id',
                    'entityId' => $employeeId,
                    'entityLabel' => __('reports.employee'),
                    'entityAllLabel' => __('reports.all_employees'),
                    'entityOptions' => $employees->pluck('name', 'id'),
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.employee_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.employee') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.salary') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.bonus') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.attendance_discount') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.loan_deduction') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_pay') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($row->employee)->employee_number ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ optional($row->employee)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format($row->salary, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-emerald-600">{{ number_format($row->bonus, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end dc-text-red-strong">{{ number_format($row->deduction, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end dc-text-red-strong">{{ number_format($row->loan_deduction, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format($row->net, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_payroll_rows_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="2">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalSalary, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalBonus, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalDeduction, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalLoanDeduction, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalNet, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
