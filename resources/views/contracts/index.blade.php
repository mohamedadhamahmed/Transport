<x-app-layout>
    <div class="p-6" dir="rtl">

        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('contracts.create') }}"
                   class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    + {{ __('contracts.add_new') }}
                </a>
                <a href="{{ route('notifications.index') }}"
                    class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    🔔 {{ __('notifications.title') }}
                </a>
            </div>
            <h1 class="text-lg font-bold text-[#0F1B4C]">{{ __('contracts.title') }}</h1>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg px-4 py-2 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.employee_name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.contract_type') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.start_date') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.end_date') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.residency_expiry') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.work_permit_expiry') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('contracts.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($contracts as $contract)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $contract->employee->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $contract->contract_type }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $contract->start_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3"><x-date-badge :date="$contract->end_date" /></td>
                            <td class="px-4 py-3"><x-date-badge :date="$contract->residency_expiry" /></td>
                            <td class="px-4 py-3"><x-date-badge :date="$contract->work_permit_expiry" /></td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="{{ route('contracts.edit', $contract) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500 hover:bg-blue-600 text-white">✎</a>
                                <form action="{{ route('contracts.destroy', $contract) }}" method="POST"
                                      onsubmit="return confirm('{{ __('contracts.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-500 hover:bg-red-600 text-white">🗑</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">{{ __('contracts.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $contracts->links() }}</div>
    </div>
</x-app-layout>