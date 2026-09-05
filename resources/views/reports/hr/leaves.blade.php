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
                    <h2 class="text-white font-bold text-lg">{{ __('reports.hr.leaves') }}</h2>
                </div>
                <a href="{{ route('reports.hr.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hr.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.hr.leaves') }} ({{ $dateFrom }} → {{ $dateTo }})</h2>

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">

                @include('reports._filters', [
                    'hasDateRange' => true,
                    'hasEntitySelect' => true,
                    'entityParam' => 'employee_id',
                    'entityId' => $employeeId,
                    'entityLabel' => __('reports.employee'),
                    'entityAllLabel' => __('reports.all_employees'),
                    'entityOptions' => $employees->pluck('name', 'id'),
                    'hasStatus' => true,
                    'statusLabel' => __('reports.leave_status'),
                    'statusOptions' => [
                        \App\Models\LeaveRequest::STATUS_PENDING => __('reports.leave_status_pending'),
                        \App\Models\LeaveRequest::STATUS_APPROVED => __('reports.leave_status_approved'),
                        \App\Models\LeaveRequest::STATUS_REJECTED => __('reports.leave_status_rejected'),
                    ],
                ])

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.employee') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.leave_type') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.leave_start_date') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.leave_end_date') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.days_count') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.leave_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leaves as $leave)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C]">{{ optional($leave->employee)->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ __('reports.leave_type_' . $leave->type) }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($leave->start_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-gray-400 text-xs">{{ optional($leave->end_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ number_format((float) $leave->days_count, 2) }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($leave->status === \App\Models\LeaveRequest::STATUS_APPROVED)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">{{ __('reports.leave_status_approved') }}</span>
                                        @elseif ($leave->status === \App\Models\LeaveRequest::STATUS_REJECTED)
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full dc-bg-red-soft dc-text-red-strong">{{ __('reports.leave_status_rejected') }}</span>
                                        @else
                                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full dc-note-blue">{{ __('reports.leave_status_pending') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_leaves_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($leaves->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="4">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($totalDays, 2) }}</td>
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
