<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')

    @php
        $status = request('status', '');
        $netTotal = (float) ($totals->subtotal ?? 0);
        $taxTotal = (float) ($totals->tax_amount ?? 0);
        $grandTotal = (float) ($totals->total ?? 0);
    @endphp

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.tax_transport_invoices'),
            'crumbs' => [['label' => __('transport.tax_transport_invoices')]],
            'buttons' => [
                ['label' => __('transport.new_invoice'), 'url' => route('transport.invoices.create'), 'style' => 'green', 'icon' => 'plus', 'can' => 'transport_invoices.create'],
                ['label' => __('transport.unbilled_loads'), 'url' => route('transport.reports.unbilled'), 'style' => 'outline', 'icon' => 'file'],
                ['label' => __('transport.zatca_title'), 'url' => route('transport.zatca.index'), 'style' => 'outline', 'icon' => 'chart', 'can' => 'zatca.view'],
                ['label' => __('transport.print'), 'onclick' => 'window.print()', 'style' => 'blue', 'icon' => 'print'],
            ],
        ])

        @include('transport.partials.flash')

        {{-- الفلاتر --}}
        <form method="GET" class="tv-card tv-pad tv-no-print">
            <div class="tv-filters tv-filters-6">
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
                        <option value="" @selected($status === '')>{{ __('transport.status_approved') }}</option>
                        <option value="zatca_pending" @selected($status === 'zatca_pending')>{{ __('transport.status_zatca_pending') }}</option>
                        <option value="zatca_sent" @selected($status === 'zatca_sent')>{{ __('transport.status_zatca_sent') }}</option>
                        <option value="draft" @selected($status === 'draft')>{{ __('transport.drafts') }} ({{ $draftsCount }})</option>
                        <option value="all" @selected($status === 'all')>{{ __('transport.all') }}</option>
                    </select>
                </div>
                <div>
                    <label class="tv-label">{{ __('transport.search_invoice_hint') }}</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="tv-input">
                </div>
                <div>
                    <button type="submit" class="tv-btn tv-btn-blue tv-btn-lg" style="min-width:100px">{{ __('transport.show') }}</button>
                </div>
            </div>
        </form>

        {{-- الإحصائيات --}}
        <div class="tv-kpis tv-kpis-5">
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#1456E8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>{{ $status === 'draft' ? __('transport.drafts') : __('transport.active_invoices') }}</div>
                <div class="v">{{ number_format($totals->invoices_count ?? 0) }}</div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>{{ __('transport.net_before_tax') }}</div>
                <div class="v">{{ number_format($netTotal, 2) }} <small>{{ __('transport.sar') }}</small></div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>{{ __('transport.vat') }}</div>
                <div class="v">{{ number_format($taxTotal, 2) }} <small>{{ __('transport.sar') }}</small></div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>{{ __('transport.grand_total_incl') }}</div>
                <div class="v">{{ number_format($grandTotal, 2) }} <small>{{ __('transport.sar') }}</small></div>
            </div>
            <div class="tv-kpi">
                <div class="l"><svg viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg>{{ __('transport.drafts') }}</div>
                <div class="v">{{ number_format($draftsCount) }}</div>
            </div>
        </div>

        {{-- الجدول --}}
        <div class="tv-card tv-pad">
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead>
                        <tr>
                            <th>{{ __('transport.invoice_number') }}</th>
                            <th>{{ __('transport.date') }}</th>
                            <th>{{ __('transport.customer') }}</th>
                            <th>{{ __('transport.vat_number') }}</th>
                            <th>{{ __('transport.type') }}</th>
                            <th>{{ __('transport.before_tax') }}</th>
                            <th>{{ __('transport.tax_amount') }}</th>
                            <th>{{ __('transport.grand_total') }}</th>
                            <th>{{ __('transport.status') }}</th>
                            <th class="tv-no-print">{{ __('transport.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td style="font-weight:800;color:#0F1B4C">
                                    @if ($inv->is_draft)
                                        {{ __('transport.draft') }} #{{ $inv->id }}
                                    @else
                                        <a href="{{ route('transport.invoices.show', $inv) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                                    @endif
                                </td>
                                <td>{{ $inv->issue_date?->format('Y-m-d') }}</td>
                                <td>{{ $inv->customer?->name ?? '-' }}</td>
                                <td style="font-family:monospace">{{ $inv->customer?->tax_number ?: '-' }}</td>
                                <td>
                                    @if ($inv->isSimplified())
                                        <span class="tv-pill tv-pill-gray">{{ __('transport.simplified') }}</span>
                                    @else
                                        <span class="tv-pill tv-pill-blue">{{ __('transport.tax_invoice_short') }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format((float) $inv->subtotal, 2) }}</td>
                                <td>{{ number_format((float) $inv->tax_amount, 2) }}</td>
                                <td style="font-weight:800">{{ number_format((float) $inv->total, 2) }}</td>
                                <td>
                                    @if ($inv->is_draft)
                                        <span class="tv-pill tv-pill-amber">{{ __('transport.draft') }}</span>
                                    @elseif ($inv->is_sent_to_zatca)
                                        <span class="tv-pill tv-pill-green">✓ {{ __('transport.zatca_sent') }}</span>
                                    @elseif ($inv->zatca_status === 'FAIL')
                                        <span class="tv-pill tv-pill-red">✗ {{ __('transport.zatca_failed') }}</span>
                                    @else
                                        <span class="tv-pill tv-pill-blue">{{ __('transport.status_active') }}</span>
                                    @endif
                                </td>
                                <td class="tv-no-print">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if ($inv->is_draft)
                                            @can('transport_invoices.edit')
                                                <a href="{{ route('transport.invoices.edit', $inv) }}" class="tr-btn tr-btn-blue">{{ __('transport.open_draft') }}</a>
                                            @endcan
                                            @can('transport_invoices.create')
                                                <form method="POST" action="{{ route('transport.invoices.approve', $inv) }}" onsubmit="return confirm('{{ __('transport.confirm_convert_draft') }}')">
                                                    @csrf
                                                    <button class="tr-btn tr-btn-green">{{ __('transport.convert_draft_to_invoice') }}</button>
                                                </form>
                                            @endcan
                                        @else
                                            <a href="{{ route('transport.invoices.show', $inv) }}" class="tr-btn tr-btn-green">{{ __('transport.print') }}</a>
                                            @if ($inv->isEditable())
                                                @can('transport_invoices.edit')
                                                    <a href="{{ route('transport.invoices.edit', $inv) }}" class="tr-btn tr-btn-blue">{{ __('transport.edit') }}</a>
                                                @endcan
                                            @endif
                                            @can('transport_invoices.edit')
                                                <a href="{{ route('transport.credit-notes.create', $inv) }}" class="tr-btn tr-btn-red" title="{{ __('transport.cn_new') }}">↩ {{ __('transport.cn_short') }}</a>
                                            @endcan
                                            @if (!$inv->is_sent_to_zatca)
                                                @can('zatca.send')
                                                    <form method="POST" action="{{ route('transport.zatca.send', $inv) }}" onsubmit="return confirm('{{ __('transport.confirm_zatca_send') }}')">
                                                        @csrf
                                                        <button class="tr-btn tr-btn-gray">{{ __('transport.send_to_zatca') }}</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        @endif
                                        @if ($inv->isEditable())
                                            @can('transport_invoices.delete')
                                                <form method="POST" action="{{ route('transport.invoices.destroy', $inv) }}" onsubmit="return confirm('{{ __('transport.confirm_delete_invoice') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="tr-btn tr-btn-red">{{ __('transport.delete') }}</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="tv-empty">{{ __('transport.no_invoices_in_period') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if ($invoices->count())
                        <tfoot>
                            <tr>
                                <td colspan="5">{{ __('transport.period_total') }}</td>
                                <td>{{ number_format($netTotal, 2) }}</td>
                                <td>{{ number_format($taxTotal, 2) }}</td>
                                <td>{{ number_format($grandTotal, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="mt-4 tv-no-print">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>

    @include('transport.partials.tv-select-script')
</x-app-layout>
