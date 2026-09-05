<x-app-layout>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="dc-print-hide rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 8v4l3 3M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('reports.accounts.aging') }}</h2>
                </div>
                <a href="{{ route('reports.accounts.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                    {{ __('reports.accounts.title') }}
                </a>
            </div>

            <h2 class="dc-print-only text-xl font-bold text-center">{{ __('reports.accounts.aging') }}</h2>

            {{-- فلاتر: نوع + بحث --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <form method="GET" action="{{ url()->current() }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-52">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.aging_type') }}</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                            <option value="" @selected(!$type)>{{ __('reports.aging_all_types') }}</option>
                            <option value="customer" @selected($type === 'customer')>{{ __('reports.customer') }}</option>
                            <option value="supplier" @selected($type === 'supplier')>{{ __('reports.supplier') }}</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-64">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.search_placeholder') }}</label>
                        <input type="text" name="q" value="{{ $q }}" placeholder="{{ __('reports.search_placeholder') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition">
                            {{ __('reports.apply_filters') }}
                        </button>
                        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
                            {{ __('reports.print') }}
                        </button>
                        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition">
                            {{ __('reports.export_excel') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- بطاقات الإجمالي --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.aging_0_30') }}</p>
                    <p class="text-lg font-bold text-[#0d9488] mt-0.5">{{ number_format($rows->sum('bucket_0_30'), 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.aging_31_60') }}</p>
                    <p class="text-lg font-bold text-[#1456E8] mt-0.5">{{ number_format($rows->sum('bucket_31_60'), 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.aging_61_90') }}</p>
                    <p class="text-lg font-bold text-[#F5811E] mt-0.5">{{ number_format($rows->sum('bucket_61_90'), 2) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-gray-400">{{ __('reports.aging_over_90') }}</p>
                    <p class="text-lg font-bold text-[#e11d48] mt-0.5">{{ number_format($rows->sum('bucket_over_90'), 2) }}</p>
                </div>
                <div class="bg-[#0F1B4C] rounded-xl p-4 shadow-sm">
                    <p class="text-[11px] text-white/50">{{ __('reports.net_total') }}</p>
                    <p class="text-lg font-bold text-white mt-0.5">{{ number_format($rows->sum('total'), 2) }}</p>
                </div>
            </div>

            {{-- جدول الأعمار --}}
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl dc-print-plain">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-start px-4 py-3">{{ __('reports.account_name') }}</th>
                                <th class="text-start px-4 py-3">{{ __('reports.aging_type') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.aging_0_30') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.aging_31_60') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.aging_61_90') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.aging_over_90') }}</th>
                                <th class="text-end px-4 py-3">{{ __('reports.net_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-4 py-2.5 text-[#0F1B4C] font-medium">
                                        {{ $row->name }}
                                        <span class="text-[11px] text-gray-400">({{ $row->account_number ?? '-' }})</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $row->type_label }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ $row->bucket_0_30 != 0 ? number_format($row->bucket_0_30, 2) : '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ $row->bucket_31_60 != 0 ? number_format($row->bucket_31_60, 2) : '-' }}</td>
                                    <td class="px-4 py-2.5 text-end text-gray-600">{{ $row->bucket_61_90 != 0 ? number_format($row->bucket_61_90, 2) : '-' }}</td>
                                    <td class="px-4 py-2.5 text-end {{ $row->bucket_over_90 > 0 ? 'text-[#e11d48] font-semibold' : 'text-gray-600' }}">{{ $row->bucket_over_90 != 0 ? number_format($row->bucket_over_90, 2) : '-' }}</td>
                                    <td class="px-4 py-2.5 text-end font-bold text-[#0F1B4C]">{{ number_format($row->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('reports.no_aging_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold text-[#0F1B4C]">
                                    <td class="px-4 py-3" colspan="2">{{ __('reports.total') }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('bucket_0_30'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('bucket_31_60'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('bucket_61_90'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('bucket_over_90'), 2) }}</td>
                                    <td class="px-4 py-3 text-end">{{ number_format($rows->sum('total'), 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
