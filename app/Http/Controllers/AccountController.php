<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
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
     * شجرة الحسابات كاملة (كل الحسابات - مش بيجينيشن) في شكل هيكل
     * أب/أبناء متداخل، بدل القايمة المسطحة في index(). parent_account_number
     * فعليًا بيخزّن id الحساب الأب (مش رقم الحساب نفسه - راجعي
     * FinancialAccount::parentAccount())، فبنبني الشجرة على أساسه.
     * بنجيب كل الأعمدة اللي شاشة الشجرة محتاجاها بمكالمة واحدة بس
     * وبنجمعها بـ groupBy على الميموري - عدد حسابات الشركة عادةً
     * محدود (مئات لحد آلاف قليلة) فمفيش داعي لاستعلامات N+1 متكررة.
     */
    public function tree(Request $request)
    {
        $accounts = FinancialAccount::query()
            ->orderBy('account_number')
            ->get(['id', 'name', 'account_number', 'parent_account_number', 'current_balance', 'debtor_current', 'creditor_current', 'active']);

        $byParent = $accounts->groupBy(fn (FinancialAccount $account) => $account->parent_account_number ?? 'root');

        $buildNode = function (FinancialAccount $account) use (&$buildNode, $byParent) {
            $children = ($byParent->get($account->id) ?? collect())
                ->map($buildNode)
                ->values();

            return [
                'account' => $account,
                'children' => $children,
            ];
        };

        $roots = ($byParent->get('root') ?? collect())->map($buildNode)->values();

        return view('accounts.tree', ['roots' => $roots]);
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('accounts.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_account_number' => ['nullable', 'integer', 'exists:financialaccount,id'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_type' => ['nullable', 'integer'],
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

        // الحساب الجديد يدوي دايمًا (orginal_type = null)، يعني طبيعته
        // مدين حسب FinancialAccount::isCreditNormal() - فرصيد افتتاحي
        // "دائن" هيظهر current_balance بالسالب، وده صحيح محاسبيًا لحساب
        // مدين بطبعه بيبدأ برصيد دائن (زي حساب هيتحول مستقبلاً لمورد).
        $account = FinancialAccount::create([
            'name' => $validated['name'],
            'account_type' => $validated['account_type'] ?? null,
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

        return redirect()->route('accounts.index')->with('success', __('accounts.created_successfully'));
    }

    public function edit(FinancialAccount $account)
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('accounts.edit', compact('account', 'branches'));
    }

    public function update(Request $request, FinancialAccount $account)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_account_number' => ['nullable', 'integer', 'exists:financialaccount,id', 'not_in:' . $account->id],
            'account_number' => ['nullable', 'string', 'max:50'],
            'branchs_id' => ['nullable', 'exists:branches,id'],
            'is_parent' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $account->update([
            'name' => $validated['name'],
            'parent_account_number' => $validated['parent_account_number'] ?? null,
            'account_number' => $validated['account_number'] ?? $account->account_number,
            'branchs_id' => $validated['branchs_id'] ?? null,
            'is_parent' => $request->boolean('is_parent', false),
            'notes' => $validated['notes'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('accounts.index')->with('success', __('accounts.updated_successfully'));
    }

    public function toggleActive(FinancialAccount $account)
    {
        $account->update([
            'active' => ! $account->active,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', $account->active
            ? __('accounts.activated_successfully')
            : __('accounts.deactivated_successfully'));
    }

    /**
     * كشف حساب: كل حركات credittransaction المسجلة على الحساب ده
     * (customer_id = account id)، مع رصيد جاري (running balance)
     * بيتحسب سطر بسطر.
     */
    public function statement(Request $request, FinancialAccount $account)
    {
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
        // بالظبط - راجعي تعليق FinancialAccount::isCreditNormal().
        $sign = $account->isCreditNormal() ? -1 : 1;

        $openingBalance = (float) $account->current_balance
            - $sign * ((float) $transactions->sum('debtor') - (float) $transactions->sum('creditor'));

        $running = $openingBalance;
        $transactions = $transactions->map(function (CreditTransaction $t) use (&$running, $sign) {
            $running = $running + $sign * ((float) $t->debtor - (float) $t->creditor);
            $t->running_balance = $running;

            return $t;
        });

        return view('accounts.statement', [
            'account' => $account,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
        ]);
    }

    /**
     * بحث AJAX عن الحسابات النشطة (لاستخدامه في اختيار حساب بالقيد
     * اليومي وسندات القبض/الصرف). بيرجع أول 20 نتيجة بس.
     *
     * scope=treasury: مقصور على حسابات الخزينة والبنوك بتاعت الفروع
     * بس (parent_account_number = 4 أو 5) - ده مطلوب في سندات القبض
     * والصرف عشان حقل "حساب الخزينة" مايظهرش فيه عملاء/موردين/حسابات
     * عامة تانية غلط. الأرقام 4 و5 دول أرقام الحسابات الأب الفعلية
     * لمجموعتي "الخزينة" و"البنوك" في شجرة الحسابات.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $scope = $request->input('scope');

        $accounts = FinancialAccount::where('active', true)
            ->when($scope === 'treasury', function ($query) {
                $query->whereIn('parent_account_number', [4, 5]);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('account_number', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'account_number', 'current_balance']);

        return response()->json($accounts);
    }
}
