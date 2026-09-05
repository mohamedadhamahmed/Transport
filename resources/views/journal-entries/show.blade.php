<x-app-layout>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16M4 12h16M4 18h10"/>
                        </svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-white font-bold text-lg leading-tight">{{ __('journal_entries.entry_no') }} #{{ $entry->entry_number }}</h2>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $entry->isOpening() ? 'bg-amber-400/20 text-amber-200' : 'bg-white/10 text-white/70' }}">
                                {{ $entry->isOpening() ? __('journal_entries.opening_badge') : __('journal_entries.daily_badge') }}
                            </span>
                        </div>
                        <p class="text-white/45 text-xs mt-0.5">{{ $entry->entry_date->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @can('journal_entries.edit')
                    <a href="{{ route('journal-entries.edit', $entry) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('journal_entries.edit_entry') }}
                    </a>
                    @endcan
                    <a href="{{ route('journal-entries.print', $entry) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        {{ __('journal_entries.print') }}
                    </a>
                    <a href="{{ route('journal-entries.index', ['type' => $entry->entry_type]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('journal_entries.back_to_list') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('journal_entries.branch') }}</div>
                        <div class="font-medium text-gray-800">{{ $entry->branch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('journal_entries.cost_center') }}</div>
                        <div class="font-medium text-gray-800">{{ $entry->costCenter?->cost_center_ar ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-1">{{ __('journal_entries.created_by') }}</div>
                        <div class="font-medium text-gray-800">{{ $entry->creator?->name ?? '-' }}</div>
                    </div>
                    @if ($entry->description)
                        <div class="md:col-span-3">
                            <div class="text-xs text-gray-400 mb-1">{{ __('journal_entries.description') }}</div>
                            <div class="text-gray-700">{{ $entry->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('journal_entries.account') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('journal_entries.note') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('journal_entries.debit') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('journal_entries.credit') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($entry->lines as $line)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $line->account?->name ?? '#' . $line->account_id }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $line->note ?? '-' }}</td>
                                    <td class="px-3 py-2 text-emerald-700">{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                                    <td class="px-3 py-2 text-red-600">{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="2" class="px-3 py-2.5 text-gray-600">{{ __('journal_entries.total_debit') }} / {{ __('journal_entries.total_credit') }}</td>
                                <td class="px-3 py-2.5 text-emerald-700">{{ number_format($entry->total_debit, 2) }}</td>
                                <td class="px-3 py-2.5 text-red-600">{{ number_format($entry->total_credit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
