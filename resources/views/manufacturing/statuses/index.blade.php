<x-app-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6">

                <h2 class="text-xl font-bold text-[#0F1B4C] mb-6">{{ __('manufacturing.statuses_title') }}</h2>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm font-medium">{{ session('error') }}</div>
                @endif

                @can('manufacturing.statuses')
                <form action="{{ route('manufacturing.statuses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.status_name') }}</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.status_color') }}</label>
                        <input type="color" name="color" value="#1456E8" class="w-full h-10 rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('manufacturing.status_type') }}</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="open">مفتوحة</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="closed">مغلقة</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
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
                                <th class="py-3 px-4">{{ __('manufacturing.status_name') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.status_type') }}</th>
                                <th class="py-3 px-4">{{ __('manufacturing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($statuses as $status)
                            <tr>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $status->color }}"></span>
                                        {{ $status->name }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600 text-sm">{{ $status->type }}</td>
                                <td class="py-3 px-4">
                                    @can('manufacturing.statuses')
                                    @unless($status->is_default)
                                    <form action="{{ route('manufacturing.statuses.destroy', $status->id) }}" method="POST" onsubmit="return confirm('{{ __('manufacturing.confirm_delete') }}');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">{{ __('manufacturing.delete') }}</button>
                                    </form>
                                    @endunless
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="py-6 text-center text-gray-400 text-sm">{{ __('manufacturing.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
