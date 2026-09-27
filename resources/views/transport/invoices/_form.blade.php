@php
    $invoice = $invoice ?? null;
    $prefill = $prefill ?? null;

    $oldItems = old('items');
    if (is_array($oldItems)) {
        $initialItems = array_values($oldItems);
    } elseif ($invoice) {
        $initialItems = $invoice->items->map(fn ($i) => [
            'truck_id' => $i->truck_id,
            'trip_date' => optional($i->trip_date)->format('Y-m-d'),
            'from_location' => $i->from_location,
            'to_location' => $i->to_location,
            'waybill_number' => $i->waybill_number,
            'trip_price' => (float) $i->trip_price,
            'has_transfer' => (bool) $i->has_transfer,
            'transfer_location' => $i->transfer_location,
            'transfer_price' => (float) $i->transfer_price,
            'note' => $i->note,
        ])->values()->all();
    } elseif ($prefill) {
        $initialItems = $prefill['items'];
    } else {
        $initialItems = [];
    }

    // الشاحنة الموقوفة اللي عليها نقلة قديمة في الفاتورة لازم تفضل ظاهرة في القائمة وقت التعديل
    $truckOptions = $trucks->map(fn ($t) => [
        'id' => $t->id,
        'label' => $t->display_name . ($t->type ? ' (' . $t->type . ')' : ''),
        'price' => (float) $t->default_trip_price,
        'driver' => $t->driver?->name,
        // تنبيه بس (مش منع): الشاحنة عليها حمل دلوقتي في حركة الشاحنات
        'busy' => $t->activeLoad ? ($t->activeLoad->from_label . ' ← ' . $t->activeLoad->to_label) : null,
    ])->values();
    if ($invoice) {
        foreach ($invoice->items as $it) {
            if (!$truckOptions->firstWhere('id', $it->truck_id)) {
                $truckOptions->push(['id' => $it->truck_id, 'label' => $it->truck_snapshot ?? ('#' . $it->truck_id), 'price' => 0, 'driver' => null]);
            }
        }
    }

    $taxPercent = old('tax_rate', $invoice ? round($invoice->tax_rate * 100, 2) : ($prefill['tax_rate'] ?? 15));
    $taxType = old('tax_type', $invoice->tax_type ?? ($prefill['tax_type'] ?? 'standard'));
    $issueDate = old('issue_date', $invoice ? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d'));
    $paymentMethod = old('payment_method', $invoice->payment_method ?? 'cash');
@endphp

<style>
    .trip-card { border: 1px solid #e5e7eb; border-radius: .75rem; padding: 1rem; background: #fafbff; position: relative; }
    .trip-card + .trip-card { margin-top: .75rem; }
    .trip-grid { display: grid; grid-template-columns: 1fr; gap: .75rem; }
    @media (min-width: 900px) { .trip-grid { grid-template-columns: 2.2fr 1.2fr 1.5fr 1.5fr 1.1fr 1.1fr; } }
    .trip-num { position: absolute; top: -10px; inset-inline-start: 14px; background: #0F1B4C; color: #fff; font-size: .7rem; font-weight: 700; padding: 2px 10px; border-radius: 999px; }
    .trip-transfer { display: grid; grid-template-columns: 1fr; gap: .75rem; margin-top: .75rem; padding: .75rem; border-radius: .5rem; background: #fff7ed; border: 1px dashed #F5811E; }
    @media (min-width: 900px) { .trip-transfer { grid-template-columns: 2fr 1fr; } }
    .trip-foot { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; margin-top: .75rem; }
    .trip-total { font-weight: 700; color: #0F1B4C; }
    .trip-driver { font-size: .72rem; color: #6b7280; margin-top: .25rem; min-height: 1rem; }
    .sum-row { display: flex; justify-content: space-between; padding: .45rem 0; font-size: .9rem; border-bottom: 1px dashed #e5e7eb; }
    .sum-row.grand { border-bottom: 0; font-size: 1.1rem; font-weight: 800; color: #0F1B4C; padding-top: .75rem; }
    .pay-opt { display: flex; align-items: center; justify-content: center; gap: .35rem; border: 1px solid #d1d5db; border-radius: .5rem; padding: .5rem; cursor: pointer; font-size: .85rem; }
    .pay-opt input { margin: 0; }
    .pay-opt.active { border-color: #1456E8; background: rgba(20,86,232,.08); color: #1456E8; font-weight: 600; }
</style>

<div class="space-y-6">
    @if ($prefill && !empty($prefill['quotation']))
        <input type="hidden" name="transport_quotation_id" value="{{ old('transport_quotation_id', $prefill['quotation']->id) }}">
        <div class="rounded-lg bg-sky-50 border border-sky-200 text-sky-800 text-sm px-4 py-2.5">
            📄 {{ __('transport.from_quotation_hint', ['number' => $prefill['quotation']->quotation_number]) }}
        </div>
    @elseif (old('transport_quotation_id'))
        <input type="hidden" name="transport_quotation_id" value="{{ old('transport_quotation_id') }}">
    @endif
    @if ($prefill && !empty($prefill['waybill']))
        <input type="hidden" name="transport_waybill_id" value="{{ old('transport_waybill_id', $prefill['waybill']->id) }}">
        <div class="rounded-lg bg-sky-50 border border-sky-200 text-sky-800 text-sm px-4 py-2.5">
            📦 {{ __('transport.from_waybill_hint', ['number' => $prefill['waybill']->waybill_number]) }}
        </div>
    @elseif (old('transport_waybill_id'))
        <input type="hidden" name="transport_waybill_id" value="{{ old('transport_waybill_id') }}">
    @endif
    {{-- بيانات الفاتورة --}}
    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
        <div class="tr-grid tr-grid-4">
            <div class="tr-span-2">
                <label class="tr-label">{{ __('transport.customer') }} *</label>
                <select name="customer_id" id="customer_id" class="tr-input">
                    <option value="">{{ __('transport.choose_customer') }}</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected((string) old('customer_id', $invoice->customer_id ?? ($prefill['customer_id'] ?? '')) === (string) $c->id)>
                            {{ $c->name }}{{ $c->phone ? ' - ' . $c->phone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tr-label">{{ __('transport.invoice_date') }} *</label>
                <input type="date" name="issue_date" id="issue_date" value="{{ $issueDate }}" required class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.branch') }} *</label>
                <select name="branch_id" required class="tr-input">
                    @foreach ($branches as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('branch_id', $invoice->branch_id ?? ($prefill['branch_id'] ?? $defaultBranchId)) === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tr-label">{{ __('transport.po_number') }}</label>
                <input type="text" name="po_number" value="{{ old('po_number', $invoice->po_number ?? '') }}" class="tr-input">
            </div>
            <div class="tr-span-3">
                <label class="tr-label">{{ __('transport.notes') }}</label>
                <input type="text" name="note" value="{{ old('note', $invoice->note ?? '') }}" class="tr-input">
            </div>
        </div>
    </div>

    {{-- النقلات --}}
    <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
            <h3 class="font-bold text-[#0F1B4C]">{{ __('transport.trips') }}</h3>
            <button type="button" id="add-trip" class="tr-btn tr-btn-blue" style="font-size:.85rem;padding:.5rem 1rem">
                + {{ __('transport.add_trip') }}
            </button>
        </div>

        <div id="trips"></div>

        <p id="no-trips" class="text-center text-gray-400 text-sm py-6" style="display:none">{{ __('transport.no_trips_yet') }}</p>
    </div>

    {{-- الإجماليات والدفع --}}
    <div class="tr-grid tr-grid-2">
        <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
            <h3 class="font-bold text-[#0F1B4C] mb-4">{{ __('transport.payment_method') }}</h3>
            <div class="tr-grid tr-grid-4" style="gap:.5rem">
                @foreach (['cash', 'bank_transfer', 'credit', 'split'] as $pm)
                    <label class="pay-opt {{ $paymentMethod === $pm ? 'active' : '' }}">
                        <input type="radio" name="payment_method" value="{{ $pm }}" @checked($paymentMethod === $pm)>
                        {{ __('transport.pay_' . $pm) }}
                    </label>
                @endforeach
            </div>
            <div id="split-box" class="tr-grid tr-grid-3 mt-4" style="{{ $paymentMethod === 'split' ? '' : 'display:none' }}">
                <div>
                    <label class="tr-label">{{ __('transport.cash_amount') }}</label>
                    <input type="number" step="0.01" min="0" name="cash_amount" id="cash_amount" value="{{ old('cash_amount', $invoice->cash_amount ?? 0) }}" class="tr-input">
                </div>
                <div>
                    <label class="tr-label">{{ __('transport.bank_amount') }}</label>
                    <input type="number" step="0.01" min="0" name="bank_amount" id="bank_amount" value="{{ old('bank_amount', $invoice->bank_amount ?? 0) }}" class="tr-input">
                </div>
                <div>
                    <label class="tr-label">{{ __('transport.credit_amount') }}</label>
                    <input type="text" id="credit_preview" readonly class="tr-input" style="background:#f9fafb">
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
            <div class="sum-row"><span>{{ __('transport.trips_total') }}</span><span id="s-trips">0.00</span></div>
            <div class="sum-row"><span>{{ __('transport.transfers_total') }}</span><span id="s-transfers">0.00</span></div>
            <div class="sum-row" style="align-items:center">
                <span>{{ __('transport.discount') }}</span>
                <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount ?? ($prefill['discount_amount'] ?? 0)) }}" class="tr-input" style="max-width:140px">
            </div>
            <div class="sum-row"><span>{{ __('transport.subtotal') }}</span><span id="s-subtotal">0.00</span></div>
            @include('transport.partials.tax-picker', ['inputId' => 'tax_rate', 'taxType' => $taxType, 'taxPercent' => $taxPercent])
            <div class="sum-row"><span>{{ __('transport.tax_amount') }}</span><span id="s-tax">0.00</span></div>
            <div class="sum-row grand"><span>{{ __('transport.grand_total') }}</span><span><span id="s-total">0.00</span> {{ $currencySymbol ?? '' }}</span></div>

            <div class="flex items-center gap-3 mt-6">
                @php $canDraft = !$invoice || $invoice->is_draft; @endphp
                <button type="submit" name="save_as" value="final" class="px-6 py-2.5 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
                    {{ ($invoice && $invoice->is_draft) ? __('transport.convert_draft_to_invoice') : __('transport.save_invoice') }}
                </button>
                @if ($canDraft)
                    <button type="submit" name="save_as" value="draft" class="px-5 py-2.5 rounded-lg text-sm font-medium" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa">
                        📝 {{ __('transport.save_as_draft') }}
                    </button>
                @endif
                <a href="{{ route('transport.invoices.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">{{ __('transport.cancel') }}</a>
            </div>
        </div>
    </div>
</div>

<template id="trip-template">
    <div class="trip-card">
        <span class="trip-num"></span>
        <div class="trip-grid">
            <div>
                <label class="tr-label">{{ __('transport.truck') }} *</label>
                <select data-f="truck_id" required class="tr-input">
                    <option value="">{{ __('transport.choose_truck') }}</option>
                </select>
                <div class="trip-driver"></div>
            </div>
            <div>
                <label class="tr-label">{{ __('transport.load_date') }} *</label>
                <input type="date" data-f="trip_date" required class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.from') }}</label>
                <input type="text" data-f="from_location" class="tr-input" list="places">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.to') }}</label>
                <input type="text" data-f="to_location" class="tr-input" list="places">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.waybill_number') }}</label>
                <input type="text" data-f="waybill_number" class="tr-input">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.trip_price') }} *</label>
                <input type="number" step="0.01" min="0" data-f="trip_price" required class="tr-input">
            </div>
        </div>

        <div class="trip-transfer" style="display:none">
            <div>
                <label class="tr-label">{{ __('transport.transfer_location') }}</label>
                <input type="text" data-f="transfer_location" class="tr-input" list="places" placeholder="{{ __('transport.transfer_location_hint') }}">
            </div>
            <div>
                <label class="tr-label">{{ __('transport.transfer_price') }}</label>
                <input type="number" step="0.01" min="0" data-f="transfer_price" class="tr-input">
            </div>
        </div>

        <div class="trip-foot">
            <div class="flex items-center gap-4 flex-wrap">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-[#F5811E] cursor-pointer">
                    <input type="checkbox" data-f="has_transfer" value="1">
                    {{ __('transport.has_transfer') }}
                </label>
                <input type="text" data-f="note" class="tr-input" style="max-width:260px" placeholder="{{ __('transport.trip_note') }}">
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ __('transport.line_total') }}:</span>
                <span class="trip-total">0.00</span>
                <button type="button" class="tr-btn tr-btn-red js-remove">{{ __('transport.remove') }}</button>
            </div>
        </div>
    </div>
</template>

<datalist id="places"></datalist>

@push('scripts')
<script>
(function () {
    const TRUCKS = @json($truckOptions);
    const INITIAL = @json($initialItems);
    const REGIONS = @json(array_values(\App\Support\SaudiRegions::options()));
    const container = document.getElementById('trips');
    const tpl = document.getElementById('trip-template');
    const issueDate = document.getElementById('issue_date');
    const fmt = n => (Math.round((+n || 0) * 100) / 100).toFixed(2);
    let counter = 0;

    function truckById(id) { return TRUCKS.find(t => String(t.id) === String(id)); }

    function addTrip(data = {}) {
        const node = tpl.content.firstElementChild.cloneNode(true);
        const idx = counter++;
        node.querySelectorAll('[data-f]').forEach(el => { el.name = `items[${idx}][${el.dataset.f}]`; });

        const sel = node.querySelector('[data-f="truck_id"]');
        TRUCKS.forEach(t => sel.add(new Option(t.busy ? t.label + '  ⛔' : t.label, t.id)));

        const $ = f => node.querySelector(`[data-f="${f}"]`);
        sel.value = data.truck_id ?? '';
        $('trip_date').value = data.trip_date || issueDate.value;
        $('from_location').value = data.from_location ?? '';
        $('to_location').value = data.to_location ?? '';
        $('waybill_number').value = data.waybill_number ?? '';
        $('trip_price').value = data.trip_price ?? '';
        $('has_transfer').checked = !!(+data.has_transfer || data.has_transfer === true || data.has_transfer === 'on');
        $('transfer_location').value = data.transfer_location ?? '';
        $('transfer_price').value = data.transfer_price ?? '';
        $('note').value = data.note ?? '';

        const showDriver = () => {
            const t = truckById(sel.value);
            const box = node.querySelector('.trip-driver');
            box.textContent = t && t.driver ? '{{ __('transport.driver') }}: ' + t.driver : '';
            if (t && t.busy) {
                const w = document.createElement('div');
                w.style.cssText = 'color:#be123c;font-weight:700';
                w.textContent = '⛔ {{ __('transport.truck_has_load') }} (' + t.busy + ')';
                box.appendChild(w);
            }
        };
        const toggleTransfer = () => {
            node.querySelector('.trip-transfer').style.display = $('has_transfer').checked ? '' : 'none';
            recalc();
        };

        sel.addEventListener('change', () => {
            const t = truckById(sel.value);
            if (t && t.price > 0 && !(+$('trip_price').value)) $('trip_price').value = t.price;
            showDriver();
            recalc();
        });
        $('has_transfer').addEventListener('change', toggleTransfer);
        node.addEventListener('input', recalc);
        node.addEventListener('change', rememberPlaces);
        node.querySelector('.js-remove').addEventListener('click', () => { node.remove(); renumber(); recalc(); });

        container.appendChild(node);
        showDriver();
        toggleTransfer();
        renumber();
        return node;
    }

    function renumber() {
        const cards = container.querySelectorAll('.trip-card');
        cards.forEach((c, i) => c.querySelector('.trip-num').textContent = '{{ __('transport.trip') }} ' + (i + 1));
        document.getElementById('no-trips').style.display = cards.length ? 'none' : '';
    }

    function rememberPlaces() {
        const list = document.getElementById('places');
        const vals = new Set(REGIONS);
        container.querySelectorAll('[data-f="from_location"],[data-f="to_location"],[data-f="transfer_location"]').forEach(i => i.value.trim() && vals.add(i.value.trim()));
        list.innerHTML = '';
        vals.forEach(v => list.appendChild(new Option(v)));
    }

    function recalc() {
        let trips = 0, transfers = 0;
        container.querySelectorAll('.trip-card').forEach(card => {
            const price = +card.querySelector('[data-f="trip_price"]').value || 0;
            const hasT = card.querySelector('[data-f="has_transfer"]').checked;
            const tPrice = hasT ? (+card.querySelector('[data-f="transfer_price"]').value || 0) : 0;
            trips += price; transfers += tPrice;
            card.querySelector('.trip-total').textContent = fmt(price + tPrice);
        });
        const gross = trips + transfers;
        const discount = Math.min(+document.getElementById('discount_amount').value || 0, gross);
        const subtotal = gross - discount;
        const tax = Math.round(subtotal * (+document.getElementById('tax_rate').value || 0)) / 100;
        const total = subtotal + tax;

        document.getElementById('s-trips').textContent = fmt(trips);
        document.getElementById('s-transfers').textContent = fmt(transfers);
        document.getElementById('s-subtotal').textContent = fmt(subtotal);
        document.getElementById('s-tax').textContent = fmt(tax);
        document.getElementById('s-total').textContent = fmt(total);

        const cash = +document.getElementById('cash_amount').value || 0;
        const bank = +document.getElementById('bank_amount').value || 0;
        document.getElementById('credit_preview').value = fmt(Math.max(0, total - cash - bank));
    }

    // طريقة الدفع
    document.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', () => {
        document.querySelectorAll('.pay-opt').forEach(l => l.classList.toggle('active', l.querySelector('input').checked));
        document.getElementById('split-box').style.display = r.value === 'split' && r.checked ? '' : 'none';
    }));

    ['discount_amount', 'tax_rate', 'cash_amount', 'bank_amount'].forEach(id => document.getElementById(id).addEventListener('input', recalc));
    document.getElementById('add-trip').addEventListener('click', () => {
        const node = addTrip();
        node.querySelector('[data-f="truck_id"]').focus();
    });

    if (INITIAL.length) INITIAL.forEach(addTrip); else addTrip();
    rememberPlaces();
    recalc();

    // بحث في العملاء لو مكتبة TomSelect موجودة
    // (سكريبت TomSelect بيتحمّل بعد الـ stack، فبنستنى تحميل الصفحة)
    window.addEventListener('load', () => {
        if (window.TomSelect) new TomSelect('#customer_id', { create: false, allowEmptyOption: true });
    });
})();
</script>
@endpush
