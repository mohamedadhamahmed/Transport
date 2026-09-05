{{--
    فورم فلاتر عام لكل تقارير أقسام المبيعات/المشتريات/المنتجات/الموارد
    البشرية - نفس فكرة reports/accounts/_filters.blade.php بالظبط من
    ناحية الشكل، لكن معمول كملف مستقل (مش تعديل في نسخة الحسابات) عشان
    منلمسش تقارير قسم الحسابات الشغالة والمُختبرة بالفعل. الفلتر هنا
    بيدعم كل الاحتياجات المختلفة للأقسام الأربعة عن طريق شوية أعلام
    اختيارية (hasDateRange/hasStatus/hasPostingSelect) بدل تكرار نفس
    الفورم في كل قسم.

    المتغيرات المتوقعة:
    - $branches, $branchId, $q
    - $hasDateRange (اختياري) + $dateFrom/$dateTo لو true
    - $hasStatus (اختياري) + $statusOptions (value => label) + $status + $statusLabel
    - $hasPostingSelect (اختياري) + $postings + $postingId
    - $searchPlaceholder (اختياري)
    - $hasEntitySelect (اختياري) + $entityOptions (id => label) + $entityId
      + $entityParam (اسم حقل الفورم، مثلًا customer_id) + $entityLabel
      (اختياري) - بديل لمربع البحث النصي $q في التقارير اللي المستخدم
      طلب فيها اختيار من قائمة منسدلة بدل كتابة اسم (العميل/المورد/الموظف)
    - $entityAjaxUrl (اختياري، بيتحط مع hasEntitySelect) - لو موجود، القايمة
      المنسدلة بتتحول لـ TomSelect ببحث Ajax حي (بحد أدنى حرفين) بدل عرض كل
      الصفوف مرة واحدة - ضروري للجداول الكبيرة زي العملاء (٢٠ ألف+) عشان
      الصفحة متتقلش. في الحالة دي $entityOptions لازم يبقى فيه بس العنصر
      المختار حاليًا (id => label) مش القائمة كاملة، لإن الكنترولر مبيحملش
      كل الصفوف أصلًا.
    - $hasPrintDetails (اختياري) - يضيف زرار "طباعة مع التفاصيل" جنب زرار
      الطباعة العادي + سكريبت toggleRowDetails/printWithDetails العام
      المستخدم في تقارير ملخص المبيعات/المشتريات/تسليم المنتج لعرض/طباعة
      بنود كل مستند

    ⚠️ فلتر الفرع هنا "صارم" (branch_id عمود عادي في كل الموديلات دي، مش
    زي branchs_id القابل للـ null في شجرة الحسابات) - علشان كده مفيش
    "ملاحظة الحسابات المشتركة" هنا زي قسم الحسابات، لإنها مش منطبقة على
    البيانات دي أصلًا.
--}}
@php
    $hasDateRange = $hasDateRange ?? false;
    $hasStatus = $hasStatus ?? false;
    $hasPostingSelect = $hasPostingSelect ?? false;
    $statusOptions = $statusOptions ?? [];
    $hasEntitySelect = $hasEntitySelect ?? false;
    $entityOptions = $entityOptions ?? [];
    $entityAjaxUrl = $entityAjaxUrl ?? null;
    $hasPrintDetails = $hasPrintDetails ?? false;
@endphp
<form method="GET" action="{{ url()->current() }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
    @if ($hasEntitySelect)
        <div class="w-full sm:w-56">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ $entityLabel ?? __('reports.filters') }}</label>
            @if ($entityAjaxUrl)
                <select name="{{ $entityParam }}" data-ajax-select data-ajax-url="{{ $entityAjaxUrl }}"
                        data-ajax-placeholder="{{ $entityAllLabel ?? __('reports.all') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    <option value="">{{ $entityAllLabel ?? __('reports.all') }}</option>
                    @if ($entityId && isset($entityOptions[$entityId]))
                        <option value="{{ $entityId }}" selected>{{ $entityOptions[$entityId] }}</option>
                    @endif
                </select>
            @else
                <select name="{{ $entityParam }}" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                    <option value="">{{ $entityAllLabel ?? __('reports.all') }}</option>
                    @foreach ($entityOptions as $id => $label)
                        <option value="{{ $id }}" @selected((string) $entityId === (string) $id)>{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    @endif

    {{-- مربع بحث نصي: يظهر لوحده كبديل لقائمة الاختيار في التقارير القديمة،
         أو جنبًا إلى جنب مع القائمة المنسدلة في التقارير اللي بتحتاج بحث
         إضافي برقم المستند (مثلًا رقم الفاتورة) مع اختيار العميل/المورد
         من قائمة --}}
    @if (!$hasEntitySelect && isset($q))
        <div class="w-full sm:w-56">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.filters') }}</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="{{ $searchPlaceholder ?? __('reports.search_placeholder') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    @elseif ($hasEntitySelect && isset($q))
        <div class="w-full sm:w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ $searchPlaceholder ?? __('reports.filters') }}</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="{{ $searchPlaceholder ?? __('reports.search_placeholder') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    @endif

    @if ($hasPostingSelect)
        <div class="w-full sm:w-64">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.posting') }}</label>
            <select name="posting_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                @forelse ($postings as $posting)
                    <option value="{{ $posting->id }}" @selected((int) $postingId === (int) $posting->id)>
                        {{ $posting->document_number }} - {{ \Illuminate\Support\Carbon::parse($posting->month)->format('Y-m') }}
                    </option>
                @empty
                    <option value="">{{ __('reports.no_postings_found') }}</option>
                @endforelse
            </select>
        </div>
    @endif

    <div class="w-full sm:w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.branch') }}</label>
        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
            <option value="">{{ __('reports.all_branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($hasDateRange)
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_from') }}</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_to') }}</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    @endif

    @if ($hasStatus)
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ $statusLabel ?? __('reports.loan_status') }}</label>
            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
                <option value="">{{ __('reports.all_statuses') }}</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="flex items-center gap-2 flex-wrap">
        <button type="submit" class="px-4 py-2 rounded-lg dc-btn-primary text-sm font-medium transition">
            {{ __('reports.apply_filters') }}
        </button>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200 transition">
            {{ __('reports.print') }}
        </button>
        @if ($hasPrintDetails)
            <button type="button" onclick="printWithDetails()" class="px-4 py-2 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-medium hover:bg-indigo-100 transition">
                {{ __('reports.print_with_details') }}
            </button>
        @endif
        <button type="submit" name="export" value="excel" formtarget="_blank" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium hover:bg-emerald-100 transition">
            {{ __('reports.export_excel') }}
        </button>
    </div>
</form>

@if ($hasPrintDetails)
    <script>
        function toggleRowDetails(id) {
            var el = document.getElementById('details-row-' + id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function printWithDetails() {
            document.querySelectorAll('.details-row').forEach(function (el) {
                el.classList.remove('hidden');
            });
            window.print();
        }

        window.addEventListener('afterprint', function () {
            document.querySelectorAll('.details-row').forEach(function (el) {
                el.classList.add('hidden');
            });
        });
    </script>
@endif

@if ($hasEntitySelect && $entityAjaxUrl)
    {{-- بحث Ajax حي بدل تحميل كل الصفوف مرة واحدة (TomSelect) - الأصول
         (سكريبت + CSS مستقل بالكامل عن Bootstrap + سكريبت التفعيل)
         مشتركة في partials.ajax-select-assets علشان تتظبط مرة واحدة
         وتتطبق في كل الأماكن اللي بتستخدم data-ajax-select --}}
    @include('partials.ajax-select-assets')
@endif
