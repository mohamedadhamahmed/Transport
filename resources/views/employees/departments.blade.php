<x-app-layout>
    @include('transport.partials.styles')
    <style>
        .dp-table { width:100%; border-collapse:collapse; font-size:.875rem; }
        .dp-table th { background:#0F1B4C; color:rgba(255,255,255,.85); font-size:.75rem; font-weight:600; padding:.7rem .9rem; text-align:start; }
        .dp-table td { padding:.7rem .9rem; border-top:1px solid #f1f5f9; vertical-align:middle; }
        .dp-edit summary { cursor:pointer; list-style:none; }
        .dp-edit summary::-webkit-details-marker { display:none; }
        .dp-edit[open] summary { margin-bottom:.6rem; }
        .dp-row-off td { opacity:.6; }
        .dp-actions { display:flex; gap:.4rem; align-items:flex-start; flex-wrap:wrap; }
        .dp-actions form { display:inline; }
    </style>

    <div class="py-6">
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('transport.partials.page-header', [
                'title' => __('employees.departments_title'),
                'subtitle' => __('employees.departments_subtitle'),
            ])

            @include('transport.partials.flash')

            @can('departments.create')
                <form method="POST" action="{{ route('employees.departments.store') }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-5">
                    @csrf
                    <h3 class="font-bold text-[#0F1B4C] text-sm mb-3">{{ __('employees.new_department') }}</h3>
                    <div class="tr-grid tr-grid-3">
                        <div>
                            <label class="tr-label">{{ __('employees.department_name') }} *</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="tr-input" required>
                            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="tr-label">{{ __('employees.department_name_en') }}</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}" class="tr-input">
                        </div>
                        <div>
                            <label class="tr-label">{{ __('employees.notes') }}</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="tr-input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="tr-btn tr-btn-blue">{{ __('employees.add_department') }}</button>
                    </div>
                </form>
            @endcan

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="dp-table">
                        <thead>
                            <tr>
                                <th>{{ __('employees.department') }}</th>
                                <th>{{ __('employees.department_name_en') }}</th>
                                <th style="text-align:center">{{ __('employees.department_employees') }}</th>
                                <th style="text-align:center">{{ __('employees.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($departments as $department)
                                @php
                                    $c = $counts[$department->name] ?? null;
                                    $isDrivers = $department->name === \App\Models\Department::DRIVERS;
                                @endphp
                                <tr class="{{ $department->is_active ? '' : 'dp-row-off' }}">
                                    <td>
                                        <span class="font-bold text-[#0F1B4C]">{{ $department->name }}</span>
                                        @if ($isDrivers)
                                            <span class="tr-badge tr-badge-amber">{{ __('employees.department_auto') }}</span>
                                        @endif
                                        @if ($department->notes)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $department->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="text-gray-500">{{ $department->name_en ?: '-' }}</td>
                                    <td style="text-align:center">
                                        <a href="{{ route('employees.index', ['department' => $department->name]) }}" class="font-bold text-[#1456E8] hover:underline">
                                            {{ (int) ($c->active ?? 0) }}
                                        </a>
                                        <span class="text-xs text-gray-400">/ {{ (int) ($c->total ?? 0) }}</span>
                                    </td>
                                    <td style="text-align:center">
                                        @if ($department->is_active)
                                            <span class="tr-badge tr-badge-green">{{ __('employees.status_active') }}</span>
                                        @else
                                            <span class="tr-badge tr-badge-gray">{{ __('employees.status_inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @canany(['departments.edit', 'departments.delete'])
                                            <div class="dp-actions">
                                                @can('departments.edit')
                                                <details class="dp-edit">
                                                    <summary class="tr-btn tr-btn-gray">{{ __('employees.edit') }}</summary>
                                                    <form method="POST" action="{{ route('employees.departments.update', $department) }}" style="display:block;min-width:260px">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="name" value="{{ $department->name }}" class="tr-input" {{ $isDrivers ? 'readonly' : '' }} required>
                                                        <input type="text" name="name_en" value="{{ $department->name_en }}" class="tr-input mt-2" placeholder="{{ __('employees.department_name_en') }}">
                                                        <input type="text" name="notes" value="{{ $department->notes }}" class="tr-input mt-2" placeholder="{{ __('employees.notes') }}">
                                                        @unless ($isDrivers)
                                                            <input type="hidden" name="is_active" value="0">
                                                            <label class="flex items-center gap-2 text-xs mt-2">
                                                                <input type="checkbox" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }}>
                                                                {{ __('employees.status_active') }}
                                                            </label>
                                                        @endunless
                                                        <button type="submit" class="tr-btn tr-btn-blue mt-2">{{ __('employees.save') }}</button>
                                                    </form>
                                                </details>
                                                @endcan
                                                @if (!$isDrivers && auth()->user()?->can('departments.delete'))
                                                    <form method="POST" action="{{ route('employees.departments.destroy', $department) }}"
                                                          onsubmit="return confirm(@js(__('employees.department_delete_confirm')))">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="tr-btn tr-btn-red">{{ __('employees.delete') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endcanany
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-gray-400" style="padding:2rem">{{ __('employees.no_departments') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($unassigned > 0)
                    <div class="px-4 py-3 text-xs text-gray-500 border-t border-gray-100">
                        {{ __('employees.department_unassigned', ['count' => $unassigned]) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
