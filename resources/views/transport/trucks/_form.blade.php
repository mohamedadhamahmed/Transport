@php $truck = $truck ?? null; @endphp

@php $own = old('ownership', $truck->ownership ?? 'owned'); @endphp
<div style="margin-bottom:1.25rem;padding:1rem;border-radius:.75rem;background:#f5f7ff">
    <label class="tr-label">{{ __('transport.ownership') }} *</label>
    <div class="flex gap-4 flex-wrap" style="margin-bottom:.8rem">
        <label class="inline-flex items-center gap-2 text-sm font-semibold cursor-pointer"><input type="radio" name="ownership" value="owned" @checked($own === 'owned')> 🏢 {{ __('transport.owned') }}</label>
        <label class="inline-flex items-center gap-2 text-sm font-semibold cursor-pointer"><input type="radio" name="ownership" value="external" @checked($own === 'external')> 🤝 {{ __('transport.external_truck') }}</label>
    </div>
    <div id="own-owned" class="tr-grid tr-grid-3">
        <div>
            <label class="tr-label">{{ __('transport.purchase_value') }}</label>
            <input type="number" step="0.01" min="0" name="purchase_value" value="{{ old('purchase_value', $truck->purchase_value ?? '') }}" class="tr-input">
            <div class="text-xs text-gray-500" style="margin-top:.25rem">{{ __('transport.purchase_value_hint') }}</div>
        </div>
        <div>
            <label class="tr-label">{{ __('transport.purchase_date') }}</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($truck?->purchase_date)->format('Y-m-d')) }}" class="tr-input">
        </div>
    </div>
    <div id="own-external" class="tr-grid tr-grid-3">
        <div>
            <label class="tr-label">{{ __('transport.owner_name') }} *</label>
            <input type="text" name="owner_name" value="{{ old('owner_name', $truck->owner_name ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.owner_phone') }}</label>
            <input type="text" name="owner_phone" value="{{ old('owner_phone', $truck->owner_phone ?? '') }}" data-sa-phone class="tr-input" placeholder="05XXXXXXXX">
        </div>
    </div>
</div>
<script>
(function () {
    function sync() {
        const v = (document.querySelector('input[name=ownership]:checked') || {}).value;
        document.getElementById('own-owned').style.display = v === 'owned' ? '' : 'none';
        document.getElementById('own-external').style.display = v === 'external' ? '' : 'none';
    }
    document.querySelectorAll('input[name=ownership]').forEach(r => r.addEventListener('change', sync));
    sync();
})();
</script>

@php
    $docStatus = $truck ? $truck->documentsStatus() : [];
    $dateVal = fn ($col) => old($col, optional($truck?->{$col})->format('Y-m-d'));
    $badge = function ($col) use ($docStatus) {
        $d = $docStatus[$col] ?? null;
        if (!$d || $d['status'] === 'none') return '';
        $cls = ['expired' => 'tr-badge-red', 'soon' => 'tr-badge-amber', 'ok' => 'tr-badge-green'][$d['status']];
        $txt = $d['status'] === 'expired' ? __('transport.doc_expired_since', ['days' => abs($d['days'])])
            : ($d['status'] === 'soon' ? __('transport.doc_expires_in', ['days' => $d['days']]) : __('transport.doc_valid'));
        return '<span class="tr-badge ' . $cls . '" style="margin-top:.3rem;display:inline-block">' . e($txt) . '</span>';
    };
@endphp
<style>
    .trk-sec { border:1px solid #eef0f6; border-radius:.9rem; padding:1.1rem 1.2rem; margin-bottom:1.1rem; }
    .trk-sec h3 { font-weight:800; color:#0F1B4C; font-size:.92rem; margin-bottom:.9rem; }
</style>

{{-- البيانات الأساسية --}}
<div class="trk-sec">
    <h3>🚚 {{ __('transport.truck_main_data') }}</h3>
    <div class="tr-grid tr-grid-3">
        <div>
            <label class="tr-label">{{ __('transport.plate_number') }} *</label>
            <input type="text" name="plate_number" value="{{ old('plate_number', $truck->plate_number ?? '') }}" required class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.truck_name') }}</label>
            <input type="text" name="name" value="{{ old('name', $truck->name ?? '') }}" class="tr-input" placeholder="{{ __('transport.truck_name_hint') }}">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.truck_type') }}</label>
            <input type="text" name="type" value="{{ old('type', $truck->type ?? '') }}" class="tr-input" list="truck-types">
            <datalist id="truck-types">
                <option value="تريلا"><option value="سطحة"><option value="دينا"><option value="لوري"><option value="قلاب"><option value="ثلاجة"><option value="صهريج">
            </datalist>
        </div>
        <div>
            <label class="tr-label">{{ __('transport.brand') }}</label>
            <input type="text" name="brand" value="{{ old('brand', $truck->brand ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.model_year') }}</label>
            <input type="text" name="model_year" value="{{ old('model_year', $truck->model_year ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.color') }}</label>
            <input type="text" name="color" value="{{ old('color', $truck->color ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.capacity') }}</label>
            <input type="number" step="0.01" min="0" name="capacity" value="{{ old('capacity', $truck->capacity ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.chassis_number') }}</label>
            <input type="text" name="chassis_number" value="{{ old('chassis_number', $truck->chassis_number ?? '') }}" class="tr-input" dir="ltr">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.serial_number') }}</label>
            <input type="text" name="serial_number" value="{{ old('serial_number', $truck->serial_number ?? '') }}" class="tr-input" dir="ltr">
        </div>
    </div>
</div>

{{-- الوثائق: الاستمارة / التأمين / كرت التشغيل / الفحص الدوري --}}
<div class="trk-sec">
    <h3>📄 {{ __('transport.truck_documents') }} <span class="text-xs text-gray-400 font-normal">— {{ __('transport.truck_documents_hint', ['days' => \App\Models\Truck::EXPIRY_ALERT_DAYS]) }}</span></h3>
    <div class="tr-grid tr-grid-3">
        <div>
            <label class="tr-label">{{ __('transport.registration_number') }}</label>
            <input type="text" name="registration_number" value="{{ old('registration_number', $truck->registration_number ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.registration_expiry') }}</label>
            <input type="date" name="registration_expiry" value="{{ $dateVal('registration_expiry') }}" class="tr-input">
            {!! $badge('registration_expiry') !!}
        </div>
        <div></div>
        <div>
            <label class="tr-label">{{ __('transport.insurance_company') }}</label>
            <input type="text" name="insurance_company" value="{{ old('insurance_company', $truck->insurance_company ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.insurance_policy_number') }}</label>
            <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number', $truck->insurance_policy_number ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.insurance_expiry') }}</label>
            <input type="date" name="insurance_expiry" value="{{ $dateVal('insurance_expiry') }}" class="tr-input">
            {!! $badge('insurance_expiry') !!}
        </div>
        <div>
            <label class="tr-label">{{ __('transport.operating_card_number') }}</label>
            <input type="text" name="operating_card_number" value="{{ old('operating_card_number', $truck->operating_card_number ?? '') }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.operating_card_expiry') }}</label>
            <input type="date" name="operating_card_expiry" value="{{ $dateVal('operating_card_expiry') }}" class="tr-input">
            {!! $badge('operating_card_expiry') !!}
        </div>
        <div>
            <label class="tr-label">{{ __('transport.inspection_expiry') }}</label>
            <input type="date" name="inspection_expiry" value="{{ $dateVal('inspection_expiry') }}" class="tr-input">
            {!! $badge('inspection_expiry') !!}
        </div>
    </div>
</div>

{{-- التشغيل --}}
<div class="trk-sec">
    <h3>⚙️ {{ __('transport.truck_operation') }}</h3>
    <div class="tr-grid tr-grid-3">
        <div>
            <label class="tr-label">{{ __('transport.driver') }}</label>
            <select name="driver_id" class="tr-input">
                <option value="">{{ __('transport.no_driver') }}</option>
                @foreach ($drivers as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('driver_id', $truck->driver_id ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
                @if ($truck?->driver && !isset($drivers[$truck->driver_id]))
                    <option value="{{ $truck->driver_id }}" selected>{{ $truck->driver->name }}</option>
                @endif
            </select>
        </div>
        <div>
            <label class="tr-label">{{ __('transport.default_trip_price') }}</label>
            <input type="number" step="0.01" min="0" name="default_trip_price" value="{{ old('default_trip_price', $truck->default_trip_price ?? 0) }}" class="tr-input">
        </div>
        <div>
            <label class="tr-label">{{ __('transport.status') }} *</label>
            <select name="status" class="tr-input">
                @foreach (['active', 'maintenance', 'inactive'] as $st)
                    <option value="{{ $st }}" @selected(old('status', $truck->status ?? 'active') === $st)>{{ __('transport.status_' . $st) }}</option>
                @endforeach
            </select>
        </div>
        @if (!$truck || !$truck->activeLoad)
            <div>
                <label class="tr-label">📍 {{ __('transport.truck_location') }}</label>
                <select name="current_region" class="tr-input">
                    <option value="">{{ __('transport.location_unknown') }}</option>
                    @foreach (\App\Support\SaudiRegions::options() as $k => $n)
                        <option value="{{ $k }}" @selected(old('current_region', $truck->current_region ?? '') === $k)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="tr-span-full">
            <label class="tr-label">{{ __('transport.notes') }}</label>
            <textarea name="notes" rows="3" class="tr-input">{{ old('notes', $truck->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">{{ __('transport.save') }}</button>
    <a href="{{ route('transport.trucks.index') }}" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">{{ __('transport.cancel') }}</a>
</div>
