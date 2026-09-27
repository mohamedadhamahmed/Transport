<x-app-layout>
    @include('transport.partials.styles')
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('transport.partials.page-header', [
                'title' => __('transport.trucks'),
                'subtitle' => __('transport.trucks_subtitle'),
                'action' => ['url' => route('transport.trucks.create'), 'label' => __('transport.new_truck'), 'can' => 'trucks.create'],
            ])

            @include('transport.partials.flash')

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('transport.search_truck') }}"
                           class="tr-input" style="max-width: 320px">
                    <select name="status" class="tr-input" style="max-width: 180px">
                        <option value="">{{ __('transport.all_statuses') }}</option>
                        @foreach (['active', 'maintenance', 'inactive'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('transport.status_' . $st) }}</option>
                        @endforeach
                    </select>
                    <select name="docs" class="tr-input" style="max-width: 220px">
                        <option value="">{{ __('transport.docs_all') }}</option>
                        <option value="alert" @selected(request('docs') === 'alert')>⚠️ {{ __('transport.docs_alert_any') }}</option>
                        @foreach (\App\Models\Truck::DOCUMENTS as $col => $key)
                            <option value="{{ $col }}" @selected(request('docs') === $col)>{{ __('transport.' . $key) }}</option>
                        @endforeach
                    </select>
                    <label class="inline-flex items-center gap-1.5 text-sm text-gray-600"><input type="checkbox" name="expired" value="1" @checked(request()->boolean('expired'))> {{ __('transport.docs_expired_only') }}</label>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.plate_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.truck_name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.truck_type') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.driver') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.default_trip_price') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.trips_count') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.trips_revenue') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.truck_documents') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold">{{ __('transport.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($trucks as $truck)
                                @php
                                    $allDocs = collect($truck->documentsStatus());
                                    $docs = $allDocs->filter(fn ($d) => in_array($d['status'], ['expired', 'soon'], true));
                                    $hasDocDates = $allDocs->contains(fn ($d) => $d['status'] === 'ok');
                                    $stClass = ['active' => 'tr-badge-green', 'maintenance' => 'tr-badge-amber', 'inactive' => 'tr-badge-gray'][$truck->status] ?? 'tr-badge-gray';
                                @endphp
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-bold text-gray-800">{{ $truck->plate_number }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $truck->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $truck->type ?? '-' }}
                                        <div style="margin-top:.2rem">
                                            @if ($truck->isOwned())
                                                <span class="tr-badge tr-badge-green">🏢 {{ __('transport.owned') }}@if ($truck->purchase_value) · {{ number_format((float) $truck->purchase_value, 0) }}@endif</span>
                                            @else
                                                <span class="tr-badge tr-badge-amber">🤝 {{ $truck->owner_name ?: __('transport.external_truck') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600" style="min-width:210px">@include('transport.partials.driver-contact', ['driver' => $truck->driver, 'compact' => true])</td>
                                    <td class="px-4 py-3 text-gray-600">{{ number_format((float) $truck->default_trip_price, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $truck->trips_count }}</td>
                                    <td class="px-4 py-3 font-medium text-[#0F1B4C]">{{ number_format((float) $truck->trips_sum_line_total, 2) }}</td>
                                    <td class="px-4 py-3" style="min-width:170px">
                                        @forelse ($docs as $d)
                                            <div style="margin-bottom:.2rem">
                                                <span class="tr-badge {{ $d['status'] === 'expired' ? 'tr-badge-red' : 'tr-badge-amber' }}" title="{{ $d['date']->format('Y-m-d') }}">
                                                    {{ $d['label'] }}:
                                                    {{ $d['status'] === 'expired' ? __('transport.doc_expired_since', ['days' => abs($d['days'])]) : __('transport.doc_expires_in', ['days' => $d['days']]) }}
                                                </span>
                                            </div>
                                        @empty
                                            @if ($hasDocDates)
                                                <span class="tr-badge tr-badge-green">✓ {{ __('transport.docs_ok') }}</span>
                                            @elseif (!$truck->isOwned())
                                                <span class="tr-badge tr-badge-gray" title="{{ __('transport.docs_optional_external_hint') }}">{{ __('transport.docs_optional_external') }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3"><span class="tr-badge {{ $stClass }}">{{ __('transport.status_' . $truck->status) }}</span></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @can('transport_invoices.view')
                                                <a href="{{ route('transport.invoices.index', ['truck_id' => $truck->id]) }}" class="tr-btn tr-btn-gray">{{ __('transport.trips') }}</a>
                                            @endcan
                                            @can('trucks.edit')
                                                <a href="{{ route('transport.trucks.edit', $truck) }}" class="tr-btn tr-btn-blue">{{ __('transport.edit') }}</a>
                                            @endcan
                                            @can('trucks.delete')
                                                <form method="POST" action="{{ route('transport.trucks.destroy', $truck) }}" onsubmit="return confirm('{{ __('transport.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="tr-btn tr-btn-red">{{ __('transport.delete') }}</button>
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

                @if ($trucks->hasPages())
                    <div class="mt-4">{{ $trucks->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
