<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#0F1B4C]">{{ __('manufacturing.orders_title') }}</h2>
                    @can('manufacturing.orders')
                    <a href="{{ route('manufacturing.orders.create') }}" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-5 rounded-lg font-medium hover:opacity-90 transition shadow-sm text-sm">
                        {{ __('manufacturing.order_add') }}
                    </a>
                    @endcan
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm font-medium">{{ session('error') }}</div>
                @endif

                <form method="GET" class="mb-4 flex gap-3">
                    <select name="status_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">كل الحالات</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected(request('status_id') == $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4">{{ __('manufacturing.code') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.name') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.bom_product') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.order_quantity') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.total_cost') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.status') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                            <tr>
                                <td class="py-3 px-4 text-gray-500 text-sm">#{{ $order->code }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $order->name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $order->product->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $order->quantity }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ number_format($order->total_cost, 2) }}</td>
                                <td class="py-3 px-4">
                                    @if($order->status)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:{{ $order->status->color }}22; color:{{ $order->status->color }}">
                                            {{ $order->status->name }}
                                        </span>
                                    @endif
                                    @if($order->completed_at)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">مكتمل</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('manufacturing.orders.edit', $order) }}" class="text-[#1456E8] hover:underline text-sm font-medium">{{ __('manufacturing.edit') }}</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $orders->links() }}</div>

            </div>
        </div>
    </div>
</x-app-layout>
