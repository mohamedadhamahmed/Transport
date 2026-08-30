<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('deliverynote.convert_title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('deliverynote.convert_subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('deliverynote.history') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                    {{ __('deliverynote.delivery_history') }}
                </a>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.chooseclient') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.operations') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($customers as $customer)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $customer->name }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('deliverynote.convert.create', $customer->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                                        {{ __('deliverynote.review_pending') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-gray-400">{{ __('deliverynote.no_pending_customers') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
