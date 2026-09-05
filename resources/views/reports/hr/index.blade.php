<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.hr.title') }}</h2>
                        <p class="text-white/60 text-sm">{{ __('reports.hr.subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hub_title') }}
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                @foreach (collect([
                    ['route' => 'reports.hr.payroll', 'label' => __('reports.hr.payroll'), 'desc' => __('reports.hr.payroll_desc'), 'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'perm' => 'reports_hr.payroll'],
                    ['route' => 'reports.hr.attendance', 'label' => __('reports.hr.attendance'), 'desc' => __('reports.hr.attendance_desc'), 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z', 'perm' => 'reports_hr.attendance'],
                    ['route' => 'reports.hr.loans', 'label' => __('reports.hr.loans'), 'desc' => __('reports.hr.loans_desc'), 'icon' => 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'perm' => 'reports_hr.loans'],
                    ['route' => 'reports.hr.employees', 'label' => __('reports.hr.employees'), 'desc' => __('reports.hr.employees_desc'), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z', 'perm' => 'reports_hr.employees'],
                    ['route' => 'reports.hr.bonuses-deductions', 'label' => __('reports.hr.bonuses_deductions'), 'desc' => __('reports.hr.bonuses_deductions_desc'), 'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'perm' => 'reports_hr.bonuses_deductions'],
                    ['route' => 'reports.hr.leaves', 'label' => __('reports.hr.leaves'), 'desc' => __('reports.hr.leaves_desc'), 'icon' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z', 'perm' => 'reports_hr.leaves'],
                ])->filter(fn ($card) => auth()->user()?->hasPermission($card['perm'])) as $card)
                    <a href="{{ route($card['route']) }}"
                       class="dc-card-link bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex items-start gap-4">
                        <span class="w-10 h-10 shrink-0 rounded-lg bg-[#1456E8]/10 text-[#1456E8] flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $card['icon'] }}"/></svg>
                        </span>
                        <div>
                            <h3 class="dc-card-link-title font-bold text-[#0F1B4C] transition">{{ $card['label'] }}</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ $card['desc'] }}</p>
                        </div>
                    </a>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>
