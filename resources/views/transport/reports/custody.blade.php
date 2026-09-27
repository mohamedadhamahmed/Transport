<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .cu-box { background:#fff; border:1px solid #f3f4f6; border-radius:.9rem; padding:1.1rem; }
        .cu-table th { background:#0F1B4C; color:rgba(255,255,255,.85); font-size:.72rem; padding:.6rem .5rem; text-align:start; }
        .cu-table td { padding:.55rem .5rem; font-size:.85rem; border-bottom:1px solid #f3f4f6; }
        @media print { aside, header, nav, .no-print, footer { display:none !important; } main { padding:0 !important; } }
    </style>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-white font-bold text-lg">💼 {{ __('transport.custody_chart') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('transport.custody_chart_subtitle', ['account' => $parent?->name ?? 'ذمم الموظفين']) }}</p>
                </div>
                <div class="no-print flex gap-2">
                    <a href="{{ route('transport.reports.custody', request()->boolean('all') ? [] : ['all' => 1]) }}" class="tr-btn" style="background:rgba(255,255,255,.12);color:#fff;font-size:.85rem;padding:.5rem 1rem">{{ request()->boolean('all') ? __('transport.only_with_balance') : __('transport.show_all_employees') }}</a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6]">🖨 {{ __('transport.print') }}</button>
                </div>
            </div>

            <div class="tr-grid tr-grid-3">
                <div class="tr-stat"><div class="l">{{ __('transport.employees_count') }}</div><div class="v">{{ $rows->count() }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.custody_account_total') }}</div><div class="v" style="color:#1456E8">{{ number_format($total, 2) }}</div></div>
                <div class="tr-stat"><div class="l">{{ __('transport.open_custody_total') }}</div><div class="v" style="color:#ea580c">{{ number_format($totalOpen, 2) }}</div></div>
            </div>

            <div class="cu-box">
                <div style="position:relative;height:{{ max(260, $rows->count() * 34) }}px"><canvas id="ch-custody"></canvas></div>
            </div>

            <div class="cu-box overflow-x-auto">
                <table class="min-w-full cu-table">
                    <thead><tr>
                        <th>#</th>
                        <th>{{ __('transport.employee') }}</th>
                        <th>{{ __('transport.account') }}</th>
                        <th>{{ __('transport.custody_balance') }}</th>
                        <th>{{ __('transport.open_custody') }}</th>
                        <th class="no-print"></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><b>{{ $r['name'] }}</b>@if ($r['job']) <span class="text-gray-500 text-xs">· {{ $r['job'] }}</span>@endif</td>
                                <td class="text-gray-500">{{ $r['account']->name }}</td>
                                <td style="font-weight:800;color:{{ $r['balance'] >= 0 ? '#1456E8' : '#be123c' }}">{{ number_format($r['balance'], 2) }}</td>
                                <td>{{ number_format($r['open_custody'], 2) }}@if ($r['open_docs']) <span class="text-xs text-gray-500">({{ $r['open_docs'] }})</span>@endif</td>
                                <td class="no-print">
                                    @if (\Illuminate\Support\Facades\Route::has('accounts.statement'))
                                        <a class="tr-btn tr-btn-gray" href="{{ route('accounts.statement', $r['account']->id) }}">{{ __('transport.account_statement') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-gray-400" style="padding:2rem">{{ __('transport.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = 'Cairo, sans-serif';
        new Chart(document.getElementById('ch-custody'), {
            type: 'bar',
            data: {
                labels: @json($rows->pluck('name')),
                datasets: [
                    { label: @json(__('transport.custody_balance')), data: @json($rows->pluck('balance')), backgroundColor: '#1456E8', borderRadius: 6 },
                    { label: @json(__('transport.open_custody')), data: @json($rows->pluck('open_custody')), backgroundColor: '#F5811E', borderRadius: 6 },
                ],
            },
            options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    })();
    </script>
    @endpush
</x-app-layout>
