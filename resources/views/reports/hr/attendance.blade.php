<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.hr.attendance') }}</h2>
                </div>
                <a href="{{ route('reports.hr.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hr.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.hr.attendance') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasDateRange' => true,
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
                                <th class="text-end px-4 py-3">{{ __('reports.present_days') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.absent_days') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.leave_days') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.late_minutes') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.overtime_amount') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.attendance_discount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $row->employee_number }}</td>
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ $row->employee_name }}</td>
                                    <td class="px-4 py-2.5 text-end text-emerald-600">{{ (int) $row->present_days }}</td>
                                    <td class="px-4 py-2.5 text-end dc-text-red-strong">{{ (int) $row->absent_days }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ (int) $row->leave_days }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ (int) $row->total_late_minutes }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $row->total_overtime_amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-end dc-text-red-strong">{{ number_format((float) $row->total_discount_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_attendance_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="2">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ $totalPresent }}</td>
                                    <td class="px-4 py-3 text-end">{{ $totalAbsent }}</td>
                                    <td class="px-4 py-3 text-end">{{ $totalLeave }}</td>
                                    <td class="px-4 py-3 text-end">{{ $totalLateMinutes }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalOvertimeAmount, 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalDiscountAmount, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
