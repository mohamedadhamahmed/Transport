{{--
    فاتورة نقل ضريبية (جديدة / تعديل): اختار العميل ← أحماله غير المفوترة
    تظهر ← علّم عليها (وعدّل السعر لو محتاج) ← بنود يدوية اختيارية ← حفظ وطباعة.
--}}
@php
    $invoice = $invoice ?? null;
    $isEdit = (bool) $invoice;
    $formUrl = $isEdit ? route('transport.invoices.edit', $invoice) : route('transport.invoices.create');

    $oldLoads = old('loads');
    $checkedLoads = is_array($oldLoads)
        ? array_map('intval', $oldLoads)
        : ($isEdit ? $loads->where('transport_invoice_id', $invoice->id)->pluck('id')->all() : $loads->where('status', 'unloaded')->pluck('id')->all());

    $manualLines = old('lines');
    if (!is_array($manualLines)) {
        $manualLines = $isEdit
            ? $invoice->items->filter(fn ($i) => $i->description)->map(fn ($i) => [
                'description' => $i->description,
                'quantity' => (float) $i->quantity,
                // لو الأسعار كانت شاملة الضريبة نرجّع السعر زي ما اتكتب
                'unit_price' => $invoice->prices_include_tax ? round($i->unit_price * (1 + $invoice->tax_rate), 2) : (float) $i->unit_price,
            ])->values()->all()
            : [];
    }
    if (!$manualLines) {
        $manualLines = [['description' => '', 'quantity' => 1, 'unit_price' => '']];
    }

    $taxType = old('tax_type', $invoice->tax_type ?? 'standard');
    $inclusive = (bool) old('prices_include_tax', $invoice->prices_include_tax ?? false);
    $issueAt = old('issue_at', $isEdit
        ? $invoice->issue_date->format('Y-m-d') . 'T' . substr($invoice->issue_time ?: '00:00', 0, 5)
        : now('Asia/Riyadh')->format('Y-m-d\TH:i'));

    // السعر: المكتوب قبل كده (old) ← سعر الحمل/أجرة البوليصة ← سعر النقلة الافتراضي للشاحنة
    $loadPrice = function ($l) {
        $p = old('load_prices.' . $l->id);
        if ($p !== null) return $p;
        return $l->billing_price ?? ((float) ($l->truck?->default_trip_price) ?: '');
    };
@endphp

<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    <style>
        .inv-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; align-items: start; }
        @media (min-width: 1100px) { .inv-grid { grid-template-columns: minmax(0, 1fr) 400px; } .inv-side { position: sticky; top: 1rem; } }
        .inv-fields { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 768px) { .inv-fields { grid-template-columns: repeat(3, 1fr); } .inv-span-2 { grid-column: span 2; } }
        .cust-bar { display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: end; }
        @media (min-width: 900px) { .cust-bar { grid-template-columns: 1.6fr 1fr 1fr 1.2fr auto; } }
        .tax-opts { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
        .tax-opt { border: 1.5px solid #e5e7eb; border-radius: 12px; padding: .7rem; text-align: center; cursor: pointer; transition: .15s; }
        .tax-opt input { display: none; }
        .tax-opt b { display: block; font-size: 1.15rem; color: #0F1B4C; }
        .tax-opt span { font-size: .7rem; color: #6b7280; }
        .tax-opt.active { border-color: #1456E8; background: #f3f7ff; box-shadow: 0 0 0 2px rgba(20,86,232,.1); }
        .sum-line { display: flex; justify-content: space-between; font-size: .82rem; padding: .45rem 0; color: #374151; }
        .sum-line b { color: #111827; font-weight: 800; }
        .sum-grand { display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #0F1B4C; margin-top: .4rem; padding-top: .7rem; font-size: 1rem; font-weight: 800; color: #0F1B4C; }
        .sum-grand b { font-size: 1.2rem; }
        .ld-price { width: 110px; min-height: 34px; padding: .35rem .5rem; }
        .ld-row.off td { opacity: .55; }
        .ml-row { display: grid; grid-template-columns: 1fr; gap: .6rem; align-items: center; padding: .6rem 0; border-bottom: 1px dashed #eef0f5; }
        @media (min-width: 768px) { .ml-row { grid-template-columns: minmax(0, 1fr) 110px 150px 36px; } }
        .ml-del { width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e5e7eb; color: #9ca3af; font-size: 1rem; }
        .ml-del:hover { color: #e11d48; border-color: #fecdd3; background: #fff1f2; }
        .chk { width: 16px; height: 16px; accent-color: #1456E8; }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => $isEdit ? __('transport.edit_invoice') . ' ' . $invoice->invoice_number : __('transport.new_tax_invoice'),
            'badge' => $isEdit ? null : __('transport.expected_number', ['number' => $expectedNumber]),
            'crumbs' => [['label' => __('transport.invoices'), 'url' => route('transport.invoices.index')], ['label' => $isEdit ? __('transport.edit_invoice') : __('transport.new_tax_invoice')]],
            'buttons' => [
                ['label' => __('transport.previous_invoices'), 'url' => route('transport.invoices.index'), 'style' => 'gray', 'icon' => 'list'],
                ['label' => __('transport.unbilled_loads'), 'url' => route('transport.reports.unbilled'), 'style' => 'outline', 'icon' => 'file'],
            ],
        ])

        @include('transport.partials.flash')

        {{-- العميل والأحمال (بيعيد تحميل الصفحة بأحمال العميل) --}}
        <div class="tv-card tv-pad">
            <div class="tv-section-title" style="margin-bottom:1rem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9.5"/><circle cx="12" cy="10" r="3"/><path d="M6.5 18.5a6.5 6.5 0 0 1 11 0"/></svg>
                {{ __('transport.customer_and_loads') }}
            </div>
            <div class="cust-bar" id="loads-filter">
                <div>
                    <label class="tv-label">{{ __('transport.customer_company') }}</label>
                    <select id="f-customer" class="tv-input tv-select">
                        <option value="">{{ __('transport.choose_customer') }}</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected($customer && $customer->id === $c->id)>{{ $c->name }}{{ $c->tax_number ? ' (' . $c->tax_number . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.loads_from_date') }}</label>
                    <input type="date" id="f-from" value="{{ request('date_from') }}" class="tv-input">
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.date_to') }}</label>
                    <input type="date" id="f-to" value="{{ request('date_to') }}" class="tv-input">
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-600" style="min-height:40px">
                    <input type="checkbox" id="f-loaded" class="chk" @checked($includeLoaded)>
                    {{ __('transport.include_loaded') }}
                </label>
                <button type="button" id="f-go" class="tv-btn tv-btn-blue tv-btn-lg" style="min-width:170px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    {{ __('transport.show_loads') }}
                </button>
            </div>
        </div>

        <form method="POST" action="{{ $isEdit ? route('transport.invoices.update', $invoice) : route('transport.invoices.store') }}" id="inv-form">
            @csrf
            @if ($isEdit) @method('PUT') @endif
            <input type="hidden" name="form_type" value="loads">
            <input type="hidden" name="customer_id" value="{{ $customer?->id }}">

            <div class="inv-grid">
                {{-- ===== العمود الرئيسي ===== --}}
                <div class="space-y-5" style="min-width:0">
                    {{-- بيانات الفاتورة --}}
                    <div class="tv-card tv-pad">
                        <div class="tv-section-title" style="margin-bottom:1rem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
                            {{ __('transport.invoice_data') }}
                        </div>
                        <div class="inv-fields">
                            <div>
                                <label class="tv-label">{{ __('transport.invoice_date') }}</label>
                                <input type="datetime-local" name="issue_at" value="{{ $issueAt }}" required class="tv-input">
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.supply_from') }}</label>
                                <input type="date" name="supply_from" value="{{ old('supply_from', $invoice?->supply_from?->format('Y-m-d') ?? request('date_from')) }}" class="tv-input">
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.supply_to') }}</label>
                                <input type="date" name="supply_to" value="{{ old('supply_to', $invoice?->supply_to?->format('Y-m-d') ?? request('date_to')) }}" class="tv-input">
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.customer_vat') }} <small>{{ __('transport.customer_vat_hint') }}</small></label>
                                <input type="text" name="customer_tax_number" id="cust-vat" value="{{ old('customer_tax_number', $customer?->tax_number) }}" maxlength="15" inputmode="numeric" class="tv-input">
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.customer_cr') }}</label>
                                <input type="text" name="customer_cr" value="{{ old('customer_cr', $customer?->commercial_registration_number) }}" class="tv-input">
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.po_reference') }}</label>
                                <input type="text" name="po_number" value="{{ old('po_number', $invoice?->po_number) }}" class="tv-input">
                            </div>
                            <div class="inv-span-2">
                                <label class="tv-label">{{ __('transport.customer_address') }}</label>
                                <input type="text" name="customer_address" value="{{ old('customer_address', $customer?->address) }}" class="tv-input">
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.customer_phone') }}</label>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone', $customer?->phone) }}" class="tv-input" placeholder="05--------">
                            </div>
                            @if ($branches->count() > 1)
                                <div>
                                    <label class="tv-label">{{ __('transport.branch') }}</label>
                                    <select name="branch_id" class="tv-input">
                                        @foreach ($branches as $id => $name)
                                            <option value="{{ $id }}" @selected((string) old('branch_id', $invoice->branch_id ?? $defaultBranchId) === (string) $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="branch_id" value="{{ old('branch_id', $invoice->branch_id ?? $defaultBranchId) }}">
                            @endif
                        </div>
                    </div>

                    {{-- الأحمال غير المفوترة --}}
                    <div class="tv-card tv-pad">
                        <div class="flex items-center justify-between gap-3 flex-wrap" style="margin-bottom:.9rem">
                            <div class="tv-section-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/></svg>
                                {{ __('transport.unbilled_loads') }}@if ($customer) {{ __('transport.for_customer', ['name' => $customer->name]) }}@endif
                                <span class="tv-count">{{ $loads->count() }}</span>
                            </div>
                            @if ($loads->isNotEmpty())
                                <label class="flex items-center gap-2 text-xs font-bold text-gray-600">
                                    <input type="checkbox" id="ld-all" class="chk"> {{ __('transport.select_all') }}
                                </label>
                            @endif
                        </div>
                        <div class="tv-table-wrap">
                            <table class="tv-table">
                                <thead>
                                    <tr>
                                        <th style="width:36px"></th>
                                        <th>{{ __('transport.load_date') }}</th>
                                        <th>{{ __('transport.plate_number') }}</th>
                                        <th>{{ __('transport.from') }}</th>
                                        <th>{{ __('transport.to') }}</th>
                                        <th>{{ __('transport.load_type') }}</th>
                                        <th>{{ __('transport.waybill_ref') }}</th>
                                        <th>{{ __('transport.status') }}</th>
                                        <th>{{ __('transport.price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($loads as $l)
                                        @php $on = in_array($l->id, $checkedLoads, true); @endphp
                                        <tr class="ld-row {{ $on ? '' : 'off' }}">
                                            <td><input type="checkbox" name="loads[]" value="{{ $l->id }}" class="chk ld-chk" @checked($on)></td>
                                            <td>{{ $l->loaded_at?->format('Y-m-d') }}</td>
                                            <td style="font-weight:700">{{ $l->truck?->plate_number ?? '-' }}</td>
                                            <td>{{ $l->from_label }}</td>
                                            <td>{{ $l->to_label }}</td>
                                            <td>{{ $l->load_type }}</td>
                                            <td>{{ $l->waybill_number ?: ($l->waybill?->waybill_number ?? '-') }}</td>
                                            <td>
                                                @if ($l->status === 'unloaded')
                                                    <span class="tv-pill tv-pill-green">{{ __('transport.load_status_unloaded') }}</span>
                                                @else
                                                    <span class="tv-pill tv-pill-amber">{{ __('transport.load_status_loaded') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" name="load_prices[{{ $l->id }}]" value="{{ $loadPrice($l) }}" class="tv-input ld-price" placeholder="0.00">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="tv-empty">
                                                {{ $customer ? __('transport.no_unbilled_for_customer') : __('transport.choose_customer_first') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- بنود إضافية (اختياري) --}}
                    <div class="tv-card tv-pad">
                        <div class="flex items-center justify-between gap-3 flex-wrap" style="margin-bottom:.5rem">
                            <div class="tv-section-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/></svg>
                                {{ __('transport.extra_lines') }}
                            </div>
                            <button type="button" id="ml-add" class="tv-btn tv-btn-outline">+ {{ __('transport.line') }}</button>
                        </div>
                        <div class="hidden md:grid text-xs font-bold text-gray-600" style="grid-template-columns: minmax(0,1fr) 110px 150px 36px; gap:.6rem; padding:.4rem 0; border-bottom:1px solid #eef0f5">
                            <span>{{ __('transport.description') }}</span><span>{{ __('transport.quantity') }}</span><span>{{ __('transport.unit_price') }}</span><span></span>
                        </div>
                        <div id="ml-list">
                            @foreach ($manualLines as $i => $m)
                                <div class="ml-row">
                                    <input type="text" name="lines[{{ $i }}][description]" value="{{ $m['description'] ?? '' }}" class="tv-input" placeholder="{{ __('transport.line_placeholder') }}">
                                    <input type="number" step="0.01" min="0" name="lines[{{ $i }}][quantity]" value="{{ $m['quantity'] ?? 1 }}" class="tv-input ml-qty">
                                    <input type="number" step="0.01" min="0" name="lines[{{ $i }}][unit_price]" value="{{ $m['unit_price'] ?? '' }}" class="tv-input ml-price" placeholder="0.00">
                                    <button type="button" class="ml-del" title="{{ __('transport.delete') }}">×</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ===== الإجماليات ===== --}}
                <div class="inv-side">
                    <div class="tv-card tv-pad space-y-4">
                        <div class="tv-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h2M12 10h2M16 10h.01M8 14h2M12 14h2M16 14h.01M8 18h2M12 18h2M16 18h.01"/></svg>
                            {{ __('transport.totals') }}
                        </div>

                        <div>
                            <label class="tv-label">{{ __('transport.tax_type') }}</label>
                            <div class="tax-opts">
                                <label class="tax-opt {{ $taxType === 'standard' ? 'active' : '' }}">
                                    <input type="radio" name="tax_type" value="standard" data-rate="0.15" @checked($taxType === 'standard')>
                                    <b>15%</b><span>{{ __('transport.tax_standard_short') }}</span>
                                </label>
                                <label class="tax-opt {{ $taxType === 'international' ? 'active' : '' }}">
                                    <input type="radio" name="tax_type" value="international" data-rate="0" @checked($taxType === 'international')>
                                    <b>0%</b><span>{{ __('transport.tax_zero_short') }}</span>
                                </label>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-xs text-gray-700">
                            <input type="hidden" name="prices_include_tax" value="0">
                            <input type="checkbox" name="prices_include_tax" value="1" id="incl" class="chk" @checked($inclusive)>
                            {{ __('transport.prices_include_tax') }}
                        </label>

                        <div>
                            <label class="tv-label">{{ __('transport.invoice_discount_before_tax') }}</label>
                            <input type="number" step="0.01" min="0" name="discount_amount" id="discount" value="{{ old('discount_amount', $invoice ? (float) $invoice->discount_amount : 0) }}" class="tv-input">
                        </div>

                        <div>
                            <div class="sum-line"><span>{{ __('transport.selected_loads_count') }}</span><b id="s-count">0</b></div>
                            <div class="sum-line"><span>{{ __('transport.total_before_tax') }}</span><b id="s-gross">0.00</b></div>
                            <div class="sum-line"><span>{{ __('transport.discount') }}</span><b id="s-discount">0.00</b></div>
                            <div class="sum-line"><span>{{ __('transport.taxable_amount') }}</span><b id="s-taxable">0.00</b></div>
                            <div class="sum-line"><span id="s-tax-label">{{ __('transport.vat_label', ['rate' => 15]) }}</span><b id="s-tax">0.00</b></div>
                            <div class="sum-grand"><span>{{ __('transport.grand_total_incl') }}</span><b id="s-total">0.00</b></div>
                        </div>

                        <div class="tv-alert-info">
                            <span class="i">i</span>
                            <div>{{ __('transport.invoice_entry_note', ['name' => $customer?->name ?? __('transport.the_customer')]) }}</div>
                        </div>

                        <button type="submit" class="tv-btn tv-btn-green tv-btn-lg tv-btn-block" @disabled(!$customer)>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                            {{ __('transport.save_and_print') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="ml-tpl">
        <div class="ml-row">
            <input type="text" data-name="description" class="tv-input" placeholder="{{ __('transport.line_placeholder') }}">
            <input type="number" step="0.01" min="0" data-name="quantity" value="1" class="tv-input ml-qty">
            <input type="number" step="0.01" min="0" data-name="unit_price" class="tv-input ml-price" placeholder="0.00">
            <button type="button" class="ml-del" title="{{ __('transport.delete') }}">×</button>
        </div>
    </template>

    @include('transport.partials.tv-select-script')

    <script>
        (function () {
            const form = document.getElementById('inv-form');
            const fmt = n => (Math.round(n * 100) / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const num = el => parseFloat(el && el.value) || 0;
            const vatLabel = @json(__('transport.vat_label', ['rate' => '__R__']));

            // ---- إعادة تحميل الشاشة بأحمال العميل المختار
            function reloadLoads() {
                const cid = document.getElementById('f-customer').value;
                const url = new URL(@json($formUrl));
                if (cid) url.searchParams.set('customer_id', cid);
                const f = document.getElementById('f-from').value, t = document.getElementById('f-to').value;
                if (f) url.searchParams.set('date_from', f);
                if (t) url.searchParams.set('date_to', t);
                if (document.getElementById('f-loaded').checked) url.searchParams.set('include_loaded', 1);
                window.location = url.toString();
            }
            document.getElementById('f-go').addEventListener('click', reloadLoads);
            document.getElementById('f-customer').addEventListener('change', function () {
                if (this.value && this.value !== @json((string) ($customer?->id ?? ''))) reloadLoads();
            });

            // ---- البنود اليدوية
            let lineIdx = {{ count($manualLines) }};
            document.getElementById('ml-add').addEventListener('click', function () {
                const node = document.getElementById('ml-tpl').content.firstElementChild.cloneNode(true);
                node.querySelectorAll('[data-name]').forEach(el => el.name = 'lines[' + lineIdx + '][' + el.dataset.name + ']');
                lineIdx++;
                document.getElementById('ml-list').appendChild(node);
                node.querySelector('input').focus();
                recalc();
            });
            document.getElementById('ml-list').addEventListener('click', function (e) {
                const btn = e.target.closest('.ml-del');
                if (!btn) return;
                const row = btn.closest('.ml-row');
                if (this.querySelectorAll('.ml-row').length > 1) row.remove();
                else row.querySelectorAll('input').forEach(i => i.value = i.classList.contains('ml-qty') ? 1 : '');
                recalc();
            });

            // ---- الأحمال
            const all = document.getElementById('ld-all');
            const chks = () => Array.from(document.querySelectorAll('.ld-chk'));
            if (all) {
                all.checked = chks().length > 0 && chks().every(c => c.checked);
                all.addEventListener('change', () => { chks().forEach(c => { c.checked = all.checked; }); recalc(); });
            }

            // ---- نوع الضريبة
            document.querySelectorAll('.tax-opt input').forEach(r => r.addEventListener('change', function () {
                document.querySelectorAll('.tax-opt').forEach(o => o.classList.toggle('active', o.querySelector('input').checked));
                recalc();
            }));

            // ---- الحساب (نفس منطق السيرفر)
            function recalc() {
                const rateEl = document.querySelector('.tax-opt input:checked');
                const rate = rateEl ? parseFloat(rateEl.dataset.rate) : 0.15;
                const incl = document.getElementById('incl').checked;
                const net = v => Math.round((incl ? v / (1 + rate) : v) * 100) / 100;

                let gross = 0, count = 0;
                chks().forEach(c => {
                    const row = c.closest('tr');
                    row.classList.toggle('off', !c.checked);
                    if (!c.checked) return;
                    count++;
                    gross += net(num(row.querySelector('.ld-price')));
                });
                document.querySelectorAll('#ml-list .ml-row').forEach(r => {
                    const desc = r.querySelector('input[type=text]').value.trim();
                    const q = num(r.querySelector('.ml-qty')), p = num(r.querySelector('.ml-price'));
                    if (desc && q > 0 && p > 0) gross += Math.round(q * net(p) * 100) / 100;
                });
                gross = Math.round(gross * 100) / 100;
                const discount = Math.min(Math.round(num(document.getElementById('discount')) * 100) / 100, gross);
                const taxable = Math.round((gross - discount) * 100) / 100;
                const tax = Math.round(taxable * rate * 100) / 100;

                document.getElementById('s-count').textContent = count;
                document.getElementById('s-gross').textContent = fmt(gross);
                document.getElementById('s-discount').textContent = fmt(discount);
                document.getElementById('s-taxable').textContent = fmt(taxable);
                document.getElementById('s-tax').textContent = fmt(tax);
                document.getElementById('s-tax-label').textContent = vatLabel.replace('__R__', Math.round(rate * 100));
                document.getElementById('s-total').textContent = fmt(taxable + tax);
                if (all) all.checked = chks().length > 0 && chks().every(c => c.checked);
            }

            form.addEventListener('input', recalc);
            form.addEventListener('change', recalc);

            // الرقم الضريبي: أرقام بس
            document.getElementById('cust-vat').addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 15); });

            form.addEventListener('submit', function (e) {
                const anyLoad = chks().some(c => c.checked);
                const anyLine = Array.from(document.querySelectorAll('#ml-list .ml-row')).some(r => r.querySelector('input[type=text]').value.trim() && num(r.querySelector('.ml-price')) > 0);
                if (!anyLoad && !anyLine) {
                    e.preventDefault();
                    const msg = @json(__('transport.pick_loads_or_lines'));
                    window.Swal ? Swal.fire({ icon: 'warning', text: msg }) : alert(msg);
                }
            });

            recalc();
        })();
    </script>
</x-app-layout>
