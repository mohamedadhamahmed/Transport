@php
    $quotation = $quotation ?? null;
    $oldItems = old('items');
    if (is_array($oldItems)) {
        $initialItems = array_values($oldItems);
    } elseif ($quotation) {
        $initialItems = $quotation->items->map(fn ($i) => [
            'truck_type' => $i->truck_type, 'from_region' => $i->from_region, 'from_city' => $i->from_city,
            'to_region' => $i->to_region, 'to_city' => $i->to_city, 'load_type' => $i->load_type,
            'trips_count' => $i->trips_count, 'trip_price' => (float) $i->trip_price,
            'has_transfer' => (bool) $i->has_transfer, 'transfer_location' => $i->transfer_location,
            'transfer_price' => (float) $i->transfer_price, 'note' => $i->note,
        ])->values()->all();
    } else {
        $initialItems = [];
    }
    $taxPercent = old('tax_rate', $quotation ? round($quotation->tax_rate * 100, 2) : 15);
    $taxType = old('tax_type', $quotation->tax_type ?? 'standard');
    $defaultTerms = "- الأسعار بالريال السعودي.\n- التحميل والتنزيل على العميل ما لم يُذكر غير ذلك.\n- أي تحويلة إضافية تُحسب حسب الاتفاق.";
@endphp

<style>
    .q-card { border:1px solid #e5e7eb; border-radius:.75rem; padding:1rem; background:#fafbff; position:relative; }
    .q-card + .q-card { margin-top:.75rem; }
    .q-grid { display:grid; grid-template-columns:1fr; gap:.75rem; }
    @media (min-width: 1000px) { .q-grid { grid-template-columns:1.2fr 1.5fr 1.5fr 1.3fr .8fr 1fr; } }
    .q-num { position:absolute; top:-10px; inset-inline-start:14px; background:#0F1B4C; color:#fff; font-size:.7rem; font-weight:700; padding:2px 10px; border-radius:999px; }
    .q-transfer { display:grid; grid-template-columns:1fr; gap:.75rem; margin-top:.75rem; padding:.75rem; border-radius:.5rem; background:#fff7ed; border:1px dashed #F5811E; }
    @media (min-width: 900px) { .q-transfer { grid-template-columns:2fr 1fr; } }
    .q-foot { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-top:.75rem; }
    .q-total { font-weight:700; color:#0F1B4C; }
    .sum-row { display:flex; justify-content:space-between; padding:.45rem 0; font-size:.9rem; border-bottom:1px dashed #e5e7eb; }
    .sum-row.grand { border-bottom:0; font-size:1.1rem; font-weight:800; color:#0F1B4C; padding-top:.75rem; }
</style>

<div class="space-y-6">
    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
        <div class="tr-grid tr-grid-4">
            <div class="tr-span-2">
                <label class="tr-label">{{ __('transport.customer') }} *</label>
                <select name="customer_id" id="q_customer" class="tr-input">
                    <option value="">{{ __('transport.choose_customer') }}</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected((string) old('customer_id', $quotation->customer_id ?? '') === (string) $c->id)>{{ $c->name }}{{ $c->phone ? ' - ' . $c->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tr-label">{{ __('transport.quotation_date') }} *</label>
                <input type="date" name="issue_date" value="{{ old('issue_date', $quotation ? $quotation->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.valid_until') }}</label>
                <input type="date" name="valid_until" value="{{ old('valid_until', $quotation ? optional($quotation->valid_until)->format('Y-m-d') : now()->addDays(15)->format('Y-m-d')) }}" class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.branch') }} *</label>
                <select name="branch_id" required class="tr-input">
                    @foreach ($branches as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('branch_id', $quotation->branch_id ?? $defaultBranchId) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tr-span-3">
                <label class="tr-label">{{ __('transport.notes') }}</label>
                <input type="text" name="note" value="{{ old('note', $quotation->note ?? '') }}" class="tr-input">
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
            <h3 class="font-bold text-[#0F1B4C]">{{ __('transport.routes') }}</h3>
            <button type="button" id="q-add" class="tr-btn tr-btn-blue" style="font-size:.85rem;padding:.5rem 1rem">+ {{ __('transport.add_route') }}</button>
        </div>
        <div id="q-lines"></div>
    </div>

    <div class="tr-grid tr-grid-2">
        <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
            <label class="tr-label">{{ __('transport.terms') }}</label>
            <textarea name="terms" rows="6" class="tr-input">{{ old('terms', $quotation->terms ?? $defaultTerms) }}</textarea>
        </div>
        <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
            <div class="sum-row"><span>{{ __('transport.trips_total') }}</span><span id="qs-trips">0.00</span></div>
            <div class="sum-row"><span>{{ __('transport.transfers_total') }}</span><span id="qs-transfers">0.00</span></div>
            <div class="sum-row" style="align-items:center"><span>{{ __('transport.discount') }}</span>
                <input type="number" step="0.01" min="0" name="discount_amount" id="q_discount" value="{{ old('discount_amount', $quotation->discount_amount ?? 0) }}" class="tr-input" style="max-width:140px"></div>
            <div class="sum-row"><span>{{ __('transport.subtotal') }}</span><span id="qs-subtotal">0.00</span></div>
            @include('transport.partials.tax-picker', ['inputId' => 'q_tax', 'taxType' => $taxType, 'taxPercent' => $taxPercent])
            <div class="sum-row"><span>{{ __('transport.tax_amount') }}</span><span id="qs-tax">0.00</span></div>
            <div class="sum-row grand"><span>{{ __('transport.grand_total') }}</span><span><span id="qs-total">0.00</span> {{ $currencySymbol ?? '' }}</span></div>
            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.save_quotation') }}</button>
                <a href="{{ route('transport.quotations.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm">{{ __('transport.cancel') }}</a>
            </div>
        </div>
    </div>
</div>

<template id="q-template">
    <div class="q-card">
        <span class="q-num"></span>
        <div class="q-grid">
            <div>
                <label class="tr-label">{{ __('transport.truck_type') }}</label>
                <input type="text" data-f="truck_type" class="tr-input" list="q-truck-types">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.from_region') }} *</label>
                <select data-f="from_region" required class="tr-input">
                    <option value="">{{ __('transport.choose_region') }}</option>
                    @foreach ($regions as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach
                </select>
                <input type="text" data-f="from_city" class="tr-input" style="margin-top:.35rem" placeholder="{{ __('transport.city_optional') }}">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.to_region') }} *</label>
                <select data-f="to_region" required class="tr-input">
                    <option value="">{{ __('transport.choose_region') }}</option>
                    @foreach ($regions as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach
                </select>
                <input type="text" data-f="to_city" class="tr-input" style="margin-top:.35rem" placeholder="{{ __('transport.city_optional') }}">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.load_type') }}</label>
                <input type="text" data-f="load_type" class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.trips_count') }} *</label>
                <input type="number" min="1" step="1" data-f="trips_count" required class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.trip_price') }} *</label>
                <input type="number" min="0" step="0.01" data-f="trip_price" required class="tr-input">
            </div>
        </div>
        <div class="q-transfer" style="display:none">
            <div><label class="tr-label">{{ __('transport.transfer_location') }}</label><input type="text" data-f="transfer_location" class="tr-input"></div>
            <div><label class="tr-label">{{ __('transport.transfer_price') }} ({{ __('transport.per_trip') }})</label><input type="number" min="0" step="0.01" data-f="transfer_price" class="tr-input"></div>
        </div>
        <div class="q-foot">
            <div class="flex items-center gap-4 flex-wrap">
                <label class="inline-flex items-center gap-2 text-sm font-medium cursor-pointer" style="color:#F5811E">
                    <input type="checkbox" data-f="has_transfer" value="1"> {{ __('transport.has_transfer') }}
                </label>
                <input type="text" data-f="note" class="tr-input" style="max-width:260px" placeholder="{{ __('transport.trip_note') }}">
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ __('transport.line_total') }}:</span>
                <span class="q-total">0.00</span>
                <button type="button" class="tr-btn tr-btn-red js-remove">{{ __('transport.remove') }}</button>
            </div>
        </div>
    </div>
</template>

<datalist id="q-truck-types">
    <option value="تريلا"><option value="سطحة"><option value="دينا"><option value="لوري"><option value="قلاب"><option value="ثلاجة"><option value="صهريج">
</datalist>

@push('scripts')
<script>
(function () {
    const INITIAL = @json($initialItems);
    const box = document.getElementById('q-lines');
    const tpl = document.getElementById('q-template');
    const fmt = n => (Math.round((+n || 0) * 100) / 100).toFixed(2);
    let counter = 0;

    function add(d = {}) {
        const node = tpl.content.firstElementChild.cloneNode(true);
        const idx = counter++;
        node.querySelectorAll('[data-f]').forEach(el => el.name = `items[${idx}][${el.dataset.f}]`);
        const $ = f => node.querySelector(`[data-f="${f}"]`);
        ['truck_type','from_region','from_city','to_region','to_city','load_type','trip_price','transfer_location','transfer_price','note'].forEach(f => $(f).value = d[f] ?? '');
        $('trips_count').value = d.trips_count ?? 1;
        $('has_transfer').checked = !!(+d.has_transfer || d.has_transfer === true);
        const toggle = () => { node.querySelector('.q-transfer').style.display = $('has_transfer').checked ? '' : 'none'; recalc(); };
        $('has_transfer').addEventListener('change', toggle);
        node.addEventListener('input', recalc);
        node.querySelector('.js-remove').addEventListener('click', () => { node.remove(); renumber(); recalc(); });
        box.appendChild(node);
        toggle(); renumber();
    }
    function renumber() { box.querySelectorAll('.q-card').forEach((c, i) => c.querySelector('.q-num').textContent = '{{ __('transport.route') }} ' + (i + 1)); }
    function recalc() {
        let trips = 0, transfers = 0;
        box.querySelectorAll('.q-card').forEach(c => {
            const n = +c.querySelector('[data-f="trips_count"]').value || 0;
            const p = +c.querySelector('[data-f="trip_price"]').value || 0;
            const t = c.querySelector('[data-f="has_transfer"]').checked ? (+c.querySelector('[data-f="transfer_price"]').value || 0) : 0;
            trips += n * p; transfers += n * t;
            c.querySelector('.q-total').textContent = fmt(n * (p + t));
        });
        const gross = trips + transfers;
        const disc = Math.min(+document.getElementById('q_discount').value || 0, gross);
        const sub = gross - disc;
        const tax = Math.round(sub * (+document.getElementById('q_tax').value || 0)) / 100;
        document.getElementById('qs-trips').textContent = fmt(trips);
        document.getElementById('qs-transfers').textContent = fmt(transfers);
        document.getElementById('qs-subtotal').textContent = fmt(sub);
        document.getElementById('qs-tax').textContent = fmt(tax);
        document.getElementById('qs-total').textContent = fmt(sub + tax);
    }
    document.getElementById('q-add').addEventListener('click', () => add());
    ['q_discount', 'q_tax'].forEach(id => document.getElementById(id).addEventListener('input', recalc));
    if (INITIAL.length) INITIAL.forEach(add); else add();
    recalc();
    window.addEventListener('load', () => { if (window.TomSelect) new TomSelect('#q_customer', { create: false, allowEmptyOption: true }); });
})();
</script>
@endpush
