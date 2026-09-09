<x-app-layout>
    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#0F1B4C]">{{ __('manufacturing.bom_title') }}</h2>
                    @can('manufacturing.bom')
                    <a href="{{ route('manufacturing.bom.create') }}" class="bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-5 rounded-lg font-medium hover:opacity-90 transition shadow-sm text-sm">
                        {{ __('manufacturing.bom_add') }}
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
                                <th class="py-3 px-4">{{ __('manufacturing.bom_production_quantity') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.bom_total_cost') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.status') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.bom_is_default') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($boms as $bom)
                            <tr>
                                <td class="py-3 px-4 text-gray-500 text-sm">#{{ $bom->code }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $bom->name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $bom->product->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $bom->production_quantity }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ number_format($bom->total_cost, 2) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $bom->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $bom->status === 'active' ? __('manufacturing.active') : __('manufacturing.inactive') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-500 text-sm">{{ $bom->is_default ? '✓' : '-' }}</td>
                                <td class="py-3 px-4 space-x-2 space-x-reverse">
                                    @can('manufacturing.bom')
                                    <a href="{{ route('manufacturing.bom.edit', $bom) }}" class="text-[#1456E8] hover:underline text-sm font-medium">{{ __('manufacturing.edit') }}</a>
                                    <form action="{{ route('manufacturing.bom.destroy', $bom) }}" method="POST" onsubmit="return confirm('{{ __('manufacturing.confirm_delete') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">{{ __('manufacturing.delete') }}</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $boms->links() }}</div>

            </div>
        </div>
    </div>
</x-app-layout>
