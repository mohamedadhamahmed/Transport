<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.cn_list'),
            'icon' => 'file',
            'crumbs' => [['label' => __('transport.invoices'), 'url' => route('transport.invoices.index')], ['label' => __('transport.cn_list')]],
            'buttons' => [['label' => __('transport.invoices'), 'url' => route('transport.invoices.index'), 'style' => 'gray', 'icon' => 'list']],
        ])
        @include('transport.partials.flash')

        <div class="tv-alert-info">
            <span class="i">i</span>
            <div>{{ __('transport.cn_index_hint') }}</div>
        </div>

        <form method="GET" class="tv-card tv-pad tv-no-print">
            <div class="tv-filters" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr))">
                <div>
                    <label class="tv-label">{{ __('transport.search') }}</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="tv-input" placeholder="{{ __('transport.cn_search_hint') }}">
                </div>
                <div>
                    <label class="tv-label">ZATCA</label>
                    <select name="zatca" class="tv-input">
                        <option value="">{{ __('transport.all') }}</option>
                        <option value="pending" @selected(request('zatca') === 'pending')>{{ __('transport.status_zatca_pending') }}</option>
                        <option value="sent" @selected(request('zatca') === 'sent')>{{ __('transport.status_zatca_sent') }}</option>
                    </select>
                </div>
                <div style="align-self:end"><button class="tv-btn tv-btn-blue tv-btn-lg">{{ __('transport.show') }}</button></div>
            </div>
        </form>

        <div class="tv-kpis tv-kpis-4">
            <div class="tv-kpi"><div class="l">{{ __('transport.cn_list') }}</div><div class="v">{{ number_format($totals->c ?? 0) }}</div></div>
            <div class="tv-kpi"><div class="l">{{ __('transport.before_tax') }}</div><div class="v">{{ number_format((float) ($totals->s ?? 0), 2) }}</div></div>
            <div class="tv-kpi"><div class="l">{{ __('transport.vat') }}</div><div class="v">{{ number_format((float) ($totals->t ?? 0), 2) }}</div></div>
            <div class="tv-kpi"><div class="l">{{ __('transport.status_zatca_pending') }}</div><div class="v" style="color:{{ ($totals->p ?? 0) ? '#be123c' : '#0F1B4C' }}">{{ number_format($totals->p ?? 0) }}</div></div>
        </div>

        <div class="tv-card tv-pad">
            <div class="tv-table-wrap">
                <table class="tv-table">
                    <thead><tr>
                        <th>{{ __('transport.cn_number') }}</th><th>{{ __('transport.date') }}</th><th>{{ __('transport.cn_original_invoice') }}</th>
                        <th>{{ __('transport.customer') }}</th><th>{{ __('transport.cn_reason') }}</th><th>{{ __('transport.before_tax') }}</th>
                        <th>{{ __('transport.tax_amount') }}</th><th>{{ __('transport.grand_total') }}</th><th>ZATCA</th><th class="tv-no-print"></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($notes as $n)
                            <tr>
                                <td style="font-weight:800"><a href="{{ route('transport.credit-notes.show', $n) }}" style="color:#9f1239">{{ $n->credit_note_number }}</a></td>
                                <td>{{ $n->issue_date?->format('Y-m-d') }}</td>
                                <td><a href="{{ route('transport.invoices.show', $n->transport_invoice_id) }}" class="hover:underline">{{ $n->invoice?->invoice_number }}</a></td>
                                <td>{{ $n->customer?->name ?? '-' }}</td>
                                <td style="max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $n->reason }}">{{ $n->reason }}</td>
                                <td>{{ number_format((float) $n->subtotal, 2) }}</td>
                                <td>{{ number_format((float) $n->tax_amount, 2) }}</td>
                                <td style="font-weight:800">{{ number_format((float) $n->total, 2) }}</td>
                                <td>
                                    @if ($n->is_sent_to_zatca)
                                        <span class="tv-pill tv-pill-green">✓ {{ __('transport.zatca_sent') }}</span>
                                    @elseif ($n->zatca_status === 'FAIL')
                                        <span class="tv-pill tv-pill-red" title="{{ $n->zatca_message }}">✗ {{ __('transport.zatca_failed') }}</span>
                                    @else
                                        <span class="tv-pill tv-pill-gray">{{ __('transport.status_zatca_pending') }}</span>
                                    @endif
                                </td>
                                <td class="tv-no-print">
                                    <div class="flex gap-1.5">
                                        <a href="{{ route('transport.credit-notes.show', $n) }}" class="tr-btn tr-btn-green">{{ __('transport.print') }}</a>
                                        @if (!$n->is_sent_to_zatca)
                                            @can('zatca.send')
                                                <form method="POST" action="{{ route('transport.credit-notes.zatca', $n) }}" onsubmit="return confirm('{{ __('transport.cn_confirm_zatca') }}')">
                                                    @csrf
                                                    <button class="tr-btn tr-btn-gray">🏛 {{ __('transport.send_to_zatca') }}</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="tv-empty">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($notes->hasPages())<div class="mt-4">{{ $notes->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
