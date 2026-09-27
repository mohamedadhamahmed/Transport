@php
    $waybill = $waybill ?? null;
    $dt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
    $truckData = $trucks->mapWithKeys(fn ($t) => [$t->id => [
        'driver_id' => $t->driver_id,
        'region' => $t->current_region,
        'busy' => $t->activeLoad && (!$waybill || $t->activeLoad->id !== $waybill->truck_load_id)
            ? $t->activeLoad->from_label . ' ← ' . $t->activeLoad->to_label : null,
    ]]);
    $linkedActive = $waybill?->truckLoad && $waybill->truckLoad->status === 'loaded';
@endphp

<style>
    .wb-sec { background:#fff; border:1px solid #f3f4f6; border-radius:.9rem; padding:1.25rem; }
    .wb-sec h3 { font-weight:800; color:#0F1B4C; margin-bottom:1rem; font-size:.95rem; }
    .wb-busy { color:#be123c; font-weight:700; font-size:.8rem; margin-top:.3rem; }
    dialog.tb-dialog { border:0; border-radius:1rem; padding:0; width:min(460px, 96vw); box-shadow:0 20px 50px rgba(0,0,0,.25); }
    dialog.tb-dialog::backdrop { background:rgba(15,27,76,.45); }
    .tb-dialog .dh { background:#0F1B4C; color:#fff; padding:.9rem 1.2rem; font-weight:800; display:flex; justify-content:space-between; }
    .tb-dialog .dh button { background:transparent; border:0; color:#fff; font-size:1.3rem; cursor:pointer; }
    .tb-dialog .db { padding:1.2rem; } .tb-dialog .df { padding:.9rem 1.2rem; border-top:1px solid #f3f4f6; display:flex; gap:.5rem; justify-content:flex-end; }
</style>

<div class="space-y-6">
    {{-- الشاحنة والسائق --}}
    <div class="wb-sec">
        <h3>🚚 {{ __('transport.truck_and_driver') }}</h3>
        <div class="tr-grid tr-grid-4">
            <div>
                <label class="tr-label">{{ __('transport.waybill_date') }} *</label>
                <input type="date" name="issue_date" value="{{ old('issue_date', $waybill ? $waybill->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.truck') }} *</label>
                <select name="truck_id" id="wb-truck" required class="tr-input">
                    <option value="">{{ __('transport.choose_truck') }}</option>
                    @foreach ($trucks as $t)
                        @php $busy = $truckData[$t->id]['busy']; @endphp
                        <option value="{{ $t->id }}" @selected((string) old('truck_id', $waybill->truck_id ?? '') === (string) $t->id)>
                            {{ $t->display_name }}{{ $t->type ? ' (' . $t->type . ')' : '' }}{{ $busy ? '  ⛔' : '' }}
                        </option>
                    @endforeach
                </select>
                <div id="wb-busy" class="wb-busy"></div>
            </div>
            <div>
                <label class="tr-label">{{ __('transport.driver') }}</label>
                <select name="driver_id" id="wb-driver" class="tr-input">
                    <option value="">{{ __('transport.truck_default_driver') }}</option>
                    @foreach ($drivers as $d)
                        <option value="{{ $d->id }}" @selected((string) old('driver_id', $waybill->driver_id ?? '') === (string) $d->id)>{{ $d->name }}{{ $d->phone ? ' - ' . $d->phone : '' }}</option>
                    @endforeach
                </select>
                @can('drivers.create')
                    <a href="#" id="wb-new-driver" style="font-size:.75rem;color:#1456E8;font-weight:700">+ {{ __('transport.new_driver') }}</a>
                @endcan
            </div>
            <div>
                <label class="tr-label">{{ __('transport.customer') }} ({{ __('transport.account') }})</label>
                <select name="customer_id" id="wb-customer" class="tr-input">
                    <option value="">-</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" data-phone="{{ $c->phone }}" @selected((string) old('customer_id', $waybill->customer_id ?? '') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- الشاحن والمستلم --}}
    <div class="tr-grid tr-grid-2">
        <div class="wb-sec">
            <h3>📤 {{ __('transport.shipper') }}</h3>
            <div class="tr-grid tr-grid-2">
                <div><label class="tr-label">{{ __('transport.shipper_name') }} *</label><input type="text" name="shipper_name" id="wb-shipper" value="{{ old('shipper_name', $waybill->shipper_name ?? '') }}" required class="tr-input"></div>
                <div><label class="tr-label">{{ __('transport.phone') }}</label><input type="text" name="shipper_phone" value="{{ old('shipper_phone', $waybill->shipper_phone ?? '') }}" data-sa-phone class="tr-input" placeholder="05XXXXXXXX"></div>
                <div>
                    <label class="tr-label">{{ __('transport.from_region') }} *</label>
                    <select name="from_region" id="wb-from" required class="tr-input">
                        <option value="">{{ __('transport.choose_region') }}</option>
                        @foreach ($regions as $k => $n)<option value="{{ $k }}" @selected(old('from_region', $waybill->from_region ?? '') === $k)>{{ $n }}</option>@endforeach
                    </select>
                </div>
                <div><label class="tr-label">{{ __('transport.city') }}</label><input type="text" name="from_city" value="{{ old('from_city', $waybill->from_city ?? '') }}" class="tr-input"></div>
                <div class="tr-span-2"><label class="tr-label">{{ __('transport.address') }}</label><input type="text" name="from_address" value="{{ old('from_address', $waybill->from_address ?? '') }}" class="tr-input"></div>
            </div>
        </div>
        <div class="wb-sec">
            <h3>📥 {{ __('transport.consignee') }}</h3>
            <div class="tr-grid tr-grid-2">
                <div><label class="tr-label">{{ __('transport.consignee_name') }} *</label><input type="text" name="consignee_name" value="{{ old('consignee_name', $waybill->consignee_name ?? '') }}" required class="tr-input"></div>
                <div><label class="tr-label">{{ __('transport.phone') }}</label><input type="text" name="consignee_phone" value="{{ old('consignee_phone', $waybill->consignee_phone ?? '') }}" data-sa-phone class="tr-input" placeholder="05XXXXXXXX"></div>
                <div>
                    <label class="tr-label">{{ __('transport.to_region') }} *</label>
                    <select name="to_region" required class="tr-input">
                        <option value="">{{ __('transport.choose_region') }}</option>
                        @foreach ($regions as $k => $n)<option value="{{ $k }}" @selected(old('to_region', $waybill->to_region ?? '') === $k)>{{ $n }}</option>@endforeach
                    </select>
                </div>
                <div><label class="tr-label">{{ __('transport.city') }}</label><input type="text" name="to_city" value="{{ old('to_city', $waybill->to_city ?? '') }}" class="tr-input"></div>
                <div class="tr-span-2"><label class="tr-label">{{ __('transport.address') }}</label><input type="text" name="to_address" value="{{ old('to_address', $waybill->to_address ?? '') }}" class="tr-input"></div>
            </div>
        </div>
    </div>

    {{-- البضاعة والمواعيد والأجرة --}}
    <div class="wb-sec">
        <h3>📦 {{ __('transport.goods_and_dates') }}</h3>
        <div class="tr-grid tr-grid-4">
            <div class="tr-span-2">
                <label class="tr-label">{{ __('transport.goods_description') }} *</label>
                <input type="text" name="goods_description" value="{{ old('goods_description', $waybill->goods_description ?? '') }}" required class="tr-input" list="wb-goods" placeholder="{{ __('transport.load_type_hint') }}">
                <datalist id="wb-goods"><option value="مواد بناء"><option value="حديد"><option value="أسمنت"><option value="مواد غذائية"><option value="مبردات"><option value="معدات"><option value="أثاث"><option value="حاويات"><option value="بضائع عامة"></datalist>
            </div>
            <div><label class="tr-label">{{ __('transport.packages_count') }}</label><input type="number" min="0" step="1" name="packages_count" value="{{ old('packages_count', $waybill->packages_count ?? '') }}" class="tr-input"></div>
            <div><label class="tr-label">{{ __('transport.weight') }} ({{ __('transport.ton') }})</label><input type="number" min="0" step="0.01" name="weight" value="{{ old('weight', $waybill->weight ?? '') }}" class="tr-input"></div>
            <div><label class="tr-label">{{ __('transport.loaded_at') }} *</label><input type="datetime-local" name="loaded_at" id="wb-loaded" value="{{ old('loaded_at', $dt($waybill?->loaded_at ?? now())) }}" required class="tr-input"></div>
            <div><label class="tr-label">{{ __('transport.expected_unload_at') }} *</label><input type="datetime-local" name="expected_unload_at" id="wb-expected" value="{{ old('expected_unload_at', $dt($waybill?->expected_unload_at)) }}" required class="tr-input"></div>
            <div><label class="tr-label">{{ __('transport.freight_amount') }}</label><input type="number" min="0" step="0.01" name="freight_amount" value="{{ old('freight_amount', $waybill->freight_amount ?? '') }}" class="tr-input"></div>
            <div>
                <label class="tr-label">{{ __('transport.freight_payer') }} *</label>
                <select name="freight_payer" class="tr-input">
                    @foreach (['customer', 'shipper', 'consignee'] as $p)
                        <option value="{{ $p }}" @selected(old('freight_payer', $waybill->freight_payer ?? 'customer') === $p)>{{ __('transport.payer_' . $p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tr-span-3"><label class="tr-label">{{ __('transport.notes') }}</label><input type="text" name="notes" value="{{ old('notes', $waybill->notes ?? '') }}" class="tr-input"></div>
        </div>

        <div style="margin-top:1rem;padding:.8rem 1rem;border-radius:.6rem;background:#f5f7ff">
            @if ($linkedActive)
                <span class="text-sm" style="color:#047857;font-weight:700">✓ {{ __('transport.waybill_load_linked') }}</span>
            @else
                <label class="inline-flex items-center gap-2 text-sm font-semibold cursor-pointer" style="color:#0F1B4C">
                    <input type="checkbox" name="register_load" value="1" id="wb-register" @checked(old('register_load', $waybill ? false : true))>
                    {{ __('transport.register_load_on_board') }}
                </label>
                <div class="text-xs text-gray-500 mt-1">{{ __('transport.register_load_hint') }}</div>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.save_waybill') }}</button>
        <a href="{{ route('transport.waybills.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm">{{ __('transport.cancel') }}</a>
    </div>
</div>


@push('scripts')
{{-- النافذة هنا (آخر الصفحة) عشان متبقاش form جوه form --}}
@can('drivers.create')
<dialog id="wb-driver-dialog" class="tb-dialog">
    <form id="wb-driver-form" onsubmit="return false">
        <div class="dh"><span>+ {{ __('transport.new_driver') }}</span><button type="button" onclick="this.closest('dialog').close()">×</button></div>
        <div class="db">
            <div class="tr-grid">
                <div><label class="tr-label">{{ __('transport.driver_name') }} *</label><input type="text" name="name" class="tr-input"></div>
                <div><label class="tr-label">{{ __('transport.phone') }} *</label><input type="text" name="phone" data-sa-phone-quick class="tr-input" placeholder="05XXXXXXXX"></div>
                <div id="wb-driver-error" style="color:#be123c;font-size:.8rem"></div>
            </div>
        </div>
        <div class="df">
            <button type="button" class="tr-btn tr-btn-gray" onclick="this.closest('dialog').close()">{{ __('transport.cancel') }}</button>
            <button type="button" id="wb-driver-save" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.save') }}</button>
        </div>
    </form>
</dialog>
@endcan
<script>
(function () {
    const TRUCKS = @json($truckData);
    const truckSel = document.getElementById('wb-truck');
    const busyBox = document.getElementById('wb-busy');
    const fromSel = document.getElementById('wb-from');
    const reg = document.getElementById('wb-register');

    function onTruck() {
        const t = TRUCKS[truckSel.value];
        busyBox.textContent = t && t.busy ? '⛔ {{ __('transport.truck_has_load') }} (' + t.busy + ') - {{ __('transport.busy_waybill_hint') }}' : '';
        if (reg) { reg.disabled = !!(t && t.busy); if (t && t.busy) reg.checked = false; }
        if (t && t.region && !fromSel.value) fromSel.value = t.region;
    }
    truckSel.addEventListener('change', onTruck);
    onTruck();

    const loaded = document.getElementById('wb-loaded'), exp = document.getElementById('wb-expected');
    loaded.addEventListener('change', () => { exp.min = loaded.value; });

    // اسم الشاحن = اسم العميل لو فاضي
    const cust = document.getElementById('wb-customer'), shipper = document.getElementById('wb-shipper');
    cust.addEventListener('change', () => {
        const o = cust.selectedOptions[0];
        if (o && o.value && !shipper.value) shipper.value = o.textContent.trim();
    });

    // سائق جديد سريع
    const link = document.getElementById('wb-new-driver');
    const dlg = document.getElementById('wb-driver-dialog');
    if (link && dlg) {
        const form = document.getElementById('wb-driver-form');
        const phone = form.querySelector('[data-sa-phone-quick]');
        phone.addEventListener('input', () => window.SaPhone && SaPhone.check(phone));
        link.addEventListener('click', e => { e.preventDefault(); form.reset(); document.getElementById('wb-driver-error').textContent = ''; dlg.showModal(); });
        document.getElementById('wb-driver-save').addEventListener('click', async () => {
            phone.value = SaPhone.normalize(phone.value);
            if (!SaPhone.isValid(phone.value)) { SaPhone.check(phone); return; }
            const res = await fetch(@json(route('transport.drivers.quick')), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: new FormData(form),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) { document.getElementById('wb-driver-error').textContent = json.message || 'Error'; return; }
            document.getElementById('wb-driver').add(new Option(json.name + ' - ' + json.phone, json.id, true, true));
            dlg.close();
        });
    }
})();
</script>
@endpush
