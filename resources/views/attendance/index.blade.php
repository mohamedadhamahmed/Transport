<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="17" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('attendance.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('attendance.subtitle') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @can('attendance.create')
                    <a href="{{ route('attendance.import.form') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition whitespace-nowrap">
                        {{ __('attendance.import_from_biometric') }}
                    </a>
                    <a href="{{ route('attendance.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        {{ __('attendance.new_entry') }}
                    </a>
                    @endcan
                </div>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                    <p class="text-xs text-gray-500">{{ __('attendance.summary_present') }}</p>
                    <p class="text-xl font-bold text-emerald-600 mt-1">{{ $summary['present'] }}</p>
                </div>
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                    <p class="text-xs text-gray-500">{{ __('attendance.summary_absent') }}</p>
                    <p class="text-xl font-bold text-rose-600 mt-1">{{ $summary['absent'] }}</p>
                </div>
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                    <p class="text-xs text-gray-500">{{ __('attendance.summary_late_minutes') }}</p>
                    <p class="text-xl font-bold text-amber-600 mt-1">{{ $summary['late_minutes'] }}</p>
                </div>
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                    <p class="text-xs text-gray-500">{{ __('attendance.summary_overtime_hours') }}</p>
                    <p class="text-xl font-bold text-[#1456E8] mt-1">{{ number_format((float) $summary['overtime_hours'], 2) }}</p>
                </div>
                <div class="bg-white shadow-sm border border-gray-100 rounded-xl p-4">
                    <p class="text-xs text-gray-500">{{ __('attendance.summary_discount') }}</p>
                    <p class="text-xl font-bold text-gray-700 mt-1">{{ number_format((float) $summary['discount_amount'], 2) }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <input type="month" name="month" value="{{ $month }}"
                           class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <select name="employee_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('attendance.all_employees') }}</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string) request('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->employee_number }})
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('attendance.filter') }}
                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.check_in') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.check_out') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.status') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.late_minutes') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.overtime_hours') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.discount_amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('attendance.source') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($attendances as $attendance)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-600">{{ optional($attendance->date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $attendance->employee->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $attendance->check_in }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $attendance->check_out }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'present' => 'bg-emerald-50 text-emerald-700',
                                            'absent' => 'bg-rose-50 text-rose-700',
                                            'leave' => 'bg-sky-50 text-sky-700',
                                            'holiday' => 'bg-purple-50 text-purple-700',
                                            'weekend' => 'bg-gray-100 text-gray-600',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ __('attendance.status_' . $attendance->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $attendance->late_minutes }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format((float) $attendance->overtime_hours, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ number_format((float) $attendance->discount_amount, 2) }}
                                    @if($attendance->is_connected_penalty)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-600 align-middle">{{ __('attendance.connected_penalty_badge') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ __('attendance.source_' . $attendance->source) }}</td>
                                <td class="px-4 py-3">
                                    @can('attendance.create')
                                    <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" class="flex justify-end">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition">
                                            {{ __('attendance.delete') }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="px-4 py-10 text-center text-gray-400">{{ __('attendance.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($attendances->hasPages())
                <div class="mt-4">{{ $attendances->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
