<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- بيانات الأمر الأساسية --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#0F1B4C]">
                        {{ $order->name }}
                        <span class="text-sm font-normal text-gray-400">#{{ $order->code }}</span>
                    </h2>

                    @if($order->status)
                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold" style="background:{{ $order->status->color }}22; color:{{ $order->status->color }}">
                            {{ $order->status->name }}
                        </span>
                    @endif
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">{{ session('success') }}</div>
                @endif
                @if(session('error') || $errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm font-medium">
                        {{ session('error') }}
                        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <form action="{{ route('manufacturing.orders.update', $order) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    @method('PUT')
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.name') }}</label>
                        <input type="text" name="name" value="{{ $order->name }}" required {{ $order->completed_at ? 'disabled' : '' }} class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.plan_starts') }}</label>
                        <input type="date" name="date_start" value="{{ $order->date_start->format('Y-m-d') }}" required {{ $order->completed_at ? 'disabled' : '' }} class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.plan_ends') }}</label>
                        <input type="date" name="date_end" value="{{ $order->date_end->format('Y-m-d') }}" required {{ $order->completed_at ? 'disabled' : '' }} class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.order_workstation') }}</label>
                        <select name="workstation_id" {{ $order->completed_at ? 'disabled' : '' }} class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">—</option>
                            @foreach($workstations as $ws)
                                <option value="{{ $ws->id }}" @selected($ws->id === $order->workstation_id)>{{ $ws->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.status') }}</label>
                        <select name="status_id" {{ $order->completed_at ? 'disabled' : '' }} class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" @selected($status->id === $order->status_id)>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @unless($order->completed_at)
                    <div class="md:col-span-4">
                        <button type="submit" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-6 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            {{ __('manufacturing.save') }}
                        </button>
                    </div>
                    @endunless
                </form>
            </div>

            {{-- المواد الخام المطلوبة --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-[#0F1B4C] mb-4">{{ __('manufacturing.items_title') }}</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-2 px-3">المادة</th>
                                <th class="py-2 px-3">{{ __('manufacturing.item_required_quantity') }}</th>
                                <th class="py-2 px-3">{{ __('manufacturing.item_consumed_quantity') }}</th>
                                <th class="py-2 px-3">تكلفة الوحدة</th>
                                <th class="py-2 px-3">الإجمالي</th>
                                @unless($order->completed_at)
                                <th class="py-2 px-3">{{ __('manufacturing.actions') }}</th>
                                @endunless
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($order->items as $item)
                            <tr>
                                <td class="py-2 px-3 font-medium text-gray-800">{{ $item->product->name ?? '-' }}</td>
                                <td class="py-2 px-3 text-gray-600">{{ $item->required_quantity }}</td>
                                <td class="py-2 px-3 text-gray-600">{{ $item->consumed_quantity ?: $item->required_quantity }}</td>
                                <td class="py-2 px-3 text-gray-600">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="py-2 px-3 text-gray-600">{{ number_format($item->total_cost, 2) }}</td>
                                @unless($order->completed_at)
                                <td class="py-2 px-3">
                                    <form action="{{ route('manufacturing.orders.items.update', [$order, $item->id]) }}" method="POST" class="flex gap-2 items-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" step="0.001" name="consumed_quantity" value="{{ $item->consumed_quantity ?: $item->required_quantity }}" class="w-24 rounded-lg border-gray-300 text-sm">
                                        <button type="submit" class="text-[#1456E8] hover:underline text-sm">{{ __('manufacturing.save') }}</button>
                                    </form>
                                </td>
                                @endunless
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }} — اربط الأمر بقائمة مواد إنتاج (BOM) عشان تتحسب تلقائيًا</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- التكاليف غير المباشرة --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-[#0F1B4C] mb-4">{{ __('manufacturing.indirect_costs_title') }}</h3>

                @unless($order->completed_at)
                <form action="{{ route('manufacturing.orders.indirect-costs.store', $order) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4 p-4 bg-gray-50 rounded-xl">
                    @csrf
                    <input type="text" name="name" placeholder="{{ __('manufacturing.indirect_cost_name') }}" required class="rounded-lg border-gray-300 text-sm">
                    <input type="number" step="0.01" name="amount" placeholder="{{ __('manufacturing.indirect_cost_amount') }}" required class="rounded-lg border-gray-300 text-sm">
                    <input type="text" name="notes" placeholder="ملاحظات" class="rounded-lg border-gray-300 text-sm">
                    <button type="submit" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white rounded-lg text-sm font-medium hover:opacity-90 transition">
                        {{ __('manufacturing.indirect_cost_add') }}
                    </button>
                </form>
                @endunless

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-2 px-3">{{ __('manufacturing.indirect_cost_name') }}</th>
                                <th class="py-2 px-3">{{ __('manufacturing.indirect_cost_amount') }}</th>
                                <th class="py-2 px-3">ملاحظات</th>
                                @unless($order->completed_at)
                                <th class="py-2 px-3">{{ __('manufacturing.actions') }}</th>
                                @endunless
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($order->indirectCosts as $cost)
                            <tr>
                                <td class="py-2 px-3 font-medium text-gray-800">{{ $cost->name }}</td>
                                <td class="py-2 px-3 text-gray-600">{{ number_format($cost->amount, 2) }}</td>
                                <td class="py-2 px-3 text-gray-500 text-sm">{{ $cost->notes }}</td>
                                @unless($order->completed_at)
                                <td class="py-2 px-3">
                                    <form action="{{ route('manufacturing.orders.indirect-costs.destroy', [$order, $cost->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">{{ __('manufacturing.delete') }}</button>
                                    </form>
                                </td>
                                @endunless
                            </tr>
                            @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ملخص التكلفة وإتمام الأمر --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-[#0F1B4C] mb-4">{{ __('manufacturing.cost_summary') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="text-xs text-gray-500 mb-1">{{ __('manufacturing.direct_materials_cost') }}</div>
                        <div class="text-lg font-bold text-[#0F1B4C]">{{ number_format($order->direct_materials_cost, 2) }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="text-xs text-gray-500 mb-1">{{ __('manufacturing.indirect_costs_total') }}</div>
                        <div class="text-lg font-bold text-[#0F1B4C]">{{ number_format($order->indirect_costs_total, 2) }}</div>
                    </div>
                    <div class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] rounded-xl p-4 text-white">
                        <div class="text-xs opacity-90 mb-1">{{ __('manufacturing.total_cost') }}</div>
                        <div class="text-lg font-bold">{{ number_format($order->total_cost, 2) }}</div>
                    </div>
                </div>

                @if($order->completed_at)
                    <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">
                        تم إتمام الأمر بتاريخ {{ $order->completed_at->format('d/m/Y H:i') }} — تم سحب المواد الخام وإضافة {{ $order->quantity }} من {{ $order->product->name }} للمخزون.
                    </div>
                @else
                    <form action="{{ route('manufacturing.orders.complete', $order) }}" method="POST" onsubmit="return confirm('{{ __('manufacturing.confirm_complete') }}');">
                        @csrf
                        <button type="submit" class="bg-emerald-600 text-white py-2.5 px-6 rounded-lg font-medium hover:bg-emerald-700 transition shadow-sm">
                            {{ __('manufacturing.complete_order') }}
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
