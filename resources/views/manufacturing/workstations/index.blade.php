<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6">{{ __('manufacturing.workstations_title') }}</h2>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                @can('manufacturing.workstations')
                <form action="{{ route('manufacturing.workstations.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.workstation_name') }}</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.workstation_description') }}</label>
                        <input type="text" name="description" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.workstation_cost') }}</label>
                        <input type="number" step="0.01" name="cost" value="0" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-4 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            {{ __('manufacturing.add_new') }}
                        </button>
                    </div>
                </form>
                @endcan

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4">{{ __('manufacturing.code') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.name') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.workstation_cost') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.status') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($workstations as $ws)
                            <tr>
                                <td class="py-3 px-4 text-gray-500 text-sm">#{{ $ws->code }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $ws->name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ number_format($ws->cost, 2) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $ws->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $ws->status === 'active' ? __('manufacturing.active') : __('manufacturing.inactive') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @can('manufacturing.workstations')
                                    <form action="{{ route('manufacturing.workstations.destroy', $ws->id) }}" method="POST" onsubmit="return confirm('{{ __('manufacturing.confirm_delete') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">{{ __('manufacturing.delete') }}</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $workstations->links() }}</div>

            </div>
        </div>
    </div>
</x-app-layout>
