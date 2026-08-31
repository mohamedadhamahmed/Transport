@php
    $account = $node['account'];
    $children = $node['children'];
    $hasChildren = $children->isNotEmpty();
    $indent = 10 + $depth * 22;
@endphp

<div class="tree-node" data-name="{{ \Illuminate\Support\Str::lower($account->name) }}" data-number="{{ \Illuminate\Support\Str::lower((string) $account->account_number) }}">
    <div class="tree-row flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-[#1456E8]/5 transition" style="padding-inline-start: {{ $indent }}px;">
        @if ($hasChildren)
            <button type="button" class="toggle-btn w-5 h-5 flex items-center justify-center text-gray-400 hover:text-[#0F1B4C] transition shrink-0" aria-expanded="true">
                <svg class="w-3.5 h-3.5 toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 6 6 6-6 6"/></svg>
            </button>
        @else
            <span class="w-5 h-5 shrink-0"></span>
        @endif

        <span class="text-xs text-gray-400 w-16 shrink-0">{{ $account->account_number ?? '-' }}</span>
        <span class="font-medium text-gray-800 flex-1 truncate">{{ $account->name }}</span>

        @unless ($account->active)
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-500 shrink-0">{{ __('accounts.inactive') }}</span>
        @endunless

        <span class="text-xs text-gray-500 w-24 text-end shrink-0">{{ number_format($account->debtor_current ?? 0, 2) }}</span>
        <span class="text-xs text-gray-500 w-24 text-end shrink-0">{{ number_format($account->creditor_current ?? 0, 2) }}</span>
        <span class="text-xs font-semibold text-[#0F1B4C] w-24 text-end shrink-0">{{ number_format($account->current_balance, 2) }}</span>

        <div class="flex items-center gap-1 shrink-0">
            <a href="{{ route('accounts.statement', $account) }}" title="{{ __('accounts.statement') }}"
               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v18M3 9h18M3 15h18"/></svg>
            </a>
            <a href="{{ route('accounts.edit', $account) }}" title="{{ __('accounts.edit_account') }}"
               class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </a>
        </div>
    </div>

    @if ($hasChildren)
        <div class="children-wrap">
            @foreach ($children as $child)
                @include('accounts.tree-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
