<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.phone-script')

    <style>
        .tb-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
        @media (min-width: 700px) { .tb-grid { grid-template-columns:repeat(2, 1fr); } }
        @media (min-width: 1100px) { .tb-grid { grid-template-columns:repeat(3, 1fr); } }
        @media (min-width: 1500px) { .tb-grid { grid-template-columns:repeat(4, 1fr); } }
        .tb-card { background:#fff; border-radius:.9rem; border:1px solid #e5e7eb; border-top:5px solid #10b981; padding:1rem; display:flex; flex-direction:column; gap:.7rem; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .tb-card.loaded { border-top-color:#F5811E; }
        .tb-card.overdue { border-top-color:#e11d48; background:#fff8f8; }
        .tb-card.maint { border-top-color:#9ca3af; background:#fafafa; }
        .tb-head { display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; }
        .tb-plate { font-size:1.15rem; font-weight:800; color:#0F1B4C; }
        .tb-sub { font-size:.75rem; color:#6b7280; }
        .tb-route { display:flex; align-items:center; gap:.4rem; font-weight:700; color:#1f2937; font-size:.95rem; flex-wrap:wrap; }
        .tb-route .arrow { color:#F5811E; }
        .tb-meta { display:grid; grid-template-columns:1fr 1fr; gap:.3rem .75rem; font-size:.78rem; color:#4b5563; }
        .tb-meta b { color:#111827; font-weight:600; }
        .tb-timer { font-size:.8rem; font-weight:800; padding:.3rem .6rem; border-radius:.5rem; text-align:center; }
        .tb-timer.ok { background:#fff7ed; color:#c2410c; }
        .tb-timer.late { background:#ffe4e6; color:#be123c; }
        .tb-empty-loc { font-size:.95rem; font-weight:700; color:#047857; }
        .tb-actions { display:flex; gap:.4rem; flex-wrap:wrap; margin-top:auto; padding-top:.5rem; border-top:1px dashed #e5e7eb; }
        .tb-actions form { display:inline; }
        .tb-big { flex:1; justify-content:center; padding:.55rem .75rem; font-size:.85rem; }
        .tb-stats { display:grid; grid-template-columns:repeat(2,1fr); gap:.75rem; }
        @media (min-width: 900px) { .tb-stats { grid-template-columns:repeat(5,1fr); } }
        .tb-stat { display:block; background:#fff; border:1px solid #f3f4f6; border-radius:.75rem; padding:.9rem 1rem; text-decoration:none; }
        .tb-stat .v { font-size:1.6rem; font-weight:800; }
        .tb-stat .l { font-size:.78rem; color:#6b7280; }
        .tb-stat.active { outline:2px solid #1456E8; }
        dialog.tb-dialog { border:0; border-radius:1rem; padding:0; width:min(760px, 96vw); box-shadow:0 20px 50px rgba(0,0,0,.25); }
        dialog.tb-dialog::backdrop { background:rgba(15,27,76,.45); }
        .tb-dialog .dh { background:#0F1B4C; color:#fff; padding:.9rem 1.2rem; font-weight:800; display:flex; justify-content:space-between; align-items:center; }
        .tb-dialog .dh button { background:transparent; border:0; color:#fff; font-size:1.3rem; cursor:pointer; }
        .tb-dialog .db { padding:1.2rem; max-height:75vh; overflow:auto; }
        .tb-dialog .df { padding:.9rem 1.2rem; border-top:1px solid #f3f4f6; display:flex; gap:.5rem; justify-content:flex-end; }
    </style>

    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">🚚 {{ __('transport.board_title') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('transport.board_subtitle') }}</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @can('truck_loads.report')
                        <a href="{{ route('transport.loads.report') }}" class="tr-btn" style="background:rgba(255,255,255,.12);color:#fff;font-size:.85rem;padding:.5rem 1rem">📊 {{ __('transport.loads_report') }}</a>
                    @endcan
                    @can('drivers.create')
                        <a href="{{ route('transport.drivers.create') }}" class="tr-btn" style="background:rgba(255,255,255,.12);color:#fff;font-size:.85rem;padding:.5rem 1rem">+ {{ __('transport.new_driver') }}</a>
                    @endcan
                    @can('trucks.create')
                        <a href="{{ route('transport.trucks.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">+ {{ __('transport.new_truck') }}</a>
                    @endcan
                </div>
            </div>

            @include('transport.partials.flash')

            {{-- الإحصائيات (بتفلتر لما تدوس عليها) --}}
            <div class="tb-stats">
                @php $q = request()->except('status', 'page'); @endphp
                <a href="{{ route('transport.loads.board', $q) }}" class="tb-stat {{ !request('status') ? 'active' : '' }}"><div class="v" style="color:#0F1B4C">{{ $stats['total'] }}</div><div class="l">{{ __('transport.all_trucks') }}</div></a>
                <a href="{{ route('transport.loads.board', $q + ['status' => 'empty']) }}" class="tb-stat {{ request('status') === 'empty' ? 'active' : '' }}"><div class="v" style="color:#059669">{{ $stats['empty'] }}</div><div class="l">🟢 {{ __('transport.empty_trucks') }}</div></a>
                <a href="{{ route('transport.loads.board', $q + ['status' => 'loaded']) }}" class="tb-stat {{ request('status') === 'loaded' ? 'active' : '' }}"><div class="v" style="color:#ea580c">{{ $stats['loaded'] }}</div><div class="l">🟠 {{ __('transport.loaded_trucks') }}</div></a>
                <a href="{{ route('transport.loads.board', $q + ['status' => 'overdue']) }}" class="tb-stat {{ request('status') === 'overdue' ? 'active' : '' }}"><div class="v" style="color:#e11d48">{{ $stats['overdue'] }}</div><div class="l">🔴 {{ __('transport.overdue_trucks') }}</div></a>
                <a href="{{ route('transport.loads.board', $q + ['status' => 'maintenance']) }}" class="tb-stat {{ request('status') === 'maintenance' ? 'active' : '' }}"><div class="v" style="color:#6b7280">{{ $stats['maintenance'] }}</div><div class="l">🔧 {{ __('transport.status_maintenance') }}</div></a>
            </div>

            <form method="GET" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-4 flex flex-wrap gap-3 items-end">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <div style="min-width:220px;flex:1">
                    <label class="tr-label">{{ __('transport.search') }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="tr-input" placeholder="{{ __('transport.board_search_hint') }}">
                </div>
                <div style="min-width:200px">
                    <label class="tr-label">{{ __('transport.region') }}</label>
                    <select name="region" class="tr-input">
                        <option value="">{{ __('transport.all_regions') }}</option>
                        @foreach ($regions as $k => $name)
                            <option value="{{ $k }}" @selected(request('region') === $k)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.search') }}</button>
                <a href="{{ route('transport.loads.board') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm">{{ __('transport.reset') }}</a>
            </form>

            <div class="tb-grid">
                @forelse ($trucks as $truck)
                    @php
                        $load = $truck->activeLoad;
                        $overdue = $load?->isOverdue();
                        $cls = $load ? ($overdue ? 'overdue' : 'loaded') : ($truck->status === 'maintenance' ? 'maint' : '');
                        $driver = $load?->driver ?? $truck->driver;
                        $waMsg = $load
                            ? 'السلام عليكم ' . ($driver?->name ?? '') . '، بخصوص حمولة الشاحنة ' . $truck->plate_number . ' من ' . $load->from_label . ' إلى ' . $load->to_label
                            : 'السلام عليكم ' . ($driver?->name ?? '') . '، بخصوص الشاحنة ' . $truck->plate_number;
                    @endphp
                    <div class="tb-card {{ $cls }}">
                        <div class="tb-head">
                            <div>
                                <div class="tb-plate">{{ $truck->plate_number }}
                                    @can('trucks.edit')
                                        <a href="{{ route('transport.trucks.edit', ['truck' => $truck, 'return_to' => request()->fullUrl()]) }}" title="{{ __('transport.edit_truck') }}" style="font-size:.8rem;margin-inline-start:.3rem;text-decoration:none">✏️</a>
                                    @endcan
                                </div>
                                <div class="tb-sub">{{ trim(($truck->name ?? '') . ' ' . ($truck->type ? '· ' . $truck->type : '')) ?: ' ' }}</div>
                            </div>
                            @if ($load)
                                <span class="tr-badge {{ $overdue ? 'tr-badge-red' : 'tr-badge-amber' }}">{{ $overdue ? __('transport.overdue') : __('transport.loaded') }}</span>
                            @elseif ($truck->status === 'maintenance')
                                <span class="tr-badge tr-badge-gray">🔧 {{ __('transport.status_maintenance') }}</span>
                            @else
                                <span class="tr-badge tr-badge-green">{{ __('transport.empty') }}</span>
                            @endif
                        </div>

                        @if ($load)
                            <div class="tb-route">
                                <span>{{ $load->from_label }}</span><span class="arrow">←</span><span>{{ $load->to_label }}</span>
                            </div>
                            <div class="tb-meta">
                                <div>{{ __('transport.load_type') }}: <b>{{ $load->load_type }}</b></div>
                                <div>{{ __('transport.weight') }}: <b>{{ $load->weight ? rtrim(rtrim(number_format($load->weight, 2), '0'), '.') . ' ط' : '-' }}</b></div>
                                <div>{{ __('transport.loaded_at') }}: <b>{{ $load->loaded_at->format('m-d H:i') }}</b></div>
                                <div>{{ __('transport.expected_unload_at') }}: <b>{{ $load->expected_unload_at->format('m-d H:i') }}</b></div>
                                @if ($load->customer)<div>{{ __('transport.customer') }}: <b>{{ $load->customer->name }}</b></div>@endif
                                @if ($load->waybill_number)<div>{{ __('transport.waybill_number') }}: <b>{{ $load->waybill_number }}</b></div>@endif
                            </div>
                            <div class="tb-timer {{ $overdue ? 'late' : 'ok' }}">⏱ {{ $load->remainingText() }}</div>
                        @else
                            <div class="tb-empty-loc">📍 {{ $truck->current_region ? \App\Support\SaudiRegions::name($truck->current_region) : __('transport.location_unknown') }}</div>
                        @endif

                        @include('transport.partials.driver-contact', ['driver' => $driver, 'msg' => $waMsg])

                        @can('truck_loads.manage')
                            <div class="tb-actions">
                                @if ($load)
                                    <button type="button" class="tr-btn tr-btn-green tb-big js-unload"
                                            data-url="{{ route('transport.loads.unload', $load) }}"
                                            data-plate="{{ $truck->plate_number }}"
                                            data-route="{{ $load->from_label }} ← {{ $load->to_label }}">✅ {{ __('transport.mark_unloaded') }}</button>
                                    <a href="{{ route('transport.loads.edit', ['load' => $load, 'return_to' => request()->fullUrl()]) }}" class="tr-btn tr-btn-blue" title="{{ __('transport.edit_load') }}">✏️</a>
                                    <form method="POST" action="{{ route('transport.loads.cancel', $load) }}" onsubmit="return confirm('{{ __('transport.confirm_cancel_load') }}')">
                                        @csrf
                                        <button type="submit" class="tr-btn tr-btn-red" title="{{ __('transport.cancel_load') }}">✕</button>
                                    </form>
                                @elseif ($truck->status === 'active')
                                    <button type="button" class="tr-btn tr-btn-blue tb-big js-load"
                                            data-url="{{ route('transport.loads.store', $truck) }}"
                                            data-plate="{{ $truck->plate_number }}"
                                            data-region="{{ $truck->current_region }}"
                                            data-driver="{{ $truck->driver_id }}">📦 {{ __('transport.load_truck') }}</button>
                                    <button type="button" class="tr-btn tr-btn-gray js-location"
                                            data-url="{{ route('transport.loads.location', $truck) }}"
                                            data-plate="{{ $truck->plate_number }}"
                                            data-region="{{ $truck->current_region }}">📍 {{ __('transport.location') }}</button>
                                @else
                                    <span class="tb-sub">{{ __('transport.maintenance_hint') }}</span>
                                @endif
                            </div>
                        @endcan
                    </div>
                @empty
                    <div class="bg-white rounded-xl p-10 text-center text-gray-400" style="grid-column:1/-1">{{ __('transport.no_trucks_match') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    @can('truck_loads.manage')
    {{-- نافذة التحميل --}}
    <dialog id="load-dialog" class="tb-dialog">
        <form method="POST" id="load-form" action="{{ old('_load_url') }}">
            @csrf
            <input type="hidden" name="_load_url" id="ld-url" value="{{ old('_load_url') }}">
            <input type="hidden" name="_load_plate" id="ld-plate-in" value="{{ old('_load_plate') }}">
            <div class="dh"><span>📦 {{ __('transport.load_truck') }}: <span id="load-plate"></span></span><button type="button" onclick="this.closest('dialog').close()">×</button></div>
            <div class="db">
                <div class="tr-grid tr-grid-2">
                    <div>
                        <label class="tr-label">{{ __('transport.from_region') }} *</label>
                        <select name="from_region" id="ld-from" required class="tr-input">
                            <option value="">{{ __('transport.choose_region') }}</option>
                            @foreach ($regions as $k => $name)<option value="{{ $k }}" @selected(old('from_region') === $k)>{{ $name }}</option>@endforeach
                        </select>
                        <input type="text" name="from_city" value="{{ old('from_city') }}" class="tr-input" style="margin-top:.4rem" placeholder="{{ __('transport.city_optional') }}">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.to_region') }} *</label>
                        <select name="to_region" required class="tr-input">
                            <option value="">{{ __('transport.choose_region') }}</option>
                            @foreach ($regions as $k => $name)<option value="{{ $k }}" @selected(old('to_region') === $k)>{{ $name }}</option>@endforeach
                        </select>
                        <input type="text" name="to_city" value="{{ old('to_city') }}" class="tr-input" style="margin-top:.4rem" placeholder="{{ __('transport.city_optional') }}">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.load_type') }} *</label>
                        <input type="text" name="load_type" value="{{ old('load_type') }}" required class="tr-input" list="load-types" placeholder="{{ __('transport.load_type_hint') }}">
                        <datalist id="load-types">
                            <option value="مواد بناء"><option value="حديد"><option value="أسمنت"><option value="مواد غذائية"><option value="مبردات"><option value="معدات"><option value="أثاث"><option value="حاويات"><option value="بضائع عامة">
                        </datalist>
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.weight') }} ({{ __('transport.ton') }})</label>
                        <input type="number" step="0.01" min="0" name="weight" value="{{ old('weight') }}" class="tr-input">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.load_price') }}</label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="tr-input" placeholder="{{ __('transport.load_price_hint') }}">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.loaded_at') }} *</label>
                        <input type="datetime-local" name="loaded_at" id="ld-loaded" value="{{ old('loaded_at') }}" required class="tr-input">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.expected_unload_at') }} *</label>
                        <input type="datetime-local" name="expected_unload_at" id="ld-expected" value="{{ old('expected_unload_at') }}" required class="tr-input">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.driver') }}</label>
                        <select name="driver_id" id="ld-driver" class="tr-input">
                            <option value="">{{ __('transport.truck_default_driver') }}</option>
                            @foreach ($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}{{ $d->phone ? ' - ' . $d->phone : '' }}</option>@endforeach
                        </select>
                        @can('drivers.create')
                            <a href="#" id="quick-driver-link" style="font-size:.75rem;color:#1456E8;font-weight:700">+ {{ __('transport.new_driver') }}</a>
                        @endcan
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.customer') }}</label>
                        <select name="customer_id" class="tr-input">
                            <option value="">-</option>
                            @foreach ($customers as $id => $name)<option value="{{ $id }}" @selected((string) old('customer_id') === (string) $id)>{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.waybill_number') }}</label>
                        <input type="text" name="waybill_number" value="{{ old('waybill_number') }}" class="tr-input">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.notes') }}</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" class="tr-input">
                    </div>
                </div>
            </div>
            <div class="df">
                <button type="button" class="tr-btn tr-btn-gray" onclick="this.closest('dialog').close()">{{ __('transport.cancel') }}</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.save_load') }}</button>
            </div>
        </form>
    </dialog>

    {{-- نافذة سائق جديد سريع --}}
    <dialog id="driver-dialog" class="tb-dialog" style="width:min(460px,96vw)">
        <form id="quick-driver-form">
            <div class="dh"><span>+ {{ __('transport.new_driver') }}</span><button type="button" onclick="this.closest('dialog').close()">×</button></div>
            <div class="db">
                <div class="tr-grid">
                    <div>
                        <label class="tr-label">{{ __('transport.driver_name') }} *</label>
                        <input type="text" name="name" required class="tr-input">
                    </div>
                    <div>
                        <label class="tr-label">{{ __('transport.phone') }} *</label>
                        <input type="text" name="phone" required data-sa-phone class="tr-input" placeholder="05XXXXXXXX">
                    </div>
                    <div id="quick-driver-error" style="color:#be123c;font-size:.8rem"></div>
                </div>
            </div>
            <div class="df">
                <button type="button" class="tr-btn tr-btn-gray" onclick="this.closest('dialog').close()">{{ __('transport.cancel') }}</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.save') }}</button>
            </div>
        </form>
    </dialog>

    {{-- نافذة التفريغ --}}
    <dialog id="unload-dialog" class="tb-dialog" style="width:min(460px,96vw)">
        <form method="POST" id="unload-form">
            @csrf
            <div class="dh"><span>✅ {{ __('transport.mark_unloaded') }}: <span id="ul-plate"></span></span><button type="button" onclick="this.closest('dialog').close()">×</button></div>
            <div class="db">
                <p id="ul-route" style="font-weight:700;margin-bottom:.8rem;color:#0F1B4C"></p>
                <label class="tr-label">{{ __('transport.unloaded_at') }} *</label>
                <input type="datetime-local" name="unloaded_at" id="ul-at" required class="tr-input">
                <p class="tb-sub" style="margin-top:.5rem">{{ __('transport.unload_hint') }}</p>
            </div>
            <div class="df">
                <button type="button" class="tr-btn tr-btn-gray" onclick="this.closest('dialog').close()">{{ __('transport.cancel') }}</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#059669] text-white text-sm font-medium">{{ __('transport.confirm_unload') }}</button>
            </div>
        </form>
    </dialog>

    {{-- نافذة المكان --}}
    <dialog id="location-dialog" class="tb-dialog" style="width:min(420px,96vw)">
        <form method="POST" id="location-form">
            @csrf
            <div class="dh"><span>📍 {{ __('transport.truck_location') }}: <span id="loc-plate"></span></span><button type="button" onclick="this.closest('dialog').close()">×</button></div>
            <div class="db">
                <select name="current_region" id="loc-region" class="tr-input">
                    <option value="">{{ __('transport.location_unknown') }}</option>
                    @foreach ($regions as $k => $name)<option value="{{ $k }}">{{ $name }}</option>@endforeach
                </select>
            </div>
            <div class="df">
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">{{ __('transport.save') }}</button>
            </div>
        </form>
    </dialog>
    @endcan

    @push('scripts')
    <script>
    (function () {
        const pad = n => String(n).padStart(2, '0');
        const localNow = (addHours = 0) => {
            const d = new Date(Date.now() + addHours * 3600e3);
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        };

        const loadDlg = document.getElementById('load-dialog');
        if (!loadDlg) return;
        const loadForm = document.getElementById('load-form');

        document.querySelectorAll('.js-load').forEach(btn => btn.addEventListener('click', () => {
            loadForm.action = btn.dataset.url;
            document.getElementById('ld-url').value = btn.dataset.url;
            document.getElementById('ld-plate-in').value = btn.dataset.plate;
            document.getElementById('load-plate').textContent = btn.dataset.plate;
            if (btn.dataset.region) document.getElementById('ld-from').value = btn.dataset.region;
            document.getElementById('ld-driver').value = '';
            const loaded = document.getElementById('ld-loaded');
            if (!loaded.value) loaded.value = localNow();
            loadDlg.showModal();
        }));

        // معاد التنزيل المتوقع لازم يبقى بعد معاد التحميل
        const loadedIn = document.getElementById('ld-loaded'), expIn = document.getElementById('ld-expected');
        loadedIn.addEventListener('change', () => { expIn.min = loadedIn.value; });

        const unloadDlg = document.getElementById('unload-dialog');
        document.querySelectorAll('.js-unload').forEach(btn => btn.addEventListener('click', () => {
            document.getElementById('unload-form').action = btn.dataset.url;
            document.getElementById('ul-plate').textContent = btn.dataset.plate;
            document.getElementById('ul-route').textContent = btn.dataset.route;
            document.getElementById('ul-at').value = localNow();
            unloadDlg.showModal();
        }));

        const locDlg = document.getElementById('location-dialog');
        document.querySelectorAll('.js-location').forEach(btn => btn.addEventListener('click', () => {
            document.getElementById('location-form').action = btn.dataset.url;
            document.getElementById('loc-plate').textContent = btn.dataset.plate;
            document.getElementById('loc-region').value = btn.dataset.region || '';
            locDlg.showModal();
        }));

        // سائق جديد من جوه نافذة التحميل
        const qLink = document.getElementById('quick-driver-link');
        const dDlg = document.getElementById('driver-dialog');
        const dForm = document.getElementById('quick-driver-form');
        if (qLink) {
            qLink.addEventListener('click', e => { e.preventDefault(); dForm.reset(); document.getElementById('quick-driver-error').textContent = ''; dDlg.showModal(); window.SaPhone && SaPhone.init(dForm); });
            dForm.addEventListener('submit', async e => {
                e.preventDefault();
                const phoneIn = dForm.querySelector('[name=phone]');
                phoneIn.value = SaPhone.normalize(phoneIn.value);
                if (!SaPhone.check(phoneIn)) return;
                const res = await fetch(@json(route('transport.drivers.quick')), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: new FormData(dForm),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) {
                    document.getElementById('quick-driver-error').textContent = json.message || 'Error';
                    return;
                }
                const sel = document.getElementById('ld-driver');
                sel.add(new Option(json.name + ' - ' + json.phone, json.id, true, true));
                dDlg.close();
            });
        }

        @if (old('_load_url'))
            // رجع بخطأ في التحميل: افتح النافذة تاني بنفس البيانات
            document.getElementById('load-plate').textContent = @json(old('_load_plate'));
            loadDlg.showModal();
        @endif
    })();
    </script>
    @endpush
</x-app-layout>
