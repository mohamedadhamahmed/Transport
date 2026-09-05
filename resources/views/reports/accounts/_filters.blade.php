{{--
    فورم فلاتر مشترك بين كل تقارير قسم الحسابات (فرع + اختياريًا فترة
    تاريخية) - جزء مشترك عشان الخمس شاشات يفضلوا متطابقين شكليًا من غير
    تكرار نفس الـ HTML.

    المتغيرات المتوقعة:
    - $branches, $branchId
    - $hasDateRange (اختياري، افتراضي false) + $dateFrom/$dateTo لو true
--}}
@php
    $hasDateRange = $hasDateRange ?? false;
    $hasSearch = $hasSearch ?? false;
@endphp
<form method="GET" action="{{ url()->current() }}" class="dc-print-hide p-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
    @if ($hasSearch)
        <div class="w-full sm:w-56">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.account_name') }}</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('reports.search_placeholder') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    @endif

    <div class="w-full sm:w-56">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.branch') }}</label>
        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
            <option value="">{{ __('reports.all_branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($hasDateRange)
        <div class="w-full sm:w-44">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_from') }}</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
        <div class="w-full sm:w-44">
            <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('reports.date_to') }}</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm dc-focus-blue text-sm">
        </div>
    @endif

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

@if ($branchId)
    <div class="dc-print-hide px-4 py-2 bg-amber-50 text-amber-700 text-xs border-b dc-border-amber-soft">
        {{ __('reports.branch_filter_note') }}
    </div>
@endif
