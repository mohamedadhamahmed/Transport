<x-app-layout>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 8l9-5 9 5-9 5-9-5Z" />
                        <path d="M3 8v8l9 5 9-5V8" />
                        <path d="M12 13v8" />
                    </svg>
                </span>
                <h2 class="text-white font-bold text-lg">
                    {{ $mode === 'receive' ? __('stock_transfers.choose_branch_receive_title') : __('stock_transfers.choose_branch_dispatch_title') }}
                </h2>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <p class="text-sm text-gray-500 mb-4">{{ __('stock_transfers.choose_branch_hint') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse ($branches as $branch)
                        <a href="{{ $mode === 'receive' ? route('stock-transfers.receive-form', $branch) : route('stock-transfers.create', $branch) }}"
                           class="flex items-center justify-between px-4 py-3 rounded-xl border border-gray-200 hover:border-[#1456E8] hover:bg-[#1456E8]/5 transition">
                            <span class="font-medium text-gray-800">{{ $branch->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 6l6 6-6 6"/></svg>
                        </a>
                    @empty
                        <p class="text-gray-400 text-sm">{{ __('stock_transfers.no_branches') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
