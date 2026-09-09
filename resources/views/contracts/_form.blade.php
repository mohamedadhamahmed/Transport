@php $c = $contract ?? null; @endphp

<div>
    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.employee_name') }}</label>
    <select name="employee_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">{{ __('contracts.select_employee') }}</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected(old('employee_id', $c->employee_id ?? null) == $employee->id)>
                {{ $employee->name }}
            </option>
        @endforeach
    </select>
    @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.contract_type') }}</label>
    <input type="text" name="contract_type" value="{{ old('contract_type', $c->contract_type ?? '') }}" required
           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    @error('contract_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.start_date') }}</label>
        <input type="date" name="start_date" value="{{ old('start_date', optional($c->start_date ?? null)->format('Y-m-d')) }}" required
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.end_date') }}</label>
        <input type="date" name="end_date" value="{{ old('end_date', optional($c->end_date ?? null)->format('Y-m-d')) }}" required
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.residency_expiry') }}</label>
        <input type="date" name="residency_expiry" value="{{ old('residency_expiry', optional($c->residency_expiry ?? null)->format('Y-m-d')) }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('residency_expiry') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.work_permit_expiry') }}</label>
        <input type="date" name="work_permit_expiry" value="{{ old('work_permit_expiry', optional($c->work_permit_expiry ?? null)->format('Y-m-d')) }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('work_permit_expiry') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('contracts.notes') }}</label>
    <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes', $c->notes ?? '') }}</textarea>
</div>