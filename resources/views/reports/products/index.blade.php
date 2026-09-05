<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8 12 3 3 8l9 5 9-5ZM3 8v8l9 5m0-13v13m9-13v8l-9 5"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ __('reports.products.title') }}</h2>
                        <p class="text-white/60 text-sm">{{ __('reports.products.subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.hub_title') }}
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                @foreach (collect([
                    ['route' => 'reports.products.stock', 'label' => __('reports.products.stock'), 'desc' => __('reports.products.stock_desc'), 'icon' => 'M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'perm' => 'reports_products.stock'],
                    ['route' => 'reports.products.low-stock', 'label' => __('reports.products.low_stock'), 'desc' => __('reports.products.low_stock_desc'), 'icon' => 'M12 9v4m0 4h.01M10.3 3.9 2.5 17a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z', 'perm' => 'reports_products.low_stock'],
                    ['route' => 'reports.products.transfers', 'label' => __('reports.products.transfers'), 'desc' => __('reports.products.transfers_desc'), 'icon' => 'M17 3v4a1 1 0 0 1-1 1H4M7 21v-4a1 1 0 0 1 1-1h12M7 7 3 3M20 21l-4-4', 'perm' => 'reports_products.stock_transfers'],
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
