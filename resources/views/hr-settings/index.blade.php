<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1100px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">{{ __('hr_settings.title') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('hr_settings.subtitle') }}</p>
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

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('hr_settings.branch') }}</label>
                    <select name="branch_id" onchange="this.form.submit()" class="w-full max-w-xs rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                </form>

                <form method="POST" action="{{ route('hr-settings.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="branchs_id" value="{{ $selectedBranchId }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('hr_settings.work_start_time') }}</label>
                            <input type="time" name="work_start_time"
                                   value="{{ old('work_start_time', \Illuminate\Support\Carbon::parse($hrSetting->work_start_time)->format('H:i')) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('hr_settings.work_end_time') }}</label>
                            <input type="time" name="work_end_time"
                                   value="{{ old('work_end_time', \Illuminate\Support\Carbon::parse($hrSetting->work_end_time)->format('H:i')) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('hr_settings.late_grace_minutes') }}</label>
                            <input type="number" min="0" name="late_grace_minutes" value="{{ old('late_grace_minutes', $hrSetting->late_grace_minutes ?? 15) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('hr_settings.overtime_multiplier') }}</label>
                            <input type="number" step="0.01" min="0" name="overtime_multiplier" value="{{ old('overtime_multiplier', $hrSetting->overtime_multiplier ?? 1.5) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('hr_settings.weekly_off_days') }}</label>
                            <div class="flex flex-wrap gap-4">
                                @php($offDays = old('weekly_off_days', $hrSetting->offDays()))
                                @foreach(['saturday','sunday','monday','tuesday','wednesday','thursday','friday'] as $day)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="weekly_off_days[]" value="{{ $day }}" {{ in_array($day, $offDays, true) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                                    {{ __('hr_settings.day_' . $day) }}
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <hr class="my-2 border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('hr_settings.absence_rules_title') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('hr_settings.absence_deduction_multiplier') }}</label>
                            <input type="number" step="0.01" min="0" name="absence_deduction_multiplier"
                                   value="{{ old('absence_deduction_multiplier', $hrSetting->absence_deduction_multiplier ?? 1) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <p class="text-xs text-gray-400 mt-1">{{ __('hr_settings.absence_deduction_multiplier_hint') }}</p>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="extend_deduction_to_weekly_off" value="1"
                                       {{ old('extend_deduction_to_weekly_off', $hrSetting->shouldExtendDeductionToWeeklyOff()) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
                                {{ __('hr_settings.extend_deduction_to_weekly_off') }}
                            </label>
                        </div>
                        <div class="md:col-span-3">
                            <p class="text-xs text-gray-400">{{ __('hr_settings.extend_deduction_hint') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-6">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                            {{ __('hr_settings.save') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('hr_settings.holidays_title') }}</h3>

                @can('hr_settings.manage')
                <form method="POST" action="{{ route('hr-settings.holidays.store') }}" class="flex flex-wrap items-end gap-3 mb-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('hr_settings.holiday_date') }}</label>
                        <input type="date" name="date" required class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('hr_settings.holiday_name') }}</label>
                        <input type="text" name="name" required class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('hr_settings.holiday_branch') }}</label>
                        <select name="branchs_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">-</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('hr_settings.add_holiday') }}
                    </button>
                </form>
                @endcan

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('hr_settings.holiday_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('hr_settings.holiday_name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('hr_settings.branch') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($holidays as $holiday)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-600">{{ optional($holiday->date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $holiday->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $holiday->branch->name ?? __('hr_settings.holiday_branch') }}</td>
                                <td class="px-4 py-3">
                                    @can('hr_settings.manage')
                                    <form method="POST" action="{{ route('hr-settings.holidays.destroy', $holiday->id) }}" class="flex justify-end">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition">
                                            {{ __('hr_settings.delete') }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">{{ __('hr_settings.no_holidays') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
