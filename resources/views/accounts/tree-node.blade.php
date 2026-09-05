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
    <div class="tree-row flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-[#1456E8]/5 transition" style="padding-inline-start: {{ $indent }}px;">
        @if ($hasChildren)
            <button type="button" class="toggle-btn w-5 h-5 flex items-center justify-center text-gray-400 hover:text-[#0F1B4C] transition shrink-0"
                    aria-expanded="false" data-loaded="0" data-depth="{{ $depth }}"
                    data-children-url="{{ route('accounts.tree.children', $account) }}">
                <svg class="w-3.5 h-3.5 toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 6 6 6-6 6"/></svg>
            </button>
        @else
            <span class="w-5 h-5 shrink-0"></span>
        @endif

        @include('accounts._account-row', ['account' => $account, 'nameWeightClass' => $nameWeightClass])
    </div>

    @if ($hasChildren)
        <div class="children-wrap hidden"></div>
    @endif
</div>
