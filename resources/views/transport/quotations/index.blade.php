<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', [
                'title' => __('transport.quotations'),
                'subtitle' => __('transport.quotations_subtitle'),
                'action' => ['url' => route('transport.quotations.create'), 'label' => __('transport.new_quotation'), 'can' => 'transport_quotations.create'],
            ])
            @include('transport.partials.flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="tr-grid tr-grid-4 mb-4" style="align-items:end">
                    <div><label class="tr-label">{{ __('transport.quotation_number') }}</label><input type="text" name="number" value="{{ request('number') }}" class="tr-input"></div>
                    <div>
                        <label class="tr-label">{{ __('transport.customer') }}</label>
                        <select name="customer_id" class="tr-input">
                            <option value="">{{ __('transport.all') }}</option>
                            @foreach ($customers as $id => $name)<option value="{{ $id }}" @selected((string) request('customer_id') === (string) $id)>{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.status') }}</label>
                        <select name="status" class="tr-input">
                            <option value="">{{ __('transport.all') }}</option>
                            @foreach (\App\Models\TransportQuotation::STATUSES as $st)<option value="{{ $st }}" @selected(request('status') === $st)>{{ __('transport.q_' . $st) }}</option>@endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
                        <a href="{{ route('transport.quotations.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm">{{ __('transport.reset') }}</a>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.quotation_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.quotation_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.customer') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.routes') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.grand_total') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.valid_until') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($quotations as $q)
                                @php $stc = ['draft' => 'tr-badge-gray', 'sent' => 'tr-badge-amber', 'accepted' => 'tr-badge-green', 'rejected' => 'tr-badge-red', 'converted' => 'tr-badge-green'][$q->status] ?? 'tr-badge-gray'; @endphp
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-bold text-[#0F1B4C]">{{ $q->quotation_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $q->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $q->customer?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $q->items_count }}</td>
                                    <td class="px-4 py-3 font-bold">{{ number_format((float) $q->total, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @if ($q->valid_until)
                                            <span class="tr-badge {{ $q->isExpired() ? 'tr-badge-red' : 'tr-badge-gray' }}">{{ $q->valid_until->format('Y-m-d') }}</span>
                                        @else - @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="tr-badge {{ $stc }}">{{ __('transport.q_' . $q->status) }}</span>
                                        @if ($q->invoice)<div class="text-xs text-gray-500 mt-1">{{ $q->invoice->invoice_number }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('transport.quotations.show', $q) }}" class="tr-btn tr-btn-green">{{ __('transport.view_print') }}</a>
                                            @if ($q->status !== 'converted')
                                                @can('transport_invoices.create')
                                                    <a href="{{ route('transport.quotations.convert', $q) }}" class="tr-btn tr-btn-blue">🧾 {{ __('transport.convert_to_invoice') }}</a>
                                                @endcan
                                                @can('transport_quotations.edit')
                                                    <a href="{{ route('transport.quotations.edit', $q) }}" class="tr-btn tr-btn-gray">{{ __('transport.edit') }}</a>
                                                @endcan
                                            @endif
                                            @can('transport_quotations.delete')
                                                <form method="POST" action="{{ route('transport.quotations.destroy', $q) }}" onsubmit="return confirm('{{ __('transport.confirm_delete') }}')">
                                                    @csrf @method('DELETE')
                                                    <button class="tr-btn tr-btn-red">{{ __('transport.delete') }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($quotations->hasPages())<div class="mt-4">{{ $quotations->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
