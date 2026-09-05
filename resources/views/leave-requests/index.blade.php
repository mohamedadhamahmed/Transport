<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="17" rx="2" /><path d="M16 2v4M8 2v4M3 10h18M8 14h3" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">{{ __('leave_requests.title') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('leave_requests.subtitle') }}</p>
                </div>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            @can('leave_requests.view')
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('leave_requests.new_request') }}</h3>
                <form method="POST" action="{{ route('leave-requests.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('leave_requests.employee') }} *</label>
                        <select name="employee_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">-</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('leave_requests.type') }} *</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="annual">{{ __('leave_requests.type_annual') }}</option>
                            <option value="sick">{{ __('leave_requests.type_sick') }}</option>
                            <option value="unpaid">{{ __('leave_requests.type_unpaid') }}</option>
                            <option value="emergency">{{ __('leave_requests.type_emergency') }}</option>
                            <option value="other">{{ __('leave_requests.type_other') }}</option>
                        </select>
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('leave_requests.start_date') }} *</label>
                        <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('leave_requests.end_date') }} *</label>
                        <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div></div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('leave_requests.reason') }}</label>
                        <input type="text" name="reason" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                            {{ __('leave_requests.save') }}
                        </button>
                    </div>
                </form>
            </div>
            @endcan

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('leave_requests.balance_title') }}</h3>
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.balance_days') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($employees as $employee)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->name }} ({{ $employee->employee_number }})</td>
                                <td class="px-4 py-3 text-gray-600">{{ $balances[$employee->id]->balance_days }}</td>
                                <td class="px-4 py-3">
                                    @can('leave_requests.view')
                                    <form method="POST" action="{{ route('leave-requests.balance.update', $employee->id) }}" class="flex items-center gap-2 justify-end">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" step="0.01" min="0" name="balance_days"
                                               value="{{ $balances[$employee->id]->balance_days }}"
                                               class="w-24 rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition whitespace-nowrap">
                                            {{ __('leave_requests.update_balance') }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <select name="employee_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('leave_requests.all_employees') }}</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string) request('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                    <select name="type" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('leave_requests.all_types') }}</option>
                        <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>{{ __('leave_requests.type_annual') }}</option>
                        <option value="sick" {{ request('type') === 'sick' ? 'selected' : '' }}>{{ __('leave_requests.type_sick') }}</option>
                        <option value="unpaid" {{ request('type') === 'unpaid' ? 'selected' : '' }}>{{ __('leave_requests.type_unpaid') }}</option>
                        <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>{{ __('leave_requests.type_emergency') }}</option>
                        <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>{{ __('leave_requests.type_other') }}</option>
                    </select>
                    <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('leave_requests.all_statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('leave_requests.status_pending') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('leave_requests.status_approved') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('leave_requests.status_rejected') }}</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('leave_requests.filter') }}
                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.type') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.start_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.end_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.days_count') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('leave_requests.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($leaveRequests as $leave)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $leave->employee->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ __('leave_requests.type_' . $leave->type) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($leave->start_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($leave->end_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $leave->days_count }}</td>
                                <td class="px-4 py-3">
                                    @if($leave->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">{{ __('leave_requests.status_approved') }}</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700">{{ __('leave_requests.status_rejected') }}</span>
                                        @if($leave->rejection_reason)
                                        <p class="text-xs text-gray-400 mt-1">{{ $leave->rejection_reason }}</p>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">{{ __('leave_requests.status_pending') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end flex-wrap">
                                        @can('leave_requests.view')
                                        @if($leave->status === 'pending')
                                        <form method="POST" action="{{ route('leave-requests.approve', $leave->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition">
                                                {{ __('leave_requests.approve') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('leave-requests.reject', $leave->id) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="rejection_reason" placeholder="{{ __('leave_requests.reject_title') }}" required
                                                   class="rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition whitespace-nowrap">
                                                {{ __('leave_requests.reject') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('leave-requests.destroy', $leave->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                                {{ __('leave_requests.delete') }}
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('leave_requests.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leaveRequests->hasPages())
                <div class="mt-4">{{ $leaveRequests->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
