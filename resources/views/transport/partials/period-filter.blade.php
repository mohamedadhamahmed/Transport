{{-- فلتر الفترة لتقارير النقليات: @include('transport.partials.period-filter', ['from' => $from, 'to' => $to, 'extra' => 'customer|truck|sort', ...]) --}}
<form method="GET" class="tv-card tv-pad tv-no-print">
    <div class="tv-filters" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
        <div>
            <label class="tv-label">{{ __('transport.date_from') }}</label>
            <input type="date" name="date_from" value="{{ $from }}" class="tv-input">
        </div>
        <div>
            <label class="tv-label">{{ __('transport.date_to') }}</label>
            <input type="date" name="date_to" value="{{ $to }}" class="tv-input">
        </div>
        @if (!empty($customers))
            <div>
                <label class="tv-label">{{ __('transport.customer') }}</label>
                <select name="customer_id" class="tv-input tv-select">
                    <option value="">{{ __('transport.all') }}</option>
                    @foreach ($customers as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('customer_id') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if (!empty($trucks))
            <div>
                <label class="tv-label">{{ __('transport.truck') }} *</label>
                <select name="truck_id" class="tv-input tv-select" required>
                    <option value="">{{ __('transport.choose_truck') }}</option>
                    @foreach ($trucks as $t)
                        <option value="{{ $t->id }}" @selected((string) request('truck_id') === (string) $t->id)>{{ $t->display_name }}{{ $t->type ? ' (' . $t->type . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if (!empty($sorts))
            <div>
                <label class="tv-label">{{ __('transport.sort_by') }}</label>
                <select name="sort" class="tv-input">
                    @foreach ($sorts as $k => $label)
                        <option value="{{ $k }}" @selected(($sort ?? '') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div style="align-self:end">
            <button type="submit" class="tv-btn tv-btn-blue tv-btn-lg" style="min-width:100px">{{ __('transport.show') }}</button>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap" style="margin-top:.75rem">
        @php
            $q = request()->except(['date_from', 'date_to', 'page', 'export']);
            $presets = [
                'this_month' => [now()->startOfMonth(), now()],
                'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                'this_quarter' => [now()->firstOfQuarter(), now()],
                'this_year' => [now()->startOfYear(), now()],
            ];
        @endphp
        @foreach ($presets as $k => [$a, $b])
            <a href="{{ request()->url() . '?' . http_build_query($q + ['date_from' => $a->toDateString(), 'date_to' => $b->toDateString()]) }}" class="tv-tab" style="padding:.25rem .75rem;font-size:.72rem">{{ __('transport.preset_' . $k) }}</a>
        @endforeach
    </div>
</form>
