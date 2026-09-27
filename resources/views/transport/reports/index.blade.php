<x-app-layout>
    @include('transport.partials.styles')
    @include('transport.partials.tv-styles')
    <style>
        .rh-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 700px) { .rh-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1200px) { .rh-grid { grid-template-columns: repeat(3, 1fr); } }
        .rh-card { display: flex; gap: .9rem; align-items: flex-start; background: #fff; border: 1px solid #eef0f5; border-radius: 14px; padding: 1.1rem 1.2rem; transition: .15s; }
        .rh-card:hover { border-color: #cddcfb; box-shadow: 0 4px 14px rgba(20,86,232,.08); transform: translateY(-1px); }
        .rh-ico { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .rh-card h3 { font-weight: 800; color: #0F1B4C; font-size: .98rem; }
        .rh-card p { font-size: .76rem; color: #6b7280; margin-top: .25rem; line-height: 1.6; }
        .rh-group { margin-top: .5rem; }
        .rh-group > .tv-section-title { margin-bottom: .8rem; }
    </style>

    @php
        $u = auth()->user();
        $groups = [
            ['title' => __('transport.rg_shipments'), 'items' => [
                ['route' => 'transport.loads.report', 'can' => 'truck_loads.report', 'icon' => '📦', 'bg' => '#fff7ed', 'title' => __('transport.loads_report'), 'desc' => __('transport.rd_loads')],
                ['route' => 'transport.reports.routes', 'can' => 'truck_loads.report', 'icon' => '📍', 'bg' => '#eaf1ff', 'title' => __('transport.routes_report'), 'desc' => __('transport.rd_routes')],
                ['route' => 'transport.reports.unbilled', 'can' => 'transport_invoices.view', 'icon' => '⏳', 'bg' => '#fef2f2', 'title' => __('transport.unbilled_loads'), 'desc' => __('transport.rd_unbilled')],
                ['route' => 'transport.waybills.index', 'can' => 'waybills.view', 'icon' => '📄', 'bg' => '#f5f3ff', 'title' => __('transport.waybills'), 'desc' => __('transport.rd_waybills')],
            ]],
            ['title' => __('transport.rg_sales'), 'items' => [
                ['route' => 'transport.reports.sales', 'can' => 'transport_invoices.view', 'icon' => '🧾', 'bg' => '#ecfdf5', 'title' => __('transport.sales_report'), 'desc' => __('transport.rd_sales')],
                ['route' => 'transport.reports.customers', 'can' => 'transport_invoices.view', 'icon' => '🏆', 'bg' => '#fffbeb', 'title' => __('transport.top_customers_report'), 'desc' => __('transport.rd_customers')],
                ['route' => 'transport.invoices.index', 'can' => 'transport_invoices.view', 'icon' => '📑', 'bg' => '#eaf1ff', 'title' => __('transport.invoices_and_tax'), 'desc' => __('transport.rd_invoices')],
                ['route' => 'transport.zatca.index', 'can' => 'zatca.view', 'icon' => '🏛', 'bg' => '#f3f4f6', 'title' => __('transport.zatca_title'), 'desc' => __('transport.rd_zatca')],
            ]],
            ['title' => __('transport.rg_trucks'), 'items' => [
                ['route' => 'transport.reports.maintenance', 'can' => 'maintenance.view', 'icon' => '🛠', 'bg' => '#fef2f2', 'title' => __('transport.maintenance_report'), 'desc' => __('transport.rd_maintenance')],
                ['route' => 'transport.reports.fleet', 'can' => 'transport_reports.fleet', 'icon' => '📈', 'bg' => '#fff1f2', 'title' => __('transport.fleet_report'), 'desc' => __('transport.rd_fleet')],
                ['route' => 'transport.reports.truck', 'can' => 'transport_reports.fleet', 'icon' => '🚚', 'bg' => '#eaf1ff', 'title' => __('transport.truck_statement'), 'desc' => __('transport.rd_truck')],
                ['route' => 'transport.trucks.index', 'can' => 'trucks.view', 'icon' => '🪪', 'bg' => '#fffbeb', 'title' => __('transport.truck_documents'), 'desc' => __('transport.rd_docs'), 'params' => ['docs' => 'alert']],
                ['route' => 'transport.reports.drivers', 'can' => 'truck_loads.report', 'icon' => '👷', 'bg' => '#ecfdf5', 'title' => __('transport.drivers_report'), 'desc' => __('transport.rd_drivers')],
                ['route' => 'transport.reports.custody', 'can' => 'employee_custody.view', 'icon' => '💼', 'bg' => '#f5f3ff', 'title' => __('transport.custody_chart'), 'desc' => __('transport.rd_custody')],
            ]],
        ];
    @endphp

    <div class="tv-page">
        @include('transport.partials.tv-header', [
            'title' => __('transport.transport_reports'),
            'icon' => 'chart',
            'crumbs' => [['label' => __('transport.transport_reports')]],
        ])

        @foreach ($groups as $g)
            @php $items = collect($g['items'])->filter(fn ($i) => $u?->can($i['can'])); @endphp
            @continue($items->isEmpty())
            <div class="rh-group">
                <div class="tv-section-title">{{ $g['title'] }}</div>
                <div class="rh-grid">
                    @foreach ($items as $i)
                        <a href="{{ route($i['route'], $i['params'] ?? []) }}" class="rh-card">
                            <span class="rh-ico" style="background:{{ $i['bg'] }}">{{ $i['icon'] }}</span>
                            <div>
                                <h3>{{ $i['title'] }}</h3>
                                <p>{{ $i['desc'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
