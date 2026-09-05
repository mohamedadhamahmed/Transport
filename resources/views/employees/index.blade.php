<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="3.5" /><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('employees.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('employees.subtitle') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @can('employees.create')
                    <a href="{{ route('employees.import.form') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition whitespace-nowrap">
                        {{ __('employees.import_title') }}
                    </a>
                    <a href="{{ route('employees.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        {{ __('employees.new_employee') }}
                    </a>
                    @endcan
                </div>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('employees.search_placeholder') }}"
                           class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('employees.all_statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('employees.status_active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('employees.status_inactive') }}</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>{{ __('employees.status_terminated') }}</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('employees.search') }}
                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employees.employee_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employees.name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employees.job_title') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employees.branch') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employees.phone') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employees.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($employees as $employee)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $employee->employee_number }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $employee->job_title }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $employee->branch->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $employee->phone }}</td>
                                <td class="px-4 py-3">
                                    @if($employee->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">{{ __('employees.status_active') }}</span>
                                    @elseif($employee->status === 'terminated')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('employees.status_terminated') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">{{ __('employees.status_inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        @can('employees.edit')
                                        <a href="{{ route('employees.edit', $employee->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                            {{ __('employees.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('employees.toggle-status', $employee->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium {{ $employee->status === 'active' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }} transition">
                                                {{ $employee->status === 'active' ? __('employees.deactivate') : __('employees.activate') }}
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('employees.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($employees->hasPages())
                <div class="mt-4">{{ $employees->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
