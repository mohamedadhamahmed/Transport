<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.unbilled_loads'),
            'crumbs' => [['label' => __('transport.unbilled_loads')]],
            'buttons' => [
                ['label' => __('transport.export_excel'), 'url' => request()->fullUrlWithQuery(['export' => 'excel']), 'style' => 'green', 'icon' => 'excel'],
                ['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print'],
            ],
        ])

        @include('transport.partials.report-tabs', ['active' => 'unbilled'])

        @include('transport.partials.flash')

        {{-- الفلاتر --}}
        <form method="GET" class="tv-card tv-pad tv-no-print">
            <div class="tv-filters tv-filters-5">
                <div>
                    <label class="tv-label">{{ __('transport.date_from') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="tv-input">
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.date_to') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="tv-input">
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.customer') }}</label>
                    <select name="customer_id" class="tv-input tv-select">
                        <option value="">{{ __('transport.all') }}</option>
                        @foreach ($customers as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('customer_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.status') }}</label>
                    <select name="status" class="tv-input">
                        <option value="unloaded" @selected($status === 'unloaded')>{{ __('transport.unbilled_status_unloaded') }}</option>
                        <option value="loaded" @selected($status === 'loaded')>{{ __('transport.unbilled_status_loaded') }}</option>
                        <option value="all" @selected($status === 'all')>{{ __('transport.all') }}</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="tv-btn tv-btn-blue tv-btn-lg" style="min-width:110px">{{ __('transport.show') }}</button>
                </div>
            </div>
        </form>

        {{-- الإحصائيات --}}
        <div class="tv-kpis tv-kpis-4">
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg>{{ __('transport.unbilled_count') }}</div>
                <div class="v">{{ number_format($summary['count']) }}</div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>{{ __('transport.unbilled_value') }}</div>
                <div class="v">{{ number_format($summary['value'], 2) }} <small>{{ __('transport.sar') }}</small></div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#1456E8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></svg>{{ __('transport.customers') }}</div>
                <div class="v">{{ number_format($summary['customers']) }}</div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 2 21h20L12 3z"/><path d="M12 10v4M12 17.5h.01"/></svg>{{ __('transport.without_price') }}</div>
                <div class="v">{{ number_format($summary['no_price']) }}</div>
            </div>
        </div>

        {{-- الأحمال مجمّعة بالعميل --}}
        @if ($groups->isEmpty())
            <div class="tv-card tv-empty">
                <div class="tv-empty-ok">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9.5"/><path d="m8 12.5 2.8 2.8L16.5 9.5"/></svg>
                    {{ __('transport.no_unbilled_loads') }}
                </div>
            </div>
        @else
            @foreach ($groups as $customerId => $rows)
                @php
                    $cust = $rows->first()->customer;
                    $groupValue = $rows->sum(fn ($l) => $l->billing_price ?? 0);
                @endphp
                <div class="tv-card tv-pad">
                    <div class="flex items-center justify-between gap-3 flex-wrap" style="margin-bottom:.9rem">
                        <div class="tv-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            {{ $cust?->name ?? __('transport.no_customer') }}
                            <span class="tv-count">{{ $rows->count() }}</span>
                            <span class="tv-pill tv-pill-blue">{{ number_format($groupValue, 2) }} {{ __('transport.sar') }}</span>
                        </div>
                        @if ($customerId)
                            @can('transport_invoices.create')
                                <a href="{{ route('transport.invoices.create', array_filter([
                                    'customer_id' => $customerId,
                                    'date_from' => request('date_from'),
                                    'date_to' => request('date_to'),
                                    'include_loaded' => $status !== 'unloaded' ? 1 : null,
                                ])) }}" class="tv-btn tv-btn-green tv-no-print">🧾 {{ __('transport.bill_customer_loads') }}</a>
                            @endcan
                        @else
                            <span class="tv-hint" style="margin:0">{{ __('transport.no_customer_hint') }}</span>
                        @endif
                    </div>
                    <div class="tv-table-wrap">
                        <table class="tv-table">
                            <thead>
                                <tr>
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
                                @foreach ($rows as $l)
                                    <tr>
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
                                        <td style="font-weight:700">
                                            @if ($l->billing_price !== null)
                                                {{ number_format($l->billing_price, 2) }}
                                            @else
                                                <span class="tv-pill tv-pill-red">{{ __('transport.without_price') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @include('transport.partials.tv-select-script')
</x-app-layout>
