<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                
                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6">{{ __('taxes.title') }}</h2>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- نموذج إضافة ضريبة جديدة -->
                <form action="{{ route('taxes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('taxes.tax_name') }}</label>
                        <input type="text" name="name" placeholder="{{ __('taxes.tax_name_placeholder') }}" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('taxes.rate') }}</label>
                        <input type="number" step="0.01" name="rate" placeholder="{{ __('taxes.rate_placeholder') }}" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('taxes.priority') }}</label>
                        <input type="number" name="priority" placeholder="{{ __('taxes.priority_placeholder') }}" value="0" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-l from-[#1456E8] to-[#6B2FD6] text-white py-2.5 px-4 rounded-lg font-medium hover:opacity-90 transition shadow-sm">
                            {{ __('taxes.add_new') }}
                        </button>
                    </div>
                </form>

                <!-- جدول عرض الضرائب -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-sm">
                                <th class="py-3 px-4">{{ __('taxes.table_priority') }}</th>
                                <th class="py-3 px-4">{{ __('taxes.table_name') }}</th>
                                <th class="py-3 px-4">{{ __('taxes.table_rate') }}</th>
                                <th class="py-3 px-4">{{ __('taxes.table_status') }}</th>
                                <th class="py-3 px-4">{{ __('taxes.table_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($taxes as $tax)
                            <tr>
                                <td class="py-3 px-4 font-bold text-[#1456E8]">{{ $tax->priority }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $tax->name }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $tax->rate }}%</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $tax->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $tax->is_active ? __('taxes.active') : __('taxes.inactive') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <form action="{{ route('taxes.destroy', $tax->id) }}" method="POST" onsubmit="return confirm('{{ __('taxes.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">{{ __('taxes.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-sm">{{ __('taxes.empty') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>