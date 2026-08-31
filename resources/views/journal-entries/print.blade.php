<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $entry->isOpening() ? __('journal_entries.opening_badge') : __('journal_entries.daily_badge') }} #{{ $entry->entry_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @include('partials.print-styles')
</head>
<body>

<div class="doc-wrap" id="print-area">

    <div class="doc-topbar">
        <button onclick="window.print()">
            {{ __('journal_entries.print') }}
        </button>
    </div>

    @include('partials.print-header')

    <div class="doc-type-badge">
        <span>{{ $entry->isOpening() ? __('journal_entries.opening_badge') : __('journal_entries.daily_badge') }}</span>
        <span class="doc-no">{{ __('journal_entries.entry_no') }}: #{{ $entry->entry_number }}</span>
    </div>

    <div class="meta-grid">
        <div>
            <div class="label">{{ __('journal_entries.entry_date') }}</div>
            <div class="value">{{ $entry->entry_date->format('Y-m-d') }}</div>
        </div>
        <div>
            <div class="label">{{ __('journal_entries.branch') }}</div>
            <div class="value">{{ $entry->branch?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('journal_entries.cost_center') }}</div>
            <div class="value">{{ $entry->costCenter?->cost_center_ar ?? '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('journal_entries.created_by') }}</div>
            <div class="value">{{ $entry->creator?->name ?? '-' }}</div>
        </div>
    </div>

    <div class="items-table-wrap">
        <table class="items-table">
            <thead>
                <tr>
                    <th>{{ __('journal_entries.account') }}</th>
                    <th>{{ __('journal_entries.note') }}</th>
                    <th>{{ __('journal_entries.debit') }}</th>
                    <th>{{ __('journal_entries.credit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entry->lines as $line)
                    <tr>
                        <td>{{ $line->account?->name ?? '#' . $line->account_id }}</td>
                        <td>{{ $line->note ?? '-' }}</td>
                        <td>{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                        <td>{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">{{ __('journal_entries.total_debit') }} / {{ __('journal_entries.total_credit') }}</td>
                    <td>{{ number_format($entry->total_debit, 2) }}</td>
                    <td>{{ number_format($entry->total_credit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if ($entry->description)
        <div class="note-box">
            <div class="label">{{ __('journal_entries.description') }}</div>
            <div>{{ $entry->description }}</div>
        </div>
    @endif

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line">{{ __('journal_entries.signature_preparer') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('journal_entries.signature_reviewer') }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">{{ __('journal_entries.signature_manager') }}</div>
        </div>
    </div>

    <div class="doc-footer">
        {{ __('journal_entries.print_footer_note', ['date' => now()->format('Y-m-d H:i')]) }}
    </div>

</div>

</body>
</html>
