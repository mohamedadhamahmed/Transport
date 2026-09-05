<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('payroll.slip_title') }} - {{ $employee->name }} - {{ $month }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @include('partials.print-styles')
</head>
<body>

<div class="doc-wrap" id="print-area">

    <div class="doc-topbar">
        <button onclick="window.print()">
            {{ __('payroll.slip_print') }}
        </button>
    </div>

    @include('partials.print-header')

    <div class="doc-type-badge">
        <span>{{ __('payroll.slip_title') }}</span>
        <span class="doc-no">{{ __('payroll.slip_period') }}: {{ $monthStart->format('Y-m') }}</span>
    </div>

    <div class="meta-grid">
        <div>
            <div class="label">{{ __('employees.employee_number') }}</div>
            <div class="value">{{ $employee->employee_number }}</div>
        </div>
        <div>
            <div class="label">{{ __('employees.name') }}</div>
            <div class="value">{{ $employee->name }}</div>
        </div>
        <div>
            <div class="label">{{ __('employees.job_title') }}</div>
            <div class="value">{{ $employee->job_title ?: '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('employees.branch') }}</div>
            <div class="value">{{ $employee->branch->name ?? '-' }}</div>
        </div>
    </div>

    <div class="items-table-wrap">
        <p style="font-size:12px;font-weight:700;color:var(--brand-navy);margin:0 0 8px;">{{ __('payroll.slip_additions') }}</p>
        <table class="items-table">
            <thead>
                <tr>
                    <th>{{ __('payroll.basic_salary') }}</th>
                    <th>{{ __('payroll.allowances') }}</th>
                    <th>{{ __('payroll.bonus') }}</th>
                    <th>{{ __('payroll.overtime_amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($row['basic_salary'], 2) }}</td>
                    <td>{{ number_format($row['allowances'], 2) }}</td>
                    <td>{{ number_format($row['bonus'], 2) }}</td>
                    <td>{{ number_format($row['overtime_amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="items-table-wrap">
        <p style="font-size:12px;font-weight:700;color:var(--brand-navy);margin:16px 0 8px;">{{ __('payroll.slip_deductions') }}</p>
        <table class="items-table">
            <thead>
                <tr>
                    <th>{{ __('payroll.absence_deduction') }}</th>
                    <th>{{ __('payroll.late_deduction') }}</th>
                    <th>{{ __('payroll.unpaid_leave_deduction') }}</th>
                    <th>{{ __('payroll.loan_deduction') }}</th>
                    <th>{{ __('payroll.total_deductions') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($row['absence_deduction'], 2) }}</td>
                    <td>{{ number_format($row['late_deduction'], 2) }}</td>
                    <td>{{ number_format($row['unpaid_leave_deduction'], 2) }}</td>
                    <td>{{ number_format($row['loan_deduction'], 2) }}</td>
                    <td>{{ number_format($row['total_deductions'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="amount-box">
        <div class="label">{{ __('payroll.slip_net_pay') }}</div>
        <div class="value">{{ number_format($row['net_pay'], 2) }}</div>
    </div>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line">{{ __('payroll.signature_employee') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('payroll.signature_hr') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('payroll.signature_manager') }}</div>
        </div>
    </div>

    <div class="doc-footer">
        {{ now()->format('Y-m-d H:i') }}
    </div>

</div>

</body>
</html>
