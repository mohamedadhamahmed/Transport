<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    @php
        $rate = (float) $invoice->tax_rate;
        $reasons = [
            __('transport.cn_reason_price'),
            __('transport.cn_reason_trip'),
            __('transport.cn_reason_discount'),
            __('transport.cn_reason_cancel'),
            __('transport.cn_reason_customer'),
        ];
    @endphp
    <style>
        .cn-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; align-items: start; }
        @media (min-width: 1100px) { .cn-grid { grid-template-columns: minmax(0, 1fr) 360px; } .cn-side { position: sticky; top: 1rem; } }
        .cn-amt { width: 130px; min-height: 34px; padding: .35rem .5rem; }
        .sum-line { display: flex; justify-content: space-between; font-size: .82rem; padding: .45rem 0; color: #374151; }
        .sum-line b { color: #111827; }
        .sum-grand { display: flex; justify-content: space-between; border-top: 2px solid #9f1239; margin-top: .4rem; padding-top: .7rem; font-weight: 800; color: #9f1239; }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.cn_new'),
            'badge' => __('transport.cn_on_invoice') . ' ' . $invoice->invoice_number,
            'icon' => 'file',
            'crumbs' => [['label' => __('transport.invoices'), 'url' => route('transport.invoices.index')], ['label' => $invoice->invoice_number, 'url' => route('transport.invoices.show', $invoice)], ['label' => __('transport.cn_new')]],
            'buttons' => [['label' => __('transport.cn_list'), 'url' => route('transport.credit-notes.index'), 'style' => 'gray', 'icon' => 'list']],
        ])
        @include('transport.partials.flash')

        <div class="tv-alert-info">
            <span class="i">i</span>
            <div>{{ __('transport.cn_hint') }}@if ($invoice->is_sent_to_zatca) {{ __('transport.cn_hint_zatca') }}@endif</div>
        </div>

        <form method="POST" action="{{ route('transport.credit-notes.store', $invoice) }}" id="cn-form">
            @csrf
            <div class="cn-grid">
                <div class="space-y-5" style="min-width:0">
                    <div class="tv-card tv-pad">
                        <div class="tv-section-title" style="margin-bottom:1rem">{{ __('transport.cn_data') }}</div>
                        <div class="tr-grid tr-grid-3">
                            <div>
                                <label class="tv-label">{{ __('transport.customer') }}</label>
                                <div class="tv-input" style="background:#f8fafc">{{ $invoice->customer?->name }}</div>
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.cn_original_invoice') }}</label>
                                <div class="tv-input" style="background:#f8fafc">{{ $invoice->invoice_number }} · {{ $invoice->issue_date->format('Y-m-d') }} · {{ number_format((float) $invoice->total, 2) }}</div>
                            </div>
                            <div>
                                <label class="tv-label">{{ __('transport.date') }} *</label>
                                <input type="date" name="issue_date" value="{{ old('issue_date', now('Asia/Riyadh')->toDateString()) }}" required class="tv-input">
                            </div>
                            <div class="tr-span-full">
                                <label class="tv-label">{{ __('transport.cn_reason') }} * <small>{{ __('transport.cn_reason_hint') }}</small></label>
                                <input type="text" name="reason" value="{{ old('reason') }}" required maxlength="500" class="tv-input" list="cn-reasons">
                                <datalist id="cn-reasons">
                                    @foreach ($reasons as $r)<option value="{{ $r }}">@endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>

                    <div class="tv-card tv-pad">
                        <div class="flex items-center justify-between gap-3 flex-wrap" style="margin-bottom:.9rem">
                            <div class="tv-section-title">{{ __('transport.cn_lines') }}</div>
                            <button type="button" id="cn-full" class="tv-btn tv-btn-red">{{ __('transport.cn_full') }}</button>
                        </div>
                        <div class="tv-table-wrap">
                            <table class="tv-table">
                                <thead><tr>
                                    <th>#</th><th>{{ __('transport.description') }}</th><th>{{ __('transport.waybill_ref') }}</th>
                                    <th>{{ __('transport.cn_line_total') }}</th><th>{{ __('transport.cn_credited_before') }}</th><th>{{ __('transport.cn_remaining') }}</th><th>{{ __('transport.cn_amount') }}</th>
                                </tr></thead>
                                <tbody>
                                    @foreach ($lines as $i => $l)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td style="font-weight:700">{{ $l['label'] }}</td>
                                            <td>{{ $l['waybill'] ?: '-' }}</td>
                                            <td>{{ number_format($l['total'], 2) }}</td>
                                            <td>{{ $l['credited'] ? number_format($l['credited'], 2) : '-' }}</td>
                                            <td style="font-weight:700">{{ number_format($l['remaining'], 2) }}</td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="{{ $l['remaining'] }}" name="amounts[{{ $l['id'] }}]"
                                                       value="{{ old('amounts.' . $l['id']) }}" data-max="{{ $l['remaining'] }}" class="tv-input cn-amt" placeholder="0.00" @disabled($l['remaining'] <= 0)>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tv-hint">{{ __('transport.cn_amount_hint') }}</div>
                    </div>
                </div>

                <div class="cn-side">
                    <div class="tv-card tv-pad space-y-3">
                        <div class="tv-section-title">{{ __('transport.totals') }}</div>
                        <div>
                            <div class="sum-line"><span>{{ __('transport.cn_invoice_net') }}</span><b>{{ number_format((float) $invoice->subtotal, 2) }}</b></div>
                            <div class="sum-line"><span>{{ __('transport.cn_credited_before') }}</span><b>{{ number_format($remaining['credited'], 2) }}</b></div>
                            <div class="sum-line"><span>{{ __('transport.cn_remaining') }}</span><b>{{ number_format($remaining['subtotal'], 2) }}</b></div>
                            <div class="sum-line" style="border-top:1px dashed #e5e7eb;margin-top:.3rem;padding-top:.7rem"><span>{{ __('transport.total_before_tax') }}</span><b id="s-sub">0.00</b></div>
                            <div class="sum-line"><span>{{ __('transport.vat_label', ['rate' => rtrim(rtrim(number_format($rate * 100, 2), '0'), '.')]) }}</span><b id="s-tax">0.00</b></div>
                            <div class="sum-grand"><span>{{ __('transport.cn_total') }}</span><b id="s-total">0.00</b></div>
                        </div>
                        <label class="flex items-start gap-2 text-xs text-gray-700" id="release-wrap" style="display:none">
                            <input type="checkbox" name="release_loads" value="1" style="margin-top:2px" @checked(old('release_loads', true))>
                            <span>{{ __('transport.cn_release_loads') }}</span>
                        </label>
                        <button type="submit" class="tv-btn tv-btn-lg tv-btn-block" style="background:#9f1239;color:#fff">{{ __('transport.cn_save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const rate = {{ $rate }};
            const remaining = {{ $remaining['subtotal'] }};
            const inputs = () => Array.from(document.querySelectorAll('.cn-amt:not([disabled])'));
            const fmt = n => (Math.round(n * 100) / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            function recalc() {
                let sub = 0;
                inputs().forEach(i => {
                    let v = parseFloat(i.value) || 0;
                    const mx = parseFloat(i.dataset.max) || 0;
                    if (v > mx) { v = mx; i.value = mx; }
                    sub += v;
                });
                sub = Math.round(sub * 100) / 100;
                const tax = Math.round(sub * rate * 100) / 100;
                document.getElementById('s-sub').textContent = fmt(sub);
                document.getElementById('s-tax').textContent = fmt(tax);
                document.getElementById('s-total').textContent = fmt(sub + tax);
                document.getElementById('release-wrap').style.display = (sub > 0 && Math.abs(sub - remaining) < 0.01) ? '' : 'none';
            }
            document.getElementById('cn-full').addEventListener('click', () => { inputs().forEach(i => i.value = i.dataset.max); recalc(); });
            document.getElementById('cn-form').addEventListener('input', recalc);
            recalc();
        })();
    </script>
</x-app-layout>
