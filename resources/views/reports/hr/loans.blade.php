<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.hr.loans') }}</h2>
                </div>
                <a href="{{ route('reports.hr.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hr.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.hr.loans') }} - {{ __('reports.as_of') }} {{ now()->format('Y-m-d') }}</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasEntitySelect' => true,
                    'entityParam' => 'employee_id',
                    'entityId' => $employeeId,
                    'entityLabel' => __('reports.employee'),
                    'entityAllLabel' => __('reports.all_employees'),
                    'entityOptions' => $employees->pluck('name', 'id'),
                    'hasStatus' => true,
                    'statusLabel' => __('reports.loan_status'),
                    'statusOptions' => [
                        \App\Models\EmployeeLoan::STATUS_ACTIVE => __('reports.loan_status_active'),
                        \App\Models\EmployeeLoan::STATUS_SETTLED => __('reports.loan_status_settled'),
                    ],
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.employee') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.loan_type') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.date') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.loan_amount') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.paid_amount') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.remaining_amount') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.loan_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loans as $loan)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ optional($loan->employee)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ $loan->type === \App\Models\EmployeeLoan::TYPE_CUSTODY ? __('reports.loan_type_custody') : __('reports.loan_type_loan') }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($loan->date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $loan->amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $loan->paid_amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format($loan->remaining, 2) }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($loan->isActive())
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full dc-note-blue">{{ __('reports.loan_status_active') }}</span>
                                        @else
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">{{ __('reports.loan_status_settled') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_loans_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($loans->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="3">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalAmount, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalPaid, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalRemaining, 2) }}</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
