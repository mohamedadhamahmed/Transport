<x-app-layout>
    <div class="py-6">
        <div class="max-w-[900px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg">{{ __('products.choose_branch_title') }}</h2>
                <p class="text-white/45 text-xs mt-0.5">{{ __('products.choose_branch_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse($branches as $branch)
                <a href="{{ route('products.index', $branch->id) }}"
                   class="bg-white shadow-sm border border-gray-100 rounded-xl p-5 text-center hover:border-[#1456E8] hover:shadow-md transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-[#1456E8]/10 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-[#1456E8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 21V10l8-6 8 6v11" /><path d="M9 21v-6h6v6" />
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-800">{{ $branch->name }}</p>
                </a>
                @empty
                <p class="text-gray-400 col-span-3 text-center py-10">{{ __('products.no_branches') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
