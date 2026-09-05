<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.hr.employees') }}</h2>
                </div>
                <a href="{{ route('reports.hr.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hr.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.hr.employees') }}</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'searchPlaceholder' => __('reports.employee'),
                    'hasStatus' => true,
                    'statusLabel' => __('reports.employee_status'),
                    'statusOptions' => [
                        \App\Models\Employee::STATUS_ACTIVE => __('reports.employee_status_active'),
                        \App\Models\Employee::STATUS_INACTIVE => __('reports.employee_status_inactive'),
                        \App\Models\Employee::STATUS_TERMINATED => __('reports.employee_status_terminated'),
                    ],
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.employee_number') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.employee') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.job_title') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.hire_date') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.gross_salary') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.employee_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $employee->employee_number }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $employee->name }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ $employee->job_title ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($employee->hire_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end font-medium text-[#0F1B4C]">{{ number_format((float) $employee->basic_salary + (float) $employee->allowances, 2) }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($employee->isActive())
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">{{ __('reports.employee_status_active') }}</span>
                                        @elseif ($employee->status === \App\Models\Employee::STATUS_TERMINATED)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full dc-bg-red-soft dc-text-red-strong">{{ __('reports.employee_status_terminated') }}</span>
                                        @else
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">{{ __('reports.employee_status_inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_employees_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($employees->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="4">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalBasic + $totalAllowances, 2) }}</td>
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
