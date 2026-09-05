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
        1 => 'أصول',
        2 => 'خصوم',
        3 => 'إيرادات',
        4 => 'مصروفات',
        5 => 'حقوق ملكية',
    ];
    $nameColor = $categoryColors[$account->account_type] ?? '#6B7280';
    $categoryName = $categoryNames[$account->account_type] ?? null;
    $nameWeightClass = $nameWeightClass ?? 'text-sm';
@endphp

<span class="text-xs text-gray-400 w-16 shrink-0">{{ $account->account_number ?? '-' }}</span>
<span class="{{ $nameWeightClass }} flex-1 truncate" style="color: {{ $nameColor }};">
    @isset($breadcrumb)
        @if (!empty($breadcrumb))
            <span class="text-gray-400 font-normal text-xs">{{ implode(' › ', $breadcrumb) }} ›</span>
        @endif
    @endisset
    {{ $account->name }}
    @if ($categoryName)
        <span class="font-normal text-xs">({{ $categoryName }})</span>
    @endif
</span>

<span class="text-xs text-gray-500 w-24 text-end shrink-0">{{ number_format($account->debtor_current ?? 0, 2) }}</span>
<span class="text-xs text-gray-500 w-24 text-end shrink-0">{{ number_format($account->creditor_current ?? 0, 2) }}</span>
<span class="text-xs font-semibold text-[#0F1B4C] w-24 text-end shrink-0">{{ number_format($account->current_balance, 2) }}</span>

<div class="flex items-center gap-2 shrink-0">
    {{-- سويتش تفعيل/تعطيل مباشر (AJAX - بدون إعادة تحميل الصفحة) -
         راجع AccountController::toggleActive. --}}
    <label class="account-active-switch relative inline-flex items-center cursor-pointer" title="{{ __('accounts.active') }}">
        <input type="checkbox" class="account-active-checkbox sr-only peer"
               data-toggle-url="{{ route('accounts.toggle', $account) }}"
               @checked($account->active)>
        <span class="w-9 h-5 rounded-full bg-gray-200 peer-checked:bg-emerald-500 transition-colors relative">
            <span class="account-active-knob absolute top-0.5 start-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4 rtl:peer-checked:-translate-x-4"></span>
        </span>
    </label>

    <a href="{{ route('accounts.statement', $account) }}" title="{{ __('accounts.statement') }}"
       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-[#1456E8]/10 text-[#1456E8] hover:bg-[#1456E8]/20 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 3v18M3 9h18M3 15h18"/></svg>
    </a>
    <a href="{{ route('accounts.edit', $account) }}" title="{{ __('accounts.edit_account') }}"
       class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
    </a>
</div>
