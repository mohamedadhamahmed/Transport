<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 6H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H7" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('end_of_service.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('end_of_service.subtitle') }}</p>
                    </div>
                </div>
                @can('end_of_service.view')
                <a href="{{ route('end-of-service.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    {{ __('end_of_service.new_settlement') }}
                </a>
                @endcan
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.document_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.termination_type') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.termination_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.years_of_service') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.gross_amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.applied_percentage') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('end_of_service.net_amount') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($settlements as $settlement)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $settlement->document_number }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $settlement->employee->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ __('end_of_service.type_' . $settlement->termination_type) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($settlement->termination_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $settlement->years_of_service }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format((float) $settlement->gross_amount, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $settlement->applied_percentage }}%</td>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ number_format((float) $settlement->net_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    @can('end_of_service.view')
                                    <form method="POST" action="{{ route('end-of-service.destroy', $settlement->id) }}" class="flex justify-end">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition">
                                            {{ __('end_of_service.delete') }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">{{ __('end_of_service.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($settlements->hasPages())
                <div class="mt-4">{{ $settlements->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
