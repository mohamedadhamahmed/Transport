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
                            <path d="M8 13h5M8 17h8" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('invoices.drafts_title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('invoices.drafts_subtitle') }}</p>
                    </div>
                </div>
                <a href="{{ route('invoices.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#F5811E] hover:brightness-95 transition shadow-sm shadow-black/10">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    {{ __('invoices.new_invoice') }}
                </a>
            </div>

            @if (session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm px-4 py-3">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm px-4 py-3">
                {{ session('error') }}
            </div>
            @endif

            {{-- الجدول --}}
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.draft_customer') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.draft_branch') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.draft_payment_method') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.draft_items_count') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.draft_total') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.draft_created_at') }}</th>
                                <th class="px-3 py-2.5 text-start text-xs font-semibold uppercase tracking-wide">{{ __('invoices.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($drafts as $draft)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-3 py-2.5 font-medium text-gray-800">{{ $draft->customer->name ?? '-' }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">{{ $draft->branch->name ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-gray-500">{{ __('invoices.' . $draft->payment_method) }}</td>
                                <td class="px-3 py-2.5 text-gray-500 tabular-nums">{{ $draft->items_count }}</td>
                                <td class="px-3 py-2.5 font-semibold text-[#0F1B4C] tabular-nums">{{ number_format($draft->total, 2) }}</td>
                                <td class="px-3 py-2.5 text-gray-500 whitespace-nowrap">{{ $draft->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('invoices.create', ['draft_id' => $draft->id]) }}"
                                            style="background-color:#1456E8; color:#ffffff;"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-[#1456E8] hover:brightness-95 transition whitespace-nowrap">
                                            {{ __('invoices.open_draft') }}
                                        </a>
                                        <form method="POST" action="{{ route('invoices.drafts.destroy', $draft) }}"
                                            onsubmit="return confirm('{{ __('invoices.confirm_delete_draft') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 transition whitespace-nowrap">
                                                {{ __('invoices.delete_draft') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center text-gray-400">
                                    {{ __('invoices.no_drafts') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>