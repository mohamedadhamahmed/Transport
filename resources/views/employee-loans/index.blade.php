<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('employee_loans.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('employee_loans.subtitle') }}</p>
                    </div>
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

            @can('employee_loans.create')
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('employee_loans.new_loan') }}</h3>
                <form method="POST" action="{{ route('employee-loans.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.employee') }} *</label>
                        <select name="employee_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">-</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.type') }} *</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="loan">{{ __('employee_loans.type_loan') }}</option>
                            <option value="custody">{{ __('employee_loans.type_custody') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.treasury_account') }} *</label>
                        <select name="treasury_account_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                            <option value="">-</option>
                            @foreach($treasuryAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.amount') }} *</label>
                        <input type="number" step="0.01" min="0" name="amount" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.monthly_installment') }}</label>
                        <input type="number" step="0.01" min="0" name="monthly_installment" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <p class="text-xs text-gray-400 mt-1">{{ __('employee_loans.monthly_installment_hint') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.date') }} *</label>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.description') }}</label>
                        <input type="text" name="description" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('employee_loans.notes') }}</label>
                        <input type="text" name="notes" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="px-5 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition">
                            {{ __('employee_loans.save') }}
                        </button>
                    </div>
                </form>
            </div>
            @endcan

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap gap-3 mb-4">
                    <select name="employee_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('employee_loans.all_employees') }}</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string) request('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                    <select name="type" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('employee_loans.all_types') }}</option>
                        <option value="loan" {{ request('type') === 'loan' ? 'selected' : '' }}>{{ __('employee_loans.type_loan') }}</option>
                        <option value="custody" {{ request('type') === 'custody' ? 'selected' : '' }}>{{ __('employee_loans.type_custody') }}</option>
                    </select>
                    <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                        <option value="">{{ __('employee_loans.all_statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('employee_loans.status_active') }}</option>
                        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>{{ __('employee_loans.status_settled') }}</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('employee_loans.filter') }}
                    </button>
                </form>

                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.document_number') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.employee') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.type') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.treasury_account') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.monthly_installment') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.remaining_amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.date') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('employee_loans.status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($loans as $loan)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $loan->document_number }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $loan->employee->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ __('employee_loans.type_' . $loan->type) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $loan->treasuryAccount->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format((float) $loan->amount, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $loan->monthly_installment !== null ? number_format((float) $loan->monthly_installment, 2) : '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($loan->remainingAmount(), 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($loan->date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    @if($loan->status === 'settled')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('employee_loans.status_settled') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">{{ __('employee_loans.status_active') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        @can('employee_loans.create')
                                        @if($loan->status !== 'settled')
                                        <form method="POST" action="{{ route('employee-loans.settle', $loan->id) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="settled_treasury_account_id" required
                                                    class="rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                                                <option value="">{{ __('employee_loans.settle_to_account') }}</option>
                                                @foreach($treasuryAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition whitespace-nowrap">
                                                {{ __('employee_loans.settle') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employee-loans.destroy', $loan->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition">
                                                {{ __('employee_loans.delete') }}
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="10" class="px-4 py-10 text-center text-gray-400">{{ __('employee_loans.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($loans->hasPages())
                <div class="mt-4">{{ $loans->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
