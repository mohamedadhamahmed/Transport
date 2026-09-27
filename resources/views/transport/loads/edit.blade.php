<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    @php
        $dt = fn ($col) => old($col, optional($load->{$col})->format('Y-m-d\TH:i'));
        $isUnloaded = $load->status === 'unloaded';
    @endphp
    <style>
        .le-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 768px) { .le-grid { grid-template-columns: repeat(3, 1fr); } .le-full { grid-column: 1 / -1; } }
        .le-sec + .le-sec { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px dashed #eef0f5; }
        .le-sec h3 { font-weight: 800; color: #0F1B4C; font-size: .92rem; margin-bottom: .9rem; }
    </style>

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.edit_load') . ' — ' . ($load->truck?->plate_number ?? ''),
            'badge' => $isUnloaded ? __('transport.load_status_unloaded') : __('transport.load_status_loaded'),
            'icon' => 'truck',
            'crumbs' => [['label' => __('transport.board_title'), 'url' => route('transport.loads.board')], ['label' => __('transport.edit_load')]],
            'buttons' => array_values(array_filter([
                $load->truck ? ['label' => __('transport.edit_truck'), 'url' => route('transport.trucks.edit', $load->truck), 'style' => 'gray', 'icon' => 'truck', 'can' => 'trucks.edit'] : null,
            ])),
        ])

        @include('transport.partials.flash')

        <form method="POST" action="{{ route('transport.loads.update', $load) }}" class="tv-card tv-pad">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ old('return_to', $returnTo) }}">

            <div class="le-sec">
                <h3>🚚 {{ __('transport.truck_and_driver') }}</h3>
                <div class="le-grid">
                    <div>
                        <label class="tv-label">{{ __('transport.truck') }} *</label>
                        <select name="truck_id" class="tv-input tv-select" required>
                            @foreach ($trucks as $t)
                                @php $busy = $t->activeLoad && $t->activeLoad->id !== $load->id; @endphp
                                <option value="{{ $t->id }}" @selected((string) old('truck_id', $load->truck_id) === (string) $t->id) @disabled(!$isUnloaded && $busy)>
                                    {{ $t->display_name }}{{ $t->type ? ' (' . $t->type . ')' : '' }}{{ !$isUnloaded && $busy ? ' — ' . __('transport.loaded') : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.driver') }}</label>
                        <select name="driver_id" class="tv-input tv-select">
                            <option value="">-</option>
                            @foreach ($drivers as $d)
                                <option value="{{ $d->id }}" @selected((string) old('driver_id', $load->driver_id) === (string) $d->id)>{{ $d->name }}{{ $d->phone ? ' - ' . $d->phone : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.customer') }}</label>
                        <select name="customer_id" class="tv-input tv-select">
                            <option value="">-</option>
                            @foreach ($customers as $id => $name)
                                <option value="{{ $id }}" @selected((string) old('customer_id', $load->customer_id) === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="le-sec">
                <h3>📍 {{ __('transport.route') }}</h3>
                <div class="le-grid">
                    <div>
                        <label class="tv-label">{{ __('transport.from_region') }} *</label>
                        <select name="from_region" class="tv-input" required>
                            @foreach ($regions as $k => $name)
                                <option value="{{ $k }}" @selected(old('from_region', $load->from_region) === $k)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="from_city" value="{{ old('from_city', $load->from_city) }}" class="tv-input" style="margin-top:.4rem" placeholder="{{ __('transport.city_optional') }}">
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.to_region') }} *</label>
                        <select name="to_region" class="tv-input" required>
                            @foreach ($regions as $k => $name)
                                <option value="{{ $k }}" @selected(old('to_region', $load->to_region) === $k)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="to_city" value="{{ old('to_city', $load->to_city) }}" class="tv-input" style="margin-top:.4rem" placeholder="{{ __('transport.city_optional') }}">
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.waybill_number') }}</label>
                        <input type="text" name="waybill_number" value="{{ old('waybill_number', $load->waybill_number) }}" class="tv-input">
                    </div>
                </div>
            </div>

            <div class="le-sec">
                <h3>📦 {{ __('transport.load_details') }}</h3>
                <div class="le-grid">
                    <div>
                        <label class="tv-label">{{ __('transport.load_type') }} *</label>
                        <input type="text" name="load_type" value="{{ old('load_type', $load->load_type) }}" required class="tv-input" list="load-types">
                        <datalist id="load-types">
                            <option value="مواد بناء"><option value="حديد"><option value="أسمنت"><option value="مواد غذائية"><option value="مبردات"><option value="معدات"><option value="أثاث"><option value="حاويات"><option value="بضائع عامة">
                        </datalist>
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.weight') }} ({{ __('transport.ton') }})</label>
                        <input type="number" step="0.01" min="0" name="weight" value="{{ old('weight', $load->weight) }}" class="tv-input">
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.load_price') }}</label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $load->price) }}" class="tv-input">
                    </div>
                </div>
            </div>

            <div class="le-sec">
                <h3>⏱ {{ __('transport.load_times') }}</h3>
                <div class="le-grid">
                    <div>
                        <label class="tv-label">{{ __('transport.loaded_at') }} *</label>
                        <input type="datetime-local" name="loaded_at" value="{{ $dt('loaded_at') }}" required class="tv-input">
                    </div>
                    <div>
                        <label class="tv-label">{{ __('transport.expected_unload_at') }} *</label>
                        <input type="datetime-local" name="expected_unload_at" value="{{ $dt('expected_unload_at') }}" required class="tv-input">
                    </div>
                    @if ($isUnloaded)
                        <div>
                            <label class="tv-label">{{ __('transport.unloaded_at') }} *</label>
                            <input type="datetime-local" name="unloaded_at" value="{{ $dt('unloaded_at') }}" required class="tv-input">
                        </div>
                    @endif
                    <div class="le-full">
                        <label class="tv-label">{{ __('transport.notes') }}</label>
                        <textarea name="notes" rows="3" class="tv-input">{{ old('notes', $load->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3" style="margin-top:1.5rem">
                <button type="submit" class="tv-btn tv-btn-green tv-btn-lg">💾 {{ __('transport.save_changes') }}</button>
                <a href="{{ $returnTo ?? route('transport.loads.board') }}" class="tv-btn tv-btn-gray tv-btn-lg">{{ __('transport.cancel') }}</a>
            </div>
        </form>
    </div>

    @include('transport.partials.tv-select-script')
</x-app-layout>
