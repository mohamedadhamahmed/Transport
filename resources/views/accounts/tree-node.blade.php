{{--
    عقدة واحدة في شجرة الحسابات - بدون أبناء جاهزين مسبقًا. لو الحساب
    ليه أبناء ($hasChildren)، بيترسم زرار توسيع مع حاوية .children-wrap
    فاضية بـ data-loaded="0"؛ أول مرة تتفتح، JS شاشة الشجرة (tree.blade.php)
    بيجيب الأبناء بـ AJAX من AccountController::treeChildren ويحطهم جوه
    الحاوية دي، بدل ما نبنيهم كلهم مقدمًا زي قبل (غير عملي مع شجرة فيها
    عشرات آلاف الحسابات).

    المتغيرات المتوقعة: $account (FinancialAccount)، $depth (int)،
    $hasChildren (bool).
--}}
@php
    $hasChildren = $hasChildren ?? false;
    $indent = 10 + $depth * 22;

    // الوزن/الحجم بس هما اللي بيختلفوا حسب العمق (أوضح وأكبر كل ما
    // قربنا من الجذر) - اللون بيتحدد جوه _account-row.blade.php من
    // account_category_id (ثابت لكل الفرع بغض النظر عن عمقه).
    $levelWeights = [
        0 => 'font-bold text-[15px]',
        1 => 'font-semibold text-sm',
        2 => 'font-medium text-sm',
        3 => 'text-sm',
    ];
    $nameWeightClass = $levelWeights[$depth] ?? 'text-xs';
@endphp

<div class="tree-node" data-account-id="{{ $account->id }}" data-name="{{ \Illuminate\Support\Str::lower($account->name) }}" data-number="{{ \Illuminate\Support\Str::lower((string) $account->account_number) }}">
    <div class="tree-row flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-blue-50/60 transition {{ $hasChildren ? 'cursor-pointer select-none' : '' }}" style="padding-inline-start: {{ $indent }}px;">
        @if ($hasChildren)
            <button type="button" class="toggle-btn w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:text-[#0F1B4C] hover:bg-gray-100 transition shrink-0"
                    aria-expanded="false" data-loaded="0" data-depth="{{ $depth }}"
                    data-children-url="{{ route('accounts.tree.children', $account) }}"
                    title="{{ __('accounts.expand_collapse') }}">
                <svg class="w-3.5 h-3.5 toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 6 6 6-6 6"/></svg>
            </button>
            <span class="folder-icon text-amber-500 shrink-0 flex items-center">
                <svg class="w-4 h-4 folder-closed" viewBox="0 0 24 24" fill="currentColor"><path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-1.5V9a3 3 0 0 0-3-3h-3.379a3 3 0 0 1-2.121-.879L8.379 3.999A3 3 0 0 0 6.257 3.12H4.5A3 3 0 0 0 1.5 6.12V18a3 3 0 0 0 3 3h15Z"/></svg>
                <svg class="w-4 h-4 folder-open hidden" viewBox="0 0 24 24" fill="currentColor"><path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V10.5a3 3 0 0 0-3-3h-6.379a3 3 0 0 1-2.121-.879l-1.121-1.12A3 3 0 0 0 7.757 4.62H4.5A3 3 0 0 0 1.5 7.62v1.05Z"/></svg>
            </span>
        @else
            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            </span>
            <span class="file-icon text-gray-400 shrink-0 flex items-center">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
        @endif

        @include('accounts._account-row', ['account' => $account, 'nameWeightClass' => $nameWeightClass])
    </div>

    @if ($hasChildren)
        <div class="children-wrap hidden"></div>
    @endif
</div>
