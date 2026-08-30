<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3 2.5-5.2 5.5-5.2s5.5 2.2 5.5 5.2"/><path d="M18 8v5M15.5 10.5h5"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('customers.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('customers.subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('customers.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    {{ __('customers.new_customer') }}
                </a>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex gap-3 mb-4">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('customers.search_placeholder') }}"
                           class="w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('customers.search') }}
                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('customers.name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('customers.phone') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('customers.balance') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($customers as $customer)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $customer->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $customer->phone }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($customer->Balance ?? 0, 2) }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                        {{ __('customers.edit') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ __('customers.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($customers->hasPages())
                <div class="mt-4">{{ $customers->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
