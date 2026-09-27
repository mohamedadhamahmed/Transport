<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .rp-cards { display:grid; grid-template-columns:repeat(2,1fr); gap:.75rem; }
        @media (min-width: 1000px) { .rp-cards { grid-template-columns:repeat(7,1fr); } }
        .rp-two { display:grid; grid-template-columns:1fr; gap:1rem; }
        @media (min-width: 1000px) { .rp-two { grid-template-columns:1fr 1fr; } }
        .rp-bar { height:8px; border-radius:99px; background:#e5e7eb; overflow:hidden; }
        .rp-bar span { display:block; height:100%; background:linear-gradient(90deg,#1456E8,#6B2FD6); }
        .rp-table th { background:#0F1B4C; color:rgba(255,255,255,.85); font-size:.72rem; padding:.6rem .5rem; text-align:start; white-space:nowrap; }
        .rp-table td { padding:.55rem .5rem; font-size:.8rem; border-bottom:1px solid #f3f4f6; vertical-align:top; }
        @media print {
            aside, header, nav, .no-print, footer { display:none !important; }
            main { padding:0 !important; }
            body { background:#fff !important; }
            .rp-table td, .rp-table th { font-size:10px; padding:4px; }
            * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>

    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">📊 {{ __('transport.loads_report') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('transport.period') }}: {{ $from }} → {{ $to }}</p>
                </div>
                <div class="flex gap-2 no-print">
                    <a href="{{ route('transport.loads.board') }}" class="tr-btn" style="background:rgba(255,255,255,.12);color:#fff;font-size:.85rem;padding:.5rem 1rem">🚚 {{ __('transport.board_title') }}</a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6]">🖨 {{ __('transport.print') }}</button>
                </div>
            </div>

            <form method="GET" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4 tr-grid tr-grid-4 no-print" style="align-items:end">
                <div><label class="tr-label">{{ __('transport.date_from') }}</label><input type="date" name="date_from" value="{{ $from }}" class="tr-input"></div>
                <div><label class="tr-label">{{ __('transport.date_to') }}</label><input type="date" name="date_to" value="{{ $to }}" class="tr-input"></div>
                <div>
                    <label class="tr-label">{{ __('transport.truck') }}</label>
                    <select name="truck_id" class="tr-input">
                        <option value="">{{ __('transport.all') }}</option>
                        @foreach ($trucks as $t)<option value="{{ $t->id }}" @selected((string) request('truck_id') === (string) $t->id)>{{ $t->display_name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="tr-label">{{ __('transport.driver') }}</label>
                    <select name="driver_id" class="tr-input">
                        <option value="">{{ __('transport.all') }}</option>
                        @foreach ($drivers as $id => $name)<option value="{{ $id }}" @selected((string) request('driver_id') === (string) $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="tr-label">{{ __('transport.from_region') }}</label>
                    <select name="from_region" class="tr-input">
                        <option value="">{{ __('transport.all') }}</option>
                        @foreach ($regions as $k => $n)<option value="{{ $k }}" @selected(request('from_region') === $k)>{{ $n }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="tr-label">{{ __('transport.to_region') }}</label>
                    <select name="to_region" class="tr-input">
                        <option value="">{{ __('transport.all') }}</option>
                        @foreach ($regions as $k => $n)<option value="{{ $k }}" @selected(request('to_region') === $k)>{{ $n }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="tr-label">{{ __('transport.status') }}</label>
                    <select name="status" class="tr-input">
                        <option value="">{{ __('transport.without_cancelled') }}</option>
                        <option value="loaded" @selected(request('status') === 'loaded')>{{ __('transport.loaded') }}</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>{{ __('transport.overdue') }}</option>
                        <option value="unloaded" @selected(request('status') === 'unloaded')>{{ __('transport.unloaded') }}</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('transport.cancelled') }}</option>
                        <option value="all" @selected(request('status') === 'all')>{{ __('transport.all') }}</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
                    <a href="{{ route('transport.loads.report') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm">{{ __('transport.reset') }}</a>
                </div>
            </form>

            <div class="rp-cards">
                <div class="tr-stat"><div class="l">{{ __('transport.loads_count') }}</div><div class="v">{{ $summary['total'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.loaded_now') }}</div><div class="v" style="color:#ea580c">{{ $summary['loaded'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.unloaded') }}</div><div class="v" style="color:#059669">{{ $summary['unloaded'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.overdue_now') }}</div><div class="v" style="color:#e11d48">{{ $summary['overdue'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.unloaded_late') }}</div><div class="v" style="color:#b45309">{{ $summary['late'] }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.avg_trip_hours') }}</div><div class="v">{{ $summary['avg_hours'] ?? '-' }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.total_weight') }}</div><div class="v">{{ rtrim(rtrim(number_format($summary['weight'], 2), '0'), '.') }}</div></div>
            </div>

            <div class="rp-two">
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5">
                    <h3 class="font-bold text-[#0F1B4C] mb-3">{{ __('transport.top_routes') }}</h3>
                    @php $maxR = max(1, $routes->max('count') ?? 1); @endphp
                    @forelse ($routes as $r)
                        <div style="margin-bottom:.6rem">
                            <div class="flex justify-between text-sm"><span>{{ $r['from'] }} ← {{ $r['to'] }}</span><b>{{ $r['count'] }}</b></div>
                            <div class="rp-bar"><span style="width:{{ round($r['count'] / $maxR * 100) }}%"></span></div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">{{ __('transport.no_data') }}</p>
                    @endforelse
                </div>
                <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5">
                    <h3 class="font-bold text-[#0F1B4C] mb-3">{{ __('transport.load_types') }}</h3>
                    @php $maxT = max(1, $loadTypes->max('count') ?? 1); @endphp
                    @forelse ($loadTypes as $t)
                        <div style="margin-bottom:.6rem">
                            <div class="flex justify-between text-sm"><span>{{ $t['type'] }}</span><span><b>{{ $t['count'] }}</b>@if ($t['weight']) · {{ rtrim(rtrim(number_format($t['weight'], 2), '0'), '.') }} {{ __('transport.ton') }}@endif</span></div>
                            <div class="rp-bar"><span style="width:{{ round($t['count'] / $maxT * 100) }}%"></span></div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">{{ __('transport.no_data') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5">
                <h3 class="font-bold text-[#0F1B4C] mb-3">{{ __('transport.loads_details') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full rp-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('transport.truck') }}</th>
                                <th>{{ __('transport.driver') }}</th>
                                <th>{{ __('transport.from') }}</th>
                                <th>{{ __('transport.to') }}</th>
                                <th>{{ __('transport.load_type') }}</th>
                                <th>{{ __('transport.weight') }}</th>
                                <th>{{ __('transport.customer') }}</th>
                                <th>{{ __('transport.loaded_at') }}</th>
                                <th>{{ __('transport.expected_unload_at') }}</th>
                                <th>{{ __('transport.unloaded_at') }}</th>
                                <th>{{ __('transport.status') }}</th>
                                @can('truck_loads.manage')<th></th>@endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loads as $l)
                                <tr>
                                    <td>{{ $l->id }}</td>
                                    <td><b>{{ $l->truck?->plate_number }}</b></td>
                                    <td>
                                        {{ $l->driver?->name ?? '-' }}
                                        @if ($l->driver?->hasValidPhone())
                                            <br><a href="{{ $l->driver->telUrl() }}" dir="ltr" style="color:#1456E8;font-weight:700">{{ $l->driver->phone }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $l->from_label }}</td>
                                    <td>{{ $l->to_label }}</td>
                                    <td>{{ $l->load_type }}</td>
                                    <td>{{ $l->weight ? rtrim(rtrim(number_format($l->weight, 2), '0'), '.') : '-' }}</td>
                                    <td>{{ $l->customer?->name ?? '-' }}</td>
                                    <td>{{ $l->loaded_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $l->expected_unload_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $l->unloaded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($l->status === 'loaded')
                                            <span class="tr-badge {{ $l->isOverdue() ? 'tr-badge-red' : 'tr-badge-amber' }}">{{ $l->isOverdue() ? __('transport.overdue') : __('transport.loaded') }}</span>
                                        @elseif ($l->status === 'unloaded')
                                            <span class="tr-badge {{ $l->wasLate() ? 'tr-badge-amber' : 'tr-badge-green' }}">{{ $l->wasLate() ? __('transport.unloaded_late') : __('transport.unloaded') }}</span>
                                        @else
                                            <span class="tr-badge tr-badge-gray">{{ __('transport.cancelled') }}</span>
                                        @endif
                                    </td>
                                    @can('truck_loads.manage')
                                        <td>
                                            @if ($l->status !== 'cancelled' && !$l->transport_invoice_id)
                                                <a href="{{ route('transport.loads.edit', ['load' => $l, 'return_to' => request()->fullUrl()]) }}" class="tr-btn tr-btn-blue">✏️ {{ __('transport.edit') }}</a>
                                            @elseif ($l->transport_invoice_id)
                                                <span class="tr-badge tr-badge-green" title="{{ __('transport.load_invoiced_short') }}">🧾</span>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr><td colspan="13" class="text-center text-gray-400" style="padding:2rem">{{ __('transport.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
