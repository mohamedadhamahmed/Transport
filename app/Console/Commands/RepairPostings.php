<?php

namespace App\Console\Commands;

use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\Purchase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إصلاح القيود القديمة اللي خلّت ميزان المراجعة والميزانية العمومية غير
 * متزنين (مرة واحدة - آمن تشغيله أكتر من مرة لإن كل حركة بتتعلّم
 * balance_posted=1 بعد ما تتعالج):
 *
 *  1) فواتير المبيعات القديمة: حركة الخزينة/البنك/الإيرادات/التكلفة/المخزون
 *     كانت بتتسجل في credittransactions من غير ما رصيد الحساب نفسه يتحدّث
 *     (الضريبة والعميل بس اللي كانوا بيتحدّثوا) - بنرحّلها على أرصدتها.
 *  2) مرتجع المبيعات: المبلغ المردود نقدي/بنك مكنش بيتخصم من رصيد الخزينة.
 *  3) المشتريات الفورية: حساب الدفع (خزينة/بنك) كان بيتسجل مدين بدل دائن -
 *     بنقلب الحركة والرصيد.
 *
 * في الآخر بيطبع ميزان المراجعة قبل/بعد + أي نوع عملية قيوده نفسها غير
 * متوازنة (عشان لو فضل فرق نعرف مصدره).
 *
 *   php artisan accounts:repair-postings --dry-run   (عرض بس من غير حفظ)
 *   php artisan accounts:repair-postings
 *   php artisan accounts:repair-postings --post-openings   (كمان يقفل الأرصدة الافتتاحية اللي في طرف واحد)
 */
class RepairPostings extends Command
{
    protected $signature = 'accounts:repair-postings
        {--dry-run : اعرض النتيجة من غير ما تحفظ}
        {--keep-seed-balances : متصفّرش الأرصدة اللي جت من سيدر شجرة الحسابات}
        {--post-openings : سجّل الطرف المقابل لأرصدة الحسابات اللي ملهاش حركات (أرصدة افتتاحية) على حساب "أرصدة افتتاحية"}';

    protected $description = 'ترحيل القيود القديمة الناقصة على أرصدة الحسابات عشان ميزان المراجعة والميزانية يتزنوا';

    /** أنواع عمليات المبيعات اللي الكود القديم مكنش بيرحّل فيها غير الضريبة والعميل. */
    private const SALES_TYPES = [1, 15, 16]; // 15 = فاتورة نقليات، 16 = إشعار دائن نقليات (OperationType)

    /** نوع عملية مرتجع المبيعات (0 = مفيش مرتجعات في المشروع ده). */
    private const SALES_RETURN_TYPE = 0;

    private const PURCHASE_TYPE = 3;

    private array $seedRows = [];

    public function handle(): int
    {
        $table = (new CreditTransaction())->getTable();
        if (!Schema::hasColumn($table, 'balance_posted')) {
            $this->error('شغّل php artisan migrate الأول (عمود balance_posted مش موجود).');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $before = $this->totals();

        DB::beginTransaction();

        try {
            $sales = $this->repairSales();
            $returns = $this->repairSalesReturns();
            $purchases = $this->repairPurchases();
            $seed = $this->option('keep-seed-balances') ? ['count' => 0, 'amount' => 0.0] : $this->clearSeedBalances();
            $openings = $this->option('post-openings') ? $this->postOpenings() : ['count' => 0, 'amount' => 0.0];
            $after = $this->totals();

            $this->newLine();
            $this->info($dry ? '== تجربة (مفيش حاجة اتحفظت) ==' : '== تم الإصلاح ==');
            $this->table(['البند', 'عدد الحركات', 'المبلغ'], [
                ['مبيعات: حركات اترحّلت على أرصدتها', $sales['count'], number_format($sales['amount'], 2)],
                ['مرتجع مبيعات: المردود النقدي اتخصم من الخزينة/البنك', $returns['count'], number_format($returns['amount'], 2)],
                ['مشتريات فورية: الدفع اتقلب من مدين لدائن', $purchases['count'], number_format($purchases['amount'], 2)],
                ['أرصدة وهمية من السيدر اتصفّرت (الحركات الحقيقية فضلت)', $seed['count'], number_format($seed['amount'], 2)],
                ['أرصدة افتتاحية: اتسجل طرفها المقابل', $openings['count'], number_format($openings['amount'], 2)],
            ]);

            $this->table(['ميزان المراجعة', 'إجمالي المدين', 'إجمالي الدائن', 'الفرق'], [
                ['قبل', number_format($before['debit'], 2), number_format($before['credit'], 2), number_format($before['debit'] - $before['credit'], 2)],
                ['بعد', number_format($after['debit'], 2), number_format($after['credit'], 2), number_format($after['debit'] - $after['credit'], 2)],
            ]);

            if ($this->seedRows) {
                $this->line('الحسابات اللي اتشال منها رصيد السيدر (راجعها قبل ما تشغّل من غير --dry-run):');
                $this->table(['#', 'الحساب', 'مدين اتشال', 'دائن اتشال'], $this->seedRows);
            }

            $this->diagnose($after);

            $dry ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function repairSales(): array
    {
        $count = 0;
        $amount = 0.0;

        if (empty(self::SALES_TYPES)) {
            return compact('count', 'amount');
        }

        $hasVat = Schema::hasColumn((new CreditTransaction())->getTable(), 'vat');

        CreditTransaction::query()
            ->whereIn('operation_type', self::SALES_TYPES)
            ->whereNull('balance_posted')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$count, &$amount, $hasVat) {
                foreach ($rows as $row) {
                    $debit = (float) $row->debtor;
                    $credit = (float) $row->creditor;
                    $account = FinancialAccount::lockForUpdate()->find($row->customer_id);

                    // الضريبة وحساب العميل كانوا بيتحدّثوا فعلاً في الكود القديم.
                    $alreadyPosted = ($hasVat && (int) $row->vat === 1)
                        || ($account && (int) $account->orginal_type === 1);

                    if ($account && !$alreadyPosted && ($debit != 0.0 || $credit != 0.0)) {
                        $this->post($account, $debit, $credit);
                        $count++;
                        $amount += $debit + $credit;
                    }

                    CreditTransaction::whereKey($row->id)->update(['balance_posted' => 1]);
                }
            });

        return compact('count', 'amount');
    }

    private function repairSalesReturns(): array
    {
        $count = 0;
        $amount = 0.0;

        if (!self::SALES_RETURN_TYPE) {
            return compact('count', 'amount');
        }

        $cashAndBankIds = FinancialAccount::whereIn('parent_account_number', [4, 5])->pluck('id')->all();

        CreditTransaction::query()
            ->where('operation_type', self::SALES_RETURN_TYPE)
            ->whereNull('balance_posted')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$count, &$amount, $cashAndBankIds) {
                foreach ($rows as $row) {
                    $credit = (float) $row->creditor;
                    if ($credit > 0 && in_array((int) $row->customer_id, $cashAndBankIds, true)) {
                        $account = FinancialAccount::lockForUpdate()->find($row->customer_id);
                        if ($account) {
                            $this->post($account, 0, $credit);
                            $count++;
                            $amount += $credit;
                        }
                    }

                    CreditTransaction::whereKey($row->id)->update(['balance_posted' => 1]);
                }
            });

        return compact('count', 'amount');
    }

    private function repairPurchases(): array
    {
        $count = 0;
        $amount = 0.0;

        $purchaseTable = (new Purchase())->getTable();
        if (!Schema::hasColumn($purchaseTable, 'payment_account_id') || !Schema::hasColumn($purchaseTable, 'purchase_number')) {
            return compact('count', 'amount');
        }

        $purchases = Purchase::query()
            ->whereNotNull('payment_account_id')
            ->get(['id', 'purchase_number', 'payment_account_id'])
            ->keyBy(fn ($p) => (string) $p->purchase_number);

        CreditTransaction::query()
            ->where('operation_type', self::PURCHASE_TYPE)
            ->whereNull('balance_posted')
            ->where('debtor', '>', 0)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$count, &$amount, $purchases) {
                foreach ($rows as $row) {
                    $purchase = $purchases->get((string) $row->invoice_number);
                    if (!$purchase || (int) $purchase->payment_account_id !== (int) $row->customer_id) {
                        continue;
                    }

                    $value = (float) $row->debtor;
                    $account = FinancialAccount::lockForUpdate()->find($row->customer_id);
                    if (!$account) {
                        continue;
                    }

                    // current_balance كان اتخصم صح وقتها - الغلط كان في العمود بس.
                    $account->update([
                        'debtor_current' => (float) $account->debtor_current - $value,
                        'creditor_current' => (float) $account->creditor_current + $value,
                    ]);

                    CreditTransaction::whereKey($row->id)->update([
                        'debtor' => 0,
                        'creditor' => $value,
                        'balance_posted' => 1,
                    ]);

                    $count++;
                    $amount += $value;
                }
            });

        return compact('count', 'amount');
    }

    /**
     * سيدر شجرة الحسابات القديم كان بيزرع الحسابات بأرصدة (بيانات تجريبية
     * من غير أي قيد). بنرجّع رصيد كل حساب من حسابات السيدر لمجموع حركاته
     * الحقيقية بس في credittransactions - أي حاجة اتسجلت بقيد/فاتورة/سند
     * بتفضل زي ما هي، والرصيد الوهمي بس اللي بيتشال.
     */
    private function clearSeedBalances(): array
    {
        $count = 0;
        $amount = 0.0;
        $seeder = \Database\Seeders\ChartOfAccountsSeeder::class;
        if (!class_exists($seeder) || !defined($seeder . '::ACCOUNTS')) {
            return compact('count', 'amount');
        }

        $ids = array_values(array_filter(array_column(constant($seeder . '::ACCOUNTS'), 'id')));
        if (empty($ids)) {
            return compact('count', 'amount');
        }

        $tx = CreditTransaction::query()
            ->whereIn('customer_id', $ids)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(COALESCE(debtor,0)) AS d, SUM(COALESCE(creditor,0)) AS c')
            ->get()
            ->keyBy('customer_id');

        $accountsTable = (new FinancialAccount())->getTable();
        $extraZero = [];
        foreach (['start_balance', 'debtor_end', 'creditor_end', 'debtor_opening', 'creditor_opening'] as $column) {
            if (Schema::hasColumn($accountsTable, $column)) {
                $extraZero[$column] = 0;
            }
        }

        foreach (FinancialAccount::whereIn('id', $ids)->get() as $account) {
            $t = $tx->get($account->id);
            $debit = round($t ? (float) $t->d : 0.0, 2);
            $credit = round($t ? (float) $t->c : 0.0, 2);
            $removedDebit = round((float) $account->debtor_current - $debit, 2);
            $removedCredit = round((float) $account->creditor_current - $credit, 2);

            if (abs($removedDebit) < 0.01 && abs($removedCredit) < 0.01) {
                continue;
            }

            $creditNature = in_array((int) $account->account_type, [2, 3, 5], true);
            DB::table($accountsTable)->where('id', $account->id)->update($extraZero + [
                'debtor_current' => $debit,
                'creditor_current' => $credit,
                'current_balance' => $creditNature ? $credit - $debit : $debit - $credit,
            ]);

            $count++;
            $amount += abs($removedDebit) + abs($removedCredit);
            $this->seedRows[] = [$account->id, $account->name, number_format($removedDebit, 2), number_format($removedCredit, 2)];
        }

        return compact('count', 'amount');
    }

    /**
     * حسابات فرعية رصيدها (مدين - دائن) مش متفسّر بحركاتها في credittransactions.
     */
    private function unexplainedBalances()
    {
        $tx = CreditTransaction::query()
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(COALESCE(debtor,0)) AS d, SUM(COALESCE(creditor,0)) AS c')
            ->get()
            ->keyBy('customer_id');

        return FinancialAccount::where('is_parent', false)->get()
            ->map(function ($account) use ($tx) {
                $t = $tx->get($account->id);
                $diff = round(((float) $account->debtor_current - (float) $account->creditor_current)
                    - ($t ? (float) $t->d - (float) $t->c : 0), 2);

                return ['account' => $account, 'diff' => $diff];
            })
            ->filter(fn ($r) => abs($r['diff']) >= 0.01)
            ->sortByDesc(fn ($r) => abs($r['diff']))
            ->values();
    }

    private function postOpenings(): array
    {
        $count = 0;
        $amount = 0.0;
        $counterpart = $this->openingBalanceAccount();
        if (!$counterpart) {
            $this->warn('مفيش حساب رئيسي في حقوق الملكية أحط تحته "أرصدة افتتاحية".');

            return compact('count', 'amount');
        }

        foreach ($this->unexplainedBalances() as $r) {
            $account = $r['account'];
            if ($account->id === $counterpart->id) {
                continue;
            }
            $diff = $r['diff'];
            $debit = $diff > 0 ? $diff : 0.0;
            $credit = $diff < 0 ? -$diff : 0.0;
            $now = now();
            $base = [
                'note' => 'رصيد افتتاحي - ' . $account->name,
                'operation_type' => 10,
                'invoice_number' => 'OB-' . $account->id,
                'branchs_id' => $account->branchs_id,
                'recive_amount' => abs($diff),
                'balance_posted' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // حركة الحساب نفسه (رصيده موجود أصلاً - بنسجّل الحركة بس).
            CreditTransaction::create($base + [
                'customer_id' => $account->id,
                'currentblance' => $account->current_balance,
                'debtor' => $debit,
                'creditor' => $credit,
            ]);

            // الطرف المقابل على "أرصدة افتتاحية".
            $counterpart->refresh();
            $this->post($counterpart, $credit, $debit);
            CreditTransaction::create($base + [
                'customer_id' => $counterpart->id,
                'currentblance' => $counterpart->fresh()->current_balance,
                'debtor' => $credit,
                'creditor' => $debit,
            ]);

            $count++;
            $amount += abs($diff);
        }

        return compact('count', 'amount');
    }

    private function openingBalanceAccount(): ?FinancialAccount
    {
        $name = 'أرصدة افتتاحية';
        $existing = FinancialAccount::where('name', $name)->where('is_parent', false)->first();
        if ($existing) {
            return $existing;
        }

        $parent = FinancialAccount::where('account_type', 5)->where('is_parent', true)
            ->where('name', 'like', '%راس المال%')->orderBy('id')->first()
            ?? FinancialAccount::where('account_type', 5)->where('is_parent', true)->orderBy('id')->first();
        if (!$parent) {
            return null;
        }

        $maxChild = (int) FinancialAccount::where('parent_account_number', $parent->id)->max('account_number');

        return FinancialAccount::create([
            'name' => $name,
            'account_type' => 5,
            'account_category_id' => 5,
            'parent_account_number' => $parent->id,
            'account_number' => (string) ($maxChild > 0 ? $maxChild + 1 : ((int) $parent->account_number) + 1),
            'start_balance' => 0,
            'current_balance' => 0,
            'debtor_current' => 0,
            'creditor_current' => 0,
            'com_code' => 1,
            'date' => now()->toDateString(),
            'active' => true,
            'is_parent' => false,
        ]);
    }

    private function post(FinancialAccount $account, float $debit, float $credit): void
    {
        $creditNature = in_array((int) $account->account_type, [2, 3, 5], true);
        $delta = $creditNature ? ($credit - $debit) : ($debit - $credit);

        $account->update([
            'current_balance' => (float) $account->current_balance + $delta,
            'debtor_current' => (float) $account->debtor_current + $debit,
            'creditor_current' => (float) $account->creditor_current + $credit,
        ]);
    }

    private function totals(): array
    {
        $row = FinancialAccount::query()
            ->where('is_parent', false)
            ->selectRaw('COALESCE(SUM(debtor_current),0) AS d, COALESCE(SUM(creditor_current),0) AS c')
            ->first();

        return ['debit' => round((float) $row->d, 2), 'credit' => round((float) $row->c, 2)];
    }

    /**
     * لو الميزان لسه فيه فرق: بنفصل بين (1) عمليات قيودها نفسها غير
     * متوازنة، و(2) أرصدة اتحطت على الحسابات من غير حركة (زي رصيد افتتاحي
     * اتكتب وقت إنشاء الحساب في طرف واحد).
     */
    private function diagnose(array $after): void
    {
        $parentsWithBalance = FinancialAccount::where('is_parent', true)
            ->where(fn ($q) => $q->where('debtor_current', '!=', 0)->orWhere('creditor_current', '!=', 0))
            ->get(['account_number', 'name', 'debtor_current', 'creditor_current']);
        if ($parentsWithBalance->isNotEmpty()) {
            $this->warn('حسابات رئيسية عليها أرصدة (مش بتظهر في ميزان المراجعة ولا الميزانية) - انقل رصيدها بقيد لحساب فرعي تحتها:');
            $this->table(['رقم الحساب', 'الحساب', 'مدين', 'دائن'], $parentsWithBalance->map(fn ($a) => [
                $a->account_number, $a->name, number_format((float) $a->debtor_current, 2), number_format((float) $a->creditor_current, 2),
            ])->all());
        }

        $diff = round($after['debit'] - $after['credit'], 2);
        if (abs($diff) < 0.01) {
            $this->info('ميزان المراجعة متزن ✔ - والميزانية العمومية هتتزن (صافي الربح بيظهر تحت حقوق الملكية).');

            return;
        }

        $this->warn('لسه فيه فرق ' . number_format($diff, 2) . ' - تفاصيل المصدر:');

        $leafIds = FinancialAccount::where('is_parent', false)->pluck('id');
        $byType = CreditTransaction::query()
            ->whereIn('customer_id', $leafIds)
            ->groupBy('operation_type')
            ->selectRaw('operation_type, SUM(COALESCE(debtor,0)) - SUM(COALESCE(creditor,0)) AS diff, COUNT(*) AS n')
            ->get()
            ->filter(fn ($r) => abs((float) $r->diff) >= 0.01);

        $txDiff = 0.0;
        $rows = [];
        foreach ($byType as $r) {
            $txDiff += (float) $r->diff;
            $examples = CreditTransaction::query()
                ->whereIn('customer_id', $leafIds)
                ->where('operation_type', $r->operation_type)
                ->groupBy('invoice_number')
                ->havingRaw('ABS(SUM(COALESCE(debtor,0)) - SUM(COALESCE(creditor,0))) >= 0.01')
                ->limit(5)
                ->pluck('invoice_number')
                ->implode(', ');
            $rows[] = [$r->operation_type, number_format((float) $r->diff, 2), $examples];
        }

        if ($rows) {
            $this->table(['نوع العملية', 'فرق القيود (مدين - دائن)', 'أمثلة أرقام مستندات'], $rows);
        }

        $outside = round($diff - $txDiff, 2);
        if (abs($outside) >= 0.01) {
            $this->warn('فرق ' . number_format($outside, 2) . ' جاي من أرصدة متسجلة على الحسابات من غير حركات (غالبًا أرصدة افتتاحية اتكتبت في طرف واحد وقت إنشاء الحساب) - شغّل الأمر بـ --post-openings يسجّل طرفها المقابل.');
            $rows = $this->unexplainedBalances()->take(15)->map(fn ($r) => [
                $r['account']->account_number, $r['account']->name, number_format($r['diff'], 2),
            ])->all();
            $this->table(['رقم الحساب', 'الحساب', 'رصيد من غير حركات (مدين - دائن)'], $rows);
        }
    }
}
