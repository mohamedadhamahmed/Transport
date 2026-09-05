<x-app-layout>

    <div class="py-6">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- هيدر الصفحة بلون البراند الكحلي --}}
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('accounts.list_title') }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('accounts.tree') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('accounts.tree_view') }}
                    </a>
                    <a href="{{ route('accounts.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        + {{ __('accounts.new_account') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                {{-- الفلاتر --}}
                <div class="p-4 border-b border-gray-100">
                    <form method="GET" action="{{ route('accounts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.name') }}</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('accounts.search_placeholder') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.branch') }}</label>
                            <select name="branchs_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('accounts.all_branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(request('branchs_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('accounts.status') }}</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('accounts.all_statuses') }}</option>
                                <option value="active" @selected(request('status') === 'active')>{{ __('accounts.active') }}</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('accounts.inactive') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                                {{ __('accounts.filter') }}
                            </button>
                            @if (request()->hasAny(['q', 'branchs_id', 'status']))
                                <a href="{{ route('accounts.index') }}"
                                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
                                    {{ __('accounts.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-start">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.account_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.parent_account') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.debtor') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.creditor') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.current_balance') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.status') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide">{{ __('accounts.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($accounts as $account)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 text-gray-500">{{ $account->account_number ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $account->name }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $account->parentAccount?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ number_format($account->debtor_current ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ number_format($account->creditor_current ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0F1B4C]">{{ number_format($account->current_balance, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($account->active)
                                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ __('accounts.active') }}</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-500">{{ __('accounts.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('accounts.statement', $account) }}" title="{{ __('accounts.statement') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v18M3 9h18M3 15h18"/></svg>
                                            </a>
                                            <a href="{{ route('accounts.edit', $account) }}" title="{{ __('accounts.edit_account') }}"
                                               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </a>
                                            <form action="{{ route('accounts.toggle', $account) }}" method="POST"
                                                  onsubmit="return confirm(@json(__('accounts.confirm_toggle')));">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="{{ $account->active ? __('accounts.deactivate') : __('accounts.activate') }}"
                                                        class="w-7 h-7 inline-flex items-center justify-center rounded-md {{ $account->active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} transition">
                                                    @if ($account->active)
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                        {{ __('accounts.no_accounts_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    @if ($accounts->hasPages())
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if ($accounts->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('accounts.previous') }}</span>
                            @else
                                <a href="{{ $accounts->previousPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('accounts.previous') }}</a>
                            @endif

                            @foreach (range(1, $accounts->lastPage()) as $page)
                                @if ($page == $accounts->currentPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white bg-[#0F1B4C]">{{ $page }}</span>
                                @else
                                    <a href="{{ $accounts->url($page) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($accounts->hasMorePages())
                                <a href="{{ $accounts->nextPageUrl() }}"
                                   class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">{{ __('accounts.next') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-300 cursor-not-allowed">{{ __('accounts.next') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
