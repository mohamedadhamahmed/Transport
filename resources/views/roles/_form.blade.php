@php
    $role = $role ?? null;
@endphp

@if ($errors->any())
    <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5 mb-4">
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('roles.name') }} *</label>
        <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('roles.name_en') }}</label>
        <input type="text" name="name_en" value="{{ old('name_en', $role->name_en ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
</div>

@if ($role && $role->is_super)
    <div class="rounded-lg bg-[#6B2FD6]/5 border border-[#6B2FD6]/20 text-[#6B2FD6] text-sm px-4 py-3 mb-6">
        {{ __('roles.super_admin_note') }}
    </div>
@else
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-700">{{ __('roles.permissions') }}</h3>
        <div class="flex items-center gap-3 text-xs">
            <button type="button" onclick="document.querySelectorAll('.permission-checkbox').forEach(c => c.checked = true)"
                    class="text-[#1456E8] hover:underline">{{ __('roles.select_all') }}</button>
            <button type="button" onclick="document.querySelectorAll('.permission-checkbox').forEach(c => c.checked = false)"
                    class="text-gray-500 hover:underline">{{ __('roles.clear_all') }}</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($modules as $moduleKey => $module)
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-[#0F1B4C]">{{ app()->getLocale() === 'ar' ? $module['label'] : ($module['label_en'] ?? $module['label']) }}</h4>
                    <button type="button"
                            onclick="document.querySelectorAll('.module-{{ $moduleKey }}').forEach(c => c.checked = !c.checked)"
                            class="text-[11px] text-gray-400 hover:text-[#1456E8]">{{ __('roles.select_all') }}</button>
                </div>
                <div class="space-y-1.5">
                    @foreach ($module['permissions'] as $key => $meta)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                   class="permission-checkbox module-{{ $moduleKey }} rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]"
                                   @checked(in_array($key, old('permissions', $checkedKeys)))>
                            {{ app()->getLocale() === 'ar' ? $meta['label'] : ($meta['label_en'] ?? $meta['label']) }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
        {{ __('roles.save') }}
    </button>
    <a href="{{ route('roles.index') }}" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
        {{ __('roles.cancel') }}
    </a>
</div>
