{{--
    محتوى صف حساب واحد (رقم/اسم/أرصدة/سويتش تفعيل/روابط) - جزء مشترك
    بين عقدة الشجرة العادية (tree-node.blade.php) وصف نتيجة البحث
    (tree-search-results.blade.php) عشان الاتنين يفضلوا متطابقين شكليًا
    من غير تكرار نفس الـ HTML مرتين.

    المتغيرات المتوقعة:
    - $account: FinancialAccount
    - $nameWeightClass: كلاس Tailwind لحجم/وزن الاسم (اختياري، افتراضي text-sm)
    - $breadcrumb: array<string> أسماء الآباء بالترتيب من الجذر للأقرب
      (اختياري - بتظهر بس في نتائج البحث، مش في الشجرة العادية لإن
      مكانه في الشجرة نفسه أصلاً بيوضح والديه).
--}}
@php
    // كل حساب بياخد لون واسم فرعه الرئيسي (أصول/خصوم/إيرادات/مصروفات/
    // حقوق ملكية) من العمود القديم account_type مباشرة - بناءً على طلب
    // صريح (مش من account_category_id/جدول account_types الجديد).
    // ⚠️ القيم دي (1-5) مش موجودة في جدول مستقل هنا - مكتوبة يدويًا زي
    // ما اتطلب بالظبط، فلو اتغيّر ترقيم account_type في مكان تاني من
    // المشروع الماب ده لازم يتحدّث معاه يدويًا كمان.
    $categoryColors = [
        1 => '#16A34A', // أصول - أخضر
        2 => '#4ADE80', // خصوم - أخضر فاتح
        3 => '#1456E8', // إيرادات - أزرق
        4 => '#D97706', // مصروفات - برتقالي
        5 => '#7C3AED', // حقوق ملكية - بنفسجي
    ];
    $categoryNames = [
        1 => __('accounts.category_assets') !== 'accounts.category_assets' ? __('accounts.category_assets') : 'أصول',
        2 => __('accounts.category_liabilities') !== 'accounts.category_liabilities' ? __('accounts.category_liabilities') : 'خصوم',
        3 => __('accounts.category_revenues') !== 'accounts.category_revenues' ? __('accounts.category_revenues') : 'إيرادات',
        4 => __('accounts.category_expenses') !== 'accounts.category_expenses' ? __('accounts.category_expenses') : 'مصروفات',
        5 => __('accounts.category_equity') !== 'accounts.category_equity' ? __('accounts.category_equity') : 'حقوق ملكية',
    ];
    $nameColor = $categoryColors[$account->account_type] ?? '#6B7280';
    $categoryName = $categoryNames[$account->account_type] ?? null;
    $nameWeightClass = $nameWeightClass ?? 'text-sm';
@endphp

<span class="inline-flex items-center justify-center font-mono text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200 min-w-[3.5rem] shrink-0 text-center tracking-wider shadow-xs" title="{{ __('accounts.account_number') }}: {{ $account->account_number ?? '-' }}">
    {{ $account->account_number ?: '-' }}
</span>
<span class="{{ $nameWeightClass }} flex-1 truncate" style="color: {{ $nameColor }};">
    @isset($breadcrumb)
        @if (!empty($breadcrumb))
            <span class="text-gray-400 font-normal text-xs">{{ implode(' › ', $breadcrumb) }} ›</span>
        @endif
    @endisset
    {{ $account->name }}
    @if ($categoryName)
        <span class="font-normal text-xs opacity-75">({{ $categoryName }})</span>
    @endif
</span>

<div class="account-balances items-center gap-3 shrink-0 hidden">
    <span class="text-xs text-gray-500 w-24 text-end shrink-0 font-mono">{{ number_format($account->debtor_current ?? 0, 2) }}</span>
    <span class="text-xs text-gray-500 w-24 text-end shrink-0 font-mono">{{ number_format($account->creditor_current ?? 0, 2) }}</span>
    <span class="text-xs font-semibold text-[#0F1B4C] w-24 text-end shrink-0 font-mono">{{ number_format($account->current_balance, 2) }}</span>
</div>

<div class="flex items-center gap-1.5 shrink-0" onclick="event.stopPropagation();">
    {{-- إضافة حساب فرعي --}}
    <a href="{{ route('accounts.create', ['parent_id' => $account->id]) }}" title="{{ __('accounts.add_sub_account') }}"
       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    </a>

    {{-- كشف حساب --}}
    <a href="{{ route('accounts.statement', $account) }}" title="{{ __('accounts.statement') }}"
       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v18M3 9h18M3 15h18"/></svg>
    </a>

    {{-- تعديل حساب --}}
    <a href="{{ route('accounts.edit', $account) }}" title="{{ __('accounts.edit_account') }}"
       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
    </a>

    {{-- سويتش تفعيل/تعطيل مباشر (AJAX) --}}
    <label class="account-active-switch relative inline-flex items-center cursor-pointer ms-1" title="{{ __('accounts.active') }}">
        <input type="checkbox" class="account-active-checkbox sr-only peer"
               data-toggle-url="{{ route('accounts.toggle', $account) }}"
               @checked($account->active)>
        <span class="w-8 h-4.5 rounded-full bg-gray-200 peer-checked:bg-emerald-500 transition-colors relative block">
            <span class="account-active-knob absolute top-0.5 start-0.5 w-3.5 h-3.5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-3.5 rtl:peer-checked:-translate-x-3.5"></span>
        </span>
    </label>
</div>
