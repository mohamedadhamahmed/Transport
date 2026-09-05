<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h11v8H3zM14 10h4l3 3v2h-7zM6.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM17.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.delivery.title') }}</h2>
                        <p class="text-white/60 text-sm">{{ __('reports.delivery.subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hub_title') }}
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                @foreach (collect([
                    ['route' => 'reports.delivery.summary', 'label' => __('reports.delivery.summary'), 'desc' => __('reports.delivery.summary_desc'), 'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 13h6M9 17h6', 'perm' => 'reports_delivery.summary'],
                    ['route' => 'reports.delivery.pending', 'label' => __('reports.delivery.pending'), 'desc' => __('reports.delivery.pending_desc'), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'perm' => 'reports_delivery.pending'],
                    ['route' => 'reports.delivery.by-employee', 'label' => __('reports.delivery.by_employee'), 'desc' => __('reports.delivery.by_employee_desc'), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'perm' => 'reports_delivery.by_employee'],
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
