<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\AccountVoucherLine;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Employee;
use App\Models\EmployeeBonus;
use App\Models\EmployeeLoan;
use App\Models\FinancialAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReturn;
use App\Models\JournalEntry;
use App\Models\LeaveRequest;
use App\Models\PayrollEmployeeLine;
use App\Models\PayrollLoanDeduction;
use App\Models\PayrollPosting;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Services\Reports\ReportExcelExporter;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * تقارير قسم الحسابات (ميزان المراجعة + القوائم المالية الأربعة) - أول
 * قسم من أقسام "مركز التقارير" الستة (حسابات/مبيعات/تسليم منتج/مشتريات/
 * منتجات/موارد بشرية) اللي طلب العميل بناءها بترتيب الحسابات الأول.
 *
 * ⚠️ التصنيف المحاسبي هنا كله معتمد على العمود القديم account_type بعد
 * ما اتفقنا نعيد استخدامه بنفس معنى account_category_id (1=الأصول،
 * 2=الخصوم، 3=الإيرادات، 4=المصروفات، 5=حقوق الملكية) - راجع ميجريشن
 * 2026_09_02_000028_normalize_account_type_to_category للتفاصيل الكاملة
 * وسبب القرار ده (طلب صريح من العميل بدل الاعتماد على account_category_id
 * الجديد، رغم إن التاني أنضف تصميميًا).
 *
 * فلتر "الفرع" في كل تقارير القسم ده: أي حساب معاه branchs_id بيتفلتر
 * عادي، لكن أي حساب مشترك بين كل الفروع (زي العملاء/الموردين/حقوق
 * الملكية اللي مالهاش branchs_id خالص في شجرة الحسابات الحالية) بيفضل
 * ظاهر مهما كان الفرع المختار - لأنه مش موزّع بفروع أصلاً. ده سلوك
 * مقصود (مش سهو) عشان لو استبعدناه هيختفي رصيد العملاء والموردين
 * تمامًا من أي تقرير لفرع محدد.
 *
 * القوائم اللي محتاج فترة (قائمة الدخل والتدفقات النقدية وقائمة التغير
 * في حقوق الملكية) بتقرأ حركاتها من جدول credittransaction (نفس الجدول
 * اللي كل الكونترولرز التانية - فواتير/مشتريات/رواتب/سندات/قيود - بترحّل
 * عليه فعليًا، برغم اسمه القديم "credittransaction" اللي بيوهم إنه
 * خاص بالعملاء بس). ميزان المراجعة والميزانية العامة تقريرين "لحظيين"
 * (لحظة الطلب) بيعتمدوا على أعمدة الحساب الجارية (debtor_current/
 * creditor_current) مباشرة بدل إعادة بناء الرصيد من الحركات - أبسط
 * وأدق لأي لحظة حالية.
 */
class ReportController extends Controller
{
    private const ASSETS = 1;
    private const LIABILITIES = 2;
    private const REVENUE = 3;
    private const EXPENSES = 4;
    private const EQUITY = 5;

    // حسابات الخزينة والبنوك (نفس أرقام الحسابات الأب الثابتة المستخدمة
    // فعليًا في AccountController::search بـ scope=treasury).
    private const BANK_PARENT_ACCOUNT_NUMBER = 4;
    private const CASH_PARENT_ACCOUNT_NUMBER = 5;

    // كل موديولات الصلاحيات الخاصة بالتقارير (واحد لكل قسم من الأقسام
    // الستة) - كل موديول فيه صلاحية مستقلة لكل تقرير لوحده (مش صلاحية
    // واحدة شاملة زي الأول)، عشان صاحب الحساب يقدر يدي كل موظف بالظبط
    // التقارير اللي محتاجها بس. شوفي config/permissions.php.
    private const REPORT_MODULES = [
        'reports_accounting',
        'reports_sales',
        'reports_purchases',
        'reports_products',
        'reports_hr',
        'reports_delivery',
    ];

    /**
     * مركز التقارير - بطاقة لكل قسم من الأقسام الستة. القسم بيظهر بس لو
     * المستخدم عنده صلاحية تقرير واحد على الأقل جواه (شوفي reports.index
     * blade - نفس الفكرة).
     */
    public function index()
    {
        $this->authorizeAnyReport(...self::REPORT_MODULES);
        return view('reports.index');
    }

    /**
     * تصريح لصفحة "قسم" تقارير (زي accountsIndex/salesIndex...) - دي
     * مجرد صفحة روابط لتقارير فرعية، فمش منطقي نطلب صلاحية واحدة بعينها
     * عشان تتفتح؛ يكفي إن المستخدم عنده صلاحية تقرير واحد على الأقل من
     * تقارير القسم ده (أو أي قسم من $moduleKeys الممرّرة). كل تقرير فرعي
     * لوحده بيتفحص بصلاحيته المحددة جوه دالته هو (شوفي trialBalance مثلاً).
     */
    private function authorizeAnyReport(string ...$moduleKeys): void
    {
        $keys = [];
        $modules = PermissionRegistry::modules();

        foreach ($moduleKeys as $moduleKey) {
            $keys = array_merge($keys, array_keys($modules[$moduleKey]['permissions'] ?? []));
        }

        $user = auth()->user();

        abort_if(
            ! $user || (! $user->isSuperAdmin() && ! collect($keys)->contains(fn ($key) => $user->hasPermission($key))),
            403
        );
    }

    /**
     * صفحة قسم الحسابات - روابط لميزان المراجعة والقوائم المالية الأربعة.
     */
    public function accountsIndex()
    {
        $this->authorizeAnyReport('reports_accounting');
        return view('reports.accounts.index');
    }

    /**
     * ميزان المراجعة: كل الحسابات الفرعية (مش حسابات التصنيف/المجموعات)
     * بأرصدتها المدينة والدائنة الحالية - لازم إجمالي المدين = إجمالي
     * الدائن لو كل القيود متوازنة فعلاً.
     */
    public function trialBalance(Request $request)
    {
        $this->authorize('reports_accounting.trial_balance');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));

        $accounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->when($branchId, fn ($query) => $this->applyBranchFilter($query, $branchId))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('account_number')
            ->get(['id', 'account_number', 'name', 'account_type', 'branchs_id', 'debtor_current', 'creditor_current']);

        // بس عشان توضيح مرئي إن فلتر الفرع فعلاً بيشتغل - أي حساب معاه
        // نفس الفرع المختار بالظبط بيتعلّم بعلامة "خاص بالفرع" في
        // الواجهة (راجع branch_specific_badge)، عشان الفرق يبان واضح
        // عن الحسابات المشتركة اللي ظاهرة بس لأنها مالهاش فرع خالص.
        foreach ($accounts as $account) {
            $account->is_branch_specific = $branchId && (int) $account->branchs_id === $branchId;
        }

        $totalDebtor = round((float) $accounts->sum('debtor_current'), 2);
        $totalCreditor = round((float) $accounts->sum('creditor_current'), 2);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.account_number'), __('reports.account_name'), __('reports.debtor'), __('reports.creditor')],
                $accounts->map(fn ($a) => [$a->account_number, $a->name, (float) $a->debtor_current, (float) $a->creditor_current])->toArray(),
                'trial-balance-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.trial-balance', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'accounts' => $accounts,
            'totalDebtor' => $totalDebtor,
            'totalCreditor' => $totalCreditor,
            'isBalanced' => abs($totalDebtor - $totalCreditor) < 0.01,
        ]);
    }

    /**
     * الميزانية العامة كما في الآن: الأصول مقابل الخصوم + حقوق الملكية.
     * كل حساب بياخد "رصيده الطبيعي" حسب فرعه المحاسبي (account_type) -
     * مش حسب FinancialAccount::isCreditNormal() اللي مبنية على
     * orginal_type لغرض تاني تمامًا (حساب اتجاه القيد وقت الترحيل، مش
     * تصنيف القوائم المالية - راجع تعليق isCreditNormal نفسه).
     */
    public function balanceSheet(Request $request)
    {
        $this->authorize('reports_accounting.balance_sheet');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));

        $accounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->whereIn('account_type', [self::ASSETS, self::LIABILITIES, self::EQUITY])
            ->when($branchId, fn ($query) => $this->applyBranchFilter($query, $branchId))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('account_number')
            ->get(['id', 'account_number', 'name', 'account_type', 'branchs_id', 'debtor_current', 'creditor_current']);

        foreach ($accounts as $account) {
            $account->natural_balance = $this->naturalBalance($account);
            $account->is_branch_specific = $branchId && (int) $account->branchs_id === $branchId;
        }

        $assets = $accounts->where('account_type', self::ASSETS)->values();
        $liabilities = $accounts->where('account_type', self::LIABILITIES)->values();
        $equity = $accounts->where('account_type', self::EQUITY)->values();

        $totalAssets = round((float) $assets->sum('natural_balance'), 2);
        $totalLiabilities = round((float) $liabilities->sum('natural_balance'), 2);
        $totalEquity = round((float) $equity->sum('natural_balance'), 2);

        if ($request->get('export') === 'excel') {
            $sections = [
                [__('reports.assets'), $assets],
                [__('reports.liabilities'), $liabilities],
                [__('reports.equity'), $equity],
            ];
            $rows = [];
            foreach ($sections as [$sectionLabel, $sectionAccounts]) {
                foreach ($sectionAccounts as $account) {
                    $rows[] = [$sectionLabel, $account->account_number, $account->name, (float) $account->natural_balance];
                }
            }

            return ReportExcelExporter::download(
                [__('reports.section'), __('reports.account_number'), __('reports.account_name'), __('reports.total')],
                $rows,
                'balance-sheet-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.balance-sheet', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'isBalanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ]);
    }

    /**
     * قائمة الدخل خلال فترة محددة (افتراضيًا من أول الشهر الحالي لحد
     * النهاردة) - الإيرادات والمصروفات بيتحسبوا من حركات الفترة نفسها
     * (جدول credittransaction) مش من الرصيد التراكمي الكلي للحساب، عشان
     * القائمة تعبّر عن أداء الفترة المحددة بس مش تاريخ الحساب كله.
     */
    public function incomeStatement(Request $request)
    {
        $this->authorize('reports_accounting.income_statement');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $result = $this->computeIncomeStatement($branchId, $dateFrom, $dateTo, $q);

        if ($request->get('export') === 'excel') {
            $rows = [];
            foreach ($result['revenueAccounts'] as $account) {
                $rows[] = [__('reports.revenue'), $account->account_number, $account->name, (float) $account->period_amount];
            }
            foreach ($result['expenseAccounts'] as $account) {
                $rows[] = [__('reports.expenses'), $account->account_number, $account->name, (float) $account->period_amount];
            }

            return ReportExcelExporter::download(
                [__('reports.section'), __('reports.account_number'), __('reports.account_name'), __('reports.total')],
                $rows,
                'income-statement-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.income-statement', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'revenueAccounts' => $result['revenueAccounts'],
            'expenseAccounts' => $result['expenseAccounts'],
            'totalRevenue' => $result['totalRevenue'],
            'totalExpenses' => $result['totalExpenses'],
            'netIncome' => $result['netIncome'],
        ]);
    }

    /**
     * قائمة التغير في حقوق الملكية خلال فترة محددة: رصيد أول الفترة +
     * صافي ربح/خسارة الفترة (من نفس حساب قائمة الدخل) +/- أي حركات
     * أخرى مباشرة على حقوق الملكية (زيادة/سحب رأس مال) = رصيد آخر الفترة.
     *
     * ⚠️ رصيد "أول الفترة" بيتحسب بنفس أسلوب AccountController::statement
     * المتّبع بالفعل في المشروع (الرصيد الحالي مطروح منه أثر حركات
     * الفترة) - يفترض إن date_to = النهاردة أو مفيش حركات بعده، غير كده
     * ممكن يطلع غير دقيق (نفس القيد الموجود في statement الأصلية).
     */
    public function equityChanges(Request $request)
    {
        $this->authorize('reports_accounting.equity_changes');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $equityAccounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->where('account_type', self::EQUITY)
            ->when($branchId, fn ($q) => $this->applyBranchFilter($q, $branchId))
            ->get(['id', 'debtor_current', 'creditor_current']);

        $closingEquity = round((float) $equityAccounts->sum(
            fn ($a) => (float) $a->creditor_current - (float) $a->debtor_current
        ), 2);

        $accountIds = $equityAccounts->pluck('id');

        $periodNet = $accountIds->isEmpty() ? 0.0 : (float) (CreditTransaction::whereIn('customer_id', $accountIds)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('SUM(creditor) - SUM(debtor) as net')
            ->value('net') ?? 0);

        $incomeStatement = $this->computeIncomeStatement($branchId, $dateFrom, $dateTo);
        $netIncomeForPeriod = round($incomeStatement['netIncome'], 2);

        $openingEquity = round($closingEquity - $periodNet, 2);
        $otherMovements = round($periodNet - $netIncomeForPeriod, 2);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.item'), __('reports.total')],
                [
                    [__('reports.opening_equity'), $openingEquity],
                    [__('reports.net_income_for_period'), $netIncomeForPeriod],
                    [__('reports.other_movements'), $otherMovements],
                    [__('reports.closing_equity'), $closingEquity],
                ],
                'equity-changes-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.equity-changes', [
            'branches' => $branches,
            'branchId' => $branchId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'hasEquityAccounts' => $equityAccounts->isNotEmpty(),
            'openingEquity' => $openingEquity,
            'netIncomeForPeriod' => $netIncomeForPeriod,
            'otherMovements' => $otherMovements,
            'closingEquity' => $closingEquity,
        ]);
    }

    /**
     * قائمة التدفقات النقدية المبسّطة: حسابات الخزينة والبنوك بس
     * (parent_account_number = 4 أو 5)، رصيد افتتاحي/صافي حركة/رصيد
     * ختامي لكل حساب خلال الفترة - بالطريقة المباشرة، من غير تقسيم
     * تشغيلي/استثماري/تمويلي (النظام مفيهوش تصنيف لكل حركة يسمح بالتقسيم
     * ده حاليًا - راجع ملاحظة cash_flow_simplified_note في الواجهة).
     */
    public function cashFlow(Request $request)
    {
        $this->authorize('reports_accounting.cash_flow');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $cashAccounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->whereIn('parent_account_number', [self::BANK_PARENT_ACCOUNT_NUMBER, self::CASH_PARENT_ACCOUNT_NUMBER])
            ->when($branchId, fn ($query) => $this->applyBranchFilter($query, $branchId))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('account_number')
            ->get(['id', 'account_number', 'name', 'debtor_current', 'creditor_current']);

        $accountIds = $cashAccounts->pluck('id');

        $movements = $accountIds->isEmpty() ? collect() : CreditTransaction::whereIn('customer_id', $accountIds)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('customer_id, SUM(debtor) - SUM(creditor) as net')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        foreach ($cashAccounts as $account) {
            // حسابات الخزينة/البنوك كلها مدينة بطبعها (تزيد بالمدين) -
            // مفيش orginal_type مميز عندها زي الموردين.
            $closing = (float) $account->debtor_current - (float) $account->creditor_current;
            $net = (float) (optional($movements->get($account->id))->net ?? 0);

            $account->closing_balance = round($closing, 2);
            $account->net_change = round($net, 2);
            $account->opening_balance = round($closing - $net, 2);
        }

        $totalNetChange = round((float) $cashAccounts->sum('net_change'), 2);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.account_number'), __('reports.account_name'), __('reports.opening_balance'), __('reports.net_change'), __('reports.closing_balance')],
                $cashAccounts->map(fn ($a) => [$a->account_number, $a->name, (float) $a->opening_balance, (float) $a->net_change, (float) $a->closing_balance])->toArray(),
                'cash-flow-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.cash-flow', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cashAccounts' => $cashAccounts,
            'totalNetChange' => $totalNetChange,
        ]);
    }

    /**
     * تقرير السندات والقيود: سندات القبض والصرف (App\Models\AccountVoucher)
     * خلال فترة محددة (فلتر فرع + نوع + فترة زي باقي تقارير القسم)، مع
     * عدد القيود اليومية والافتتاحية (App\Models\JournalEntry) المسجّلة
     * في نفس الفترة، ومخطط يومي يقارن إجمالي القبض بإجمالي الصرف.
     *
     * ⚠️ فلتر $type (لو موجود) بيأثر بس على جدول السندات التفصيلي تحت -
     * المخطط والبطاقات الملخّصة دايمًا بتوريّ القبض والصرف مع بعض، عشان
     * "المقارنة" تفضل ليها معنى حتى لو المستخدم فلتر الجدول بنوع واحد.
     *
     * ⚠️ بعد دعم "السندات متعددة البنود" (App\Models\AccountVoucherLine)
     * بقى مصدر المبالغ هنا هو بنود السند مش السند نفسه - فالجدول التفصيلي
     * تحت بيعرض سطر لكل بند (رقم/نوع/تاريخ/خزينة السند بيتكرروا لكل بند
     * تابع لنفس السند)، وعدد السندات (receiptCount/paymentCount) بيتحسب
     * من عدد السندات (account_voucher_id) المميزة مش عدد البنود.
     */
    public function vouchersSummary(Request $request)
    {
        $this->authorize('reports_accounting.vouchers');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $type = in_array($request->input('type'), [AccountVoucher::TYPE_RECEIPT, AccountVoucher::TYPE_PAYMENT], true)
            ? $request->input('type')
            : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $lines = AccountVoucherLine::query()
            ->whereHas('voucher', function ($query) use ($dateFrom, $dateTo, $branchId, $type) {
                $query->whereDate('voucher_date', '>=', $dateFrom)
                    ->whereDate('voucher_date', '<=', $dateTo)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->when($type, fn ($q) => $q->where('type', $type));
            })
            ->with([
                'voucher:id,voucher_number,type,voucher_date,treasury_account_id',
                'voucher.treasuryAccount:id,name',
                'counterpartAccount:id,name',
            ])
            ->get()
            ->sortByDesc(fn ($line) => $line->voucher?->voucher_date)
            ->values();

        $receiptLines = $lines->filter(fn ($line) => $line->voucher?->type === AccountVoucher::TYPE_RECEIPT);
        $paymentLines = $lines->filter(fn ($line) => $line->voucher?->type === AccountVoucher::TYPE_PAYMENT);

        $totalReceipts = round((float) $receiptLines->sum('amount'), 2);
        $totalPayments = round((float) $paymentLines->sum('amount'), 2);

        $openingEntriesCount = JournalEntry::query()
            ->where('entry_type', JournalEntry::TYPE_OPENING)
            ->whereDate('entry_date', '>=', $dateFrom)
            ->whereDate('entry_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->count();

        $dailyEntriesCount = JournalEntry::query()
            ->where('entry_type', JournalEntry::TYPE_DAILY)
            ->whereDate('entry_date', '>=', $dateFrom)
            ->whereDate('entry_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->count();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('vouchers.voucher_no'), __('reports.voucher_type'), __('reports.date'), __('vouchers.treasury_account'), __('reports.counterpart_account'), __('vouchers.amount')],
                $lines->map(fn ($line) => [
                    $line->voucher?->voucher_number,
                    $line->voucher?->type === AccountVoucher::TYPE_RECEIPT ? __('vouchers.receipt') : __('vouchers.payment'),
                    optional($line->voucher?->voucher_date)->format('Y-m-d'),
                    optional($line->voucher?->treasuryAccount)->name ?? '-',
                    optional($line->counterpartAccount)->name ?? '-',
                    (float) $line->amount,
                ])->toArray(),
                'vouchers-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.vouchers', [
            'branches' => $branches,
            'branchId' => $branchId,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'lines' => $lines,
            'receiptCount' => $receiptLines->pluck('account_voucher_id')->unique()->count(),
            'paymentCount' => $paymentLines->pluck('account_voucher_id')->unique()->count(),
            'totalReceipts' => $totalReceipts,
            'totalPayments' => $totalPayments,
            'totalFilteredAmount' => round((float) $lines->sum('amount'), 2),
            'netCashMovement' => round($totalReceipts - $totalPayments, 2),
            'openingEntriesCount' => $openingEntriesCount,
            'dailyEntriesCount' => $dailyEntriesCount,
            'chartTrend' => $this->vouchersDailyTrend($dateFrom, $dateTo, $branchId),
        ]);
    }

    /**
     * إجمالي القبض والصرف يوميًا خلال الفترة - مصدر بيانات مخطط "مقارنة
     * سندات القبض والصرف" في نفس التقرير. نفس أسلوب
     * DashboardController::salesPurchasesTrend (استعلام واحد مجمّع لكل
     * نوع، بعدين استكمال أي يوم من غير حركات بصفر).
     *
     * ⚠️ المبلغ اليومي بقى مجموع بنود account_voucher_lines (مش عمود
     * amount على مستوى السند اللي اتشال بعد دعم تعدد البنود).
     */
    private function vouchersDailyTrend(string $dateFrom, string $dateTo, ?int $branchId): array
    {
        $days = collect();
        $cursor = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        while ($cursor->lte($end)) {
            $days->push($cursor->toDateString());
            $cursor->addDay();
        }

        $dailyTotalsFor = function (string $type) use ($dateFrom, $dateTo, $branchId) {
            return DB::table('account_voucher_lines')
                ->join('account_vouchers', 'account_vouchers.id', '=', 'account_voucher_lines.account_voucher_id')
                ->where('account_vouchers.type', $type)
                ->whereDate('account_vouchers.voucher_date', '>=', $dateFrom)
                ->whereDate('account_vouchers.voucher_date', '<=', $dateTo)
                ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
                ->selectRaw('DATE(account_vouchers.voucher_date) as d, SUM(account_voucher_lines.amount) as total')
                ->groupBy('d')
                ->pluck('total', 'd');
        };

        $receiptsByDay = $dailyTotalsFor(AccountVoucher::TYPE_RECEIPT);
        $paymentsByDay = $dailyTotalsFor(AccountVoucher::TYPE_PAYMENT);

        return $days->map(fn ($d) => [
            'date' => $d,
            'receipts' => round((float) ($receiptsByDay[$d] ?? 0), 2),
            'payments' => round((float) ($paymentsByDay[$d] ?? 0), 2),
        ])->values()->all();
    }

    /**
     * تقرير مراكز التكلفة: مجموع المبالغ المسجلة على كل مركز تكلفة خلال
     * الفترة، من 3 مصادر مختلفة (مركز التكلفة مش عمود موحّد في جدول
     * واحد - كل نوع مستند بيسجله في مكانه):
     * - purchases.cost_center_id (هيدر فاتورة الشراء - عمود واحد لكل فاتورة)
     * - account_voucher_lines.cost_center_id (بند سند قبض/صرف - ممكن
     *   يبقى لكل بند في نفس السند مركز تكلفة مختلف)
     * - journal_entries.cost_center_id (هيدر القيد اليومي اليدوي)
     * أي حساب/فاتورة/سند من غير مركز تكلفة (cost_center_id = null) مش
     * بيظهر في التقرير ده أصلًا - ده تقرير "حسب مركز التكلفة" مش تقرير
     * شامل لكل الحركات.
     */
    public function costCenters(Request $request)
    {
        $this->authorize('reports_accounting.cost_centers');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $purchasesTotals = DB::table('purchases')
            ->whereNotNull('cost_center_id')
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('cost_center_id, SUM(grand_total) as total')
            ->groupBy('cost_center_id')
            ->pluck('total', 'cost_center_id');

        $paymentsTotals = DB::table('account_voucher_lines')
            ->join('account_vouchers', 'account_vouchers.id', '=', 'account_voucher_lines.account_voucher_id')
            ->where('account_vouchers.type', AccountVoucher::TYPE_PAYMENT)
            ->whereNotNull('account_voucher_lines.cost_center_id')
            ->whereDate('account_vouchers.voucher_date', '>=', $dateFrom)
            ->whereDate('account_vouchers.voucher_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->selectRaw('account_voucher_lines.cost_center_id, SUM(account_voucher_lines.amount) as total')
            ->groupBy('account_voucher_lines.cost_center_id')
            ->pluck('total', 'cost_center_id');

        $receiptsTotals = DB::table('account_voucher_lines')
            ->join('account_vouchers', 'account_vouchers.id', '=', 'account_voucher_lines.account_voucher_id')
            ->where('account_vouchers.type', AccountVoucher::TYPE_RECEIPT)
            ->whereNotNull('account_voucher_lines.cost_center_id')
            ->whereDate('account_vouchers.voucher_date', '>=', $dateFrom)
            ->whereDate('account_vouchers.voucher_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->selectRaw('account_voucher_lines.cost_center_id, SUM(account_voucher_lines.amount) as total')
            ->groupBy('account_voucher_lines.cost_center_id')
            ->pluck('total', 'cost_center_id');

        $journalTotals = DB::table('journal_entries')
            ->whereNotNull('cost_center_id')
            ->whereDate('entry_date', '>=', $dateFrom)
            ->whereDate('entry_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('cost_center_id, SUM(total_debit) as total')
            ->groupBy('cost_center_id')
            ->pluck('total', 'cost_center_id');

        $costCenterIds = collect()
            ->merge($purchasesTotals->keys())
            ->merge($paymentsTotals->keys())
            ->merge($receiptsTotals->keys())
            ->merge($journalTotals->keys())
            ->unique();

        $rows = CostCenter::query()
            ->whereIn('id', $costCenterIds)
            ->orderBy('cost_center_ar')
            ->get()
            ->map(function ($cc) use ($purchasesTotals, $paymentsTotals, $receiptsTotals, $journalTotals) {
                $purchases = round((float) ($purchasesTotals[$cc->id] ?? 0), 2);
                $payments = round((float) ($paymentsTotals[$cc->id] ?? 0), 2);
                $receipts = round((float) ($receiptsTotals[$cc->id] ?? 0), 2);
                $journal = round((float) ($journalTotals[$cc->id] ?? 0), 2);

                return (object) [
                    'id' => $cc->id,
                    'name' => $cc->display_name,
                    'purchases' => $purchases,
                    'payments' => $payments,
                    'receipts' => $receipts,
                    'journal' => $journal,
                    // صافي التكلفة = المصروفات (مشتريات + سندات صرف + قيود) ناقص أي
                    // سند قبض مسجّل على نفس مركز التكلفة (مثلًا استرداد مبلغ).
                    'net_total' => round($purchases + $payments + $journal - $receipts, 2),
                ];
            });

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.cost_center'), __('reports.purchases_total'), __('reports.payment_vouchers'), __('reports.receipt_vouchers'), __('reports.journal_entries'), __('reports.net_total')],
                $rows->map(fn ($r) => [$r->name, $r->purchases, $r->payments, $r->receipts, $r->journal, $r->net_total])->toArray(),
                'cost-centers-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.cost-centers', [
            'branches' => $branches,
            'branchId' => $branchId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
        ]);
    }

    /**
     * تقرير أعمار ديون العملاء والموردين: لكل حساب عميل (orginal_type=1)
     * أو مورد (orginal_type=2) - الرصيد الحالي (current_balance) مقسّم
     * على فئات عمرية (0-30 / 31-60 / 61-90 / أكتر من 90 يوم) حسب "عمر"
     * كل جزء من المديونية لسه معلّق.
     *
     * الطريقة: بنمشي على كل حركات credittransaction الخاصة بالحساب
     * بالترتيب الزمني، وبنطبّق كل "سداد" (دفعة عكس اتجاه الحساب) على
     * أقدم "مديونية" (حركة في نفس اتجاه الحساب) لسه متبقّى منها حاجة -
     * بالظبط زي أي تقرير أعمار ديون FIFO تقليدي. اللي فاضل في الآخر من
     * كل حركة مديونية قديمة هو المتبقي منها، وعمره بيتحسب من تاريخها.
     * ⚠️ ده تبسيط مقصود (زي باقي تقارير القسم ده) - مبني على افتراض إن
     * السداد بيتم على الأقدم أولًا، مش بمطابقة صريحة فاتورة بفاتورة (النظام
     * الحالي مفيهوش ربط مباشر بين كل حركة سداد وفاتورة بعينها).
     */
    public function receivablesPayablesAging(Request $request)
    {
        $this->authorize('reports_accounting.aging');
        $type = in_array($request->input('type'), ['customer', 'supplier'], true) ? $request->input('type') : null;
        $orginalType = $type === 'supplier' ? 2 : ($type === 'customer' ? 1 : null);
        $q = trim((string) $request->input('q'));

        $accounts = FinancialAccount::query()
            ->whereIn('orginal_type', [1, 2])
            ->when($orginalType, fn ($query) => $query->where('orginal_type', $orginalType))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('name')
            ->get(['id', 'name', 'account_number', 'orginal_type', 'current_balance']);

        $today = Carbon::now('Asia/Riyadh')->startOfDay();

        $rows = $accounts->map(function ($account) use ($today) {
            $buckets = $this->agingBucketsForAccount($account, $today);

            return (object) [
                'name' => $account->name,
                'account_number' => $account->account_number,
                'type_label' => (int) $account->orginal_type === 2 ? __('reports.supplier') : __('reports.customer'),
                'bucket_0_30' => $buckets['bucket_0_30'],
                'bucket_31_60' => $buckets['bucket_31_60'],
                'bucket_61_90' => $buckets['bucket_61_90'],
                'bucket_over_90' => $buckets['bucket_over_90'],
                'total' => $buckets['total'],
            ];
        })->filter(fn ($r) => abs($r->total) > 0.009)->values();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.account_name'), __('reports.aging_type'), __('reports.aging_0_30'), __('reports.aging_31_60'), __('reports.aging_61_90'), __('reports.aging_over_90'), __('reports.net_total')],
                $rows->map(fn ($r) => [$r->name, $r->type_label, $r->bucket_0_30, $r->bucket_31_60, $r->bucket_61_90, $r->bucket_over_90, $r->total])->toArray(),
                'aging-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.aging', [
            'type' => $type,
            'q' => $q,
            'rows' => $rows,
        ]);
    }

    /**
     * راجع تعليق receivablesPayablesAging() أعلاه لشرح خوارزمية FIFO
     * الكاملة. بترجع مصفوفة [bucket_0_30, bucket_31_60, bucket_61_90,
     * bucket_over_90, total] بالجنيه/الريال (مش نسب مئوية).
     */
    private function agingBucketsForAccount(FinancialAccount $account, Carbon $today): array
    {
        $isSupplier = (int) $account->orginal_type === 2;

        $transactions = CreditTransaction::where('customer_id', $account->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['created_at', 'debtor', 'creditor']);

        // قايمة المديونيات القديمة اللي لسه متبقي منها حاجة - كل عنصر
        // [date, amount]، بترتيب الأقدم أولًا عشان السداد الجديد يتطبق
        // عليها الأول (FIFO).
        $outstanding = [];

        foreach ($transactions as $t) {
            $increase = $isSupplier ? (float) $t->creditor : (float) $t->debtor;
            $decrease = $isSupplier ? (float) $t->debtor : (float) $t->creditor;

            if ($increase > 0) {
                $outstanding[] = ['date' => $t->created_at, 'amount' => $increase];
            }

            if ($decrease > 0) {
                $remaining = $decrease;
                foreach ($outstanding as &$entry) {
                    if ($remaining <= 0.009) {
                        break;
                    }
                    $consume = min($entry['amount'], $remaining);
                    $entry['amount'] -= $consume;
                    $remaining -= $consume;
                }
                unset($entry);
                $outstanding = array_values(array_filter($outstanding, fn ($e) => $e['amount'] > 0.009));
            }
        }

        $buckets = ['bucket_0_30' => 0.0, 'bucket_31_60' => 0.0, 'bucket_61_90' => 0.0, 'bucket_over_90' => 0.0];

        foreach ($outstanding as $entry) {
            $age = $entry['date'] ? $today->diffInDays(Carbon::parse($entry['date'])->startOfDay()) : 0;

            if ($age <= 30) {
                $buckets['bucket_0_30'] += $entry['amount'];
            } elseif ($age <= 60) {
                $buckets['bucket_31_60'] += $entry['amount'];
            } elseif ($age <= 90) {
                $buckets['bucket_61_90'] += $entry['amount'];
            } else {
                $buckets['bucket_over_90'] += $entry['amount'];
            }
        }

        $buckets = array_map(fn ($v) => round($v, 2), $buckets);
        $buckets['total'] = round(array_sum($buckets), 2);

        return $buckets;
    }

    /**
     * تقرير المصروفات: كل حسابات "مصروفات" في شجرة الحسابات (account_type
     * = self::EXPENSES = 4) وإجمالي كل حساب خلال الفترة - نفس منطق حساب
     * period_amount في computeIncomeStatement() بالظبط (مدين - دائن، لإن
     * حسابات المصروفات مدينة بطبعها)، لكن هنا معروضة كتقرير تفصيلي مستقل
     * (مش بس رقم إجمالي واحد في قائمة الدخل).
     */
    public function expensesReport(Request $request)
    {
        $this->authorize('reports_accounting.expenses');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $accountId = $request->filled('account_id') ? (int) $request->input('account_id') : null;
        $accountOptions = $this->entitySelectedOption($accountId, FinancialAccount::class);
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $accounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->where('account_type', self::EXPENSES)
            ->when($branchId, fn ($query) => $this->applyBranchFilter($query, $branchId))
            ->when($accountId, fn ($query) => $query->where('id', $accountId))
            ->orderBy('account_number')
            ->get(['id', 'account_number', 'name', 'branchs_id']);

        $accountIds = $accounts->pluck('id');

        $movements = $accountIds->isEmpty() ? collect() : CreditTransaction::whereIn('customer_id', $accountIds)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('customer_id, SUM(debtor) as total_debtor, SUM(creditor) as total_creditor')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        foreach ($accounts as $account) {
            $movement = $movements->get($account->id);
            $account->period_amount = round((float) ($movement->total_debtor ?? 0) - (float) ($movement->total_creditor ?? 0), 2);
            $account->is_branch_specific = $branchId && (int) $account->branchs_id === $branchId;
        }

        // نستبعد الحسابات اللي مالهاش أي حركة في الفترة عشان الجدول
        // يفضل مختصر ومركّز على المصروفات اللي فعلًا حصلت.
        $accounts = $accounts->filter(fn ($a) => abs($a->period_amount) > 0.009)
            ->sortByDesc('period_amount')
            ->values();

        $totalExpenses = round((float) $accounts->sum('period_amount'), 2);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.account_number'), __('reports.account_name'), __('reports.net_total')],
                $accounts->map(fn ($a) => [$a->account_number, $a->name, (float) $a->period_amount])->toArray(),
                'expenses-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.expenses', [
            'branches' => $branches,
            'branchId' => $branchId,
            'accountId' => $accountId,
            'accountOptions' => $accountOptions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accounts' => $accounts,
            'totalExpenses' => $totalExpenses,
        ]);
    }

    /**
     * بحث سريع (Ajax) عن حسابات المصروفات فقط، لاستخدامه في قايمة
     * TomSelect بتقرير المصروفات بدل مربع البحث النصي القديم - نفس فكرة
     * CustomerController::search() بالظبط بس مقصور على account_type=EXPENSES
     * (الحسابات الفرعية بس، مش الحسابات الأب).
     */
    public function expensesAccountsSearch(Request $request)
    {
        $this->authorize('reports_accounting.expenses');
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $accounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->where('account_type', self::EXPENSES)
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('account_number', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'account_number']);

        return response()->json($accounts->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'text' => $a->name . ($a->account_number ? " ({$a->account_number})" : ''),
        ]));
    }

    /**
     * قائمة حسابات العملاء والموردين بأرصدتهم الحالية - صفحة أولى بتترندر
     * كل الحسابات عادي، والبحث فيها بعد كده Ajax حي (بدون إعادة تحميل
     * الصفحة) على الـ endpoint اللي تحت (customerSupplierAccountsSearch).
     */
    public function customerSupplierAccounts(Request $request)
    {
        $this->authorize('reports_accounting.customer_supplier_accounts');
        $type = in_array($request->input('type'), ['customer', 'supplier'], true) ? $request->input('type') : null;
        $orginalType = $type === 'supplier' ? 2 : ($type === 'customer' ? 1 : null);
        $q = trim((string) $request->input('q'));

        $accounts = FinancialAccount::query()
            ->whereIn('orginal_type', [1, 2])
            ->when($orginalType, fn ($query) => $query->where('orginal_type', $orginalType))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('name')
            ->get(['id', 'name', 'account_number', 'orginal_type', 'current_balance', 'active']);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.account_number'), __('reports.account_name'), __('reports.aging_type'), __('reports.net_total')],
                $accounts->map(fn ($a) => [
                    $a->account_number,
                    $a->name,
                    (int) $a->orginal_type === 2 ? __('reports.supplier') : __('reports.customer'),
                    (float) $a->current_balance,
                ])->toArray(),
                'customer-supplier-accounts-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.customer-supplier-accounts', [
            'type' => $type,
            'q' => $q,
            'accounts' => $accounts,
        ]);
    }

    /**
     * بحث Ajax حي لقائمة حسابات العملاء والموردين - بيرجع JSON بس (مش
     * View)، بيتنادى من JS في الشاشة نفسها (fetch) على كل كبسة زرار في
     * مربع البحث من غير أي إعادة تحميل. نفس فكرة AccountController::search()
     * الموجودة بالفعل، بس هنا مقصورة على orginal_type=1/2 ومعاها الرصيد.
     */
    public function customerSupplierAccountsSearch(Request $request)
    {
        $this->authorize('reports_accounting.customer_supplier_accounts');
        $q = trim((string) $request->input('q'));
        $type = in_array($request->input('type'), ['customer', 'supplier'], true) ? $request->input('type') : null;
        $orginalType = $type === 'supplier' ? 2 : ($type === 'customer' ? 1 : null);

        $accounts = FinancialAccount::query()
            ->whereIn('orginal_type', [1, 2])
            ->when($orginalType, fn ($query) => $query->where('orginal_type', $orginalType))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'account_number', 'orginal_type', 'current_balance', 'active']);

        return response()->json($accounts->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'account_number' => $a->account_number,
            'type_label' => (int) $a->orginal_type === 2 ? __('reports.supplier') : __('reports.customer'),
            'current_balance' => round((float) $a->current_balance, 2),
            'active' => (bool) $a->active,
            'statement_url' => route('accounts.statement', $a->id),
        ])->values());
    }

    /**
     * التقرير الختامي اليومي: خلاصة يوم واحد بالكامل - المبيعات مقسّمة
     * حسب طريقة الدفع (نقدي/شبكة/تحويل بنك/آجل)، إجمالي المشتريات، سندات
     * القبض والصرف، والقيود اليومية اللي حصلت في نفس اليوم. مفيد لإقفال
     * اليوم ومطابقة الخزينة.
     *
     * ملحوظة: أعمدة الدفع في الفواتير (migration جدول invoices) بتفرّق
     * بين cash/bank_transfer/card/credit/split كـ payment_method، لكن
     * "شبكة" و"تحويل بنك" الاتنين بيتخزنوا في نفس عمود bank_amount، فبنرجع
     * لعمود payment_method نفسه عشان نفرّق بينهم. أما الفواتير المقسّمة
     * (split) فمفيش فيها تمييز بين شبكة وتحويل بنك أصلاً (بس cash/bank/credit)
     * فبنحط جزء الـ bank بتاعها مع "شبكة" افتراضيًا - راجع daily_closing_split_note.
     */
    public function dailyClosingReport(Request $request)
    {
        $this->authorize('reports_accounting.daily_closing');

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $date = $request->filled('date') ? $request->input('date') : Carbon::now('Asia/Riyadh')->toDateString();

        // ===== المبيعات مقسّمة حسب طريقة الدفع =====
        $invoices = Invoice::query()
            ->whereDate('issue_date', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get(['id', 'payment_method', 'subtotal', 'tax_amount', 'discount_amount', 'cash_amount', 'bank_amount', 'credit_amount']);

        $salesCash = 0.0;
        $salesCard = 0.0;
        $salesBankTransfer = 0.0;
        $salesCredit = 0.0;
        $hasSplitInvoices = false;

        foreach ($invoices as $invoice) {
            $net = (float) $invoice->subtotal + (float) $invoice->tax_amount - (float) $invoice->discount_amount;

            switch ($invoice->payment_method) {
                case 'cash':
                    $salesCash += $net;
                    break;
                case 'card':
                    $salesCard += $net;
                    break;
                case 'bank_transfer':
                    $salesBankTransfer += $net;
                    break;
                case 'credit':
                    $salesCredit += $net;
                    break;
                case 'split':
                    $hasSplitInvoices = true;
                    $salesCash += (float) $invoice->cash_amount;
                    $salesCard += (float) $invoice->bank_amount;
                    $salesCredit += (float) $invoice->credit_amount;
                    break;
            }
        }

        $salesCash = round($salesCash, 2);
        $salesCard = round($salesCard, 2);
        $salesBankTransfer = round($salesBankTransfer, 2);
        $salesCredit = round($salesCredit, 2);
        $totalSales = round($salesCash + $salesCard + $salesBankTransfer + $salesCredit, 2);
        $invoicesCount = $invoices->count();

        // ===== المشتريات =====
        $purchases = Purchase::query()
            ->whereDate('issue_date', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get(['id', 'grand_total']);
        $totalPurchases = round((float) $purchases->sum('grand_total'), 2);
        $purchasesCount = $purchases->count();

        // ===== سندات القبض والصرف =====
        $voucherLines = AccountVoucherLine::query()
            ->whereHas('voucher', function ($q) use ($date, $branchId) {
                $q->whereDate('voucher_date', $date)
                    ->when($branchId, fn ($qq) => $qq->where('branch_id', $branchId));
            })
            ->with(['voucher', 'counterpartAccount'])
            ->get();

        $receiptLines = $voucherLines->filter(fn ($line) => $line->voucher?->type === AccountVoucher::TYPE_RECEIPT)->values();
        $paymentLines = $voucherLines->filter(fn ($line) => $line->voucher?->type === AccountVoucher::TYPE_PAYMENT)->values();
        $totalReceipts = round((float) $receiptLines->sum('amount'), 2);
        $totalPayments = round((float) $paymentLines->sum('amount'), 2);
        $receiptVouchersCount = $receiptLines->pluck('account_voucher_id')->unique()->count();
        $paymentVouchersCount = $paymentLines->pluck('account_voucher_id')->unique()->count();

        // ===== القيود اليومية =====
        $journalEntries = JournalEntry::query()
            ->whereDate('entry_date', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('entry_number')
            ->get(['id', 'entry_number', 'entry_date', 'entry_type', 'description', 'total_debit', 'total_credit']);
        $journalEntriesCount = $journalEntries->count();
        $totalJournalDebit = round((float) $journalEntries->sum('total_debit'), 2);
        $totalJournalCredit = round((float) $journalEntries->sum('total_credit'), 2);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.section'), __('reports.description'), __('reports.amount')],
                [
                    [__('reports.sales_by_cash'), '', $salesCash],
                    [__('reports.sales_by_card'), '', $salesCard],
                    [__('reports.sales_by_bank_transfer'), '', $salesBankTransfer],
                    [__('reports.sales_by_credit'), '', $salesCredit],
                    [__('reports.total_sales'), __('reports.invoices_count') . ': ' . $invoicesCount, $totalSales],
                    [__('reports.total_purchases'), __('reports.purchases_count') . ': ' . $purchasesCount, $totalPurchases],
                    [__('reports.receipt_vouchers_total'), __('reports.receipt_vouchers_count') . ': ' . $receiptVouchersCount, $totalReceipts],
                    [__('reports.payment_vouchers_total'), __('reports.payment_vouchers_count') . ': ' . $paymentVouchersCount, $totalPayments],
                    [__('reports.daily_entries_count'), '', $journalEntriesCount],
                    [__('reports.journal_total_debit'), '', $totalJournalDebit],
                    [__('reports.journal_total_credit'), '', $totalJournalCredit],
                ],
                'daily-closing-' . $date . '.xlsx'
            );
        }

        return view('reports.accounts.daily-closing', [
            'branches' => $branches,
            'branchId' => $branchId,
            'date' => $date,
            'salesCash' => $salesCash,
            'salesCard' => $salesCard,
            'salesBankTransfer' => $salesBankTransfer,
            'salesCredit' => $salesCredit,
            'totalSales' => $totalSales,
            'invoicesCount' => $invoicesCount,
            'hasSplitInvoices' => $hasSplitInvoices,
            'totalPurchases' => $totalPurchases,
            'purchasesCount' => $purchasesCount,
            'totalReceipts' => $totalReceipts,
            'totalPayments' => $totalPayments,
            'receiptVouchersCount' => $receiptVouchersCount,
            'paymentVouchersCount' => $paymentVouchersCount,
            'receiptLines' => $receiptLines,
            'paymentLines' => $paymentLines,
            'journalEntries' => $journalEntries,
            'journalEntriesCount' => $journalEntriesCount,
            'totalJournalDebit' => $totalJournalDebit,
            'totalJournalCredit' => $totalJournalCredit,
        ]);
    }

    /**
     * تقرير الإقرار الضريبي (ضريبة القيمة المضافة):
     * ملخص شامل ودقيق لضريبة المخرجات (مبيعات - مرتجع مبيعات)
     * وضريبة المدخلات (مشتريات - مرتجع مشتريات + مصروفات خاضعة للضريبة)،
     * وحساب صافي الضريبة المستحقة للسداد أو المستردة مع بيان عدد الفواتير والمستندات.
     */
    public function taxReport(Request $request)
    {
        $this->authorize('reports_accounting.tax_report');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        // 1. المبيعات (فواتير المبيعات الصادرة)
        $salesQuery = Invoice::query()
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $salesCount = (clone $salesQuery)->count();
        $salesTaxable = round((float) (clone $salesQuery)->selectRaw('COALESCE(SUM(subtotal - discount_amount), 0) as val')->value('val'), 2);
        $salesTax = round((float) (clone $salesQuery)->selectRaw('COALESCE(SUM(tax_amount), 0) as val')->value('val'), 2);
        $salesTotalWithTax = round($salesTaxable + $salesTax, 2);

        $salesInvoices = (clone $salesQuery)
            ->with(['customer:id,name'])
            ->orderByDesc('issue_date')
            ->limit(100)
            ->get(['id', 'invoice_number', 'customer_id', 'issue_date', 'subtotal', 'discount_amount', 'tax_amount', 'branch_id']);

        // 2. مرتجع المبيعات
        $salesReturnsQuery = InvoiceReturn::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $salesReturnsCount = (clone $salesReturnsQuery)->count();
        $salesReturnsTaxable = round((float) (clone $salesReturnsQuery)->selectRaw('COALESCE(SUM((unit_price * quantity) - discount_amount), 0) as val')->value('val'), 2);
        $salesReturnsTax = round((float) (clone $salesReturnsQuery)->selectRaw('COALESCE(SUM(tax_amount), 0) as val')->value('val'), 2);
        $salesReturnsTotalWithTax = round($salesReturnsTaxable + $salesReturnsTax, 2);

        $salesReturnsList = (clone $salesReturnsQuery)
            ->with(['invoice:id,invoice_number', 'product:id,name'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // صافي المبيعات
        $netSalesTaxable = round($salesTaxable - $salesReturnsTaxable, 2);
        $netSalesTax = round($salesTax - $salesReturnsTax, 2);
        $netSalesTotalWithTax = round($salesTotalWithTax - $salesReturnsTotalWithTax, 2);

        // 3. المشتريات
        $purchasesQuery = Purchase::query()
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $purchasesCount = (clone $purchasesQuery)->count();
        $purchasesTaxable = round((float) (clone $purchasesQuery)->selectRaw('COALESCE(SUM(subtotal - discount_amount), 0) as val')->value('val'), 2);
        $purchasesTax = round((float) (clone $purchasesQuery)->selectRaw('COALESCE(SUM(tax_amount), 0) as val')->value('val'), 2);
        $purchasesTotalWithTax = round($purchasesTaxable + $purchasesTax, 2);

        $purchasesList = (clone $purchasesQuery)
            ->with(['supplier:id,name'])
            ->orderByDesc('issue_date')
            ->limit(100)
            ->get(['id', 'purchase_number', 'supplier_id', 'issue_date', 'subtotal', 'discount_amount', 'tax_amount', 'grand_total', 'branch_id']);

        // 4. مرتجع المشتريات
        $purchaseReturnsQuery = PurchaseReturn::query()
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $purchaseReturnsCount = (clone $purchaseReturnsQuery)->count();
        $purchaseReturnsTaxable = round((float) (clone $purchaseReturnsQuery)->selectRaw('COALESCE(SUM(subtotal - discount_amount), 0) as val')->value('val'), 2);
        $purchaseReturnsTax = round((float) (clone $purchaseReturnsQuery)->selectRaw('COALESCE(SUM(tax_amount), 0) as val')->value('val'), 2);
        $purchaseReturnsTotalWithTax = round($purchaseReturnsTaxable + $purchaseReturnsTax, 2);

        $purchaseReturnsList = (clone $purchaseReturnsQuery)
            ->with(['supplier:id,name'])
            ->orderByDesc('return_date')
            ->limit(100)
            ->get(['id', 'return_number', 'supplier_id', 'return_date', 'subtotal', 'discount_amount', 'tax_amount', 'grand_total', 'branch_id']);

        // صافي المشتريات
        $netPurchasesTaxable = round($purchasesTaxable - $purchaseReturnsTaxable, 2);
        $netPurchasesTax = round($purchasesTax - $purchaseReturnsTax, 2);
        $netPurchasesTotalWithTax = round($purchasesTotalWithTax - $purchaseReturnsTotalWithTax, 2);

        // 5. المصروفات الخاضعة للضريبة (من سندات الصرف)
        $expensesQuery = DB::table('account_voucher_lines')
            ->join('account_vouchers', 'account_vouchers.id', '=', 'account_voucher_lines.account_voucher_id')
            ->where('account_vouchers.type', AccountVoucher::TYPE_PAYMENT)
            ->where(function ($q) {
                $q->where('account_voucher_lines.is_taxable', 1)
                  ->orWhere('account_voucher_lines.tax_amount', '>', 0);
            })
            ->whereDate('account_vouchers.voucher_date', '>=', $dateFrom)
            ->whereDate('account_vouchers.voucher_date', '<=', $dateTo)
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId));

        $taxableExpensesCount = (clone $expensesQuery)->distinct('account_vouchers.id')->count('account_vouchers.id');
        $expensesTaxable = round((float) (clone $expensesQuery)->selectRaw('COALESCE(SUM(account_voucher_lines.net_amount), 0) as val')->value('val'), 2);
        $expensesTax = round((float) (clone $expensesQuery)->selectRaw('COALESCE(SUM(account_voucher_lines.tax_amount), 0) as val')->value('val'), 2);
        $expensesTotalWithTax = round((float) (clone $expensesQuery)->selectRaw('COALESCE(SUM(account_voucher_lines.amount), 0) as val')->value('val'), 2);

        $accountsTable = (new FinancialAccount())->getTable();
        $taxableExpensesList = (clone $expensesQuery)
            ->leftJoin($accountsTable, "{$accountsTable}.id", '=', 'account_voucher_lines.counterpart_account_id')
            ->select([
                'account_vouchers.id as voucher_id',
                'account_vouchers.voucher_number',
                'account_vouchers.voucher_date',
                "{$accountsTable}.name as expense_account_name",
                'account_voucher_lines.description',
                'account_voucher_lines.tax_rate',
                'account_voucher_lines.net_amount',
                'account_voucher_lines.tax_amount',
                'account_voucher_lines.amount as total_amount',
            ])
            ->orderByDesc('account_vouchers.voucher_date')
            ->limit(100)
            ->get();

        // 6. الإجماليات وصافي الضريبة
        $totalInputTax = round($netPurchasesTax + $expensesTax, 2);
        $netTaxDue = round($netSalesTax - $totalInputTax, 2);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.item'), __('reports.taxable_amount'), __('reports.tax_amount'), __('reports.total_with_tax'), __('reports.invoices_count')],
                [
                    [__('reports.sales_taxable'), $salesTaxable, $salesTax, $salesTotalWithTax, $salesCount],
                    [__('reports.sales_returns_taxable'), $salesReturnsTaxable, $salesReturnsTax, $salesReturnsTotalWithTax, $salesReturnsCount],
                    [__('reports.net_sales_tax'), $netSalesTaxable, $netSalesTax, $netSalesTotalWithTax, '-'],
                    ['---', '---', '---', '---', '---'],
                    [__('reports.purchases_taxable'), $purchasesTaxable, $purchasesTax, $purchasesTotalWithTax, $purchasesCount],
                    [__('reports.purchases_returns_taxable'), $purchaseReturnsTaxable, $purchaseReturnsTax, $purchaseReturnsTotalWithTax, $purchaseReturnsCount],
                    [__('reports.net_purchases_tax'), $netPurchasesTaxable, $netPurchasesTax, $netPurchasesTotalWithTax, '-'],
                    ['---', '---', '---', '---', '---'],
                    [__('reports.expenses_taxable'), $expensesTaxable, $expensesTax, $expensesTotalWithTax, $taxableExpensesCount],
                    ['---', '---', '---', '---', '---'],
                    [__('reports.total_input_tax'), round($netPurchasesTaxable + $expensesTaxable, 2), $totalInputTax, round($netPurchasesTotalWithTax + $expensesTotalWithTax, 2), '-'],
                    [__('reports.net_declaration_result'), '-', $netTaxDue, '-', '-'],
                ],
                'tax-declaration-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.accounts.tax', [
            'branches' => $branches,
            'branchId' => $branchId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'salesCount' => $salesCount,
            'salesTaxable' => $salesTaxable,
            'salesTax' => $salesTax,
            'salesTotalWithTax' => $salesTotalWithTax,
            'salesInvoices' => $salesInvoices,
            'salesReturnsCount' => $salesReturnsCount,
            'salesReturnsTaxable' => $salesReturnsTaxable,
            'salesReturnsTax' => $salesReturnsTax,
            'salesReturnsTotalWithTax' => $salesReturnsTotalWithTax,
            'salesReturnsList' => $salesReturnsList,
            'netSalesTaxable' => $netSalesTaxable,
            'netSalesTax' => $netSalesTax,
            'netSalesTotalWithTax' => $netSalesTotalWithTax,
            'purchasesCount' => $purchasesCount,
            'purchasesTaxable' => $purchasesTaxable,
            'purchasesTax' => $purchasesTax,
            'purchasesTotalWithTax' => $purchasesTotalWithTax,
            'purchasesList' => $purchasesList,
            'purchaseReturnsCount' => $purchaseReturnsCount,
            'purchaseReturnsTaxable' => $purchaseReturnsTaxable,
            'purchaseReturnsTax' => $purchaseReturnsTax,
            'purchaseReturnsTotalWithTax' => $purchaseReturnsTotalWithTax,
            'purchaseReturnsList' => $purchaseReturnsList,
            'netPurchasesTaxable' => $netPurchasesTaxable,
            'netPurchasesTax' => $netPurchasesTax,
            'netPurchasesTotalWithTax' => $netPurchasesTotalWithTax,
            'taxableExpensesCount' => $taxableExpensesCount,
            'expensesTaxable' => $expensesTaxable,
            'expensesTax' => $expensesTax,
            'expensesTotalWithTax' => $expensesTotalWithTax,
            'taxableExpensesList' => $taxableExpensesList,
            'totalInputTax' => $totalInputTax,
            'netTaxDue' => $netTaxDue,
        ]);
    }

    // =====================================================================
    // قسم المبيعات - راجع Invoice/InvoiceItem/InvoiceReturn. الفرع هنا
    // "صارم" (branch_id عمود عادي غير قابل للـ null في الفواتير الفعلية)
    // مش زي فلتر الفرع "المرن" في قسم الحسابات، فمفيش داعي لـ
    // applyBranchFilter هنا - where('branch_id', $branchId) مباشرة.
    // =====================================================================

    public function salesIndex()
    {
        $this->authorizeAnyReport('reports_sales');
        return view('reports.sales.index');
    }

    /**
     * ملخص المبيعات: كل فاتورة صدرت خلال الفترة المحددة بإجمالياتها.
     */
    public function salesSummary(Request $request)
    {
        $this->authorize('reports_sales.summary');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $customerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $customers = $this->entitySelectedOption($customerId, Customer::class);
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $invoices = Invoice::query()
            ->with(['customer:id,name', 'items.product:id,name'])
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->when($q !== '', fn ($query) => $query->where('invoice_number', 'like', "%{$q}%"))
            ->orderByDesc('issue_date')
            ->get(['id', 'invoice_number', 'customer_id', 'branch_id', 'issue_date', 'subtotal', 'tax_amount', 'discount_amount', 'total_quantity']);

        foreach ($invoices as $invoice) {
            $invoice->net_total = round((float) $invoice->subtotal + (float) $invoice->tax_amount - (float) $invoice->discount_amount, 2);
        }

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.invoice_number'), __('reports.customer'), __('reports.date'), __('reports.quantity'), __('reports.subtotal'), __('reports.tax'), __('reports.discount'), __('reports.net_total')],
                $invoices->map(fn ($i) => [
                    $i->invoice_number,
                    optional($i->customer)->name ?? '-',
                    optional($i->issue_date)->format('Y-m-d'),
                    (float) $i->total_quantity,
                    (float) $i->subtotal,
                    (float) $i->tax_amount,
                    (float) $i->discount_amount,
                    (float) $i->net_total,
                ])->toArray(),
                'sales-summary-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.summary', [
            'branches' => $branches,
            'branchId' => $branchId,
            'customers' => $customers,
            'customerId' => $customerId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'invoices' => $invoices,
            'totalSubtotal' => round((float) $invoices->sum('subtotal'), 2),
            'totalTax' => round((float) $invoices->sum('tax_amount'), 2),
            'totalDiscount' => round((float) $invoices->sum('discount_amount'), 2),
            'totalNet' => round((float) $invoices->sum('net_total'), 2),
            'totalQuantity' => round((float) $invoices->sum('total_quantity'), 2),
        ]);
    }

    /**
     * تقرير أرباح المبيعات: كل فاتورة صدرت خلال الفترة مع تكلفة بضاعتها
     * ومجمل الربح ونسبة هامش الربح وتفاصيل أرباح كل بند.
     */
    public function salesProfits(Request $request)
    {
        $this->authorize('reports_sales.profits');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $customerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $customers = $this->entitySelectedOption($customerId, Customer::class);
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $invoices = Invoice::query()
            ->with([
                'customer:id,name',
                'creator:id,name',
                'branch:id,name',
                'items.product:id,name,code,purchase_price,average_cost',
            ])
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->when($q !== '', fn ($query) => $query->where('invoice_number', 'like', "%{$q}%"))
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->sales_revenue = round((float) $invoice->subtotal - (float) $invoice->discount_amount, 2);
            $invoice->net_total = round((float) $invoice->subtotal + (float) $invoice->tax_amount - (float) $invoice->discount_amount, 2);

            $invoiceCost = 0.0;
            foreach ($invoice->items as $item) {
                $product = $item->product;
                $unitCost = $product ? (float) ($product->average_cost > 0 ? $product->average_cost : $product->purchase_price) : 0.0;
                $itemQty = (float) $item->quantity;
                $itemCost = round($unitCost * $itemQty, 2);
                $itemRevenue = round(((float) $item->unit_price * $itemQty) - (float) $item->discount_amount, 2);
                $itemProfit = round($itemRevenue - $itemCost, 2);
                $itemMargin = $itemRevenue > 0 ? round(($itemProfit / $itemRevenue) * 100, 1) : 0.0;

                $item->unit_cost = $unitCost;
                $item->total_cost = $itemCost;
                $item->revenue = $itemRevenue;
                $item->profit = $itemProfit;
                $item->margin = $itemMargin;

                $invoiceCost += $itemCost;
            }

            $invoice->total_cost = round($invoiceCost, 2);
            $invoice->profit = round($invoice->sales_revenue - $invoice->total_cost, 2);
            $invoice->profit_margin = $invoice->sales_revenue > 0 ? round(($invoice->profit / $invoice->sales_revenue) * 100, 1) : 0.0;
        }

        $totalSales = round((float) $invoices->sum('sales_revenue'), 2);
        $totalCost = round((float) $invoices->sum('total_cost'), 2);
        $totalProfit = round((float) $invoices->sum('profit'), 2);
        $totalNet = round((float) $invoices->sum('net_total'), 2);
        $totalQuantity = round((float) $invoices->sum('total_quantity'), 2);
        $avgMargin = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0.0;

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.invoice_number'), __('reports.customer'), __('reports.date'), __('reports.quantity'), __('reports.total_sales'), __('reports.total_cost'), __('reports.profit'), __('reports.profit_margin_percent')],
                $invoices->map(fn ($i) => [
                    $i->invoice_number,
                    optional($i->customer)->name ?? '-',
                    optional($i->issue_date)->format('Y-m-d'),
                    (float) $i->total_quantity,
                    (float) $i->sales_revenue,
                    (float) $i->total_cost,
                    (float) $i->profit,
                    (float) $i->profit_margin . '%',
                ])->toArray(),
                'sales-profits-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.profits', [
            'branches' => $branches,
            'branchId' => $branchId,
            'customers' => $customers,
            'customerId' => $customerId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'invoices' => $invoices,
            'totalSales' => $totalSales,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'totalNet' => $totalNet,
            'totalQuantity' => $totalQuantity,
            'avgMargin' => $avgMargin,
        ]);
    }

    /**
     * تقرير أرباح مبيعات الموظفين: إجمالي المبيعات، التكلفة، وصافي الربح
     * ونسبة هامش الربح ومعدل ربح الفاتورة لكل موظف.
     */
    public function salesEmployeeProfits(Request $request)
    {
        $this->authorize('reports_sales.employee_profits');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        $invoices = Invoice::query()
            ->with([
                'items.product:id,average_cost,purchase_price',
                'creator:id,name',
            ])
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($userId, fn ($query) => $query->where('created_by', $userId))
            ->get();

        $employeesMap = [];
        foreach ($invoices as $inv) {
            $uid = $inv->created_by ?: 0;
            $uName = optional($inv->creator)->name ?? __('reports.all_employees');
            if (! isset($employeesMap[$uid])) {
                $employeesMap[$uid] = (object) [
                    'user_id' => $uid,
                    'user_name' => $uName,
                    'invoices_count' => 0,
                    'total_quantity' => 0.0,
                    'total_sales' => 0.0,
                    'total_cost' => 0.0,
                    'net_profit' => 0.0,
                    'profit_margin' => 0.0,
                    'avg_profit_per_invoice' => 0.0,
                ];
            }
            $employeesMap[$uid]->invoices_count++;
            $employeesMap[$uid]->total_quantity += (float) $inv->total_quantity;
            $invRevenue = (float) $inv->subtotal - (float) $inv->discount_amount;
            $employeesMap[$uid]->total_sales += $invRevenue;

            $invCost = 0.0;
            foreach ($inv->items as $item) {
                $p = $item->product;
                $uCost = $p ? (float) ($p->average_cost > 0 ? $p->average_cost : $p->purchase_price) : 0.0;
                $invCost += $uCost * (float) $item->quantity;
            }
            $employeesMap[$uid]->total_cost += $invCost;
            $employeesMap[$uid]->net_profit += ($invRevenue - $invCost);
        }

        $rows = collect($employeesMap)->map(function ($emp) {
            $emp->total_quantity = round($emp->total_quantity, 2);
            $emp->total_sales = round($emp->total_sales, 2);
            $emp->total_cost = round($emp->total_cost, 2);
            $emp->net_profit = round($emp->net_profit, 2);
            $emp->profit_margin = $emp->total_sales > 0 ? round(($emp->net_profit / $emp->total_sales) * 100, 1) : 0.0;
            $emp->avg_profit_per_invoice = $emp->invoices_count > 0 ? round($emp->net_profit / $emp->invoices_count, 2) : 0.0;

            return $emp;
        })->sortByDesc('net_profit')->values();

        $totalInvoices = (int) $rows->sum('invoices_count');
        $totalQuantity = round((float) $rows->sum('total_quantity'), 2);
        $totalSales = round((float) $rows->sum('total_sales'), 2);
        $totalCost = round((float) $rows->sum('total_cost'), 2);
        $totalProfit = round((float) $rows->sum('net_profit'), 2);
        $avgMargin = $totalSales > 0 ? round(($totalProfit / $totalSales) * 100, 1) : 0.0;
        $topEmployee = $rows->first();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.invoices_count'), __('reports.quantity'), __('reports.total_sales'), __('reports.total_cost'), __('reports.net_profit'), __('reports.profit_margin_percent'), __('reports.avg_profit_per_invoice')],
                $rows->map(fn ($r) => [
                    $r->user_name,
                    (int) $r->invoices_count,
                    (float) $r->total_quantity,
                    (float) $r->total_sales,
                    (float) $r->total_cost,
                    (float) $r->net_profit,
                    (float) $r->profit_margin . '%',
                    (float) $r->avg_profit_per_invoice,
                ])->toArray(),
                'employee-sales-profits-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.employee-profits', [
            'branches' => $branches,
            'branchId' => $branchId,
            'users' => $users,
            'userId' => $userId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalInvoices' => $totalInvoices,
            'totalQuantity' => $totalQuantity,
            'totalSales' => $totalSales,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'avgMargin' => $avgMargin,
            'topEmployee' => $topEmployee,
        ]);
    }

    /**
     * تقرير المنتجات الأكثر مبيعاً: ترتيب الأصناف تنازلياً حسب
     * الكمية أو الإيراد أو الربح، مع نسبة المساهمة والتكلفة.
     */
    public function topSellingProducts(Request $request)
    {
        $this->authorize('reports_sales.top_products');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        $sortBy = in_array($request->input('sort_by'), ['qty', 'revenue', 'profit'], true) ? $request->input('sort_by') : 'qty';
        $limitParam = $request->input('limit', '10');
        $limit = in_array((int) $limitParam, [10, 25, 50, 100], true) ? (int) $limitParam : ($limitParam === 'all' ? null : 10);
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $items = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereDate('invoices.issue_date', '>=', $dateFrom)
            ->whereDate('invoices.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('invoice_items.branch_id', $branchId))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('products.name', 'like', "%{$q}%")
                    ->orWhere('products.code', 'like', "%{$q}%");
            }))
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.code as product_code',
                'products.average_cost',
                'products.purchase_price',
                'products.unit',
                'invoice_items.invoice_id',
                'invoice_items.quantity',
                'invoice_items.unit_price',
                'invoice_items.discount_amount',
            ])
            ->get();

        $productsMap = [];
        $overallRevenue = 0.0;
        $overallQuantity = 0.0;
        $overallCost = 0.0;
        $overallProfit = 0.0;

        foreach ($items as $item) {
            $pid = $item->product_id;
            if (! isset($productsMap[$pid])) {
                $productsMap[$pid] = (object) [
                    'product_id' => $pid,
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'unit' => $item->unit,
                    'invoices_set' => [],
                    'total_quantity' => 0.0,
                    'total_revenue' => 0.0,
                    'total_cost' => 0.0,
                    'net_profit' => 0.0,
                    'profit_margin' => 0.0,
                    'contribution_percent' => 0.0,
                ];
            }
            $productsMap[$pid]->invoices_set[$item->invoice_id] = true;
            $qty = (float) $item->quantity;
            $productsMap[$pid]->total_quantity += $qty;
            $rev = ((float) $item->unit_price * $qty) - (float) $item->discount_amount;
            $productsMap[$pid]->total_revenue += $rev;

            $uCost = (float) ($item->average_cost > 0 ? $item->average_cost : $item->purchase_price);
            $cost = $uCost * $qty;
            $productsMap[$pid]->total_cost += $cost;
            $productsMap[$pid]->net_profit += ($rev - $cost);

            $overallRevenue += $rev;
            $overallQuantity += $qty;
            $overallCost += $cost;
            $overallProfit += ($rev - $cost);
        }

        $collection = collect($productsMap)->map(function ($p) use ($overallRevenue) {
            $p->invoices_count = count($p->invoices_set);
            unset($p->invoices_set);
            $p->total_quantity = round($p->total_quantity, 2);
            $p->total_revenue = round($p->total_revenue, 2);
            $p->total_cost = round($p->total_cost, 2);
            $p->net_profit = round($p->net_profit, 2);
            $p->profit_margin = $p->total_revenue > 0 ? round(($p->net_profit / $p->total_revenue) * 100, 1) : 0.0;
            $p->contribution_percent = $overallRevenue > 0 ? round(($p->total_revenue / $overallRevenue) * 100, 1) : 0.0;

            return $p;
        });

        if ($sortBy === 'revenue') {
            $sorted = $collection->sortByDesc('total_revenue');
        } elseif ($sortBy === 'profit') {
            $sorted = $collection->sortByDesc('net_profit');
        } else {
            $sorted = $collection->sortByDesc('total_quantity');
        }

        $rows = $limit ? $sorted->take($limit)->values() : $sorted->values();

        $totalProductsCount = $collection->count();
        $totalQuantity = round($overallQuantity, 2);
        $totalRevenue = round($overallRevenue, 2);
        $totalCost = round($overallCost, 2);
        $totalProfit = round($overallProfit, 2);
        $avgMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0.0;

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.rank'), __('reports.product_code'), __('reports.product'), __('reports.invoices_count'), __('reports.quantity'), __('reports.total_sales'), __('reports.total_cost'), __('reports.profit'), __('reports.profit_margin_percent'), __('reports.sales_contribution')],
                $rows->map(fn ($r, $idx) => [
                    $idx + 1,
                    $r->product_code ?? '-',
                    $r->product_name,
                    (int) $r->invoices_count,
                    (float) $r->total_quantity,
                    (float) $r->total_revenue,
                    (float) $r->total_cost,
                    (float) $r->net_profit,
                    (float) $r->profit_margin . '%',
                    (float) $r->contribution_percent . '%',
                ])->toArray(),
                'top-selling-products-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.top-products', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'sortBy' => $sortBy,
            'limit' => $limitParam,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalProductsCount' => $totalProductsCount,
            'totalQuantity' => $totalQuantity,
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'avgMargin' => $avgMargin,
        ]);
    }

    /**
     * المبيعات مجمّعة حسب العميل خلال الفترة المحددة، مرتبة تنازليًا حسب
     * صافي المبيعات.
     */
    public function salesByCustomer(Request $request)
    {
        $this->authorize('reports_sales.by_customer');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $customerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $customers = $this->entitySelectedOption($customerId, Customer::class);

        $rows = Invoice::query()
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereDate('invoices.issue_date', '>=', $dateFrom)
            ->whereDate('invoices.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('invoices.branch_id', $branchId))
            ->when($customerId, fn ($query) => $query->where('invoices.customer_id', $customerId))
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('net_total')
            ->get([
                'customers.id as customer_id',
                'customers.name as customer_name',
                DB::raw('COUNT(DISTINCT invoices.id) as invoices_count'),
                DB::raw('SUM(invoices.total_quantity) as total_quantity'),
                DB::raw('SUM(invoices.subtotal + invoices.tax_amount - invoices.discount_amount) as net_total'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.customer'), __('reports.invoices_count'), __('reports.quantity'), __('reports.net_total')],
                $rows->map(fn ($r) => [$r->customer_name, (int) $r->invoices_count, (float) $r->total_quantity, (float) $r->net_total])->toArray(),
                'sales-by-customer-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.by-customer', [
            'branches' => $branches,
            'branchId' => $branchId,
            'customers' => $customers,
            'customerId' => $customerId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalInvoices' => (int) $rows->sum('invoices_count'),
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalNet' => round((float) $rows->sum('net_total'), 2),
        ]);
    }

    /**
     * المبيعات مجمّعة حسب الموظف اللي أنشأ الفاتورة (created_by) خلال
     * الفترة المحددة - نفس بنية salesByCustomer بالظبط بس التجميع على
     * users بدل customers.
     */
    public function salesByEmployee(Request $request)
    {
        $this->authorize('reports_sales.by_employee');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        $rows = Invoice::query()
            ->join('users', 'users.id', '=', 'invoices.created_by')
            ->whereDate('invoices.issue_date', '>=', $dateFrom)
            ->whereDate('invoices.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('invoices.branch_id', $branchId))
            ->when($userId, fn ($query) => $query->where('invoices.created_by', $userId))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('net_total')
            ->get([
                'users.id as user_id',
                'users.name as user_name',
                DB::raw('COUNT(DISTINCT invoices.id) as invoices_count'),
                DB::raw('SUM(invoices.total_quantity) as total_quantity'),
                DB::raw('SUM(invoices.subtotal + invoices.tax_amount - invoices.discount_amount) as net_total'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.invoices_count'), __('reports.quantity'), __('reports.net_total')],
                $rows->map(fn ($r) => [$r->user_name, (int) $r->invoices_count, (float) $r->total_quantity, (float) $r->net_total])->toArray(),
                'sales-by-employee-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.by-employee', [
            'branches' => $branches,
            'branchId' => $branchId,
            'users' => $users,
            'userId' => $userId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalInvoices' => (int) $rows->sum('invoices_count'),
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalNet' => round((float) $rows->sum('net_total'), 2),
        ]);
    }

    /**
     * المبيعات مجمّعة حسب الصنف خلال الفترة المحددة (من بنود الفواتير)،
     * مرتبة تنازليًا حسب الإيراد.
     */
    public function salesByProduct(Request $request)
    {
        $this->authorize('reports_sales.by_product');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        // فلترة اختيارية على صنف بعينه بالـ id (مش بالاسم) - مستخدمة من
        // زرار "العمليات" في مودال اختيار منتج (شاشة إنشاء الفاتورة)
        // عشان تفتح نفس تقرير "المبيعات حسب الصنف" ده مفلتر على منتج
        // واحد بس، من غير ما نكرر منطق التقرير في مكان تاني.
        $productId = $request->filled('product_id') ? (int) $request->input('product_id') : null;
        $productFilter = $productId ? Product::find($productId, ['id', 'name', 'code']) : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $rows = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereDate('invoices.issue_date', '>=', $dateFrom)
            ->whereDate('invoices.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('invoice_items.branch_id', $branchId))
            ->when($productId, fn ($query) => $query->where('invoice_items.product_id', $productId))
            ->when(! $productId && $q !== '', fn ($query) => $query->where('products.name', 'like', "%{$q}%"))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->get([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
                DB::raw('SUM((invoice_items.unit_price * invoice_items.quantity) + invoice_items.tax_amount - invoice_items.discount_amount) as revenue'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.product'), __('reports.quantity'), __('reports.revenue')],
                $rows->map(fn ($r) => [$r->product_name, (float) $r->total_quantity, (float) $r->revenue])->toArray(),
                'sales-by-product-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.by-product', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'productId' => $productId,
            'productFilter' => $productFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalRevenue' => round((float) $rows->sum('revenue'), 2),
        ]);
    }

    /**
     * مرتجعات المبيعات: كل سطر مرتجع (invoice_returns) خلال الفترة
     * المحددة - الفترة هنا بتاريخ تسجيل المرتجع نفسه (created_at) مش
     * تاريخ الفاتورة الأصلية.
     */
    public function salesReturns(Request $request)
    {
        $this->authorize('reports_sales.returns');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $returns = InvoiceReturn::query()
            ->with(['invoice:id,invoice_number', 'product:id,name'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('created_at')
            ->get();

        foreach ($returns as $return) {
            $return->line_total = round((float) $return->unit_price * (float) $return->quantity - (float) $return->discount_amount + (float) $return->tax_amount, 2);
        }

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.invoice_number'), __('reports.product'), __('reports.date'), __('reports.quantity'), __('reports.return_amount')],
                $returns->map(fn ($r) => [
                    optional($r->invoice)->invoice_number ?? '-',
                    optional($r->product)->name ?? '-',
                    optional($r->created_at)->format('Y-m-d'),
                    (float) $r->quantity,
                    (float) $r->line_total,
                ])->toArray(),
                'sales-returns-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.sales.returns', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'returns' => $returns,
            'totalQuantity' => round((float) $returns->sum('quantity'), 2),
            'totalAmount' => round((float) $returns->sum('line_total'), 2),
        ]);
    }

    // =====================================================================
    // قسم المشتريات - نفس بنية قسم المبيعات بالظبط، بس على Purchase/
    // PurchaseItem/PurchaseReturn/Supplier. مرتجع المشتريات هنا (على
    // عكس مرتجع المبيعات) عبارة عن "سند" برأس واحد وإجمالي جاهز
    // (grand_total/total_quantity) مش سطر سطر، فالتقرير أبسط.
    // =====================================================================

    public function purchasesIndex()
    {
        $this->authorizeAnyReport('reports_purchases');
        return view('reports.purchases.index');
    }

    public function purchasesSummary(Request $request)
    {
        $this->authorize('reports_purchases.summary');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $supplierId = $request->filled('supplier_id') ? (int) $request->input('supplier_id') : null;
        $suppliers = $this->entitySelectedOption($supplierId, Supplier::class);
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $purchases = Purchase::query()
            ->with(['supplier:id,name', 'items.product:id,name'])
            ->whereDate('issue_date', '>=', $dateFrom)
            ->whereDate('issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
            ->when($q !== '', fn ($query) => $query->where('purchase_number', 'like', "%{$q}%"))
            ->orderByDesc('issue_date')
            ->get(['id', 'purchase_number', 'supplier_id', 'branch_id', 'issue_date', 'subtotal', 'tax_amount', 'discount_amount', 'grand_total', 'total_quantity']);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.purchase_number'), __('reports.supplier'), __('reports.date'), __('reports.quantity'), __('reports.subtotal'), __('reports.tax'), __('reports.discount'), __('reports.net_total')],
                $purchases->map(fn ($p) => [
                    $p->purchase_number,
                    optional($p->supplier)->name ?? '-',
                    optional($p->issue_date)->format('Y-m-d'),
                    (float) $p->total_quantity,
                    (float) $p->subtotal,
                    (float) $p->tax_amount,
                    (float) $p->discount_amount,
                    (float) $p->grand_total,
                ])->toArray(),
                'purchases-summary-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.purchases.summary', [
            'branches' => $branches,
            'branchId' => $branchId,
            'suppliers' => $suppliers,
            'supplierId' => $supplierId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'purchases' => $purchases,
            'totalSubtotal' => round((float) $purchases->sum('subtotal'), 2),
            'totalTax' => round((float) $purchases->sum('tax_amount'), 2),
            'totalDiscount' => round((float) $purchases->sum('discount_amount'), 2),
            'totalNet' => round((float) $purchases->sum('grand_total'), 2),
            'totalQuantity' => round((float) $purchases->sum('total_quantity'), 2),
        ]);
    }

    public function purchasesBySupplier(Request $request)
    {
        $this->authorize('reports_purchases.by_supplier');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $supplierId = $request->filled('supplier_id') ? (int) $request->input('supplier_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $suppliers = $this->entitySelectedOption($supplierId, Supplier::class);

        $rows = Purchase::query()
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->whereDate('purchases.issue_date', '>=', $dateFrom)
            ->whereDate('purchases.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('purchases.branch_id', $branchId))
            ->when($supplierId, fn ($query) => $query->where('purchases.supplier_id', $supplierId))
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('net_total')
            ->get([
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                DB::raw('COUNT(DISTINCT purchases.id) as purchases_count'),
                DB::raw('SUM(purchases.total_quantity) as total_quantity'),
                DB::raw('SUM(purchases.grand_total) as net_total'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.supplier'), __('reports.purchases_count'), __('reports.quantity'), __('reports.net_total')],
                $rows->map(fn ($r) => [$r->supplier_name, (int) $r->purchases_count, (float) $r->total_quantity, (float) $r->net_total])->toArray(),
                'purchases-by-supplier-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.purchases.by-supplier', [
            'branches' => $branches,
            'branchId' => $branchId,
            'suppliers' => $suppliers,
            'supplierId' => $supplierId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalPurchases' => (int) $rows->sum('purchases_count'),
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalNet' => round((float) $rows->sum('net_total'), 2),
        ]);
    }

    /**
     * المشتريات مجمّعة حسب الموظف اللي أنشأ فاتورة الشراء (created_by)
     * خلال الفترة المحددة - نفس بنية purchasesBySupplier بالظبط.
     */
    public function purchasesByEmployee(Request $request)
    {
        $this->authorize('reports_purchases.by_employee');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        $rows = Purchase::query()
            ->join('users', 'users.id', '=', 'purchases.created_by')
            ->whereDate('purchases.issue_date', '>=', $dateFrom)
            ->whereDate('purchases.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('purchases.branch_id', $branchId))
            ->when($userId, fn ($query) => $query->where('purchases.created_by', $userId))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('net_total')
            ->get([
                'users.id as user_id',
                'users.name as user_name',
                DB::raw('COUNT(DISTINCT purchases.id) as purchases_count'),
                DB::raw('SUM(purchases.total_quantity) as total_quantity'),
                DB::raw('SUM(purchases.grand_total) as net_total'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.purchases_count'), __('reports.quantity'), __('reports.net_total')],
                $rows->map(fn ($r) => [$r->user_name, (int) $r->purchases_count, (float) $r->total_quantity, (float) $r->net_total])->toArray(),
                'purchases-by-employee-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.purchases.by-employee', [
            'branches' => $branches,
            'branchId' => $branchId,
            'users' => $users,
            'userId' => $userId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalPurchases' => (int) $rows->sum('purchases_count'),
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalNet' => round((float) $rows->sum('net_total'), 2),
        ]);
    }

    public function purchasesByProduct(Request $request)
    {
        $this->authorize('reports_purchases.by_product');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        // فلترة اختيارية على صنف بعينه بالـ id - نفس فكرة salesByProduct
        // فوق، مستخدمة من زرار "العمليات" في مودال اختيار منتج.
        $productId = $request->filled('product_id') ? (int) $request->input('product_id') : null;
        $productFilter = $productId ? Product::find($productId, ['id', 'name', 'code']) : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $rows = PurchaseItem::query()
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->whereDate('purchases.issue_date', '>=', $dateFrom)
            ->whereDate('purchases.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('purchases.branch_id', $branchId))
            ->when($productId, fn ($query) => $query->where('purchase_items.product_id', $productId))
            ->when(! $productId && $q !== '', fn ($query) => $query->where('products.name', 'like', "%{$q}%"))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('cost_total')
            ->get([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(purchase_items.quantity) as total_quantity'),
                DB::raw('SUM((purchase_items.unit_price * purchase_items.quantity) + purchase_items.tax_amount - purchase_items.discount_amount) as cost_total'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.product'), __('reports.quantity'), __('reports.cost')],
                $rows->map(fn ($r) => [$r->product_name, (float) $r->total_quantity, (float) $r->cost_total])->toArray(),
                'purchases-by-product-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.purchases.by-product', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'productId' => $productId,
            'productFilter' => $productFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalCost' => round((float) $rows->sum('cost_total'), 2),
        ]);
    }

    /**
     * تقرير مقارنة مشتريات ومبيعات الأصناف: إجمالي الكميات والتكاليف المشتراة
     * مقابل الكميات والإيرادات المباعة ونسبة التصريف والأرباح والمخزون المتبقي.
     */
    public function purchasesVsSales(Request $request)
    {
        $this->authorize('reports_purchases.purchases_vs_sales');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        $productId = $request->filled('product_id') ? (int) $request->input('product_id') : null;
        $productFilter = $productId ? Product::find($productId, ['id', 'name', 'code']) : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $purchasesQuery = PurchaseItem::query()
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->whereDate('purchases.issue_date', '>=', $dateFrom)
            ->whereDate('purchases.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('purchases.branch_id', $branchId))
            ->when($productId, fn ($query) => $query->where('purchase_items.product_id', $productId))
            ->when(! $productId && $q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('products.name', 'like', "%{$q}%")
                    ->orWhere('products.code', 'like', "%{$q}%");
            }))
            ->groupBy('products.id', 'products.name', 'products.code', 'products.stock_quantity', 'products.average_cost', 'products.purchase_price', 'products.unit')
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.code as product_code',
                'products.stock_quantity',
                'products.average_cost',
                'products.purchase_price',
                'products.unit',
                DB::raw('COALESCE(SUM(purchase_items.quantity), 0) as purchased_qty'),
                DB::raw('COALESCE(SUM((purchase_items.unit_price * purchase_items.quantity) + purchase_items.tax_amount - purchase_items.discount_amount), 0) as purchased_cost'),
            ])
            ->get();

        $salesQuery = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->whereDate('invoices.issue_date', '>=', $dateFrom)
            ->whereDate('invoices.issue_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('invoices.branch_id', $branchId))
            ->when($productId, fn ($query) => $query->where('invoice_items.product_id', $productId))
            ->when(! $productId && $q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('products.name', 'like', "%{$q}%")
                    ->orWhere('products.code', 'like', "%{$q}%");
            }))
            ->groupBy('products.id', 'products.name', 'products.code', 'products.stock_quantity', 'products.average_cost', 'products.purchase_price', 'products.unit')
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.code as product_code',
                'products.stock_quantity',
                'products.average_cost',
                'products.purchase_price',
                'products.unit',
                DB::raw('COALESCE(SUM(invoice_items.quantity), 0) as sold_qty'),
                DB::raw('COALESCE(SUM((invoice_items.unit_price * invoice_items.quantity) - invoice_items.discount_amount), 0) as sales_revenue'),
            ])
            ->get();

        $productsMap = [];

        foreach ($purchasesQuery as $p) {
            $pid = $p->product_id;
            $productsMap[$pid] = (object) [
                'product_id' => $pid,
                'product_name' => $p->product_name,
                'product_code' => $p->product_code,
                'stock_quantity' => (float) $p->stock_quantity,
                'average_cost' => (float) $p->average_cost,
                'purchase_price' => (float) $p->purchase_price,
                'unit' => $p->unit,
                'purchased_qty' => (float) $p->purchased_qty,
                'purchased_cost' => (float) $p->purchased_cost,
                'sold_qty' => 0.0,
                'sales_revenue' => 0.0,
            ];
        }

        foreach ($salesQuery as $s) {
            $pid = $s->product_id;
            if (! isset($productsMap[$pid])) {
                $productsMap[$pid] = (object) [
                    'product_id' => $pid,
                    'product_name' => $s->product_name,
                    'product_code' => $s->product_code,
                    'stock_quantity' => (float) $s->stock_quantity,
                    'average_cost' => (float) $s->average_cost,
                    'purchase_price' => (float) $s->purchase_price,
                    'unit' => $s->unit,
                    'purchased_qty' => 0.0,
                    'purchased_cost' => 0.0,
                    'sold_qty' => (float) $s->sold_qty,
                    'sales_revenue' => (float) $s->sales_revenue,
                ];
            } else {
                $productsMap[$pid]->sold_qty = (float) $s->sold_qty;
                $productsMap[$pid]->sales_revenue = (float) $s->sales_revenue;
            }
        }

        $rows = collect($productsMap)->map(function ($row) {
            $row->purchased_qty = round($row->purchased_qty, 2);
            $row->purchased_cost = round($row->purchased_cost, 2);
            $row->sold_qty = round($row->sold_qty, 2);
            $row->sales_revenue = round($row->sales_revenue, 2);
            $row->current_stock = round((float) $row->stock_quantity, 2);

            $row->sell_through_percent = $row->purchased_qty > 0
                ? round(($row->sold_qty / $row->purchased_qty) * 100, 1)
                : ($row->sold_qty > 0 ? 100.0 : 0.0);

            $unitCost = $row->average_cost > 0 ? $row->average_cost : $row->purchase_price;
            $row->cogs = round($row->sold_qty * $unitCost, 2);
            $row->profit = round($row->sales_revenue - $row->cogs, 2);
            $row->profit_margin = $row->sales_revenue > 0 ? round(($row->profit / $row->sales_revenue) * 100, 1) : 0.0;

            return $row;
        })->sortByDesc('purchased_cost')->values();

        $totalPurchasedQty = round((float) $rows->sum('purchased_qty'), 2);
        $totalPurchasedCost = round((float) $rows->sum('purchased_cost'), 2);
        $totalSoldQty = round((float) $rows->sum('sold_qty'), 2);
        $totalSalesRevenue = round((float) $rows->sum('sales_revenue'), 2);
        $totalProfit = round((float) $rows->sum('profit'), 2);
        $overallSellThrough = $totalPurchasedQty > 0 ? round(($totalSoldQty / $totalPurchasedQty) * 100, 1) : 0.0;

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [
                    __('reports.rank'),
                    __('reports.product_code'),
                    __('reports.product'),
                    __('reports.purchased_qty'),
                    __('reports.purchased_cost'),
                    __('reports.sold_qty'),
                    __('reports.sales_revenue'),
                    __('reports.current_stock'),
                    __('reports.sell_through_percent'),
                    __('reports.profit'),
                ],
                $rows->map(fn ($r, $idx) => [
                    $idx + 1,
                    $r->product_code ?? '-',
                    $r->product_name,
                    (float) $r->purchased_qty,
                    (float) $r->purchased_cost,
                    (float) $r->sold_qty,
                    (float) $r->sales_revenue,
                    (float) $r->current_stock,
                    (float) $r->sell_through_percent . '%',
                    (float) $r->profit,
                ])->toArray(),
                'purchases-vs-sales-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.purchases.purchases-vs-sales', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'productId' => $productId,
            'productFilter' => $productFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalPurchasedQty' => $totalPurchasedQty,
            'totalPurchasedCost' => $totalPurchasedCost,
            'totalSoldQty' => $totalSoldQty,
            'totalSalesRevenue' => $totalSalesRevenue,
            'totalProfit' => $totalProfit,
            'overallSellThrough' => $overallSellThrough,
        ]);
    }

    public function purchasesReturns(Request $request)
    {
        $this->authorize('reports_purchases.returns');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $returns = PurchaseReturn::query()
            ->with(['purchase:id,purchase_number', 'supplier:id,name'])
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('return_number', 'like', "%{$q}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('return_date')
            ->get(['id', 'purchase_id', 'supplier_id', 'branch_id', 'return_number', 'grand_total', 'total_quantity', 'return_date', 'reason']);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.return_number'), __('reports.supplier'), __('reports.date'), __('reports.quantity'), __('reports.return_amount'), __('reports.reason')],
                $returns->map(fn ($r) => [
                    $r->return_number,
                    optional($r->supplier)->name ?? '-',
                    optional($r->return_date)->format('Y-m-d'),
                    (float) $r->total_quantity,
                    (float) $r->grand_total,
                    $r->reason,
                ])->toArray(),
                'purchases-returns-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.purchases.returns', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'returns' => $returns,
            'totalQuantity' => round((float) $returns->sum('total_quantity'), 2),
            'totalAmount' => round((float) $returns->sum('grand_total'), 2),
        ]);
    }

    // =====================================================================
    // قسم المنتجات - المخزون الحالي/نواقص المخزون (من جدول products
    // مباشرة، كل فرع له صفوف منتجات منفصلة) + تحويلات المخزون بين
    // الفروع (StockTransfer، ميزة قسم المستودعات).
    // =====================================================================

    public function productsIndex()
    {
        $this->authorizeAnyReport('reports_products');
        return view('reports.products.index');
    }

    /**
     * المخزون الحالي لكل منتج: الكمية وقيمتها بمتوسط التكلفة (أو سعر
     * الشراء لو مفيش متوسط تكلفة محسوب بعد).
     */
    public function productsStock(Request $request)
    {
        $this->authorize('reports_products.stock');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));

        $products = Product::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'branch_id', 'unit', 'stock_quantity', 'purchase_price', 'average_cost', 'sale_price', 'low_stock_alert_quantity']);

        $this->annotateStockValue($products);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.product_code'), __('reports.product'), __('reports.unit'), __('reports.stock_quantity'), __('reports.unit_cost'), __('reports.stock_value')],
                $products->map(fn ($p) => [$p->code, $p->name, $p->unit, (float) $p->stock_quantity, (float) ($p->average_cost ?: $p->purchase_price), (float) $p->stock_value])->toArray(),
                'products-stock-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.products.stock', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'products' => $products,
            'totalQuantity' => round((float) $products->sum('stock_quantity'), 2),
            'totalValue' => round((float) $products->sum('stock_value'), 2),
        ]);
    }

    /**
     * الأصناف اللي وصلت (أو نزلت تحت) حد التنبيه بنقص المخزون المحدد
     * لكل صنف - نفس تقرير المخزون الحالي بس مفلتر ومرتب حسب الأولوية
     * (الأقل رصيدًا أولًا).
     */
    public function productsLowStock(Request $request)
    {
        $this->authorize('reports_products.low_stock');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));

        $products = Product::query()
            ->where('low_stock_alert_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('stock_quantity')
            ->get(['id', 'name', 'code', 'branch_id', 'unit', 'stock_quantity', 'purchase_price', 'average_cost', 'sale_price', 'low_stock_alert_quantity']);

        $this->annotateStockValue($products);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.product_code'), __('reports.product'), __('reports.unit'), __('reports.stock_quantity'), __('reports.unit_cost'), __('reports.stock_value')],
                $products->map(fn ($p) => [$p->code, $p->name, $p->unit, (float) $p->stock_quantity, (float) ($p->average_cost ?: $p->purchase_price), (float) $p->stock_value])->toArray(),
                'products-low-stock-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.products.low-stock', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'products' => $products,
            'totalQuantity' => round((float) $products->sum('stock_quantity'), 2),
            'totalValue' => round((float) $products->sum('stock_value'), 2),
        ]);
    }

    /**
     * سندات تحويل المخزون بين الفروع خلال فترة محددة - فلتر الفرع هنا
     * بيشمل الفرع كمُرسل أو كمُستلم للتحويل.
     */
    public function productsStockTransfers(Request $request)
    {
        $this->authorize('reports_products.stock_transfers');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        // فلترة اختيارية على صنف بعينه - مستخدمة من زرار "العمليات" في
        // مودال اختيار منتج عشان نعرض بس سندات التحويل اللي فيها الصنف
        // ده (سواء كان هو منتج الفرع المرسل from_product_id أو منتج
        // الفرع المستلم to_product_id بعد التأكيد).
        $productId = $request->filled('product_id') ? (int) $request->input('product_id') : null;
        $productFilter = $productId ? Product::find($productId, ['id', 'name', 'code']) : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $transfers = StockTransfer::query()
            ->with(['fromBranch:id,name', 'toBranch:id,name'])
            ->withSum('items', 'quantity')
            ->whereDate('transfer_date', '>=', $dateFrom)
            ->whereDate('transfer_date', '<=', $dateTo)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($w) use ($branchId) {
                    $w->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId);
                });
            })
            ->when($productId, function ($query) use ($productId) {
                $query->whereHas('items', function ($itemQuery) use ($productId) {
                    $itemQuery->where('from_product_id', $productId)
                        ->orWhere('to_product_id', $productId);
                });
            })
            ->when(! $productId && $q !== '', fn ($query) => $query->where('transfer_number', 'like', "%{$q}%"))
            ->orderByDesc('transfer_date')
            ->get();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.transfer_number'), __('reports.from_branch'), __('reports.to_branch'), __('reports.date'), __('reports.quantity')],
                $transfers->map(fn ($t) => [
                    $t->transfer_number,
                    optional($t->fromBranch)->name ?? '-',
                    optional($t->toBranch)->name ?? '-',
                    optional($t->transfer_date)->format('Y-m-d'),
                    (float) $t->items_sum_quantity,
                ])->toArray(),
                'products-transfers-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.products.transfers', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'productId' => $productId,
            'productFilter' => $productFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'transfers' => $transfers,
            'totalQuantity' => round((float) $transfers->sum('items_sum_quantity'), 2),
        ]);
    }

    /**
     * تقرير "حركة منتج" - نفس البيانات الموحّدة (مبيعات + مشتريات +
     * تحويلات) اللي مودال "العمليات" بيعرضها من داخل شاشة اختيار منتج
     * في الفواتير/المشتريات، بس هنا كصفحة تقرير قائمة بذاتها ليها
     * اختيار منتج خاص بيها (بما إنها بتتفتح من قايمة التقارير مباشرة،
     * مش من صف منتج جاهز). الصفحة دي شِل بسيط بس - كل جلب البيانات
     * الفعلي بيحصل بالـ AJAX من نفس endpoint الموجود بالفعل
     * (ProductController::operationsData) عشان منكررش نفس منطق
     * الاستعلامات والصلاحيات مرتين. نفس صلاحيات التلات أنواع بالظبط
     * (لو المستخدم مالوش ولا واحدة فيهم، الصفحة نفسها بترفض 403 - زي
     * ما كارت "حركة منتج" في reports.products.index مبيظهرش أصلاً).
     */
    public function productsMovement(Request $request)
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('reports_sales.by_product')
                || $user?->hasPermission('reports_purchases.by_product')
                || $user?->hasPermission('reports_products.stock_transfers'),
            403
        );

        return view('reports.products.movement');
    }

    public function stockAdjustments(Request $request)
{
    $this->authorize('reports.products.stock_adjustments');

    $query = StockAdjustment::with(['product', 'user'])->latest();

    // فلترة الفرع إذا وجد
    $branchId = $request->get('branch_id');
    if ($branchId) {
        // إذا كان جدول السجلات مرتبطاً بالمنتج والمنتج يتبع فرعاً، أو إذا كنت تريد فلترتها حسب فرع المنتج
        // $query->whereHas('product', fn($q) => $q->where('branch_id', $branchId));
    }

    // دعم الفلترة بالمنتج
    $productId = $request->get('product_id');
    $productFilter = null;
    if ($productId) {
        $query->where('product_id', $productId);
        $productFilter = \App\Models\Product::find($productId);
    }

    // دعم البحث (الكلمة المفتاحية)
    $q = $request->get('search');
    if ($q) {
        $query->where(function($queryBuilder) use ($q) {
            $queryBuilder->where('reason', 'like', "%{$q}%")
                         ->orWhereHas('product', function($sub) use ($q) {
                             $sub->where('name', 'like', "%{$q}%")
                                 ->orWhere('code', 'like', "%{$q}%");
                         });
        });
    }

    // دعم فلتر التواريخ إن كان مستخدماً في _filters
    $dateFrom = $request->get('date_from');
    $dateTo = $request->get('date_to');
    if ($dateFrom) {
        $query->whereDate('created_at', '>=', $dateFrom);
    }
    if ($dateTo) {
        $query->whereDate('created_at', '<=', $dateTo);
    }

    // جلب الفروع لتظهر في الفلتر العلوي
    $branches = Branch::all(); 

    $adjustments = $query->paginate(20)->withQueryString();

    return view('reports.products.stock_adjustments', [
        'branches' => $branches,
        'branchId' => $branchId,
        'q' => $q,
        'productId' => $productId,
        'productFilter' => $productFilter,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'adjustments' => $adjustments,
    ]);
}





    private function annotateStockValue($products): void
    {
        foreach ($products as $product) {
            $cost = (float) ($product->average_cost ?: $product->purchase_price);
            $product->stock_value = round((float) $product->stock_quantity * $cost, 2);
            $product->is_low_stock = (float) $product->low_stock_alert_quantity > 0
                && (float) $product->stock_quantity <= (float) $product->low_stock_alert_quantity;
        }
    }

    // =====================================================================
    // قسم الموارد البشرية - كشف رواتب شهر مُرحّل معيّن (من PayrollPosting/
    // PayrollEmployeeLine بعد الترحيل الفعلي، مش إعادة حساب)، الحضور
    // والانصراف، السلف والعهد، ودليل الموظفين.
    // =====================================================================

    public function hrIndex()
    {
        $this->authorizeAnyReport('reports_hr');
        return view('reports.hr.index');
    }

    /**
     * كشف رواتب شهر مُرحّل معيّن (اختار من القائمة المنسدلة) موزّع
     * لكل موظف: راتب + مكافأة - خصم حضور - قسط سلفة = الصافي. البيانات
     * دي مأخوذة من البنود الفعلية اللي اتنفذت وقت الترحيل
     * (PayrollController@postJournal) مش بإعادة حساب مستقل.
     */
    public function hrPayroll(Request $request)
    {
        $this->authorize('reports_hr.payroll');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;

        $employees = Employee::orderBy('name')->get(['id', 'name']);

        $postings = PayrollPosting::orderByDesc('month')->orderByDesc('id')
            ->get(['id', 'document_number', 'month', 'total_net', 'funding_source', 'paid_at']);

        $postingId = $request->filled('posting_id')
            ? (int) $request->input('posting_id')
            : optional($postings->first())->id;

        $byEmployee = collect();

        if ($postingId) {
            $lines = PayrollEmployeeLine::where('payroll_posting_id', $postingId)
                ->with('employee:id,name,employee_number,branch_id')
                ->when($branchId, fn ($query) => $query->whereHas('employee', fn ($e) => $e->where('branch_id', $branchId)))
                ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
                ->get();

            $loanDeductions = PayrollLoanDeduction::where('payroll_posting_id', $postingId)
                ->get()
                ->groupBy('employee_id');

            $byEmployee = $lines->groupBy('employee_id')->map(function ($group) use ($loanDeductions) {
                $employee = $group->first()->employee;
                $salary = (float) $group->where('type', PayrollEmployeeLine::TYPE_SALARY)->sum('amount');
                $bonus = (float) $group->where('type', PayrollEmployeeLine::TYPE_BONUS)->sum('amount');
                $deduction = (float) $group->where('type', PayrollEmployeeLine::TYPE_DEDUCTION)->sum('amount');
                $loan = (float) optional($loanDeductions->get($employee?->id))->sum('amount');

                return (object) [
                    'employee' => $employee,
                    'salary' => round($salary, 2),
                    'bonus' => round($bonus, 2),
                    'deduction' => round($deduction, 2),
                    'loan_deduction' => round($loan, 2),
                    'net' => round($salary + $bonus - $deduction - $loan, 2),
                ];
            })->values();
        }

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.employee_number'), __('reports.salary'), __('reports.bonus'), __('reports.attendance_discount'), __('reports.loan_deduction'), __('reports.net_pay')],
                $byEmployee->map(fn ($r) => [
                    optional($r->employee)->name ?? '-',
                    optional($r->employee)->employee_number ?? '-',
                    (float) $r->salary,
                    (float) $r->bonus,
                    (float) $r->deduction,
                    (float) $r->loan_deduction,
                    (float) $r->net,
                ])->toArray(),
                'hr-payroll-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.hr.payroll', [
            'branches' => $branches,
            'branchId' => $branchId,
            'employees' => $employees,
            'employeeId' => $employeeId,
            'postings' => $postings,
            'postingId' => $postingId,
            'rows' => $byEmployee,
            'totalSalary' => round((float) $byEmployee->sum('salary'), 2),
            'totalBonus' => round((float) $byEmployee->sum('bonus'), 2),
            'totalDeduction' => round((float) $byEmployee->sum('deduction'), 2),
            'totalLoanDeduction' => round((float) $byEmployee->sum('loan_deduction'), 2),
            'totalNet' => round((float) $byEmployee->sum('net'), 2),
        ]);
    }

    /**
     * ملخص الحضور والانصراف لكل موظف خلال فترة محددة: عدد أيام كل
     * حالة + إجمالي دقائق التأخير وساعات/قيمة الأوفرتايم وخصم الحضور.
     */
    public function hrAttendance(Request $request)
    {
        $this->authorize('reports_hr.attendance');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $employees = Employee::orderBy('name')->get(['id', 'name']);

        $rows = Attendance::query()
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->whereDate('attendances.date', '>=', $dateFrom)
            ->whereDate('attendances.date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('employees.branch_id', $branchId))
            ->when($employeeId, fn ($query) => $query->where('employees.id', $employeeId))
            ->groupBy('employees.id', 'employees.name', 'employees.employee_number')
            ->orderBy('employees.name')
            ->get([
                'employees.id as employee_id',
                'employees.name as employee_name',
                'employees.employee_number as employee_number',
                DB::raw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present_days"),
                DB::raw("SUM(CASE WHEN attendances.status = 'absent' THEN 1 ELSE 0 END) as absent_days"),
                DB::raw("SUM(CASE WHEN attendances.status = 'leave' THEN 1 ELSE 0 END) as leave_days"),
                DB::raw('SUM(attendances.late_minutes) as total_late_minutes'),
                DB::raw('SUM(attendances.overtime_hours) as total_overtime_hours'),
                DB::raw('SUM(attendances.overtime_amount) as total_overtime_amount'),
                DB::raw('SUM(attendances.discount_amount) as total_discount_amount'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.employee_number'), __('reports.present_days'), __('reports.absent_days'), __('reports.leave_days'), __('reports.late_minutes'), __('reports.overtime_hours'), __('reports.overtime_amount'), __('reports.attendance_discount')],
                $rows->map(fn ($r) => [
                    $r->employee_name,
                    $r->employee_number,
                    (int) $r->present_days,
                    (int) $r->absent_days,
                    (int) $r->leave_days,
                    (int) $r->total_late_minutes,
                    (float) $r->total_overtime_hours,
                    (float) $r->total_overtime_amount,
                    (float) $r->total_discount_amount,
                ])->toArray(),
                'hr-attendance-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.hr.attendance', [
            'branches' => $branches,
            'branchId' => $branchId,
            'employees' => $employees,
            'employeeId' => $employeeId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalPresent' => (int) $rows->sum('present_days'),
            'totalAbsent' => (int) $rows->sum('absent_days'),
            'totalLeave' => (int) $rows->sum('leave_days'),
            'totalLateMinutes' => (int) $rows->sum('total_late_minutes'),
            'totalOvertimeHours' => round((float) $rows->sum('total_overtime_hours'), 2),
            'totalOvertimeAmount' => round((float) $rows->sum('total_overtime_amount'), 2),
            'totalDiscountAmount' => round((float) $rows->sum('total_discount_amount'), 2),
        ]);
    }

    /**
     * السلف والعهد النشطة والمسدّدة - المتبقي الفعلي لكل واحدة بعد خصم
     * أي أقساط اتخصمت من الرواتب لحد دلوقتي.
     */
    public function hrLoans(Request $request)
    {
        $this->authorize('reports_hr.loans');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        $status = trim((string) $request->input('status'));

        $employees = Employee::orderBy('name')->get(['id', 'name']);

        $loans = EmployeeLoan::query()
            ->with('employee:id,name,employee_number,branch_id')
            ->when($branchId, fn ($query) => $query->whereHas('employee', fn ($e) => $e->where('branch_id', $branchId)))
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('date')
            ->get();

        foreach ($loans as $loan) {
            $loan->remaining = $loan->remainingAmount();
        }

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.loan_type'), __('reports.date'), __('reports.loan_amount'), __('reports.paid_amount'), __('reports.remaining_amount'), __('reports.loan_status')],
                $loans->map(fn ($l) => [
                    optional($l->employee)->name ?? '-',
                    __('reports.loan_type_' . $l->type),
                    optional($l->date)->format('Y-m-d'),
                    (float) $l->amount,
                    (float) $l->paid_amount,
                    (float) $l->remaining,
                    __('reports.loan_status_' . $l->status),
                ])->toArray(),
                'hr-loans-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.hr.loans', [
            'branches' => $branches,
            'branchId' => $branchId,
            'employees' => $employees,
            'employeeId' => $employeeId,
            'status' => $status,
            'loans' => $loans,
            'totalAmount' => round((float) $loans->sum('amount'), 2),
            'totalPaid' => round((float) $loans->sum('paid_amount'), 2),
            'totalRemaining' => round((float) $loans->sum('remaining'), 2),
        ]);
    }

    /**
     * دليل الموظفين: بيانات كل موظف الأساسية وراتبه الحالي (أساسي +
     * بدلات) - مفيد كمرجع سريع لكل الموظفين مهما كانت حالتهم.
     */
    public function hrEmployees(Request $request)
    {
        $this->authorize('reports_hr.employees');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $q = trim((string) $request->input('q'));
        $status = trim((string) $request->input('status'));

        $employees = Employee::query()
            ->with('branch:id,name')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('employee_number', 'like', "%{$q}%")
                        ->orWhere('job_title', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->get(['id', 'employee_number', 'name', 'job_title', 'department', 'branch_id', 'hire_date', 'status', 'basic_salary', 'allowances']);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee_number'), __('reports.employee'), __('reports.job_title'), __('reports.department'), __('reports.hire_date'), __('reports.employee_status'), __('reports.basic_salary'), __('reports.allowances')],
                $employees->map(fn ($e) => [
                    $e->employee_number,
                    $e->name,
                    $e->job_title,
                    $e->department,
                    optional($e->hire_date)->format('Y-m-d'),
                    __('reports.employee_status_' . $e->status),
                    (float) $e->basic_salary,
                    (float) $e->allowances,
                ])->toArray(),
                'hr-employees-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.hr.employees', [
            'branches' => $branches,
            'branchId' => $branchId,
            'q' => $q,
            'status' => $status,
            'employees' => $employees,
            'totalBasic' => round((float) $employees->sum('basic_salary'), 2),
            'totalAllowances' => round((float) $employees->sum('allowances'), 2),
        ]);
    }

    /**
     * المكافآت (EmployeeBonus) وخصومات الحضور (Attendance.discount_amount)
     * لكل موظف خلال فترة محددة - تقرير منفصل عن "السلف والعهد" (اللي
     * ليها تقريرها الخاص hrLoans) لإنها نوع تاني من الحركات المالية على
     * ذمة الموظف (مكافأة تُضاف / خصم تأخير أو غياب يُخصم من الحضور نفسه)
     * مش سلفة/عهدة بيتم سدادها بالتقسيط.
     */
    public function hrBonusesDeductions(Request $request)
    {
        $this->authorize('reports_hr.bonuses_deductions');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $employees = Employee::orderBy('name')->get(['id', 'name']);

        $employeesQuery = Employee::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->pluck('id');

        $bonusByEmployee = EmployeeBonus::query()
            ->whereIn('employee_id', $employeesQuery)
            ->whereDate('month', '>=', $dateFrom)
            ->whereDate('month', '<=', $dateTo)
            ->selectRaw('employee_id, SUM(amount) as total_bonus')
            ->groupBy('employee_id')
            ->pluck('total_bonus', 'employee_id');

        $deductionByEmployee = Attendance::query()
            ->whereIn('employee_id', $employeesQuery)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->selectRaw('employee_id, SUM(discount_amount) as total_deduction')
            ->groupBy('employee_id')
            ->pluck('total_deduction', 'employee_id');

        $rows = Employee::query()
            ->whereIn('id', $employeesQuery)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_number'])
            ->map(function ($employee) use ($bonusByEmployee, $deductionByEmployee) {
                $bonus = (float) ($bonusByEmployee->get($employee->id) ?? 0);
                $deduction = (float) ($deductionByEmployee->get($employee->id) ?? 0);

                return (object) [
                    'employee' => $employee,
                    'total_bonus' => round($bonus, 2),
                    'total_deduction' => round($deduction, 2),
                    'net_amount' => round($bonus - $deduction, 2),
                ];
            })
            ->filter(fn ($row) => $row->total_bonus > 0 || $row->total_deduction > 0)
            ->values();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.employee_number'), __('reports.total_bonus'), __('reports.total_deduction'), __('reports.net_amount')],
                $rows->map(fn ($r) => [$r->employee->name, $r->employee->employee_number, $r->total_bonus, $r->total_deduction, $r->net_amount])->toArray(),
                'hr-bonuses-deductions-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.hr.bonuses-deductions', [
            'branches' => $branches,
            'branchId' => $branchId,
            'employees' => $employees,
            'employeeId' => $employeeId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalBonus' => round((float) $rows->sum('total_bonus'), 2),
            'totalDeduction' => round((float) $rows->sum('total_deduction'), 2),
            'totalNet' => round((float) $rows->sum('net_amount'), 2),
        ]);
    }

    /**
     * طلبات الإجازات (LeaveRequest) خلال فترة محددة (بتاريخ بداية
     * الإجازة) - كل طلب بحالته (قيد الانتظار/موافق عليها/مرفوضة) بغض
     * النظر عن نوعه.
     */
    public function hrLeaves(Request $request)
    {
        $this->authorize('reports_hr.leaves');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        $status = trim((string) $request->input('status'));
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $employees = Employee::orderBy('name')->get(['id', 'name']);

        $leaves = LeaveRequest::query()
            ->with('employee:id,name,employee_number,branch_id')
            ->whereDate('start_date', '>=', $dateFrom)
            ->whereDate('start_date', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->whereHas('employee', fn ($e) => $e->where('branch_id', $branchId)))
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('start_date')
            ->get();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.leave_type'), __('reports.leave_start_date'), __('reports.leave_end_date'), __('reports.days_count'), __('reports.leave_status')],
                $leaves->map(fn ($l) => [
                    optional($l->employee)->name ?? '-',
                    __('reports.leave_type_' . $l->type),
                    optional($l->start_date)->format('Y-m-d'),
                    optional($l->end_date)->format('Y-m-d'),
                    (float) $l->days_count,
                    __('reports.leave_status_' . $l->status),
                ])->toArray(),
                'hr-leaves-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.hr.leaves', [
            'branches' => $branches,
            'branchId' => $branchId,
            'employees' => $employees,
            'employeeId' => $employeeId,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'leaves' => $leaves,
            'totalDays' => round((float) $leaves->sum('days_count'), 2),
        ]);
    }

    // =====================================================================
    // قسم تسليم المنتج - راجع DeliveryNote/DeliveryNoteItem. "سند
    // التسليم" ده تسجيل بدون أثر مالي لحد ما يتحول (جزئيًا أو كليًا)
    // لفاتورة ضريبية حقيقية عبر DeliveryNoteConvertController، فالتقرير
    // هنا تشغيلي بحت (مفيش مبالغ محاسبية معتمدة) مش مالي زي باقي الأقسام.
    // =====================================================================

    public function deliveryIndex()
    {
        $this->authorizeAnyReport('reports_delivery');
        return view('reports.delivery.index');
    }

    /**
     * ملخص سندات التسليم المسجّلة (save=1) خلال فترة محددة، بتاريخ
     * التسجيل نفسه (created_at) - مش issue_date اللي بيتحدد بس وقت
     * التحويل الفعلي لفاتورة.
     */
    public function deliverySummary(Request $request)
    {
        $this->authorize('reports_delivery.summary');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $customerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $customers = $this->entitySelectedOption($customerId, Customer::class);
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $notes = DeliveryNote::query()
            ->with(['customer:id,name', 'items.product:id,name'])
            ->where('save', 1)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('branchs_id', $branchId))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->orderByDesc('created_at')
            ->get(['id', 'customer_id', 'branchs_id', 'Price', 'Number_of_Quantity', 'status', 'created_at']);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.delivery_note_number'), __('reports.customer'), __('reports.date'), __('reports.quantity'), __('reports.delivery_price'), __('reports.delivery_status')],
                $notes->map(fn ($n) => [
                    $n->id,
                    optional($n->customer)->name ?? '-',
                    optional($n->created_at)->format('Y-m-d'),
                    (float) $n->Number_of_Quantity,
                    (float) $n->Price,
                    (int) $n->status === 3 ? __('reports.delivery_status_converted') : ((int) $n->status === 0 ? __('reports.delivery_status_pending') : __('reports.delivery_status_partial')),
                ])->toArray(),
                'delivery-summary-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.delivery.summary', [
            'branches' => $branches,
            'branchId' => $branchId,
            'customers' => $customers,
            'customerId' => $customerId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'notes' => $notes,
            'totalQuantity' => round((float) $notes->sum('Number_of_Quantity'), 2),
            'totalPrice' => round((float) $notes->sum('Price'), 2),
        ]);
    }

    /**
     * سندات التسليم اللي لسه فيها كمية "معلّقة" (متسلّمة للعميل لكن لسه
     * ما اتحولتش لفاتورة ولا اترجعت بالكامل) - snapshot لحظي مش بفترة،
     * نفس شرط whereColumn المستخدم فعليًا في
     * DeliveryNoteConvertController لتحديد الأصناف المتاحة للتحويل.
     */
    public function deliveryPending(Request $request)
    {
        $this->authorize('reports_delivery.pending');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $customerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $customers = $this->entitySelectedOption($customerId, Customer::class);

        $notes = DeliveryNote::query()
            ->with(['customer:id,name', 'items'])
            ->where('save', 1)
            ->when($branchId, fn ($query) => $query->where('branchs_id', $branchId))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->orderByDesc('created_at')
            ->get(['id', 'customer_id', 'branchs_id', 'Price', 'Number_of_Quantity', 'status', 'created_at']);

        foreach ($notes as $note) {
            $note->remaining_quantity = round((float) $note->items->sum(
                fn ($item) => (float) $item->quantity - (float) $item->quantityreturn - (float) $item->invoiced_quantity
            ), 2);
        }

        $pending = $notes->filter(fn ($note) => $note->remaining_quantity > 0.001)->values();

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.delivery_note_number'), __('reports.customer'), __('reports.date'), __('reports.remaining_quantity')],
                $pending->map(fn ($n) => [
                    $n->id,
                    optional($n->customer)->name ?? '-',
                    optional($n->created_at)->format('Y-m-d'),
                    (float) $n->remaining_quantity,
                ])->toArray(),
                'delivery-pending-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.delivery.pending', [
            'branches' => $branches,
            'branchId' => $branchId,
            'customers' => $customers,
            'customerId' => $customerId,
            'notes' => $pending,
            'totalRemaining' => round((float) $pending->sum('remaining_quantity'), 2),
        ]);
    }

    /**
     * سندات التسليم مجمّعة حسب الموظف اللي أنشأ السند (user_id) خلال
     * الفترة المحددة - نفس بنية salesByEmployee/purchasesByEmployee.
     */
    public function deliveryByEmployee(Request $request)
    {
        $this->authorize('reports_delivery.by_employee');
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        [$dateFrom, $dateTo] = $this->resolvePeriod($request);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        $rows = DeliveryNote::query()
            ->join('users', 'users.id', '=', 'delivery_note.user_id')
            ->where('delivery_note.save', 1)
            ->whereDate('delivery_note.created_at', '>=', $dateFrom)
            ->whereDate('delivery_note.created_at', '<=', $dateTo)
            ->when($branchId, fn ($query) => $query->where('delivery_note.branchs_id', $branchId))
            ->when($userId, fn ($query) => $query->where('delivery_note.user_id', $userId))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_price')
            ->get([
                'users.id as user_id',
                'users.name as user_name',
                DB::raw('COUNT(DISTINCT delivery_note.id) as notes_count'),
                DB::raw('SUM(delivery_note.Number_of_Quantity) as total_quantity'),
                DB::raw('SUM(delivery_note.Price) as total_price'),
            ]);

        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [__('reports.employee'), __('reports.delivery_note_number'), __('reports.quantity'), __('reports.delivery_price')],
                $rows->map(fn ($r) => [$r->user_name, (int) $r->notes_count, (float) $r->total_quantity, (float) $r->total_price])->toArray(),
                'delivery-by-employee-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('reports.delivery.by-employee', [
            'branches' => $branches,
            'branchId' => $branchId,
            'users' => $users,
            'userId' => $userId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'totalNotes' => (int) $rows->sum('notes_count'),
            'totalQuantity' => round((float) $rows->sum('total_quantity'), 2),
            'totalPrice' => round((float) $rows->sum('total_price'), 2),
        ]);
    }

    /**
     * الإيرادات والمصروفات المتحركة خلال فترة معينة - مُستخدمة في
     * incomeStatement() و equityChanges() مع بعض عشان صافي الربح/الخسارة
     * يتحسب بنفس المنطق بالظبط في القائمتين من غير تكرار.
     */
    private function computeIncomeStatement(?int $branchId, string $dateFrom, string $dateTo, string $q = ''): array
    {
        $accounts = FinancialAccount::query()
            ->where('is_parent', false)
            ->whereIn('account_type', [self::REVENUE, self::EXPENSES])
            ->when($branchId, fn ($query) => $this->applyBranchFilter($query, $branchId))
            ->when($q !== '', fn ($query) => $this->applySearch($query, $q))
            ->orderBy('account_number')
            ->get(['id', 'account_number', 'name', 'account_type']);

        $accountIds = $accounts->pluck('id');

        $movements = $accountIds->isEmpty() ? collect() : CreditTransaction::whereIn('customer_id', $accountIds)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('customer_id, SUM(debtor) as total_debtor, SUM(creditor) as total_creditor')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        foreach ($accounts as $account) {
            $movement = $movements->get($account->id);
            $debtor = (float) ($movement->total_debtor ?? 0);
            $creditor = (float) ($movement->total_creditor ?? 0);

            // الإيرادات دائنة بطبعها والمصروفات مدينة بطبعها.
            $account->period_amount = round(
                (int) $account->account_type === self::REVENUE ? $creditor - $debtor : $debtor - $creditor,
                2
            );
        }

        $revenueAccounts = $accounts->where('account_type', self::REVENUE)->values();
        $expenseAccounts = $accounts->where('account_type', self::EXPENSES)->values();

        $totalRevenue = round((float) $revenueAccounts->sum('period_amount'), 2);
        $totalExpenses = round((float) $expenseAccounts->sum('period_amount'), 2);

        return [
            'revenueAccounts' => $revenueAccounts,
            'expenseAccounts' => $expenseAccounts,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netIncome' => round($totalRevenue - $totalExpenses, 2),
        ];
    }

    /**
     * فلتر الفرع المشترك لكل تقارير القسم - راجع تعليق الكلاس فوق
     * لتفسير ليه الحسابات اللي branchs_id فاضي عندها بتفضل ظاهرة دايمًا.
     */
    private function applyBranchFilter($query, int $branchId)
    {
        return $query->where(function ($w) use ($branchId) {
            $w->where('branchs_id', $branchId)->orWhereNull('branchs_id');
        });
    }

    /**
     * بحث بالاسم أو رقم الحساب - نفس فكرة مربع البحث الموجود في شاشة
     * "شجرة الحسابات"/"قائمة الحسابات"، مضاف هنا لكل تقارير القسم اللي
     * بتعرض جدول حسابات (ميزان المراجعة/الميزانية العامة/قائمة الدخل/
     * التدفقات النقدية) عشان يسهل الوصول لحساب معيّن في جدول طويل.
     */
    private function applySearch($query, string $q)
    {
        return $query->where(function ($w) use ($q) {
            $w->where('name', 'like', "%{$q}%")->orWhere('account_number', 'like', "%{$q}%");
        });
    }

    private function naturalBalance(FinancialAccount $account): float
    {
        $debit = (float) $account->debtor_current;
        $credit = (float) $account->creditor_current;

        return in_array((int) $account->account_type, [self::LIABILITIES, self::REVENUE, self::EQUITY], true)
            ? $credit - $debit
            : $debit - $credit;
    }

    /**
     * الفترة الافتراضية لأي تقرير محتاج فترة (لو المستخدم ما اختارش):
     * من أول الشهر الحالي لحد النهاردة.
     */
    private function resolvePeriod(Request $request): array
    {
        $dateFrom = $request->filled('date_from')
            ? $request->input('date_from')
            : Carbon::now('Asia/Riyadh')->startOfMonth()->toDateString();

        $dateTo = $request->filled('date_to')
            ? $request->input('date_to')
            : Carbon::now('Asia/Riyadh')->toDateString();

        return [$dateFrom, $dateTo];
    }

    /**
     * بيرجع [id => name] للعنصر المختار حاليًا بس (مش كل الصفوف) - مستخدم
     * مع قوائم الـ Ajax select (TomSelect) في التقارير عشان نعرض اسم
     * العميل/المورد المختار مبدئيًا من غير ما نحمّل جدول العملاء/الموردين
     * كامل (ممكن يكون فيه عشرات الآلاف من الصفوف).
     */
    private function entitySelectedOption(?int $id, string $modelClass): array
    {
        if (!$id) {
            return [];
        }

        $name = $modelClass::where('id', $id)->value('name');

        return $name ? [$id => $name] : [];
    }
}
