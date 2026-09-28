@php
    $isAr = app()->getLocale() === 'ar';
    $t = fn ($ar, $en) => $isAr ? $ar : $en;
    $money = fn ($v) => number_format((float) $v, 2);
    $catName = fn ($c) => [3 => $t('إيرادات', 'Revenue'), 4 => $t('مصروفات', 'Expenses')][$c] ?? '';
@endphp
<x-app-layout>
    @include('year-closing._style')

    <div class="py-6 fyc">
        <div class="dc-max-w-page mx-auto sm:px-6 lg:px-8 fyc-stack">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/><path d="M9 15l2 2 4-4"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ $t('إقفال السنة المالية وترحيل الأرصدة', 'Fiscal year closing & carry forward') }}</h2>
                </div>
            </div>

            @includeIf('partials.sweet-alert-flash')

            @if (session('success'))
                <div class="fyc-alert fyc-alert-info">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="fyc-alert fyc-alert-err">{{ $errors->first() }}</div>
            @endif

            <div class="fyc-card">
                <div class="fyc-card-h"><h3>{{ $t('إيه اللي بيحصل في الإقفال؟', 'What does closing do?') }}</h3></div>
                <div class="fyc-card-b">
                    <ol class="fyc-steps">
                        <li>{{ $t('كل حسابات الإيرادات والمصروفات بتتصفّر بقيد بتاريخ يوم الإقفال.', 'All revenue and expense accounts are zeroed by an entry dated on the closing date.') }}</li>
                        <li>{{ $t('صافي الربح (أو الخسارة) بيتنقل لحساب "الأرباح المرحّلة" في حقوق الملكية.', 'Net profit (or loss) moves to the "Retained earnings" equity account.') }}</li>
                        <li>{{ $t('أرصدة الأصول والخصوم وحقوق الملكية بتترحّل للسنة الجديدة كأرصدة افتتاحية (بتكمل في نفس الحسابات من غير قيد مكرر).', 'Asset, liability and equity balances carry forward to the new year as opening balances (they continue in the same accounts, no duplicate entry).') }}</li>
                        <li>{{ $t('قائمة الدخل للفترات بتتجاهل قيد الإقفال، فأرقام السنة المقفولة بتفضل زي ما هي.', 'The income statement ignores the closing entry, so the closed year figures stay intact.') }}</li>
                    </ol>
                </div>
            </div>

            <div class="fyc-card">
                <div class="fyc-card-h">
                    <h3>{{ $t('معاينة الإقفال', 'Closing preview') }}</h3>
                    @if ($lastClosing)
                        <span class="fyc-badge">{{ $t('آخر إقفال:', 'Last closing:') }} {{ $lastClosing->closing_date->format('Y-m-d') }}</span>
                    @endif
                </div>
                <div class="fyc-card-b fyc-stack">
                    <form method="GET" class="fyc-form">
                        <div class="fyc-field">
                            <label>{{ $t('تاريخ الإقفال (آخر يوم في السنة المالية)', 'Closing date (last day of fiscal year)') }}</label>
                            <input type="date" name="closing_date" value="{{ $closingDate->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
                        </div>
                        <button type="submit" class="fyc-btn fyc-btn-light">{{ $t('معاينة', 'Preview') }}</button>
                    </form>

                    @if ($previewError)
                        <div class="fyc-alert fyc-alert-warn">{{ $previewError }}</div>
                    @elseif ($preview)
                        <div class="fyc-grid">
                            <div class="fyc-stat"><div class="l">{{ $t('الفترة', 'Period') }}</div><div class="v" style="font-size:15px">{{ $preview['date_from'] ?? '—' }} → {{ $preview['closing_date']->format('Y-m-d') }}</div></div>
                            <div class="fyc-stat"><div class="l">{{ $t('إجمالي الإيرادات', 'Total revenue') }}</div><div class="v">{{ $money($preview['total_revenue']) }}</div></div>
                            <div class="fyc-stat"><div class="l">{{ $t('إجمالي المصروفات', 'Total expenses') }}</div><div class="v">{{ $money($preview['total_expenses']) }}</div></div>
                            <div class="fyc-stat {{ $preview['net_income'] >= 0 ? 'pos' : 'neg' }}">
                                <div class="l">{{ $preview['net_income'] >= 0 ? $t('صافي الربح → الأرباح المرحّلة', 'Net profit → Retained earnings') : $t('صافي الخسارة → الأرباح المرحّلة', 'Net loss → Retained earnings') }}</div>
                                <div class="v">{{ $money(abs($preview['net_income'])) }}</div>
                            </div>
                        </div>

                        @if ($preview['accounts']->isEmpty())
                            <div class="fyc-alert fyc-alert-info">{{ $t('مفيش أرصدة إيرادات أو مصروفات للفترة دي - الإقفال هيسجل الأرصدة الافتتاحية بس.', 'No revenue/expense balances for this period - closing will only record opening balances.') }}</div>
                        @else
                            <div class="fyc-scroll">
                                <table class="fyc-table">
                                    <thead>
                                        <tr>
                                            <th>{{ $t('رقم الحساب', 'Account #') }}</th>
                                            <th>{{ $t('الحساب', 'Account') }}</th>
                                            <th>{{ $t('النوع', 'Type') }}</th>
                                            <th class="num">{{ $t('مدين (قيد الإقفال)', 'Debit (closing)') }}</th>
                                            <th class="num">{{ $t('دائن (قيد الإقفال)', 'Credit (closing)') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sumD = 0; $sumC = 0; @endphp
                                        @foreach ($preview['accounts'] as $a)
                                            @php
                                                $d = $a->balance < 0 ? -$a->balance : 0;
                                                $c = $a->balance > 0 ? $a->balance : 0;
                                                $sumD += $d; $sumC += $c;
                                            @endphp
                                            <tr>
                                                <td>{{ $a->account_number }}</td>
                                                <td>{{ $a->name }}</td>
                                                <td>{{ $catName($a->category) }}</td>
                                                <td class="num">{{ $d ? $money($d) : '' }}</td>
                                                <td class="num">{{ $c ? $money($c) : '' }}</td>
                                            </tr>
                                        @endforeach
                                        @php $ni = (float) $preview['net_income']; @endphp
                                        <tr>
                                            <td>{{ $preview['retained_account']->account_number ?? '—' }}</td>
                                            <td>{{ \App\Services\FiscalYearClosingService::RETAINED_EARNINGS_NAME }} @unless ($preview['retained_account']) <span class="fyc-badge">{{ $t('هيتعمل تلقائي', 'auto-created') }}</span> @endunless</td>
                                            <td>{{ $t('حقوق ملكية', 'Equity') }}</td>
                                            <td class="num">{{ $ni < 0 ? $money(-$ni) : '' }}</td>
                                            <td class="num">{{ $ni > 0 ? $money($ni) : '' }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">{{ $t('الإجمالي', 'Total') }}</td>
                                            <td class="num">{{ $money($sumD + ($ni < 0 ? -$ni : 0)) }}</td>
                                            <td class="num">{{ $money($sumC + ($ni > 0 ? $ni : 0)) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('year-closing.store') }}" class="fyc-stack"
                              onsubmit="return confirm('{{ $t('متأكد من إقفال السنة المالية؟', 'Close the fiscal year?') }}');">
                            @csrf
                            <input type="hidden" name="closing_date" value="{{ $preview['closing_date']->format('Y-m-d') }}">
                            <div class="fyc-field">
                                <label>{{ $t('ملاحظات (اختياري)', 'Notes (optional)') }}</label>
                                <textarea name="notes">{{ old('notes') }}</textarea>
                            </div>
                            <label class="fyc-check">
                                <input type="checkbox" name="confirm" value="1">
                                <span>{{ $t('راجعت ميزان المراجعة والأرقام اللي فوق، وعايز أقفل السنة لحد ', 'I reviewed the trial balance and the figures above and want to close the year up to ') }}{{ $preview['closing_date']->format('Y-m-d') }}</span>
                            </label>
                            <div>
                                <button type="submit" class="fyc-btn fyc-btn-primary">{{ $t('إقفال السنة وترحيل الأرصدة', 'Close year & carry forward') }}</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="fyc-card">
                <div class="fyc-card-h"><h3>{{ $t('الإقفالات السابقة', 'Previous closings') }}</h3></div>
                <div class="fyc-scroll">
                    <table class="fyc-table">
                        <thead>
                            <tr>
                                <th>{{ $t('السنة', 'Year') }}</th>
                                <th>{{ $t('من', 'From') }}</th>
                                <th>{{ $t('تاريخ الإقفال', 'Closing date') }}</th>
                                <th>{{ $t('المرجع', 'Reference') }}</th>
                                <th class="num">{{ $t('الإيرادات', 'Revenue') }}</th>
                                <th class="num">{{ $t('المصروفات', 'Expenses') }}</th>
                                <th class="num">{{ $t('صافي الربح', 'Net income') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($closings as $c)
                                <tr>
                                    <td><strong>{{ $c->fiscal_year }}</strong></td>
                                    <td>{{ optional($c->date_from)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $c->closing_date->format('Y-m-d') }}</td>
                                    <td>{{ $c->reference }}</td>
                                    <td class="num">{{ $money($c->total_revenue) }}</td>
                                    <td class="num">{{ $money($c->total_expenses) }}</td>
                                    <td class="num" style="color: {{ $c->net_income >= 0 ? '#0E9F6E' : '#DC2626' }}">{{ $money($c->net_income) }}</td>
                                    <td class="num"><a class="fyc-btn fyc-btn-light" style="padding:5px 12px;font-size:13px" href="{{ route('year-closing.show', $c) }}">{{ $t('التفاصيل', 'Details') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" style="text-align:center;color:#6B7280;padding:22px">{{ $t('لسه مفيش أي إقفال.', 'No closings yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
