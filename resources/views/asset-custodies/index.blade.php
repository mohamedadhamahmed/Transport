<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2" /><path d="M8 21h8M12 17v4" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">{{ __('asset_custodies.title') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('asset_custodies.subtitle') }}</p>
                </div>
            </div>

            @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            @can('asset_custodies.view')
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('asset_custodies.new_asset') }}</h3>
                <form method="POST" action="{{ route('asset-custodies.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.employee') }} *</label>
                        <select name="employee_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">-</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.item_name') }} *</label>
                        <input type="text" name="item_name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.category') }}</label>
                        <input type="text" name="category" list="asset-categories" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <datalist id="asset-categories">
                            <option value="{{ __('asset_custodies.category_laptop') }}">
                            <option value="{{ __('asset_custodies.category_vehicle') }}">
                            <option value="{{ __('asset_custodies.category_phone') }}">
                            <option value="{{ __('asset_custodies.category_other') }}">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.serial_number') }}</label>
                        <input type="text" name="serial_number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.value') }}</label>
                        <input type="number" step="0.01" min="0" name="value" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.condition') }}</label>
                        <select name="condition_on_issue" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="new">{{ __('asset_custodies.condition_new') }}</option>
                            <option value="good" selected>{{ __('asset_custodies.condition_good') }}</option>
                            <option value="used">{{ __('asset_custodies.condition_used') }}</option>
                            <option value="damaged">{{ __('asset_custodies.condition_damaged') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.issued_date') }} *</label>
                        <input type="date" name="issued_date" value="{{ now()->toDateString() }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.expected_return_date') }}</label>
                        <input type="date" name="expected_return_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('asset_custodies.notes') }}</label>
                        <input type="text" name="notes" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                            {{ __('asset_custodies.save') }}
                        </button>
                    </div>
                </form>
            </div>
            @endcan

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <select name="employee_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('asset_custodies.all_employees') }}</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string) request('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('asset_custodies.all_statuses') }}</option>
                        <option value="with_employee" {{ request('status') === 'with_employee' ? 'selected' : '' }}>{{ __('asset_custodies.status_with_employee') }}</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>{{ __('asset_custodies.status_returned') }}</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>{{ __('asset_custodies.status_lost') }}</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('asset_custodies.filter') }}
                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('asset_custodies.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('asset_custodies.item_name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('asset_custodies.serial_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('asset_custodies.value') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('asset_custodies.issued_date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('asset_custodies.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($assets as $asset)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $asset->employee->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $asset->item_name }} @if($asset->category)<span class="text-gray-400 text-xs">({{ $asset->category }})</span>@endif</td>
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $asset->serial_number ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $asset->value !== null ? number_format((float) $asset->value, 2) : '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($asset->issued_date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    @if($asset->status === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('asset_custodies.status_returned') }}</span>
                                    @elseif($asset->status === 'lost')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700">{{ __('asset_custodies.status_lost') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">{{ __('asset_custodies.status_with_employee') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        @can('asset_custodies.view')
                                        @if($asset->isWithEmployee())
                                        <form method="POST" action="{{ route('asset-custodies.return', $asset->id) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="condition_on_return" required class="rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                                                <option value="good">{{ __('asset_custodies.condition_good') }}</option>
                                                <option value="used">{{ __('asset_custodies.condition_used') }}</option>
                                                <option value="damaged">{{ __('asset_custodies.condition_damaged') }}</option>
                                                <option value="lost">{{ __('asset_custodies.condition_lost') }}</option>
                                            </select>
                                            <input type="hidden" name="returned_date" value="{{ now()->toDateString() }}">
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition whitespace-nowrap">
                                                {{ __('asset_custodies.return_asset') }}
                                            </button>
                                        </form>
                                        @endif
                                        <form method="POST" action="{{ route('asset-custodies.destroy', $asset->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition">
                                                {{ __('asset_custodies.delete') }}
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ __('asset_custodies.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($assets->hasPages())
                <div class="mt-4">{{ $assets->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
