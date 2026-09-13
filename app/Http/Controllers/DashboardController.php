<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Employee;
use App\Models\FinancialAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReturn;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\StockTransfer;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // حسابات الخزينة والبنوك (نفس أرقام الحسابات الأب الثابتة المستخدمة
    // في ReportController::cashFlow) - مصدر بطاقة "الرصيد النقدي الحالي"
    // الجديدة في الشاشة الرئيسية.
    private const BANK_PARENT_ACCOUNT_NUMBER = 4;
    private const CASH_PARENT_ACCOUNT_NUMBER = 5;

    /**
     * الشاشة الرئيسية - بترجع فاضية وسريعة على طول (كروت skeleton بس)،
     * وكل الأرقام الفعلية بتتحمل بعد كده بطلب Ajax واحد (stats()) عشان
     * الصفحة متتقلش أو تستنى استعلامات قاعدة البيانات قبل ما تظهر.
     */
    public function index()
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('dashboard', compact('branches'));
    }

    /**
     * كل أرقام الشاشة الرئيسية في استعلام واحد لكل مصدر بيانات (بدون
     * تحميل صفوف فعلية غير المطلوبة - SUM/COUNT/GROUP BY بس) عشان تكون
     * سريعة حتى مع آلاف الصفوف. بيترجع JSON يستخدمه الـ Ajax في
     * dashboard.blade.php.
     *
     * فلتر الفرع (?branch_id=): كل الأرقام والرسوم بتتفلتر على الفرع
     * المختار لو موجود - غير عدد العملاء/الموردين (الجدولين دول مالهمش
     * عمود فرع في التطبيق) والموظفين النشطين حاليًا. لو فيه فرع مختار،
     * "أكتر فرع بيعًا اليوم" مالهاش معنى (فرع واحد بس)، فبتتبدّل تلقائيًا
     * بـ "أكتر منتج بيعًا اليوم" لنفس الفرع - ده اللي طلبتيه ("اختار
     * الفرع يجيب تصنيفه").
     */
    public function stats(Request $request): JsonResponse
    {
        $branchId = $request->integer('branch_id') ?: null;
        $selectedBranchName = $branchId ? Branch::query()->where('id', $branchId)->value('name') : null;

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $netTotalExpr = '(subtotal + tax_amount - discount_amount)';

        $todaySales = Invoice::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', $today)
            ->selectRaw("COUNT(*) as cnt, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->first();

        $monthSales = Invoice::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', '>=', $monthStart)
            ->selectRaw("COUNT(*) as cnt, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->first();

        $todaySalesProfit = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->when($branchId, fn ($q) => $q->where('invoices.branch_id', $branchId))
            ->whereDate('invoices.issue_date', $today)
            ->selectRaw('COALESCE(SUM(((invoice_items.unit_price * invoice_items.quantity) - invoice_items.discount_amount) - (invoice_items.quantity * CASE WHEN products.average_cost > 0 THEN products.average_cost ELSE products.purchase_price END)), 0) as profit')
            ->value('profit');

        $monthSalesProfit = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->when($branchId, fn ($q) => $q->where('invoices.branch_id', $branchId))
            ->whereDate('invoices.issue_date', '>=', $monthStart)
            ->selectRaw('COALESCE(SUM(((invoice_items.unit_price * invoice_items.quantity) - invoice_items.discount_amount) - (invoice_items.quantity * CASE WHEN products.average_cost > 0 THEN products.average_cost ELSE products.purchase_price END)), 0) as profit')
            ->value('profit');

        $todayPurchases = Purchase::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', $today)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(grand_total), 0) as net')
            ->first();

        $monthPurchases = Purchase::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', '>=', $monthStart)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(grand_total), 0) as net')
            ->first();

        // ⚠️ بعد دعم "السندات متعددة البنود" (App\Models\AccountVoucherLine)
        // بقى المبلغ الفعلي على مستوى بنود السند مش عمود amount اللي
        // اتشال من account_vouchers - فبنجمع من account_voucher_lines
        // بعد الربط بالسند لتطبيق فلتري الفرع والتاريخ، وبنعدّ السندات
        // المميزة (COUNT DISTINCT) مش عدد البنود.
        $todayReceipts = DB::table('account_vouchers')
            ->join('account_voucher_lines', 'account_voucher_lines.account_voucher_id', '=', 'account_vouchers.id')
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->where('account_vouchers.type', AccountVoucher::TYPE_RECEIPT)
            ->whereDate('account_vouchers.voucher_date', $today)
            ->selectRaw('COUNT(DISTINCT account_vouchers.id) as cnt, COALESCE(SUM(account_voucher_lines.amount), 0) as net')
            ->first();

        $todayPayments = DB::table('account_vouchers')
            ->join('account_voucher_lines', 'account_voucher_lines.account_voucher_id', '=', 'account_vouchers.id')
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->where('account_vouchers.type', AccountVoucher::TYPE_PAYMENT)
            ->whereDate('account_vouchers.voucher_date', $today)
            ->selectRaw('COUNT(DISTINCT account_vouchers.id) as cnt, COALESCE(SUM(account_voucher_lines.amount), 0) as net')
            ->first();

        // ملخص الحسابات في الشاشة الرئيسية (طلب: "ملخص الحسابات...سندات
        // القبض والصرف والقيد اليومي لو حتى اليوم بس") - بنطبّق نفس فلتر
        // الفرع "المرن" المستخدم في تقرير السندات والقيود
        // (ReportController::vouchersSummary): أي قيد خاص بالفرع المختار
        // بيدخل، وأي قيد مشترك بين الفروع (branch_id فاضي) بيفضل يدخل
        // كمان مهما كان الفرع المختار.
        $todayDailyEntriesCount = JournalEntry::query()
            ->where('entry_type', JournalEntry::TYPE_DAILY)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->whereDate('entry_date', $today)
            ->count();

        $todayOpeningEntriesCount = JournalEntry::query()
            ->where('entry_type', JournalEntry::TYPE_OPENING)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->whereDate('entry_date', $today)
            ->count();

        $todayStockTransfersCount = class_exists(StockTransfer::class)
            ? StockTransfer::query()
                ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId)))
                ->whereDate('transfer_date', $today)
                ->count()
            : 0;

        $todaySalesReturnsCount = class_exists(InvoiceReturn::class)
            ? InvoiceReturn::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', $today)
                ->distinct()
                ->count('reference_value')
            : 0;

        $todayPurchasesReturnsCount = class_exists(PurchaseReturn::class)
            ? PurchaseReturn::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereDate('return_date', $today)
                ->count()
            : 0;

        $lowStockCount = Product::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('low_stock_alert_quantity')
            ->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')
            ->count();

        $pendingDeliveryNotes = DeliveryNote::query()
            ->when($branchId, fn ($q) => $q->where('branchs_id', $branchId))
            ->where('save', 1)
            ->where('status', '!=', 3)
            ->count();

        // العملاء والموردين والموظفين مالهومش فلترة بفرع حاليًا (جدول
        // العملاء/الموردين مفيهوش عمود فرع في التطبيق ده أصلًا).
        $activeEmployeesCount = class_exists(Employee::class)
            ? Employee::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', Employee::STATUS_ACTIVE)
                ->count()
            : 0;

        // الرصيد النقدي الحالي (خزينة + بنوك) - نفس منطق فلتر الفرع "المرن"
        // المستخدم في ReportController::applyBranchFilter: أي حساب خزينة/بنك
        // خاص بالفرع المختار بيدخل، وأي حساب مشترك بين الفروع (branchs_id
        // فاضي) بيفضل يدخل كمان مهما كان الفرع المختار.
        $currentCashBalance = FinancialAccount::query()
            ->where('is_parent', false)
            ->whereIn('parent_account_number', [self::BANK_PARENT_ACCOUNT_NUMBER, self::CASH_PARENT_ACCOUNT_NUMBER])
            ->when($branchId, fn ($q) => $q->where(function ($w) use ($branchId) {
                $w->where('branchs_id', $branchId)->orWhereNull('branchs_id');
            }))
            ->selectRaw('COALESCE(SUM(debtor_current - creditor_current), 0) as balance')
            ->value('balance');

        return response()->json([
            'selected_branch_id' => $branchId,
            'selected_branch_name' => $selectedBranchName,
            'current_cash_balance' => round((float) $currentCashBalance, 2),

            'customers_count' => Customer::count(),
            'suppliers_count' => Supplier::count(),

            'today_sales_count' => (int) $todaySales->cnt,
            'today_sales_net' => round((float) $todaySales->net, 2),
            'today_sales_profit' => round((float) $todaySalesProfit, 2),
            'month_sales_count' => (int) $monthSales->cnt,
            'month_sales_net' => round((float) $monthSales->net, 2),
            'month_sales_profit' => round((float) $monthSalesProfit, 2),

            'today_purchases_count' => (int) $todayPurchases->cnt,
            'today_purchases_net' => round((float) $todayPurchases->net, 2),
            'month_purchases_count' => (int) $monthPurchases->cnt,
            'month_purchases_net' => round((float) $monthPurchases->net, 2),

            'today_receipts_count' => (int) $todayReceipts->cnt,
            'today_receipts_net' => round((float) $todayReceipts->net, 2),
            'today_payments_count' => (int) $todayPayments->cnt,
            'today_payments_net' => round((float) $todayPayments->net, 2),
            'today_net_cash_movement' => round((float) $todayReceipts->net - (float) $todayPayments->net, 2),
            'today_daily_entries_count' => $todayDailyEntriesCount,
            'today_opening_entries_count' => $todayOpeningEntriesCount,

            'today_stock_transfers_count' => $todayStockTransfersCount,
            'today_sales_returns_count' => $todaySalesReturnsCount,
            'today_purchases_returns_count' => $todayPurchasesReturnsCount,

            'low_stock_count' => $lowStockCount,
            'pending_delivery_notes_count' => $pendingDeliveryNotes,
            'active_employees_count' => $activeEmployeesCount,

            'sales_purchases_trend' => $this->salesPurchasesTrend($netTotalExpr, $branchId),
            'top_employees_today' => $this->topEmployeesToday($netTotalExpr, $today, $branchId),

            // لو فيه فرع مختار: مفيش معنى لـ"أكتر فرع" (فرع واحد بس) -
            // بنستبدلها بـ"أكتر منتج بيعًا" لنفس الفرع بدل منها.
            'top_branches_mode' => $branchId ? 'products' : 'branches',
            'top_branches_today' => $branchId
                ? $this->topProductsToday($today, $branchId)
                : $this->topBranchesToday($netTotalExpr, $today),

            'top_selling_products' => $this->topSellingProductsDashboard($branchId),

            'recent_sales' => $this->recentSales($branchId),
            'recent_purchases' => $this->recentPurchases($branchId),
        ]);
    }

    /**
     * مبيعات ومشتريات آخر 7 أيام (بما فيهم اليوم) - مصدر بيانات "مقارنة
     * المبيعات والمشتريات" في الشاشة الرئيسية. بنعمل استعلام واحد لكل
     * جدول (مجمّع باليوم) بدل 7 استعلامات منفصلة، وبعدين بنكمّل أي يوم
     * من غير عمليات بصفر عشان الرسم البياني يفضل متصل.
     */
    private function salesPurchasesTrend(string $netTotalExpr, ?int $branchId): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $salesByDay = Invoice::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', '>=', $days->first())
            ->selectRaw("DATE(issue_date) as d, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->groupBy('d')
            ->pluck('net', 'd');

        $purchasesByDay = Purchase::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', '>=', $days->first())
            ->selectRaw('DATE(issue_date) as d, COALESCE(SUM(grand_total), 0) as net')
            ->groupBy('d')
            ->pluck('net', 'd');

        // ملحوظة: مبعتش اسم اليوم بالعربي/الإنجليزي من هنا - عشان الاختيار
        // بين اللغتين بيبقى حسب لغة الواجهة الحالية (auth locale)، فبنسيب
        // الـ JS في dashboard.blade.php يشتق اسم اليوم من التاريخ مباشرة
        // (Intl.DateTimeFormat) حسب لغة الصفحة وقت العرض.
        return $days->map(fn ($d) => [
            'date' => $d,
            'sales' => round((float) ($salesByDay[$d] ?? 0), 2),
            'purchases' => round((float) ($purchasesByDay[$d] ?? 0), 2),
        ])->values()->all();
    }

    /**
     * أكتر 5 مستخدمين (اللي بيسجلوا الفواتير - created_by) مبيعات
     * صافية اليوم - مصدر "أكتر موظف بيعا اليوم".
     */
    private function topEmployeesToday(string $netTotalExpr, string $today, ?int $branchId): array
    {
        return Invoice::query()
            ->join('users', 'users.id', '=', 'invoices.created_by')
            ->when($branchId, fn ($q) => $q->where('invoices.branch_id', $branchId))
            ->whereDate('invoices.issue_date', $today)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('net')
            ->limit(5)
            ->selectRaw("users.name as name, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'net' => round((float) $row->net, 2)])
            ->all();
    }

    /**
     * أكتر 5 فروع مبيعات صافية اليوم - مصدر "أكتر فرع بيعا اليوم" (لما
     * مفيش فرع مختار في الفلتر).
     */
    private function topBranchesToday(string $netTotalExpr, string $today): array
    {
        return Invoice::query()
            ->join('branches', 'branches.id', '=', 'invoices.branch_id')
            ->whereDate('invoices.issue_date', $today)
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('net')
            ->limit(5)
            ->selectRaw("branches.name as name, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'net' => round((float) $row->net, 2)])
            ->all();
    }

    /**
     * أكتر 5 منتجات بيعًا اليوم لفرع معين - بتظهر بدل "أكتر فرع" لما
     * يكون فيه فرع مختار في فلتر الشاشة الرئيسية.
     */
    private function topProductsToday(string $today, int $branchId): array
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.branch_id', $branchId)
            ->whereDate('invoices.issue_date', $today)
            ->groupBy('invoice_items.product_name_snapshot')
            ->orderByDesc('net')
            ->limit(5)
            ->selectRaw('invoice_items.product_name_snapshot as name, COALESCE(SUM(invoice_items.unit_price * invoice_items.quantity), 0) as net')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'net' => round((float) $row->net, 2)])
            ->all();
    }

    /**
     * أكثر 5 منتجات مبيعاً بالكمية والقيمة
     */
    private function topSellingProductsDashboard(?int $branchId): array
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->when($branchId, fn ($q) => $q->where('invoices.branch_id', $branchId))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->selectRaw('products.name as name, COALESCE(SUM(invoice_items.quantity), 0) as qty, COALESCE(SUM((invoice_items.unit_price * invoice_items.quantity) - invoice_items.discount_amount), 0) as net')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'qty' => round((float) $row->qty, 2),
                'net' => round((float) $row->net, 2),
            ])
            ->all();
    }

    /**
     * آخر 6 فواتير بيع (لجدول "أحدث العمليات" في الشاشة الرئيسية).
     */
    private function recentSales(?int $branchId): array
    {
        return Invoice::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('customer:id,name')
            ->latest('id')
            ->limit(6)
            ->get(['id', 'invoice_number', 'customer_id', 'subtotal', 'tax_amount', 'discount_amount', 'payment_method', 'issue_date', 'created_at'])
            ->map(fn (Invoice $invoice) => [
                'number' => $invoice->invoice_number ?: ('#' . $invoice->id),
                'customer' => $invoice->customer?->name,
                'total' => round((float) ($invoice->subtotal + $invoice->tax_amount - $invoice->discount_amount), 2),
                'payment_method' => $invoice->payment_method,
                'time' => optional($invoice->created_at)->diffForHumans(),
            ])
            ->all();
    }

    /**
     * آخر 6 عمليات شراء (لجدول "آخر عمليات الشراء" في الشاشة الرئيسية).
     */
    private function recentPurchases(?int $branchId): array
    {
        return Purchase::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('supplier:id,name')
            ->latest('id')
            ->limit(6)
            ->get(['id', 'purchase_number', 'supplier_id', 'grand_total', 'issue_date', 'created_at'])
            ->map(fn (Purchase $purchase) => [
                'number' => $purchase->purchase_number ?: ('#' . $purchase->id),
                'supplier' => $purchase->supplier?->name,
                'total' => round((float) $purchase->grand_total, 2),
                'time' => optional($purchase->created_at)->diffForHumans(),
            ])
            ->all();
    }
}
