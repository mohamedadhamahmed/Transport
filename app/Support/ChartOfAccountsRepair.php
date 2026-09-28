<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إصلاح شجرة الحسابات (financialaccount) - بيستخدمه ChartOfAccountsSeeder
 * وأمر php artisan accounts:repair.
 *
 * المشكلة اللي بيحلها:
 * الميجريشنز 2026_09_01_000022 / 000023 كانت بتعمل حسابات جذر جديدة
 * ("الأصول" / "الخصوم" / "حقوق الملكية" / "المصروفات" / "الإيرادات الرئيسية"
 * / "الموارد البشرية" ...) بـ auto-increment. على قاعدة بيانات جديدة فاضية
 * الحسابات دي كانت بتاخد ids من 1 لـ 8 - وهي نفس الـ ids الثابتة اللي الكود
 * والسيدر معتمدين عليها (2=العملاء، 4=البنوك، 5=الخزينة، 6=المصاريف
 * الإدارية...). فالسيدر كان بيلاقي الـ id موجود ومبيعملش الحساب الحقيقي،
 * والنتيجة: العملاء بيتسجلوا تحت "الخصوم"، الخزينة تحت "المصروفات"، البنك
 * تحت "حقوق الملكية"، والمصاريف الإدارية تحت "الإيرادات"... إلخ.
 *
 * الخطوات (كلها آمنة تتكرر، وبتحافظ على الأرصدة والحركات زي ما هي):
 *  1. أي id ثابت من شجرة الحسابات الأصلية لقيناه شايل اسم من الأسماء
 *     المُولّدة دي → بيرجع للحساب الصحيح (اسم/أب/رقم/تصنيف).
 *  2. تصحيحات هيكلية معروفة في الشجرة نفسها (حساب في مكان غلط أو رقم غلط).
 *  3. حسابات الموردين كلها تحت "الموردين" (id=1 - خصوم) بدل
 *     "الحساب الرئيسي للمناديب" (id=3 - أصول)، وحسابات العملاء كلها تحت
 *     "العملاء" (أصول متداولة).
 *  3ب. دمج الحسابات المكررة (DUPLICATE_MERGES) وتغيير أسماء الحسابات اللي
 *      اسمها زي أبوها (RENAMES) - مفيش حسابين بنفس الاسم.
 *  4. الحسابات المُولّدة الزيادة اللي فضلت فاضية ومن غير حركات بتتمسح.
 *  5. إعادة حساب التصنيف (account_type + account_category_id) لكل حساب من
 *     الجذر بتاعه، عشان كل حساب يطلع في مكانه الصح في ميزان المراجعة
 *     والقوائم المالية.
 */
class ChartOfAccountsRepair
{
    public const SUPPLIERS_PARENT_ID = 1;
    public const CUSTOMERS_PARENT_ID = 2;

    /**
     * أسماء الحسابات اللي كانت بتتولّد تلقائيًا من الميجريشنز القديمة.
     */
    public const SYNTHETIC_NAMES = [
        'الأصول',
        'الخصوم',
        'حقوق الملكية',
        'المصروفات',
        'الإيرادات',
        'الإيرادات الرئيسية',
        'الموارد البشرية',
        'مصروفات الموارد البشرية',
        'التزامات الموارد البشرية',
    ];

    /**
     * تصحيحات هيكلية على الشجرة الأصلية (بتتطبق بس لو اسم الحساب هو نفس
     * الاسم المتوقع، عشان منلمسش حساب ليه معنى تاني في قاعدة بيانات تانية).
     *
     * التصنيف نفسه مش محتاج يتحط هنا - بيتحسب تلقائيًا من الجذر.
     */
    public const STRUCTURE_FIXES = [
        // "ذمم الموظفين" تحت الأصول المتداولة (1200) لكن رقمه كان 5501 (رقم حقوق ملكية).
        82 => ['name' => 'ذمم الموظفين', 'changes' => ['account_number' => '1209']],
        // "تكلفة البضاعة المباعة" كانت تحت "المبيعات" (إيرادات) - مكانها المصروفات.
        116 => ['name' => 'تكلفة البضاعة المباعة', 'changes' => ['parent_account_number' => 182, 'account_number' => '4404']],
        // "فواتير اجله" كانت جذر منفصل برقم 276 - مكانها تحت المبيعات.
        384 => ['name' => 'فواتير اجله', 'changes' => ['parent_account_number' => 111, 'account_number' => '3104']],
        // "القروض والتمويل الشخصي" (خصوم) كان رقمه 1419 (رقم أصول).
        1528 => ['name' => 'القروض والتمويل الشخصي', 'changes' => ['account_number' => '2400']],
    ];

    /**
     * حسابات مكررة بنفس الاسم: [المكرر => الحساب اللي هيفضل]. المكرر
     * بيتدمج في الأصلي (حساباته الفرعية + كل حركاته وقيوده وسنداته +
     * رصيده) وبعدين بيتمسح.
     */
    public const DUPLICATE_MERGES = [
        1529 => ['keep' => 1528, 'name' => 'القروض والتمويل الشخصي'],
        1530 => ['keep' => 1528, 'name' => 'القروض والتمويل الشخصي'],
        2718 => ['keep' => 2717, 'name' => 'حساب التسوية المؤقتة'],
        305 => ['keep' => 302, 'name' => 'مصاريف اخري'],
        140 => ['keep' => 139, 'name' => 'تصديق غرفة تجارية'],
        2727 => ['keep' => 2726, 'name' => 'مصروفات سيارة'],
    ];

    /**
     * حسابات بنفس اسم حساب تاني (أب وابنه) - بتتغير أسماؤها بس عشان
     * متبقاش مكررة (الـ id زي ما هو لإن الكود معتمد عليه).
     */
    public const RENAMES = [
        111 => ['from' => 'المبيعات', 'to' => 'إيرادات المبيعات'],
    ];

    /**
     * الأعمدة اللي بتشاور على financialaccount.id في باقي الجداول.
     */
    private const ACCOUNT_REFERENCES = [
        'credittransactions' => ['customer_id'],
        'journal_entry_lines' => ['account_id'],
        'account_vouchers' => ['treasury_account_id', 'counterpart_account_id'],
        'account_voucher_lines' => ['counterpart_account_id', 'vat_account_id'],
        'customers' => ['accounting_account_id'],
    ];

    private const BALANCE_COLUMNS = [
        'start_balance', 'current_balance',
        'debtor_end', 'creditor_end',
        'debtor_current', 'creditor_current',
        'debtor_opening', 'creditor_opening',
    ];

    /** @var string[] */
    private array $log = [];

    public function __construct(private bool $dryRun = false)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $canonicalAccounts شجرة الحسابات الأصلية (ChartOfAccountsSeeder::ACCOUNTS)
     * @return string[] سجل بكل التغييرات
     */
    public function run(array $canonicalAccounts): array
    {
        $this->log = [];

        if (!Schema::hasTable('financialaccount')) {
            return ['جدول financialaccount مش موجود.'];
        }

        $apply = function () use ($canonicalAccounts) {
            $this->restoreCanonicalIds($canonicalAccounts);
            $this->applyStructureFixes();
            $this->moveSuppliersUnderSuppliersAccount();
            $this->moveCustomersUnderCustomersAccount();
            $this->mergeDuplicateAccounts();
            $this->renameConflictingAccounts();
            $this->deleteEmptySyntheticAccounts($canonicalAccounts);
            $this->recomputeCategories();
        };

        if ($this->dryRun) {
            DB::beginTransaction();
            try {
                $apply();
            } finally {
                DB::rollBack();
            }
        } else {
            DB::transaction($apply);
        }

        return $this->log;
    }

    private function restoreCanonicalIds(array $canonicalAccounts): void
    {
        foreach ($canonicalAccounts as $row) {
            $current = DB::table('financialaccount')->where('id', $row['id'])->first();
            if (!$current || !$this->isSynthetic($current->name)) {
                continue;
            }

            $parentId = $row['parent_account_number'];
            if ($parentId && !DB::table('financialaccount')->where('id', $parentId)->exists()) {
                $this->log[] = "تحذير: الأب #{$parentId} للحساب #{$row['id']} مش موجود - الحساب هيفضل جذر لحد ما تشغّل السيدر.";
                $parentId = null;
            }

            DB::table('financialaccount')->where('id', $row['id'])->update([
                'name' => $row['name'],
                'parent_account_number' => $parentId,
                'account_number' => $row['account_number'],
                'account_type' => $row['account_type'],
                'account_category_id' => $row['account_type'],
                'is_parent' => $row['is_parent'],
                'active' => $row['active'],
                'branchs_id' => $row['branchs_id'],
                'updated_at' => now(),
            ]);

            $this->log[] = "حساب #{$row['id']} كان اسمه \"{$current->name}\" (حساب مُولّد بالغلط) → رجع \"{$row['name']}\".";
        }
    }

    private function applyStructureFixes(): void
    {
        foreach (self::STRUCTURE_FIXES as $id => $fix) {
            $current = DB::table('financialaccount')->where('id', $id)->first();
            if (!$current || trim((string) $current->name) !== $fix['name']) {
                continue;
            }

            $changes = [];
            foreach ($fix['changes'] as $column => $value) {
                if ((string) $current->{$column} !== (string) $value) {
                    $changes[$column] = $value;
                }
            }

            if (isset($changes['parent_account_number'])
                && !DB::table('financialaccount')->where('id', $changes['parent_account_number'])->exists()) {
                unset($changes['parent_account_number']);
            }

            if (empty($changes)) {
                continue;
            }

            DB::table('financialaccount')->where('id', $id)->update($changes + ['updated_at' => now()]);
            $this->log[] = "حساب #{$id} \"{$fix['name']}\": " . json_encode($changes, JSON_UNESCAPED_UNICODE);
        }
    }

    private function moveSuppliersUnderSuppliersAccount(): void
    {
        $suppliersRoot = DB::table('financialaccount')->where('id', self::SUPPLIERS_PARENT_ID)->first();
        if (!$suppliersRoot || trim((string) $suppliersRoot->name) !== 'الموردين') {
            $this->log[] = 'تحذير: حساب "الموردين" (id=1) مش موجود أو اسمه مختلف - حسابات الموردين ما اتنقلتش.';

            return;
        }

        $count = DB::table('financialaccount')
            ->where('orginal_type', 2)
            ->where(fn ($q) => $q->whereNull('parent_account_number')
                ->orWhere('parent_account_number', '!=', self::SUPPLIERS_PARENT_ID))
            ->update(['parent_account_number' => self::SUPPLIERS_PARENT_ID, 'updated_at' => now()]);

        if ($count > 0) {
            $this->log[] = "اتنقل {$count} حساب مورد تحت \"الموردين\" (خصوم).";
        }
    }

    private function moveCustomersUnderCustomersAccount(): void
    {
        $customersRoot = DB::table('financialaccount')->where('id', self::CUSTOMERS_PARENT_ID)->first();
        if (!$customersRoot || trim((string) $customersRoot->name) !== 'العملاء') {
            $this->log[] = 'تحذير: حساب "العملاء" (id=' . self::CUSTOMERS_PARENT_ID . ') مش موجود أو اسمه مختلف - حسابات العملاء ما اتنقلتش.';

            return;
        }

        $count = DB::table('financialaccount')
            ->where('orginal_type', 1)
            ->where(fn ($q) => $q->whereNull('parent_account_number')
                ->orWhere('parent_account_number', '!=', self::CUSTOMERS_PARENT_ID))
            ->update(['parent_account_number' => self::CUSTOMERS_PARENT_ID, 'updated_at' => now()]);

        if ($count > 0) {
            $this->log[] = "اتنقل {$count} حساب عميل تحت \"العملاء\" (أصول متداولة).";
        }
    }

    private function deleteEmptySyntheticAccounts(array $canonicalAccounts): void
    {
        $canonicalIds = array_column($canonicalAccounts, 'id');

        $candidates = DB::table('financialaccount')
            ->whereIn('name', self::SYNTHETIC_NAMES)
            ->whereNull('orginal_id')
            ->whereNotIn('id', $canonicalIds)
            ->orderByDesc('id')
            ->get(['id', 'name']);

        foreach ($candidates as $account) {
            if ($this->isUsed((int) $account->id)) {
                $this->log[] = "تحذير: الحساب المُولّد #{$account->id} \"{$account->name}\" عليه حسابات فرعية أو حركات - اتساب زي ما هو، راجعه يدويًا من شجرة الحسابات.";

                continue;
            }

            DB::table('financialaccount')->where('id', $account->id)->delete();
            $this->log[] = "اتمسح الحساب المُولّد الفاضي #{$account->id} \"{$account->name}\".";
        }
    }

    private function mergeDuplicateAccounts(): void
    {
        foreach (self::DUPLICATE_MERGES as $duplicateId => $merge) {
            $duplicate = DB::table('financialaccount')->where('id', $duplicateId)->first();
            $keep = DB::table('financialaccount')->where('id', $merge['keep'])->first();

            if (!$duplicate || !$keep
                || $this->normalizeName($duplicate->name) !== $merge['name']
                || $this->normalizeName($keep->name) !== $merge['name']) {
                continue;
            }

            $sameCategory = (int) $duplicate->account_type === (int) $keep->account_type;
            if (!$sameCategory && ($this->hasBalance($duplicate) || $this->isUsed($duplicateId))) {
                $this->log[] = "تحذير: الحساب المكرر #{$duplicateId} \"{$merge['name']}\" تصنيفه مختلف عن #{$merge['keep']} وعليه رصيد أو حركات - ما اتدمجش، راجعه يدويًا.";

                continue;
            }

            DB::table('financialaccount')
                ->where('parent_account_number', $duplicateId)
                ->update(['parent_account_number' => $merge['keep'], 'updated_at' => now()]);

            foreach (self::ACCOUNT_REFERENCES as $table => $columns) {
                if (!Schema::hasTable($table)) {
                    continue;
                }
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::table($table)->where($column, $duplicateId)->update([$column => $merge['keep']]);
                    }
                }
            }

            $balances = [];
            foreach (self::BALANCE_COLUMNS as $column) {
                if (property_exists($keep, $column)) {
                    $balances[$column] = (float) $keep->{$column} + (float) ($duplicate->{$column} ?? 0);
                }
            }
            DB::table('financialaccount')->where('id', $merge['keep'])->update($balances + [
                'is_parent' => ($keep->is_parent || $duplicate->is_parent) ? 1 : 0,
                'updated_at' => now(),
            ]);

            DB::table('financialaccount')->where('id', $duplicateId)->delete();
            $this->log[] = "اتدمج الحساب المكرر #{$duplicateId} في #{$merge['keep']} \"{$merge['name']}\" (الحركات والرصيد اتنقلوا) واتمسح.";
        }
    }

    private function renameConflictingAccounts(): void
    {
        foreach (self::RENAMES as $id => $rename) {
            $updated = DB::table('financialaccount')
                ->where('id', $id)
                ->whereRaw('TRIM(name) = ?', [$rename['from']])
                ->update(['name' => $rename['to'], 'updated_at' => now()]);

            if ($updated) {
                $this->log[] = "حساب #{$id}: الاسم اتغيّر من \"{$rename['from']}\" لـ \"{$rename['to']}\" (كان مكرر مع حساب تحته).";
            }
        }

        // حساب "مردود المبيعات" الخاص بفرع (تحت 184) كان واخد نفس اسم الأب.
        $branchReturns = DB::table('financialaccount')
            ->where('parent_account_number', 184)
            ->whereNotNull('branchs_id')
            ->whereRaw('TRIM(name) = ?', ['مردود المبيعات'])
            ->get(['id', 'branchs_id']);

        foreach ($branchReturns as $row) {
            $branchName = Schema::hasTable('branches')
                ? DB::table('branches')->where('id', $row->branchs_id)->value('name')
                : null;
            $newName = 'مردود المبيعات فرع ' . ($branchName ?: $row->branchs_id);

            DB::table('financialaccount')->where('id', $row->id)->update(['name' => $newName, 'updated_at' => now()]);
            $this->log[] = "حساب #{$row->id}: الاسم اتغيّر لـ \"{$newName}\" (كان مكرر مع الحساب الأب).";
        }
    }

    private function hasBalance(object $row): bool
    {
        foreach (self::BALANCE_COLUMNS as $column) {
            if (property_exists($row, $column) && abs((float) $row->{$column}) > 0.0001) {
                return true;
            }
        }

        return false;
    }

    private function category($value): ?int
    {
        $value = $value === null ? null : (int) $value;

        return in_array($value, [1, 2, 3, 4, 5], true) ? $value : null;
    }

    private function normalizeName(?string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $name));
    }

    private function recomputeCategories(): void
    {
        $rows = DB::table('financialaccount')
            ->get(['id', 'name', 'parent_account_number', 'account_type', 'account_category_id']);

        $children = [];
        $roots = [];
        foreach ($rows as $row) {
            if ($row->parent_account_number) {
                $children[(int) $row->parent_account_number][] = $row;
            } else {
                $roots[] = $row;
            }
        }

        $changed = 0;
        $queue = [];

        foreach ($roots as $root) {
            $category = $this->category($root->account_type)
                ?? $this->category($root->account_category_id);

            if ($category === null) {
                $this->log[] = "تحذير: الحساب الجذر #{$root->id} \"{$root->name}\" ملوش تصنيف (أصول/خصوم/...) - حدده من شاشة شجرة الحسابات.";

                continue;
            }

            $changed += $this->setCategory($root, $category);
            $queue[] = [(int) $root->id, $category];
        }

        $visited = [];
        while (!empty($queue)) {
            [$parentId, $category] = array_shift($queue);
            if (isset($visited[$parentId])) {
                continue;
            }
            $visited[$parentId] = true;

            foreach ($children[$parentId] ?? [] as $child) {
                $changed += $this->setCategory($child, $category);
                $queue[] = [(int) $child->id, $category];
            }
        }

        $this->log[] = "اتصحح تصنيف {$changed} حساب (account_type / account_category_id) حسب الجذر بتاعه.";
    }

    private function setCategory(object $row, int $category): int
    {
        if ((int) $row->account_type === $category && (int) $row->account_category_id === $category) {
            return 0;
        }

        DB::table('financialaccount')->where('id', $row->id)->update([
            'account_type' => $category,
            'account_category_id' => $category,
        ]);

        return 1;
    }

    private function isUsed(int $accountId): bool
    {
        if (DB::table('financialaccount')->where('parent_account_number', $accountId)->exists()) {
            return true;
        }

        foreach (self::ACCOUNT_REFERENCES as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)
                    && DB::table($table)->where($column, $accountId)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isSynthetic(?string $name): bool
    {
        return in_array(trim((string) $name), self::SYNTHETIC_NAMES, true);
    }
}
