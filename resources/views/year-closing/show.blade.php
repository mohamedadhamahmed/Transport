@php
    $isAr = app()->getLocale() === 'ar';
    $t = fn ($ar, $en) => $isAr ? $ar : $en;
    $money = fn ($v) => number_format((float) $v, 2);
    $sections = [
        1 => $t('الأصول', 'Assets'),
        2 => $t('الخصوم', 'Liabilities'),
        5 => $t('حقوق الملكية', 'Equity'),
    ];
    $grouped = $closing->openingBalances->groupBy('category');
    $totalD = (float) $closing->openingBalances->sum('debit');
    $totalC = (float) $closing->openingBalances->sum('credit');
@endphp
<x-app-layout>
    @include('year-closing._style')

    <div class="py-6 fyc">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 fyc-stack">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-white font-bold text-lg">{{ $t('إقفال السنة المالية', 'Fiscal year closing') }} {{ $closing->fiscal_year }} — {{ $closing->reference }}</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button type="button" onclick="window.print()" class="fyc-btn fyc-btn-light">{{ $t('طباعة', 'Print') }}</button>
                    <a href="{{ route('year-closing.index') }}" class="fyc-btn fyc-btn-light">{{ $t('رجوع', 'Back') }}</a>
                </div>
            </div>

            @includeIf('partials.sweet-alert-flash')
            @if (session('success'))
                <div class="fyc-alert fyc-alert-info">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="fyc-alert fyc-alert-err">{{ $errors->first() }}</div>
            @endif

            <div class="fyc-grid">
                <div class="fyc-stat"><div class="l">{{ $t('الفترة', 'Period') }}</div><div class="v" style="font-size:15px">{{ optional($closing->date_from)->format('Y-m-d') ?? '—' }} → {{ $closing->closing_date->format('Y-m-d') }}</div></div>
                <div class="fyc-stat"><div class="l">{{ $t('إجمالي الإيرادات', 'Total revenue') }}</div><div class="v">{{ $money($closing->total_revenue) }}</div></div>
                <div class="fyc-stat"><div class="l">{{ $t('إجمالي المصروفات', 'Total expenses') }}</div><div class="v">{{ $money($closing->total_expenses) }}</div></div>
                <div class="fyc-stat {{ $closing->net_income >= 0 ? 'pos' : 'neg' }}">
                    <div class="l">{{ $t('صافي الربح المرحّل لـ', 'Net income moved to') }} "{{ $closing->retainedAccount->name ?? \App\Services\FiscalYearClosingService::RETAINED_EARNINGS_NAME }}"</div>
                    <div class="v">{{ $money($closing->net_income) }}</div>
                </div>
            </div>

            <div class="fyc-card">
                <div class="fyc-card-h">
                    <h3>{{ $t('الأرصدة الافتتاحية للسنة', 'Opening balances for') }} {{ $closing->fiscal_year + 1 }}</h3>
                    <span class="fyc-badge">{{ $t('مدين', 'Debit') }} {{ $money($totalD) }} · {{ $t('دائن', 'Credit') }} {{ $money($totalC) }}</span>
                </div>
                @if (abs($totalD - $totalC) >= 0.01)
                    <div class="fyc-card-b"><div class="fyc-alert fyc-alert-warn">{{ $t('الأرصدة المرحّلة مش متزنة - راجع ميزان المراجعة (غالبًا فيه حساب رصيده متسجل من غير حركة).', 'Carried-forward balances are not balanced - review the trial balance.') }}</div></div>
                @endif
                <div class="fyc-scroll">
                    <table class="fyc-table">
                        <thead>
                            <tr>
                                <th>{{ $t('رقم الحساب', 'Account #') }}</th>
                                <th>{{ $t('الحساب', 'Account') }}</th>
                                <th class="num">{{ $t('مدين', 'Debit') }}</th>
                                <th class="num">{{ $t('دائن', 'Credit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sections as $cat => $label)
                                @php $rows = $grouped->get($cat, collect()); @endphp
                                @if ($rows->isNotEmpty())
                                    <tr><td colspan="4" style="background:#F6F8FC;font-weight:700;color:#0F1B4C">{{ $label }}</td></tr>
                                    @foreach ($rows as $r)
                                        <tr>
                                            <td>{{ $r->account_number }}</td>
                                            <td>{{ $r->account_name }}</td>
                                            <td class="num">{{ (float) $r->debit ? $money($r->debit) : '' }}</td>
                                            <td class="num">{{ (float) $r->credit ? $money($r->credit) : '' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                            @endforelse
                            @if ($closing->openingBalances->isEmpty())
                                <tr><td colspan="4" style="text-align:center;color:#6B7280;padding:22px">{{ $t('مفيش أرصدة.', 'No balances.') }}</td></tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">{{ $t('الإجمالي', 'Total') }}</td>
                                <td class="num">{{ $money($totalD) }}</td>
                                <td class="num">{{ $money($totalC) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if ($closing->notes)
                <div class="fyc-card"><div class="fyc-card-b"><strong>{{ $t('ملاحظات:', 'Notes:') }}</strong> {{ $closing->notes }}</div></div>
            @endif

            @if ($isLast)
                <div class="fyc-card">
                    <div class="fyc-card-b" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                        <span style="font-size:13px;color:#6B7280">{{ $t('إلغاء الإقفال بيعكس قيد الإقفال ويرجّع أرصدة الإيرادات والمصروفات زي ما كانت.', 'Reopening reverses the closing entry and restores revenue/expense balances.') }}</span>
                        <form method="POST" action="{{ route('year-closing.destroy', $closing) }}" onsubmit="return confirm('{{ $t('متأكد من إلغاء الإقفال؟', 'Reopen this year?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="fyc-btn fyc-btn-danger">{{ $t('إلغاء الإقفال', 'Reopen year') }}</button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
