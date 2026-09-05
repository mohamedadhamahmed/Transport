<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @if($employee->exists)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.employee_number') }}</label>
        <input type="text" value="{{ $employee->employee_number }}" disabled
               class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-500 shadow-sm">
    </div>
    @endif
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.name') }} *</label>
        <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.name_en') }}</label>
        <input type="text" name="name_en" value="{{ old('name_en', $employee->name_en ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.national_id') }}</label>
        <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.phone') }}</label>
        <input type="text" name="phone" value="{{ old('phone', $employee->phone ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('employees.job_title') }} / {{ __('employees.department') }}</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.job_title') }}</label>
        <input type="text" name="job_title" value="{{ old('job_title', $employee->job_title ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.department') }}</label>
        <input type="text" name="department" value="{{ old('department', $employee->department ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.branch') }}</label>
        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <option value="">-</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" {{ (string) old('branch_id', $employee->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.hire_date') }}</label>
        <input type="date" name="hire_date" value="{{ old('hire_date', optional($employee->hire_date ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <hr class="my-2 border-gray-100">
        <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('employees.basic_salary') }} / {{ __('employees.pay_method') }}</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.basic_salary') }}</label>
        <input type="number" step="0.01" min="0" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.allowances') }}</label>
        <input type="number" step="0.01" min="0" name="allowances" value="{{ old('allowances', $employee->allowances ?? 0) }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.pay_method') }}</label>
        <select name="pay_method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            <option value="Cash" {{ old('pay_method', $employee->pay_method ?? 'Cash') === 'Cash' ? 'selected' : '' }}>{{ __('employees.pay_method_cash') }}</option>
            <option value="Bank" {{ old('pay_method', $employee->pay_method ?? 'Cash') === 'Bank' ? 'selected' : '' }}>{{ __('employees.pay_method_bank') }}</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.bank_name') }}</label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.iban') }}</label>
        <input type="text" name="iban" value="{{ old('iban', $employee->iban ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.national_address') }}</label>
        <input type="text" name="national_address" value="{{ old('national_address', $employee->national_address ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>

    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employees.notes') }}</label>
        <input type="text" name="notes" value="{{ old('notes', $employee->notes ?? '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
        {{ __('employees.save') }}
    </button>
    <a href="{{ route('employees.index') }}" class="px-5 py-2 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        {{ __('employees.cancel') }}
    </a>
</div>
