{{-- تابات تقارير النقليات: @include('transport.partials.report-tabs', ['active' => 'unbilled']) --}}
@php
    $tvTabs = [
        'loads' => ['label' => __('transport.loads_report'), 'route' => 'transport.loads.report', 'can' => 'truck_loads.report'],
        'fleet' => ['label' => __('transport.fleet_report'), 'route' => 'transport.reports.fleet', 'can' => 'transport_reports.fleet'],
        'unbilled' => ['label' => __('transport.unbilled_loads'), 'route' => 'transport.reports.unbilled', 'can' => 'transport_invoices.view'],
        'invoices' => ['label' => __('transport.invoices_and_tax'), 'route' => 'transport.invoices.index', 'can' => 'transport_invoices.view'],
        'custody' => ['label' => __('transport.custody_chart'), 'route' => 'transport.reports.custody', 'can' => 'employee_custody.view'],
    ];
@endphp
<div class="tv-tabs">
    @foreach ($tvTabs as $key => $tab)
        @can($tab['can'])
            <a href="{{ route($tab['route']) }}" class="tv-tab {{ ($active ?? '') === $key ? 'active' : '' }}">{{ $tab['label'] }}</a>
        @endcan
    @endforeach
</div>
