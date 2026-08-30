<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5V6a2 2 0 0 1 2-2h9l5 5v10.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                            <path d="M14 4v4a1 1 0 0 0 1 1h4" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('deliverynote.delivery_history') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('deliverynote.delivery_product') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('deliverynote.convert.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#F5811E] hover:brightness-95 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                        </svg>
                        {{ __('deliverynote.approve_and_invoice') }}
                    </a>
                    <a href="{{ route('deliverynote.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        {{ __('deliverynote.new_product') }}
                    </a>
                </div>
            </div>

            {{-- فلترة --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" action="{{ route('deliverynote.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.from') }}</label>
                        <input type="date" name="start_at" value="{{ request('start_at', date('Y-m-01')) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.to') }}</label>
                        <input type="date" name="end_at" value="{{ request('end_at', date('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('deliverynote.chooseclient') }}</label>
                        <select name="customer_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('deliverynote.all') }}</option>
                            @foreach($Customer as $customer)
                                <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#0F1B4C] hover:bg-[#0F1B4C]/90 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            {{ __('deliverynote.search') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- جدول التسليمات --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">#</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.decoumentNo') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.chooseclient') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.status_active') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('deliverynote.operations') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($invoices as $invoice)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $invoice->id }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $invoice->customer->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $invoice->user->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($invoice->Price, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if($invoice->status == 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">{{ __('deliverynote.pending') }}</span>
                                    @elseif($invoice->status == 1)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">{{ __('deliverynote.returned') }}</span>
                                    @elseif($invoice->status == 3)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">{{ __('deliverynote.converted') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">{{ __('deliverynote.partially_returned') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('deliverynote.show', $invoice->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            {{ __('deliverynote.view') }}
                                        </a>
                                        @if($invoice->status != 1 && $invoice->status != 3)
                                        <a href="{{ route('deliverynote.return.create', $invoice->id) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[#F5811E] bg-[#F5811E]/10 hover:bg-[#F5811E]/20 transition">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 7v6h6" /><path d="M3 13a9 9 0 1 0 3-6.7L3 9" />
                                            </svg>
                                            {{ __('deliverynote.delivery_return') }}
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400">{{ __('deliverynote.no_data') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($invoices->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $invoices->appends(request()->all())->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
