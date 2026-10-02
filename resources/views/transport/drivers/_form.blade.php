@php $driver = $driver ?? null; @endphp

@php
    $dtype = old('driver_type', $driver->driver_type ?? 'company');
    $employees = $employees ?? collect();
@endphp
<div style="margin-bottom:1.25rem;padding:1rem;border-radius:.75rem;background:#f5f7ff">
    <label class="tr-label">{{ __('transport.driver_type') }} *</label>
    <div class="flex gap-4 flex-wrap" style="margin-bottom:.8rem">
        <label class="inline-flex items-center gap-2 text-sm font-semibold cursor-pointer"><input type="radio" name="driver_type" value="company" @checked($dtype === 'company')> 🏢 {{ __('transport.company_driver') }}</label>
        <label class="inline-flex items-center gap-2 text-sm font-semibold cursor-pointer"><input type="radio" name="driver_type" value="external" @checked($dtype === 'external')> 🤝 {{ __('transport.external_driver') }}</label>
    </div>
    <div id="drv-company">
        <div>
            <label class="tr-label">{{ __('transport.link_employee') }}</label>
            <select name="employee_id" id="drv-employee" class="tr-input" style="max-width:540px">
                <option value="">✨ {{ __('transport.new_employee_auto', [], 'ar') ?: 'تلقائي: إنشاء موظف جديد في قسم الموارد البشرية وشجرة الحسابات' }}</option>
                @foreach ($employees as $e)
                    <option value="{{ $e->id }}" @selected((string) old('employee_id', $driver->employee_id ?? '') === (string) $e->id)>{{ $e->name }} ({{ $e->employee_number }})</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1.5">
                💡 السائق التابع للمؤسسة يُسجّل تلقائياً كموظف في قسم السائقين بالموارد البشرية وتُفتح له حساباته المالية، إلا إذا اخترت ربطه بموظف موجود بالفعل.
            </p>
        </div>
    </div>
    <div id="drv-external-hint" class="text-xs text-gray-500">{{ __('transport.external_driver_hint') }}</div>
</div>
<script>
(function () {
    function sync() {
        const v = (document.querySelector('input[name=driver_type]:checked') || {}).value;
        const comp = document.getElementById('drv-company');
        const ext = document.getElementById('drv-external-hint');
        if (comp) comp.style.display = v === 'company' ? '' : 'none';
        if (ext) ext.style.display = v === 'external' ? '' : 'none';
    }
    document.querySelectorAll('input[name=driver_type]').forEach(r => r.addEventListener('change', sync));
    sync();
})();
</script>

<div class="tr-grid tr-grid-3">
    <div>
        <label class="tr-label">{{ __('transport.driver_name') }} *</label>
        <input type="text" name="name" value="{{ old('name', $driver->name ?? '') }}" required class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.phone') }} *</label>
        <input type="text" name="phone" value="{{ old('phone', $driver->phone ?? '') }}" required data-sa-phone class="tr-input" placeholder="05XXXXXXXX">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.nationality') }}</label>
        <input type="text" name="nationality" value="{{ old('nationality', $driver->nationality ?? '') }}" class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.id_number') }}</label>
        <input type="text" name="id_number" value="{{ old('id_number', $driver->id_number ?? '') }}" class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.id_expiry') }}</label>
        <input type="date" name="id_expiry" value="{{ old('id_expiry', optional($driver?->id_expiry)->format('Y-m-d')) }}" class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.salary') }}</label>
        <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $driver->salary ?? 0) }}" class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.license_number') }}</label>
        <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number ?? '') }}" class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.license_expiry') }}</label>
        <input type="date" name="license_expiry" value="{{ old('license_expiry', optional($driver?->license_expiry)->format('Y-m-d')) }}" class="tr-input">
    </div>
    <div>
        <label class="tr-label">{{ __('transport.status') }} *</label>
        <select name="status" class="tr-input">
            <option value="active" @selected(old('status', $driver->status ?? 'active') === 'active')>{{ __('transport.status_active') }}</option>
            <option value="inactive" @selected(old('status', $driver->status ?? 'active') === 'inactive')>{{ __('transport.status_inactive') }}</option>
        </select>
    </div>
    <div class="tr-span-full">
        <label class="tr-label">{{ __('transport.notes') }}</label>
        <textarea name="notes" rows="3" class="tr-input">{{ old('notes', $driver->notes ?? '') }}</textarea>
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">{{ __('transport.save') }}</button>
    <a href="{{ route('transport.drivers.index') }}" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">{{ __('transport.cancel') }}</a>
</div>
