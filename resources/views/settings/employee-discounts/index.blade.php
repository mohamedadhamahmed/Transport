<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('settings.employee_discounts_title') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('partials.sweet-alert-flash')

            {{-- اختيار الفرع --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <form method="GET" class="flex items-center gap-3">
                    <label class="text-sm text-gray-600">{{ __('settings.branch') }}:</label>
                    <select name="branch_id" onchange="this.form.submit()"
                        class="rounded-lg border-gray-300 text-sm focus:ring-[#1456E8] focus:border-[#1456E8]">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branch->id == $selectedBranchId)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- نسبة الخصم الافتراضية للفرع --}}
            <div class="bg-white shadow-sm sm:rounded-xl p-6">
                <div class="font-semibold text-gray-700 mb-4">{{ __('settings.branch_default_discount') }}</div>

                @can('settings.employee_discounts')
                <form method="POST" action="{{ route('employee-discounts.branch-default') }}"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="branchs_id" value="{{ $selectedBranchId }}">

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.max_default_discount') }}</label>
                        <input type="number" step="0.01" min="0" max="100" name="max_employee_discount"
                               value="{{ old('max_employee_discount', $branchDefault) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">{{ __('settings.requires_approval_above') }}</label>
                        <input type="number" step="0.01" min="0" max="100" name="requires_approval_above"
                               value="{{ old('requires_approval_above', $requiresApprovalAbove) }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-[#1456E8] focus:border-[#1456E8]">
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full rounded-lg py-2 text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                            {{ __('settings.save_default_discount') }}
                        </button>
                    </div>
                </form>
                @endcan
            </div>

            {{-- تخصيص نسبة لكل موظف --}}
            <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">
                    {{ __('settings.custom_employee_discounts') }}
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-start">{{ __('settings.employee') }}</th>
                                <th class="px-4 py-3 text-start">{{ __('settings.current_rate') }}</th>
                                <th class="px-4 py-3 text-start">{{ __('settings.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-500">
                                        @if (isset($overrides[$user->id]))
                                            {{ $overrides[$user->id] }}% <span class="text-xs text-[#1456E8]">({{ __('settings.custom_label') }})</span>
                                        @else
                                            {{ $branchDefault }}% <span class="text-xs text-gray-400">({{ __('settings.default_label') }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @can('settings.employee_discounts')
                                        <form method="POST" action="{{ route('employee-discounts.user-override') }}"
                                              class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="branchs_id" value="{{ $selectedBranchId }}">

                                            <input type="number" step="0.01" min="0" max="100"
                                                   name="max_discount"
                                                   value="{{ $overrides[$user->id] ?? '' }}"
                                                   placeholder="{{ __('settings.leave_empty_for_default') }}"
                                                   class="w-32 rounded-lg border-gray-300 text-sm focus:ring-[#1456E8] focus:border-[#1456E8]">

                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-white text-xs bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90">
                                                {{ __('settings.save') }}
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>