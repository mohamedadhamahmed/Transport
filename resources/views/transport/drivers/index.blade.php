<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .dr-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
        @media (min-width: 700px) { .dr-grid { grid-template-columns:repeat(2,1fr); } }
        @media (min-width: 1150px) { .dr-grid { grid-template-columns:repeat(3,1fr); } }
        @media (min-width: 1550px) { .dr-grid { grid-template-columns:repeat(4,1fr); } }
        .dr-card { background:#fff; border:1px solid #e5e7eb; border-radius:.9rem; padding:1rem; display:flex; flex-direction:column; gap:.7rem; }
        .dr-card.off { opacity:.65; }
        .dr-top { display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; }
        .dr-name { font-weight:800; color:#0F1B4C; font-size:1.05rem; }
        .dr-phone { display:block; font-size:1.5rem; font-weight:800; color:#0F1B4C; letter-spacing:1px; text-decoration:none; direction:ltr; text-align:center; background:#f5f7ff; border-radius:.6rem; padding:.45rem; }
        .dr-phone:hover { color:#1456E8; }
        .dr-nophone { display:block; text-align:center; background:#fff1f2; color:#be123c; font-weight:700; font-size:.85rem; border-radius:.6rem; padding:.6rem; }
        .dr-btns { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
        .dr-btn { display:flex; align-items:center; justify-content:center; gap:.4rem; padding:.6rem; border-radius:.6rem; font-weight:800; font-size:.9rem; text-decoration:none; }
        .dr-call { background:#1456E8; color:#fff; }
        .dr-wa { background:#16a34a; color:#fff; }
        .dr-trip { font-size:.8rem; border-radius:.5rem; padding:.45rem .6rem; }
        .dr-trip.busy { background:#fff7ed; color:#c2410c; }
        .dr-trip.free { background:#ecfdf5; color:#047857; }
        .dr-meta { font-size:.75rem; color:#6b7280; display:flex; flex-wrap:wrap; gap:.3rem .9rem; }
        .dr-foot { display:flex; gap:.4rem; border-top:1px dashed #e5e7eb; padding-top:.6rem; }
        .dr-foot form { display:inline; }
    </style>

    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('transport.partials.page-header', [
                'title' => __('transport.drivers'),
                'subtitle' => __('transport.drivers_subtitle'),
                'action' => ['url' => route('transport.drivers.create'), 'label' => __('transport.new_driver'), 'can' => 'drivers.create'],
            ])

            @include('transport.partials.flash')

            <form method="GET" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4 flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('transport.search_driver') }}" class="tr-input" style="max-width:340px">
                <select name="driver_type" class="tr-input" style="max-width:180px">
                    <option value="">{{ __('transport.all') }}</option>
                    <option value="company" @selected(request('driver_type') === 'company')>{{ __('transport.company_driver') }}</option>
                    <option value="external" @selected(request('driver_type') === 'external')>{{ __('transport.external_driver') }}</option>
                </select>
                <select name="status" class="tr-input" style="max-width:180px">
                    <option value="">{{ __('transport.all_statuses') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('transport.status_active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('transport.status_inactive') }}</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
            </form>

            <div class="dr-grid">
                @forelse ($drivers as $driver)
                    @php $load = $driver->activeLoad; @endphp
                    <div class="dr-card {{ $driver->status === 'inactive' ? 'off' : '' }}">
                        <div class="dr-top">
                            <div>
                                <div class="dr-name">👤 {{ $driver->name }}</div>
                                <div style="margin-top:.2rem">
                                    @if ($driver->isExternal())
                                        <span class="tr-badge tr-badge-amber">🤝 {{ __('transport.external_driver') }}</span>
                                    @else
                                        <span class="tr-badge tr-badge-green">🏢 {{ __('transport.company_driver') }}@if ($driver->employee) · {{ $driver->employee->employee_number }}@endif</span>
                                    @endif
                                </div>
                                <div class="dr-meta">
                                    @if ($driver->nationality)<span>{{ $driver->nationality }}</span>@endif
                                    @if ($driver->trucks->isNotEmpty())<span>🚚 {{ $driver->trucks->pluck('plate_number')->implode('، ') }}</span>@endif
                                </div>
                            </div>
                            <span class="tr-badge {{ $driver->status === 'active' ? 'tr-badge-green' : 'tr-badge-gray' }}">{{ __('transport.status_' . $driver->status) }}</span>
                        </div>

                        @if ($driver->hasValidPhone())
                            <a href="{{ $driver->telUrl() }}" class="dr-phone">{{ $driver->phone }}</a>
                            <div class="dr-btns">
                                <a href="{{ $driver->telUrl() }}" class="dr-btn dr-call">📞 {{ __('transport.call') }}</a>
                                <a href="{{ $driver->whatsappUrl('السلام عليكم ' . $driver->name) }}" target="_blank" rel="noopener" class="dr-btn dr-wa">💬 واتساب</a>
                            </div>
                        @else
                            <span class="dr-nophone">{{ __('transport.no_phone_edit') }}</span>
                        @endif

                        @if ($load)
                            <div class="dr-trip busy">🟠 {{ __('transport.on_trip') }}: <b>{{ $load->truck?->plate_number }}</b> · {{ $load->from_label }} ← {{ $load->to_label }}</div>
                        @else
                            <div class="dr-trip free">🟢 {{ __('transport.available') }}</div>
                        @endif

                        <div class="dr-meta">
                            @if ($driver->id_number)<span>🪪 {{ $driver->id_number }}</span>@endif
                            @if ($driver->license_expiry)
                                <span style="{{ $driver->isLicenseExpired() ? 'color:#be123c;font-weight:700' : ($driver->isLicenseExpiringSoon() ? 'color:#b45309;font-weight:700' : '') }}">
                                    {{ __('transport.license_expiry') }}: {{ $driver->license_expiry->format('Y-m-d') }}
                                </span>
                            @endif
                        </div>

                        <div class="dr-foot">
                            @can('drivers.edit')
                                <a href="{{ route('transport.drivers.edit', $driver) }}" class="tr-btn tr-btn-blue">{{ __('transport.edit') }}</a>
                            @endcan
                            @can('drivers.delete')
                                <form method="POST" action="{{ route('transport.drivers.destroy', $driver) }}" onsubmit="return confirm('{{ __('transport.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tr-btn tr-btn-red">{{ __('transport.delete') }}</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl p-10 text-center text-gray-400" style="grid-column:1/-1">{{ __('transport.no_data') }}</div>
                @endforelse
            </div>

            @if ($drivers->hasPages())
                <div>{{ $drivers->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
