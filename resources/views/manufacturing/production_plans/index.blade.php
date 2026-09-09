<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#0F1B4C]">{{ __('manufacturing.plans_title') }}</h2>
                    @can('manufacturing.production-plans')
                    <a href="{{ route('manufacturing.production-plans.create') }}" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-5 rounded-lg font-medium hover:opacity-90 transition shadow-sm text-sm">
                        {{ __('manufacturing.plan_add') }}
                    </a>
                    @endcan
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">{{ session('success') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4">{{ __('manufacturing.code') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.name') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.bom_product') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.order_quantity') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.plan_date') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.status') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($plans as $plan)
                            <tr>
                                <td class="py-3 px-4 text-gray-500 text-sm">#{{ $plan->code }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $plan->name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $plan->product->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $plan->quantity }}</td>
                                <td class="py-3 px-4 text-gray-600 text-sm">
                                    {{ __('manufacturing.plan_starts') }}: {{ $plan->date_start->format('d/m/Y') }}<br>
                                    {{ __('manufacturing.plan_ends') }}: {{ $plan->date_end->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($plan->status)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background:{{ $plan->status->color }}22; color:{{ $plan->status->color }}">
                                            {{ $plan->status->name }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 space-x-2 space-x-reverse">
                                    @can('manufacturing.production-plans')
                                    <a href="{{ route('manufacturing.production-plans.edit', $plan) }}" class="text-[#1456E8] hover:underline text-sm font-medium">{{ __('manufacturing.edit') }}</a>
                                    <form action="{{ route('manufacturing.production-plans.convert', $plan) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:underline text-sm font-medium">{{ __('manufacturing.plan_convert') }}</button>
                                    </form>
                                    <form action="{{ route('manufacturing.production-plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('{{ __('manufacturing.confirm_delete') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">{{ __('manufacturing.delete') }}</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $plans->links() }}</div>

            </div>
        </div>
    </div>
</x-app-layout>
