<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('transport.partials.page-header', [
                'title' => __('transport.waybills'),
                'subtitle' => __('transport.waybills_subtitle'),
                'action' => ['url' => route('transport.waybills.create'), 'label' => __('transport.new_waybill'), 'can' => 'waybills.create'],
            ])
            @include('transport.partials.flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="tr-grid tr-grid-4 mb-4" style="align-items:end">
                    <div><label class="tr-label">{{ __('transport.waybill_number') }}</label><input type="text" name="number" value="{{ request('number') }}" class="tr-input"></div>
                    <div><label class="tr-label">{{ __('transport.search') }}</label><input type="text" name="search" value="{{ request('search') }}" class="tr-input" placeholder="{{ __('transport.waybill_search_hint') }}"></div>
                    <div>
                        <label class="tr-label">{{ __('transport.truck') }}</label>
                        <select name="truck_id" class="tr-input">
                            <option value="">{{ __('transport.all') }}</option>
                            @foreach ($trucks as $t)<option value="{{ $t->id }}" @selected((string) request('truck_id') === (string) $t->id)>{{ $t->display_name }}</option>@endforeach
                        </select>
                    </div>
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
                            @foreach (['open', 'delivered', 'cancelled'] as $st)<option value="{{ $st }}" @selected(request('status') === $st)>{{ __('transport.wb_' . $st) }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="tr-label">{{ __('transport.date_from') }}</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="tr-input"></div>
                    <div><label class="tr-label">{{ __('transport.date_to') }}</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="tr-input"></div>
                    <div class="flex gap-2">
                        <button class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
                        <a href="{{ route('transport.waybills.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm">{{ __('transport.reset') }}</a>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.waybill_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.waybill_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.truck') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.driver') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.from') }} ← {{ __('transport.to') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.shipper') }} / {{ __('transport.consignee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.goods_description') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.freight_amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($waybills as $w)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-bold text-[#0F1B4C]">{{ $w->waybill_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $w->issue_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $w->truck?->plate_number }}</td>
                                    <td class="px-4 py-3" style="min-width:190px">@include('transport.partials.driver-contact', ['driver' => $w->driver ?? $w->truck?->driver, 'compact' => true, 'msg' => 'السلام عليكم، بخصوص البوليصة ' . $w->waybill_number])</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $w->from_label }} ← {{ $w->to_label }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $w->shipper_name }}<br><span class="text-xs">{{ $w->consignee_name }}</span></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $w->goods_description }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $w->freight_amount, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="tr-badge {{ ['open' => 'tr-badge-amber', 'delivered' => 'tr-badge-green', 'cancelled' => 'tr-badge-gray'][$w->status] }}">{{ __('transport.wb_' . $w->status) }}</span>
                                        @if ($w->invoice)<div class="text-xs text-gray-500 mt-1">🧾 {{ $w->invoice->invoice_number }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('transport.waybills.show', $w) }}" class="tr-btn tr-btn-green">{{ __('transport.view_print') }}</a>
                                            @if ($w->status === 'open')
                                                @can('waybills.edit')
                                                    <a href="{{ route('transport.waybills.edit', $w) }}" class="tr-btn tr-btn-blue">{{ __('transport.edit') }}</a>
                                                @endcan
                                            @endif
                                            @can('waybills.delete')
                                                <form method="POST" action="{{ route('transport.waybills.destroy', $w) }}" onsubmit="return confirm('{{ __('transport.confirm_delete') }}')">
                                                    @csrf @method('DELETE')
                                                    <button class="tr-btn tr-btn-red">{{ __('transport.delete') }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-10 text-center text-gray-400">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($waybills->hasPages())<div class="mt-4">{{ $waybills->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
