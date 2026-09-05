<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1680px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="17" rx="2" /><path d="M8 9h8M8 13h8M8 17h5" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">{{ __('payroll.title') }}</h2>
                    <p class="text-white/45 text-xs mt-0.5">{{ __('payroll.subtitle') }}</p>
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

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('payroll.month') }}</label>
                        <input type="month" name="month" value="{{ $month }}"
                               class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('payroll.branch') }}</label>
                        <select name="branch_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('payroll.all_branches') }}</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('payroll.employee') }}</label>
                        <select name="employee_id" class="rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('payroll.all_employees') }}</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) request('employee_id') === (string) $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium">
                        {{ __('payroll.filter') }}
                    </button>

                    <div class="flex-1"></div>

                    @if($posting)
                    <div class="flex items-center gap-3">
                        @if($posting->isAwaitingPayment())
                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-amber-50 text-amber-700">
                            {{ __('payroll.awaiting_payment_badge') }} - {{ __('payroll.posted_on', ['date' => $posting->created_at->format('Y-m-d'), 'document' => $posting->document_number]) }}
                        </span>
                        <form method="POST" action="{{ route('payroll.posting.pay', $posting->id) }}" class="flex items-center gap-2">
                            @csrf
                            <select name="treasury_account_id" required class="rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                                <option value="">{{ __('payroll.treasury_account') }}</option>
                                @foreach($treasuryAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-2 rounded-lg text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition whitespace-nowrap">
                                {{ __('payroll.pay_now') }}
                            </button>
                        </form>
                        @else
                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700">
                            {{ __('payroll.already_posted_badge') }} - {{ __('payroll.posted_on', ['date' => $posting->created_at->format('Y-m-d'), 'document' => $posting->document_number]) }}
                            @if($posting->paid_at)
                            - {{ __('payroll.paid_on', ['date' => $posting->paid_at->format('Y-m-d')]) }}
                            @endif
                        </span>
                        @endif
                        <form method="POST" action="{{ route('payroll.posting.destroy', $posting->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-lg text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 transition">
                                {{ __('payroll.cancel_posting') }}
                            </button>
                        </form>
                    </div>
                    @else
                    <form method="POST" action="{{ route('payroll.post') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <select name="treasury_account_id" class="rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                            <option value="">{{ __('payroll.defer_payment_option') }}</option>
                            @foreach($treasuryAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-lg text-white text-sm font-medium bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                            {{ __('payroll.post_month') }}
                        </button>
                    </form>
                    @endif
                </form>
            </div>

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.employee') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.basic_salary') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.allowances') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.bonus') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.overtime_amount') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.absence_deduction') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.late_deduction') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.unpaid_leave_deduction') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.loan_deduction') }}</th>
                                <th class="px-3 py-3 text-start text-xs font-semibold uppercase">{{ __('payroll.net_pay') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($rows as $row)
                            <tr class="hover:bg-[#1456E8]/5 transition">
                                <td class="px-3 py-3 font-medium text-gray-800">{{ $row['employee']->name }}</td>
                                <td class="px-3 py-3 text-gray-600">{{ number_format($row['basic_salary'], 2) }}</td>
                                <td class="px-3 py-3 text-gray-600">{{ number_format($row['allowances'], 2) }}</td>
                                <td class="px-3 py-3">
                                    <form method="POST" action="{{ route('payroll.bonus.store') }}" class="flex items-center gap-1">
                                        @csrf
                                        <input type="hidden" name="employee_id" value="{{ $row['employee']->id }}">
                                        <input type="hidden" name="month" value="{{ $month }}">
                                        <input type="number" step="0.01" min="0" name="amount" value="{{ $row['bonus'] }}"
                                               class="w-20 rounded-lg border-gray-300 shadow-sm text-xs focus:border-[#1456E8] focus:ring-[#1456E8]">
                                        <button type="submit" title="{{ __('payroll.save_bonus') }}" class="px-2 py-1 rounded-lg text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition text-xs">
                                            ✓
                                        </button>
                                    </form>
                                </td>
                                <td class="px-3 py-3 text-emerald-700">{{ number_format($row['overtime_amount'], 2) }}</td>
                                <td class="px-3 py-3 text-rose-600">{{ number_format($row['absence_deduction'], 2) }}</td>
                                <td class="px-3 py-3 text-rose-600">{{ number_format($row['late_deduction'], 2) }}</td>
                                <td class="px-3 py-3 text-rose-600">{{ number_format($row['unpaid_leave_deduction'], 2) }}</td>
                                <td class="px-3 py-3 text-rose-600">{{ number_format($row['loan_deduction'], 2) }}</td>
                                <td class="px-3 py-3 font-semibold text-gray-800">{{ number_format($row['net_pay'], 2) }}</td>
                                <td class="px-3 py-3">
                                    <a href="{{ route('payroll.slip', ['employee' => $row['employee']->id, 'month' => $month]) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition whitespace-nowrap">
                                        {{ __('payroll.print_slip') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="11" class="px-4 py-10 text-center text-gray-400">{{ __('payroll.no_data') }}</td></tr>
                            @endforelse
                        </tbody>
                        @if($rows->isNotEmpty())
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold text-gray-800">
                                <td class="px-3 py-3">{{ __('payroll.totals_row') }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['basic_salary'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['allowances'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['bonus'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['overtime_amount'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['absence_deduction'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['late_deduction'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['unpaid_leave_deduction'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['loan_deduction'], 2) }}</td>
                                <td class="px-3 py-3">{{ number_format($totals['net_pay'], 2) }}</td>
                                <td class="px-3 py-3"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
