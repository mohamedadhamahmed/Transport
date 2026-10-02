<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Branch;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Services\Reports\ReportExcelExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * شجرة الحسابات (Chart of Accounts) + كشف حساب (Account Statement).
 *
 * بيشتغل على نفس App\Models\FinancialAccount (الجدول الفعلي
 * "financialaccount") المستخدم بالفعل في PurchaseController/
 * PurchaseReturnController/InvoiceController. مفيش حذف نهائي للحساب
 * (destroy) - بس تعطيل/تفعيل (toggleActive)، عشان أي حساب ليه حركات
 * قديمة في credittransaction م تفضلش الحركات دي يتيمة (orphan).
 *
 * الحقول اللي بتتكتب فعليًا هنا كلها مؤكدة من كود شغال بالفعل في
 * PurchaseController@quickStoreSupplier و InvoiceController@quickStoreCustomer
 * (نفس أسماء الأعمدة بالظبط): name, account_type, parent_account_number,
 * account_number, start_balance, current_balance, start_balance_status,
 * other_table_FK, notes, added_by, updated_by, com_code, date, active,
 * is_parent, orginal_id, orginal_type, branchs_id, debtor_current,
 * creditor_current. ملاحظة: مسيبتش start_balance_status علي حسابات
 * يدوية (القيمة 3 دي كانت خاصة بربط حساب عميل/مورد تلقائي بس).
 */
class AccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('accounts.view');

        $query = FinancialAccount::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('account_number', 'like', "%{$q}%");
            });
        }

        if ($request->filled('branchs_id')) {
            $query->where('branchs_id', $request->input('branchs_id'));
        }

        if ($request->filled('status')) {
            $query->where('active', $request->input('status') === 'active' ? 1 : 0);
        }

        $accounts = $query->orderBy('parent_account_number')
            ->orderBy('account_number')
            ->paginate(30)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('accounts.index', compact('accounts', 'branches'));
    }

    /**
     * الأعمدة اللي شاشة الشجرة (وبحثها وتحميل أبنائها بـ AJAX) محتاجاها
     * في كل مستوى - نفس القايمة في الثلاث دوال (tree/treeChildren/treeSearch)
     * عشان الأداء يفضل ثابت في كل حتة.
     */
    private const TREE_COLUMNS = ['id', 'name', 'account_number', 'parent_account_number', 'account_type', 'current_balance', 'debtor_current', 'creditor_current', 'active'];

    /**
     * شجرة الحسابات - المستوى الأول (الجذور) بس بيتجاب هنا فورًا،
     * وأي مستوى تحته بيتجاب بـ AJAX أول مرة يتفتح (راجع treeChildren)
     * بدل ما نجيب كل الحسابات دفعة واحدة زي قبل. التغيير ده ضروري لإن
     * شجرة الحسابات ممكن تبقى فيها عشرات أو مئات الآلاف من الحسابات
     * (حساب شخصي لكل موظف/عميل/مورد...) - جلبها كلها وبناء شجرة متداخلة
     * في الميموري دفعة واحدة كان هيبقى تقيل جدًا على السيرفر والمتصفح
     * مع الحجم ده.
     */
    public function tree(Request $request)
    {
        $this->authorize('accounts.view');

        $roots = FinancialAccount::whereNull('parent_account_number')
            ->orderBy('account_number')
            ->get(self::TREE_COLUMNS);

        return view('accounts.tree', ['roots' => $this->attachHasChildren($roots)]);
    }

    /**
     * أبناء حساب معيّن بس (مستوى واحد، مش الشجرة الفرعية كاملة) - بترجع
     * كجزء HTML جاهز (نفس partial العقدة المستخدم في tree()) عشان
     * JS شاشة الشجرة يحطه مباشرة جوه .children-wrap بتاع الحساب اللي
     * اتفتح، من غير ما يعيد بناء الصفحة. بتتنادى مرة واحدة بس لكل فرع
     * (JS بيحفظ إنه اتحمل بعد أول مرة) - مش في كل توسيع/طي.
     */
    public function treeChildren(Request $request, FinancialAccount $account)
    {
        $this->authorize('accounts.view');

        $depth = max(0, (int) $request->input('depth', 1));

        $children = FinancialAccount::where('parent_account_number', $account->id)
            ->orderBy('account_number')
            ->get(self::TREE_COLUMNS);

        return view('accounts.tree-children', [
            'nodes' => $this->attachHasChildren($children),
            'depth' => $depth,
        ]);
    }

    /**
     * بحث AJAX داخل شاشة شجرة الحسابات - نتيجة مسطّحة (مش شجرة) لأول
     * 50 حساب مطابق، وكل نتيجة معاها "مسار" آباءها (breadcrumb) عشان
     * تبان في سياقها حتى لو أبوها لسه متفتحش في الشجرة. البحث هنا شامل
     * الحسابات المعطّلة كمان (على عكس accounts.search المستخدم في
     * اختيار حساب بقيد/سند، واللي بيرجع النشط بس) عشان تقدر تلاقي حساب
     * معطّل من هنا وتفعّليه تاني. مبني على AJAX (مش فلترة على العميل)
     * لنفس سبب lazy-loading المستويات: مفيش ضمانة إن كل الحسابات محمّلة
     * أصلاً في المتصفح مع شجرة كبيرة.
     */
    public function treeSearch(Request $request)
    {
        $this->authorize('accounts.view');

        $q = trim((string) $request->input('q', ''));

        if ($q === '') {
            return response('', 200);
        }

        $matches = FinancialAccount::where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('account_number', 'like', "%{$q}%");
            })
            ->orderBy('account_number')
            ->limit(50)
            ->get(self::TREE_COLUMNS);

        $ancestorNames = [];
        $resolveBreadcrumb = function (?int $parentId) use (&$resolveBreadcrumb, &$ancestorNames) {
            if (!$parentId) {
                return [];
            }

            if (!array_key_exists($parentId, $ancestorNames)) {
                $parent = FinancialAccount::find($parentId, ['id', 'name', 'parent_account_number']);
                $ancestorNames[$parentId] = $parent
                    ? array_merge($resolveBreadcrumb($parent->parent_account_number), [$parent->name])
                    : [];
            }

            return $ancestorNames[$parentId];
        };

        $results = $matches->map(fn (FinancialAccount $account) => [
            'account' => $account,
            'breadcrumb' => $resolveBreadcrumb($account->parent_account_number),
        ]);

        return view('accounts.tree-search-results', ['results' => $results]);
    }

    /**
     * بيحدد لكل حساب في المجموعة هل ليه أبناء ولا لأ (استعلام واحد بس
     * لكل المجموعة، مش استعلام لكل حساب) - عشان نعرف نرسم زرار
     * التوسيع (toggle-btn) بس للحسابات اللي فعلاً ليها أبناء، من غير ما
     * نجيب الأبناء نفسها قبل الأوان.
     *
     * @return array<int, array{account: FinancialAccount, hasChildren: bool}>
     */
    private function attachHasChildren($accounts): array
    {
        if ($accounts->isEmpty()) {
            return [];
        }

        $parentIdsWithChildren = FinancialAccount::whereIn('parent_account_number', $accounts->pluck('id'))
            ->distinct()
            ->pluck('parent_account_number')
            ->all();

        return $accounts->map(fn (FinancialAccount $account) => [
            'account' => $account,
            'hasChildren' => in_array($account->id, $parentIdsWithChildren),
        ])->all();
    }

    public function create(Request $request)
    {
        $this->authorize('accounts.create');

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $accountTypes = AccountType::where('active', true)->orderBy('id')->get();
        $parentAccount = null;
        if ($request->filled('parent_id')) {
            $parentAccount = FinancialAccount::find($request->input('parent_id'));
        }

        return view('accounts.create', compact('branches', 'accountTypes', 'parentAccount'));
    }

    public function store(Request $request)
    {
        $this->authorize('accounts.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_account_number' => ['nullable', 'integer', 'exists:financialaccount,id'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_category_id' => ['nullable', 'integer', 'exists:account_types,id'],
            'branchs_id' => ['nullable', 'exists:branches,id'],
            'start_balance' => ['nullable', 'numeric', 'min:0'],
            'start_balance_side' => ['nullable', 'in:debtor,creditor'],
            'is_parent' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $accountNumber = $validated['account_number'] ?? null;
        if (! $accountNumber) {
            $accountNumber = (string) ((int) FinancialAccount::where('parent_account_number', $validated['parent_account_number'] ?? null)
                ->max('account_number') + 1);
        }

        $startBalance = (float) ($validated['start_balance'] ?? 0);
        $side = $validated['start_balance_side'] ?? 'debtor';
        $debtorCurrent = $startBalance > 0 && $side === 'debtor' ? $startBalance : 0;
        $creditorCurrent = $startBalance > 0 && $side === 'creditor' ? $startBalance : 0;

        // الحساب الرئيسي مالوش رصيد (ميزان المراجعة والميزانية بيقروا
        // الحسابات الفرعية بس) - الرصيد الافتتاحي بيتسجل على حساب فرعي.
        if ($request->boolean('is_parent', false)) {
            $startBalance = 0;
            $debtorCurrent = 0;
            $creditorCurrent = 0;
        }

        // الحساب الجديد يدوي دايمًا (orginal_type = null)، يعني طبيعته
        // مدين حسب FinancialAccount::isCreditNormal() - فرصيد افتتاحي
        // "دائن" هيظهر current_balance بالسالب، وده صحيح محاسبيًا لحساب
        // مدين بطبعه بيبدأ برصيد دائن (زي حساب هيتحول مستقبلاً لمورد).
        // لو الأدمن ما اختارتش تصنيف صريح، بنورّث تصنيف الحساب الأب
        // (لو موجود) بدل ما يفضل account_category_id فاضي. account_type
        // بياخد نفس القيمة بالظبط - العمودين بقوا بنفس المعنى المحاسبي
        // (أصول/خصوم/إيرادات/مصروفات/حقوق ملكية) بعد ميجريشن
        // 2026_09_02_000028.
        $accountCategoryId = $validated['account_category_id']
            ?? FinancialAccount::inheritedCategoryId($validated['parent_account_number'] ?? null);

        $account = FinancialAccount::create([
            'name' => $validated['name'],
            'account_type' => $accountCategoryId,
            'account_category_id' => $accountCategoryId,
            'parent_account_number' => $validated['parent_account_number'] ?? null,
            'account_number' => $accountNumber,
            'start_balance' => $startBalance,
            'current_balance' => $debtorCurrent - $creditorCurrent,
            'other_table_FK' => null,
            'notes' => $validated['notes'] ?? null,
            'added_by' => Auth::id(),
            'updated_by' => null,
            'com_code' => 1,
            'date' => Carbon::now('Asia/Riyadh'),
            'active' => $request->boolean('active', true),
            'is_parent' => $request->boolean('is_parent', false),
            'orginal_id' => null,
            'orginal_type' => null,
            'branchs_id' => $validated['branchs_id'] ?? null,
            'debtor_current' => $debtorCurrent,
            'creditor_current' => $creditorCurrent,
        ]);

        // الرصيد الافتتاحي كان بيتكتب في طرف واحد بس فميزان المراجعة يطلع
        // غير متزن - الطرف التاني بيتسجل على حساب "أرصدة افتتاحية" (حقوق ملكية).
        $this->postOpeningBalanceCounterpart($account, (float) $debtorCurrent, (float) $creditorCurrent);

        return redirect()->route('accounts.index')->with('success', __('accounts.created_successfully'));
    }

    public function edit(FinancialAccount $account)
    {
        $this->authorize('accounts.edit');

        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $accountTypes = AccountType::where('active', true)
            ->orWhere('id', $account->account_category_id ?? 0)
            ->orderBy('id')
            ->get();

        return view('accounts.edit', compact('account', 'branches', 'accountTypes'));
    }

    public function update(Request $request, FinancialAccount $account)
    {
        $this->authorize('accounts.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_account_number' => ['nullable', 'integer', 'exists:financialaccount,id', 'not_in:' . $account->id],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_category_id' => ['nullable', 'integer', 'exists:account_types,id'],
            'branchs_id' => ['nullable', 'exists:branches,id'],
            'is_parent' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        // account_type بيتزامن مع account_category_id دايمًا (نفس القيمة
        // أو فاضي مع بعض) - العمودين بقوا بنفس المعنى المحاسبي بعد
        // ميجريشن 2026_09_02_000028.

        if ($request->boolean('is_parent', false) && ! $account->is_parent
            && (abs((float) $account->debtor_current) + abs((float) $account->creditor_current) > 0
                || CreditTransaction::where('customer_id', $account->id)->exists())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'is_parent' => 'مينفعش تحوّل حساب عليه رصيد أو حركات لحساب رئيسي - رصيده هيختفي من ميزان المراجعة والميزانية.',
            ]);
        }
        $account->update([
            'name' => $validated['name'],
            'parent_account_number' => $validated['parent_account_number'] ?? null,
            'account_number' => $validated['account_number'] ?? $account->account_number,
            'account_type' => $validated['account_category_id'] ?? null,
            'account_category_id' => $validated['account_category_id'] ?? null,
            'branchs_id' => $validated['branchs_id'] ?? null,
            'is_parent' => $request->boolean('is_parent', false),
            'notes' => $validated['notes'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('accounts.index')->with('success', __('accounts.updated_successfully'));
    }

    /**
     * تفعيل/تعطيل حساب. بيدعم نوعين من الاستخدام: فورم عادي (شاشة
     * accounts.index) بيرجع redirect + flash session زي ما كان، أو
     * طلب AJAX (سويتش التفعيل المباشر في شاشة الشجرة - accounts.tree)
     * بيرجع JSON بدل ما يعمل صفحة كاملة من جديد.
     */
    public function toggleActive(Request $request, FinancialAccount $account)
    {
        $this->authorize('accounts.edit');

        $account->update([
            'active' => ! $account->active,
            'updated_by' => Auth::id(),
        ]);

        $message = $account->active
            ? __('accounts.activated_successfully')
            : __('accounts.deactivated_successfully');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'active' => $account->active, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * كشف حساب: كل حركات credittransaction المسجلة على الحساب ده
     * (customer_id = account id)، مع رصيد جاري (running balance)
     * بيتحسب سطر بسطر.
     */
    public function statement(Request $request, FinancialAccount $account)
    {
        $this->authorize('accounts.view');

        $query = CreditTransaction::where('customer_id', $account->id);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('operation_type')) {
            $query->where('operation_type', $request->input('operation_type'));
        }

        $transactions = $query->orderBy('created_at')->orderBy('id')->get();

        // الرصيد الجاري بيتحسب حسب طبيعة الحساب (مدين/دائن) زي current_balance
        // بالظبط - راجع تعليق FinancialAccount::isCreditNormal().
        $sign = $account->isCreditNormal() ? -1 : 1;

        // لو فيه فلترة بتاريخ "من" - الرصيد الافتتاحي المفروض يبقى رصيد
        // الحساب لحد قبل الفترة المفلترة دي (مش الرصيد الحالي مطروح منه
        // كل الحركات اللي في الفترة بس)، عشان لو فيه حركات بعد تاريخ "إلى"
        // برضه محسوبة في current_balance. فبنجيب كل الحركات اللي قبل
        // تاريخ "من" ونحسب أثرها لوحدها.
        if ($request->filled('date_from')) {
            $beforePeriod = CreditTransaction::where('customer_id', $account->id)
                ->whereDate('created_at', '<', $request->input('date_from'))
                ->selectRaw('COALESCE(SUM(debtor), 0) as sum_debtor, COALESCE(SUM(creditor), 0) as sum_creditor')
                ->first();

            $openingBalance = $sign * ((float) $beforePeriod->sum_debtor - (float) $beforePeriod->sum_creditor);
        } else {
            $openingBalance = (float) $account->current_balance
                - $sign * ((float) $transactions->sum('debtor') - (float) $transactions->sum('creditor'));
        }

        $running = $openingBalance;
        $transactions = $transactions->map(function (CreditTransaction $t) use (&$running, $sign) {
            $running = $running + $sign * ((float) $t->debtor - (float) $t->creditor);
            $t->running_balance = $running;

            return $t;
        });

        // ثابتة هنا صراحةً (بدل الاعتماد على ثابت في موديل CreditTransaction)
        // عشان تشتغل مهما كان تعريف الموديل الفعلي المُحمَّل وقت التشغيل.
        $operationTypes = [
            1 => 'مبيعات',
            2 => 'مشتريات',
            3 => 'سند قبض',
            4 => 'سند صرف',
            5 => 'قيد يومية',
            6 => 'قيد افتتاحي',
        ];

        if ($request->get('export') === 'excel') {
            $rows = [[__('accounts.opening_balance_label'), '', '', '', '', number_format($openingBalance, 2)]];
            foreach ($transactions as $t) {
                $rows[] = [
                    optional($t->created_at)->format('Y-m-d'),
                    $operationTypes[$t->operation_type] ?? '-',
                    $t->note ?? '-',
                    $t->invoice_number ?? '-',
                    $t->debtor > 0 ? (float) $t->debtor : '',
                    $t->creditor > 0 ? (float) $t->creditor : '',
                    (float) $t->running_balance,
                ];
            }

            return ReportExcelExporter::download(
                [__('accounts.date'), __('accounts.operation_type'), __('accounts.description'), __('accounts.reference'), __('accounts.debtor'), __('accounts.creditor'), __('accounts.running_balance')],
                $rows,
                'account-statement-' . $account->id . '-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $allAccounts = FinancialAccount::orderBy('account_number')
            ->get(['id', 'name', 'account_number', 'parent_account_number', 'is_parent', 'active']);

        return view('accounts.statement', [
            'account' => $account,
            'allAccounts' => $allAccounts,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'dateFrom' => $request->input('date_from', ''),
            'dateTo' => $request->input('date_to', ''),
            'operationType' => $request->input('operation_type', ''),
            'operationTypes' => $operationTypes,
        ]);
    }

    /**
     * كشف حساب عام من قسم التقارير المالية (/reports/accounts/statement)
     * بيسمح باختيار أي حساب من شجرة الحسابات عبر قائمة بحث تفاعلية فورية (Searchable Select)،
     * أو بيعرض أول حساب متاح تلقائيًا إذا لم يُحدد account_id في الطلب.
     */
    public function statementReport(Request $request)
    {
        $this->authorize('accounts.view');

        $accountId = $request->input('account_id');
        $account = null;

        if ($accountId) {
            $account = FinancialAccount::find($accountId);
        }

        if (!$account) {
            $account = FinancialAccount::orderBy('account_number')->first();
        }

        if (!$account) {
            abort(404, __('accounts.no_accounts_found') ?? 'لا توجد حسابات مسجلة في شجرة الحسابات');
        }

        return $this->statement($request, $account);
    }

    /**
     * بحث AJAX عن الحسابات (لاستخدامه في اختيار حساب بالقيد اليومي وسندات القبض/الصرف وقوائم البحث).
     *
     * scope=treasury: مقصور على حسابات الخزينة والبنوك بتاعت الفروع بس (parent_account_number = 4 أو 5).
     * scope=all: يشمل كل الحسابات (النشطة وغير النشطة).
     * الافتراضي: الحسابات النشطة.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $scope = $request->input('scope');

        $accounts = FinancialAccount::query()
            ->when($scope === 'treasury', function ($query) {
                $query->whereIn('parent_account_number', [4, 5]);
            })
            ->when($scope !== 'all', function ($query) {
                // الحسابات الرئيسية مينفعش يتسجل عليها قيد/سند (مش بتدخل
                // في ميزان المراجعة والميزانية) - الفرعية بس.
                $query->where('active', true)->where('is_parent', false);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('account_number', 'like', "%{$q}%");
                });
            })
            ->orderBy('account_number')
            ->limit(50)
            ->get(['id', 'name', 'account_number', 'current_balance']);

        return response()->json($accounts->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'account_number' => $a->account_number,
                'text' => ($a->account_number ? $a->account_number . ' - ' : '') . $a->name,
                'current_balance' => $a->current_balance,
            ];
        }));
    }

    /**
     * بيسجّل الطرف المقابل للرصيد الافتتاحي على حساب "أرصدة افتتاحية"
     * (تحت رأس المال في حقوق الملكية) + حركتين في credittransactions عشان
     * يظهروا في كشف الحساب.
     */
    private function postOpeningBalanceCounterpart(FinancialAccount $account, float $debit, float $credit): void
    {
        if (($debit <= 0 && $credit <= 0) || $account->is_parent) {
            return;
        }

        $counterpart = $this->openingBalanceAccount();
        if (! $counterpart || $counterpart->id === $account->id) {
            return;
        }

        // حقوق الملكية طبيعتها دائنة: الطرف المقابل عكس طرف الحساب.
        $counterDebit = $credit;
        $counterCredit = $debit;
        $counterpart->update([
            'current_balance' => (float) $counterpart->current_balance + $counterCredit - $counterDebit,
            'debtor_current' => (float) $counterpart->debtor_current + $counterDebit,
            'creditor_current' => (float) $counterpart->creditor_current + $counterCredit,
        ]);

        $now = Carbon::now('Asia/Riyadh');
        $base = [
            'user_id' => Auth::id(),
            'branchs_id' => $account->branchs_id,
            'note' => 'رصيد افتتاحي - ' . $account->name,
            'operation_type' => 10,
            'invoice_number' => 'OB-' . $account->id,
            'date_export' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        CreditTransaction::create($base + [
            'customer_id' => $account->id,
            'recive_amount' => max($debit, $credit),
            'currentblance' => $account->current_balance,
            'debtor' => $debit,
            'creditor' => $credit,
        ]);

        CreditTransaction::create($base + [
            'customer_id' => $counterpart->id,
            'recive_amount' => max($debit, $credit),
            'currentblance' => $counterpart->current_balance,
            'debtor' => $counterDebit,
            'creditor' => $counterCredit,
        ]);
    }

    private function openingBalanceAccount(): ?FinancialAccount
    {
        $name = 'أرصدة افتتاحية';

        $existing = FinancialAccount::where('name', $name)->where('is_parent', false)->first();
        if ($existing) {
            return $existing;
        }

        // تحت "راس المال" لو موجود، وإلا أول حساب رئيسي في حقوق الملكية.
        $parent = FinancialAccount::where('account_type', 5)->where('is_parent', true)
            ->where('name', 'like', '%راس المال%')->orderBy('id')->first()
            ?? FinancialAccount::where('account_type', 5)->where('is_parent', true)->orderBy('id')->first();
        if (! $parent) {
            return null;
        }

        $maxChild = (int) FinancialAccount::where('parent_account_number', $parent->id)->max('account_number');
        $number = $maxChild > 0 ? $maxChild + 1 : ((int) $parent->account_number) + 1;

        return FinancialAccount::create([
            'name' => $name,
            'account_type' => 5,
            'account_category_id' => 5,
            'parent_account_number' => $parent->id,
            'account_number' => (string) $number,
            'start_balance' => 0,
            'current_balance' => 0,
            'debtor_current' => 0,
            'creditor_current' => 0,
            'added_by' => Auth::id(),
            'com_code' => 1,
            'date' => Carbon::now('Asia/Riyadh'),
            'active' => true,
            'is_parent' => false,
            'branchs_id' => null,
        ]);
    }
}
