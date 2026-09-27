<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', ['title' => '🏛 ' . __('transport.zatca_title'), 'subtitle' => __('transport.zatca_subtitle')])
            @include('transport.partials.flash')

            <div class="flex gap-2 flex-wrap items-center justify-between">
                <div class="flex gap-2">
                    <a href="{{ route('transport.zatca.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ !$sent ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200' }}">{{ __('transport.zatca_not_sent') }} ({{ $notSentCount }})</a>
                    <a href="{{ route('transport.zatca.index', ['sent' => 1]) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $sent ? 'bg-[#0F1B4C] text-white' : 'bg-white text-gray-600 border border-gray-200' }}">{{ __('transport.zatca_sent') }} ({{ $sentCount }})</a>
                </div>
                @if (!$sent && $notSentCount > 0)
                    @can('zatca.send')
                        <form method="POST" action="{{ route('transport.zatca.send-all') }}" onsubmit="return confirm('{{ __('transport.confirm_zatca_send_all') }}')">
                            @csrf
                            <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6]">🏛 {{ __('transport.zatca_send_all') }}</button>
                        </form>
                    @endcan
                @endif
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4 items-end">
                    <input type="hidden" name="sent" value="{{ $sent ? 1 : 0 }}">
                    <div><label class="tr-label">{{ __('transport.date_from') }}</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="tr-input"></div>
                    <div><label class="tr-label">{{ __('transport.date_to') }}</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="tr-input"></div>
                    @if (!$sent)
                        <div>
                            <label class="tr-label">{{ __('transport.status') }}</label>
                            <select name="status" class="tr-input">
                                <option value="">{{ __('transport.all') }}</option>
                                <option value="FAIL" @selected(request('status') === 'FAIL')>{{ __('transport.zatca_failed') }}</option>
                            </select>
                        </div>
                    @endif
                    <button class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.invoice_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.invoice_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.customer') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.zatca_doc_type') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($invoices as $inv)
                                <tr>
                                    <td class="px-4 py-3 font-bold text-[#0F1B4C]">{{ $inv->invoice_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $inv->issue_date?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ $inv->customer?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ strlen((string) $inv->customer?->tax_number) === 15 ? __('transport.zatca_standard') : __('transport.zatca_simplified') }}</td>
                                    <td class="px-4 py-3 font-bold">{{ number_format((float) $inv->total, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($inv->is_sent_to_zatca)
                                            <span class="tr-badge tr-badge-green">✓ {{ __('transport.zatca_sent') }}</span>
                                            <div class="text-xs text-gray-500">{{ $inv->zatca_signed_at?->format('Y-m-d H:i') }}</div>
                                        @elseif ($inv->zatca_status === 'FAIL')
                                            <span class="tr-badge tr-badge-red">✗ {{ __('transport.zatca_failed') }}</span>
                                            <div class="text-xs" style="color:#be123c;max-width:360px">{{ \Illuminate\Support\Str::limit($inv->zatca_message, 200) }}</div>
                                        @else
                                            <span class="tr-badge tr-badge-gray">{{ __('transport.zatca_not_sent') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="{{ route('transport.invoices.show', $inv) }}" class="tr-btn tr-btn-gray">{{ __('transport.print') }}</a>
                                            @if ($inv->is_sent_to_zatca)
                                                <a href="{{ route('transport.zatca.xml', $inv) }}" class="tr-btn tr-btn-blue">XML</a>
                                            @else
                                                @can('zatca.send')
                                                    <form method="POST" action="{{ route('transport.zatca.send', $inv) }}">
                                                        @csrf
                                                        <button class="tr-btn tr-btn-green">🏛 {{ __('transport.send_to_zatca') }}</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($invoices->hasPages())<div class="mt-4">{{ $invoices->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
