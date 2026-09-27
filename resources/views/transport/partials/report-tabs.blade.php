{{-- تابات تقارير النقليات: @include('transport.partials.report-tabs', ['active' => 'sales']) --}}
@php
    $tvTabs = [
        'hub' => ['label' => '📊 ' . __('transport.all_reports'), 'route' => 'transport.reports.index', 'can' => null],
        'loads' => ['label' => __('transport.loads_report'), 'route' => 'transport.loads.report', 'can' => 'truck_loads.report'],
        'sales' => ['label' => __('transport.sales_report'), 'route' => 'transport.reports.sales', 'can' => 'transport_invoices.view'],
        'customers' => ['label' => __('transport.top_customers_report'), 'route' => 'transport.reports.customers', 'can' => 'transport_invoices.view'],
        'routes' => ['label' => __('transport.routes_report'), 'route' => 'transport.reports.routes', 'can' => 'truck_loads.report'],
        'fleet' => ['label' => __('transport.fleet_report'), 'route' => 'transport.reports.fleet', 'can' => 'transport_reports.fleet'],
        'truck' => ['label' => __('transport.truck_statement'), 'route' => 'transport.reports.truck', 'can' => 'transport_reports.fleet'],
        'maintenance' => ['label' => __('transport.maintenance_report'), 'route' => 'transport.reports.maintenance', 'can' => 'maintenance.view'],
        'drivers' => ['label' => __('transport.drivers_report'), 'route' => 'transport.reports.drivers', 'can' => 'truck_loads.report'],
        'unbilled' => ['label' => __('transport.unbilled_loads'), 'route' => 'transport.reports.unbilled', 'can' => 'transport_invoices.view'],
    ];
@endphp
<div class="tv-tabs tv-no-print">
    @foreach ($tvTabs as $key => $tab)
        @if (!$tab['can'] || auth()->user()?->can($tab['can']))
            <a href="{{ route($tab['route']) }}" class="tv-tab {{ ($active ?? '') === $key ? 'active' : '' }}">{{ $tab['label'] }}</a>
        @endif
    @endforeach
</div>
