<x-app-layout>
    <div class="py-6">
        <div class="max-w-[900px] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg">
                <h2 class="text-white font-bold text-lg">{{ __('end_of_service.new_settlement') }}</h2>
            </div>

            @if($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('end-of-service.store') }}" class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('end_of_service.employee') }} *</label>
                        <select name="employee_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">-</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) old('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->employee_number }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('end_of_service.termination_type') }} *</label>
                        <select name="termination_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="resignation">{{ __('end_of_service.type_resignation') }}</option>
                            <option value="termination">{{ __('end_of_service.type_termination') }}</option>
                            <option value="contract_end">{{ __('end_of_service.type_contract_end') }}</option>
                            <option value="death">{{ __('end_of_service.type_death') }}</option>
                            <option value="termination_for_cause">{{ __('end_of_service.type_termination_for_cause') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('end_of_service.termination_date') }} *</label>
                        <input type="date" name="termination_date" value="{{ old('termination_date', now()->toDateString()) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('end_of_service.treasury_account') }} *</label>
                        <select name="treasury_account_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">-</option>
                            @foreach($treasuryAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('end_of_service.wage_basis') }}</label>
                        <input type="number" step="0.01" min="0" name="wage_override" value="{{ old('wage_override') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <p class="text-xs text-gray-400 mt-1">{{ __('end_of_service.wage_override_hint') }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('end_of_service.notes') }}</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6">
                    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                        {{ __('end_of_service.save') }}
                    </button>
                    <a href="{{ route('end-of-service.index') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        {{ __('employees.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
