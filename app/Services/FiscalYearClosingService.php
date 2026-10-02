<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\FiscalYearClosing;
use App\Models\FiscalYearOpeningBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * إقفال السنة المالية وترحيل الأرصدة للسنة الجديدة.
 *
 * الإقفال بيعمل قيد بتاريخ يوم الإقفال:
 *   - كل حساب إيرادات/مصروفات بيتصفّر (بعكس رصيده لحد يوم الإقفال).
 *   - صافي الربح (أو الخسارة) بيروح لحساب "الأرباح المرحّلة" في حقوق الملكية.
 * وبعدها بيتحفظ رصيد كل حساب ميزانية (أصول/خصوم/حقوق ملكية) كرصيد افتتاحي
 * للسنة الجديدة. الأرصدة دي نفسها بتكمل في الحسابات (مفيش قيد افتتاحي
 * بيتعمل تاني عشان منكررش الرصيد) - الجدول ده للعرض والطباعة والمراجعة.
 *
 * الإقفال ممكن يتلغي (آخر إقفال بس) - بيتعكس القيد بالظبط.
 */
class FiscalYearClosingService
{
    public const OPERATION_TYPE = 20;

    private const ASSETS = 1;
    private const LIABILITIES = 2;
    private const REVENUE = 3;
    private const EXPENSES = 4;
    private const EQUITY = 5;

    public const RETAINED_EARNINGS_NAME = 'الأرباح المرحّلة';

    private ?Collection $categoryMap = null;

    public function lastClosing(): ?FiscalYearClosing
    {
        return FiscalYearClosing::orderByDesc('closing_date')->first();
    }

    /**
     * معاينة الإقفال لتاريخ معيّن من غير أي حفظ.
     */
    public function preview(Carbon $closingDate): array
    {
        $this->validateDate($closingDate);

        $leaves = $this->leavesWithBalances($closingDate);
        $pnl = $leaves->filter(fn ($a) => in_array($a->category, [self::REVENUE, self::EXPENSES], true)
            && abs($a->balance) >= 0.01)->values();

        $totalRevenue = round((float) $pnl->where('category', self::REVENUE)->sum(fn ($a) => -$a->balance), 2);
        $totalExpenses = round((float) $pnl->where('category', self::EXPENSES)->sum(fn ($a) => $a->balance), 2);

        return [
            'closing_date' => $closingDate->copy(),
            'date_from' => $this->periodStart(),
            'accounts' => $pnl,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => round($totalRevenue - $totalExpenses, 2),
            'retained_account' => $this->findRetainedEarningsAccount(),
        ];
    }

    public function close(Carbon $closingDate, ?string $notes, ?int $userId): FiscalYearClosing
    {
        return DB::transaction(function () use ($closingDate, $notes, $userId) {
            $preview = $this->preview($closingDate);
            $retained = $this->findRetainedEarningsAccount() ?? $this->createRetainedEarningsAccount($userId);
            if (!$retained) {
                throw ValidationException::withMessages([
                    'closing_date' => 'مفيش حساب رئيسي في حقوق الملكية يتحط تحته حساب "الأرباح المرحّلة" - أضف واحد في شجرة الحسابات الأول.',
                ]);
            }

            $reference = 'CLOSE-' . $closingDate->format('Y');
            if (FiscalYearClosing::where('reference', $reference)->exists()) {
                $reference .= '-' . $closingDate->format('md');
            }

            $postedAt = $closingDate->copy()->endOfDay();
            $note = 'قيد إقفال السنة المالية ' . $closingDate->format('Y') . ' (' . $reference . ')';

            // 1) تصفير كل حساب إيرادات/مصروفات.
            foreach ($preview['accounts'] as $account) {
                $balance = (float) $account->balance; // مدين - دائن
                $debit = $balance < 0 ? -$balance : 0.0;
                $credit = $balance > 0 ? $balance : 0.0;
                $this->post($account->id, $debit, $credit, $reference, $note, $postedAt, $account->branchs_id, $userId);
            }

            // 2) صافي الربح/الخسارة لحساب الأرباح المرحّلة.
            $netIncome = (float) $preview['net_income'];
            if (abs($netIncome) >= 0.01) {
                $this->post(
                    $retained->id,
                    $netIncome < 0 ? -$netIncome : 0.0,
                    $netIncome > 0 ? $netIncome : 0.0,
                    $reference,
                    $note,
                    $postedAt,
                    null,
                    $userId
                );
            }

            $closing = FiscalYearClosing::create([
                'fiscal_year' => (int) $closingDate->format('Y'),
                'date_from' => $preview['date_from'],
                'closing_date' => $closingDate->toDateString(),
                'reference' => $reference,
                'total_revenue' => $preview['total_revenue'],
                'total_expenses' => $preview['total_expenses'],
                'net_income' => $netIncome,
                'retained_account_id' => $retained->id,
                'closed_accounts_count' => $preview['accounts']->count(),
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            // 3) ترحيل أرصدة حسابات الميزانية كأرصدة افتتاحية للسنة الجديدة.
            $now = now();
            $rows = [];
            foreach ($this->leavesWithBalances($closingDate) as $account) {
                if (!in_array($account->category, [self::ASSETS, self::LIABILITIES, self::EQUITY], true)
                    || abs($account->balance) < 0.01) {
                    continue;
                }
                $rows[] = [
                    'fiscal_year_closing_id' => $closing->id,
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'account_name' => $account->name,
                    'category' => $account->category,
                    'branchs_id' => $account->branchs_id,
                    'debit' => $account->balance > 0 ? round($account->balance, 2) : 0,
                    'credit' => $account->balance < 0 ? round(-$account->balance, 2) : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                FiscalYearOpeningBalance::insert($chunk);
            }

            return $closing;
        });
    }

    /**
     * إلغاء إقفال (آخر إقفال بس): بيعكس قيد الإقفال بالظبط ويمسح الأرصدة المرحّلة.
     */
    public function reopen(FiscalYearClosing $closing): void
    {
        $last = $this->lastClosing();
        if (!$last || $last->id !== $closing->id) {
            throw ValidationException::withMessages([
                'closing' => 'ممكن تلغي آخر إقفال بس - ألغِ الإقفالات اللي بعده الأول.',
            ]);
        }

        DB::transaction(function () use ($closing) {
            $rows = CreditTransaction::where('operation_type', self::OPERATION_TYPE)
                ->where('invoice_number', $closing->reference)
                ->lockForUpdate()
                ->get();

            foreach ($rows as $row) {
                $this->applyToAccount((int) $row->customer_id, -(float) $row->debtor, -(float) $row->creditor);
            }

            CreditTransaction::where('operation_type', self::OPERATION_TYPE)
                ->where('invoice_number', $closing->reference)
                ->delete();

            $closing->openingBalances()->delete();
            $closing->delete();
        });
    }

    // ------------------------------------------------------------------

    private function validateDate(Carbon $closingDate): void
    {
        if ($closingDate->isAfter(now()->endOfDay())) {
            throw ValidationException::withMessages(['closing_date' => 'تاريخ الإقفال مينفعش يكون في المستقبل.']);
        }

        $last = $this->lastClosing();
        if ($last && !$closingDate->isAfter($last->closing_date)) {
            throw ValidationException::withMessages([
                'closing_date' => 'آخر إقفال كان بتاريخ ' . $last->closing_date->format('Y-m-d') . ' - تاريخ الإقفال الجديد لازم يكون بعده.',
            ]);
        }
    }

    private function periodStart(): ?string
    {
        $last = $this->lastClosing();
        if ($last) {
            return $last->closing_date->copy()->addDay()->toDateString();
        }

        $first = CreditTransaction::min('created_at');

        return $first ? Carbon::parse($first)->toDateString() : null;
    }

    /**
     * كل الحسابات الفرعية + تصنيف كل واحد (من نفسه أو أقرب أب) + رصيده
     * (مدين - دائن) لحد آخر يوم الإقفال = الرصيد الحالي ناقص أي حركات بعده.
     */
    private function leavesWithBalances(Carbon $closingDate): Collection
    {
        $all = FinancialAccount::query()
            ->get(['id', 'name', 'account_number', 'account_type', 'account_category_id', 'parent_account_number', 'is_parent', 'branchs_id', 'debtor_current', 'creditor_current'])
            ->keyBy('id');

        // الحسابات اللي ليها أبناء بيشيروا ليها تعتبر آباء وليست أوراق طرفية
        $parentIds = $all->pluck('parent_account_number')->filter()->unique()->flip();

        $after = CreditTransaction::query()
            ->where('created_at', '>', $closingDate->copy()->endOfDay())
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(COALESCE(debtor,0)) AS d, SUM(COALESCE(creditor,0)) AS c')
            ->get()
            ->keyBy('customer_id');

        return $all->filter(fn ($a) => !isset($parentIds[$a->id]) && !$a->is_parent)
            ->map(function ($account) use ($all, $after) {
                $later = $after->get($account->id);
                $account->category = $this->categoryOf($account, $all);
                $account->balance = round(
                    ((float) $account->debtor_current - (float) $account->creditor_current)
                    - ($later ? (float) $later->d - (float) $later->c : 0),
                    2
                );

                return $account;
            })
            ->sortBy('account_number')
            ->values();
    }

    private function categoryOf($account, Collection $all): ?int
    {
        $valid = [self::ASSETS, self::LIABILITIES, self::REVENUE, self::EXPENSES, self::EQUITY];
        $node = $account;
        for ($guard = 0; $node && $guard < 50; $guard++) {
            $cat = in_array((int) $node->account_type, $valid, true) ? (int) $node->account_type : null;
            $cat ??= in_array((int) ($node->account_category_id ?? null), $valid, true) ? (int) $node->account_category_id : null;
            if ($cat !== null) {
                return $cat;
            }
            $node = $node->parent_account_number ? $all->get($node->parent_account_number) : null;
        }

        return null;
    }

    private function post(int $accountId, float $debit, float $credit, string $reference, string $note, Carbon $postedAt, $branchId, ?int $userId): void
    {
        if ($debit <= 0 && $credit <= 0) {
            return;
        }

        $account = $this->applyToAccount($accountId, $debit, $credit);

        $data = [
            'user_id' => $userId,
            'customer_id' => $accountId,
            'recive_amount' => max($debit, $credit),
            'branchs_id' => $branchId,
            'note' => $note,
            'currentblance' => $account['current_balance'],
            'debtor' => $debit,
            'creditor' => $credit,
            'operation_type' => self::OPERATION_TYPE,
            'invoice_number' => $reference,
            'created_at' => $postedAt,
            'updated_at' => now(),
        ];
        $table = (new CreditTransaction())->getTable();
        if (Schema::hasColumn($table, 'date_export')) {
            $data['date_export'] = $postedAt->toDateString();
        }
        if (Schema::hasColumn($table, 'balance_posted')) {
            $data['balance_posted'] = 1;
        }

        CreditTransaction::create($data);
    }

    /**
     * بيضيف مدين/دائن على رصيد الحساب مباشرة (من غير model events).
     */
    private function applyToAccount(int $accountId, float $debit, float $credit): array
    {
        $table = (new FinancialAccount())->getTable();
        $account = DB::table($table)->where('id', $accountId)->lockForUpdate()->first();
        if (!$account) {
            return ['current_balance' => 0];
        }

        $this->categoryMap ??= FinancialAccount::query()->get(['id', 'account_type', 'parent_account_number'])->keyBy('id');
        $category = $this->categoryOf($account, $this->categoryMap);
        $creditNature = in_array($category, [self::LIABILITIES, self::REVENUE, self::EQUITY], true);
        $delta = $creditNature ? ($credit - $debit) : ($debit - $credit);

        $values = [
            'debtor_current' => round((float) $account->debtor_current + $debit, 2),
            'creditor_current' => round((float) $account->creditor_current + $credit, 2),
            'current_balance' => round((float) $account->current_balance + $delta, 2),
            'updated_at' => now(),
        ];
        DB::table($table)->where('id', $accountId)->update($values);

        return $values;
    }

    private function findRetainedEarningsAccount(): ?FinancialAccount
    {
        return FinancialAccount::where('name', self::RETAINED_EARNINGS_NAME)
            ->where('is_parent', false)
            ->first();
    }

    private function createRetainedEarningsAccount(?int $userId): ?FinancialAccount
    {
        $parent = FinancialAccount::where('account_type', self::EQUITY)->where('is_parent', true)
            ->where(fn ($q) => $q->where('name', 'like', '%الارباح%')->orWhere('name', 'like', '%الأرباح%'))
            ->orderBy('id')->first()
            ?? FinancialAccount::where('account_type', self::EQUITY)->where('is_parent', true)->orderBy('id')->first();

        if (!$parent) {
            return null;
        }

        $maxChild = (int) FinancialAccount::where('parent_account_number', $parent->id)->max('account_number');

        return FinancialAccount::create([
            'name' => self::RETAINED_EARNINGS_NAME,
            'account_type' => self::EQUITY,
            'account_category_id' => self::EQUITY,
            'parent_account_number' => $parent->id,
            'account_number' => (string) ($maxChild > 0 ? $maxChild + 1 : ((int) $parent->account_number) + 1),
            'start_balance' => 0,
            'current_balance' => 0,
            'debtor_current' => 0,
            'creditor_current' => 0,
            'added_by' => $userId,
            'com_code' => 1,
            'date' => now()->toDateString(),
            'active' => true,
            'is_parent' => false,
        ]);
    }
}
