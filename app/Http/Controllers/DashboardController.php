<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\TransportInvoice;
use App\Models\TransportInvoiceItem;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Support\SaudiRegions;
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
    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $branchId = $request->integer('branch_id') ?: null;

        return view('dashboard', ['branches' => $branches, 'branchId' => $branchId] + $this->transportOverview($branchId));
    }

    /**
     * كل أرقام الشاشة الرئيسية الجديدة (نشاط النقل): الإيراد والربح مقارنة
     * بنفس الفترة من الشهر اللي فات، الأحمال، حالة الأسطول، الرسوم،
     * أكثر العملاء/الوجهات/الشاحنات، التنبيهات، وآخر الحركات.
     * كل حاجة استعلامات تجميعية (SUM/COUNT/GROUP BY).
     */
    private function transportOverview(?int $branchId): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        // نفس عدد الأيام من الشهر اللي فات (مقارنة عادلة)
        $prevStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $prevEnd = now()->subMonthNoOverflow()->startOfMonth()->addDays(now()->day - 1)->toDateString();

        $inv = fn () => TransportInvoice::query()->where('is_draft', false)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $sumInv = fn ($from, $to) => $inv()->whereDate('issue_date', '>=', $from)->whereDate('issue_date', '<=', $to)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(subtotal),0) as net, COALESCE(SUM(total),0) as total')->first();

        $expenses = fn ($from, $to) => (float) DB::table('account_vouchers')
            ->join('account_voucher_lines', 'account_voucher_lines.account_voucher_id', '=', 'account_vouchers.id')
            ->whereNotNull('account_vouchers.truck_id')
            ->where('account_vouchers.type', AccountVoucher::TYPE_PAYMENT)
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->whereDate('account_vouchers.voucher_date', '>=', $from)
            ->whereDate('account_vouchers.voucher_date', '<=', $to)
            ->sum('account_voucher_lines.amount');

        $loadsCount = fn ($from, $to) => TruckLoad::where('status', '!=', 'cancelled')
            ->whereDate('loaded_at', '>=', $from)->whereDate('loaded_at', '<=', $to)->count();

        $m = $sumInv($monthStart, $today);
        $p = $sumInv($prevStart, $prevEnd);
        $mExp = $expenses($monthStart, $today);
        $pExp = $expenses($prevStart, $prevEnd);
        $mLoads = $loadsCount($monthStart, $today);
        $pLoads = $loadsCount($prevStart, $prevEnd);
        $change = fn ($now, $before) => $before > 0 ? round(($now - $before) * 100 / $before) : null;

        $todayInv = $sumInv($today, $today);

        // غير مفوتر
        $unbilled = TruckLoad::unbilled()->with('waybill:id,truck_load_id,freight_amount')
            ->where('status', 'unloaded')->get(['id', 'price', 'customer_id']);

        // الرصيد النقدي (خزينة + بنوك) ومستحقات العملاء
        $cash = (float) FinancialAccount::query()->where('is_parent', false)
            ->whereIn('parent_account_number', [self::BANK_PARENT_ACCOUNT_NUMBER, self::CASH_PARENT_ACCOUNT_NUMBER])
            ->when($branchId, fn ($q) => $q->where(fn ($w) => $w->where('branchs_id', $branchId)->orWhereNull('branchs_id')))
            ->selectRaw('COALESCE(SUM(debtor_current - creditor_current), 0) as b')->value('b');
        $receivables = (float) Customer::where('balance', '>', 0)->sum('balance');

        // حالة الأسطول
        $trucks = Truck::with(['activeLoad.truck:id,plate_number', 'activeLoad.customer:id,name', 'activeLoad.driver:id,name,phone'])
            ->where('status', '!=', 'inactive')->get(['id', 'plate_number', 'name', 'status', 'current_region', 'driver_id']);
        $loadedTrucks = $trucks->filter(fn ($t) => $t->activeLoad);
        $overdue = $loadedTrucks->filter(fn ($t) => $t->activeLoad->isOverdue())
            ->sortBy(fn ($t) => $t->activeLoad->expected_unload_at)->values();
        $fleet = [
            'total' => $trucks->count(),
            'loaded' => $loadedTrucks->count() - $overdue->count(),
            'overdue' => $overdue->count(),
            'empty' => $trucks->filter(fn ($t) => !$t->activeLoad && $t->status === 'active')->count(),
            'maintenance' => $trucks->filter(fn ($t) => !$t->activeLoad && $t->status === 'maintenance')->count(),
        ];
        $fleet['utilization'] = $fleet['total'] ? round(($fleet['loaded'] + $fleet['overdue']) * 100 / $fleet['total']) : 0;
        $emptyByRegion = $trucks->filter(fn ($t) => !$t->activeLoad && $t->status === 'active')
            ->groupBy(fn ($t) => $t->current_region ?: '_none')
            ->map(fn ($g, $r) => ['label' => $r === '_none' ? __('transport.location_unknown') : SaudiRegions::name($r), 'count' => $g->count(), 'key' => $r])
            ->sortByDesc('count')->values();
        $upcoming = $loadedTrucks->filter(fn ($t) => !$t->activeLoad->isOverdue())
            ->sortBy(fn ($t) => $t->activeLoad->expected_unload_at)->take(5)->values();

        // رسم: الإيراد مقابل المصروفات آخر 6 شهور
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonthsNoOverflow($i)->startOfMonth());
        $revByMonth = $inv()->whereDate('issue_date', '>=', $months->first()->toDateString())
            ->selectRaw("DATE_FORMAT(issue_date, '%Y-%m') as m, SUM(subtotal) as v")->groupBy('m')->pluck('v', 'm');
        $expByMonth = DB::table('account_vouchers')
            ->join('account_voucher_lines', 'account_voucher_lines.account_voucher_id', '=', 'account_vouchers.id')
            ->whereNotNull('account_vouchers.truck_id')->where('account_vouchers.type', AccountVoucher::TYPE_PAYMENT)
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->whereDate('account_vouchers.voucher_date', '>=', $months->first()->toDateString())
            ->selectRaw("DATE_FORMAT(account_vouchers.voucher_date, '%Y-%m') as m, SUM(account_voucher_lines.amount) as v")
            ->groupBy('m')->pluck('v', 'm');
        $finance = $months->map(fn ($d) => [
            'label' => $d->translatedFormat('M Y'),
            'revenue' => round((float) ($revByMonth[$d->format('Y-m')] ?? 0), 2),
            'expenses' => round((float) ($expByMonth[$d->format('Y-m')] ?? 0), 2),
        ])->map(fn ($r) => $r + ['net' => round($r['revenue'] - $r['expenses'], 2)])->values();

        // رسم: الأحمال يوم بيوم آخر 14 يوم
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i));
        $loadsByDay = TruckLoad::where('status', '!=', 'cancelled')
            ->whereDate('loaded_at', '>=', $days->first()->toDateString())
            ->selectRaw('DATE(loaded_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
        $loadsTrend = $days->map(fn ($d) => ['label' => $d->format('m/d'), 'count' => (int) ($loadsByDay[$d->toDateString()] ?? 0)])->values();

        // الأكثر (الشهر ده)
        $monthLoads = TruckLoad::with(['truck:id,plate_number', 'customer:id,name'])->where('status', '!=', 'cancelled')
            ->whereDate('loaded_at', '>=', $monthStart)->get(['id', 'truck_id', 'customer_id', 'to_region', 'weight']);
        $topCustomers = $inv()->whereDate('issue_date', '>=', $monthStart)->with('customer:id,name')
            ->selectRaw('customer_id, COUNT(*) as cnt, SUM(subtotal) as net')->groupBy('customer_id')
            ->orderByDesc('net')->limit(5)->get()
            ->map(fn ($r) => ['label' => $r->customer?->name ?? '-', 'value' => (float) $r->net, 'sub' => $r->cnt]);
        $topDestinations = $monthLoads->groupBy('to_region')
            ->map(fn ($g, $r) => ['label' => SaudiRegions::name($r), 'value' => $g->count()])
            ->sortByDesc('value')->take(5)->values();
        $topTrucks = $monthLoads->groupBy('truck_id')
            ->map(fn ($g) => ['label' => $g->first()->truck?->plate_number ?? '-', 'value' => $g->count(), 'sub' => (float) $g->sum('weight'), 'id' => $g->first()->truck_id])
            ->sortByDesc('value')->take(5)->values();

        // تنبيهات
        $docsLimit = now()->addDays(Truck::EXPIRY_ALERT_DAYS)->toDateString();
        $docsAlert = Truck::where('status', '!=', 'inactive')->documentsAlert()->count();
        $docsExpired = Truck::where('status', '!=', 'inactive')->documentsAlert(null, 'expired')->count();
        $zatcaFailed = TransportInvoice::where('is_draft', false)->where('is_sent_to_zatca', false)->where('zatca_status', 'FAIL')->count();
        $zatcaPending = TransportInvoice::where('is_draft', false)->where('is_sent_to_zatca', false)->count();
        $noPrice = $unbilled->filter(fn ($l) => $l->billing_price === null)->count();

        // آخر الحركات
        $activity = TruckLoad::with(['truck:id,plate_number', 'customer:id,name'])
            ->where('status', '!=', 'cancelled')->latest('id')->limit(6)->get()
            ->map(fn ($l) => [
                'type' => $l->status === 'unloaded' ? 'unloaded' : 'loaded',
                'title' => ($l->truck?->plate_number ?? '-') . ' · ' . $l->from_label . ' ← ' . $l->to_label,
                'sub' => trim(($l->customer?->name ?? '') . ' ' . ($l->load_type ? '· ' . $l->load_type : ''), ' ·'),
                'at' => $l->status === 'unloaded' ? ($l->unload_recorded_at ?? $l->unloaded_at) : $l->created_at,
                'url' => route('transport.loads.board'),
            ])
            ->concat($inv()->with('customer:id,name')->latest('id')->limit(6)->get(['id', 'invoice_number', 'customer_id', 'total', 'created_at'])
                ->map(fn ($i) => [
                    'type' => 'invoice',
                    'title' => $i->invoice_number . ' · ' . number_format((float) $i->total, 2),
                    'sub' => $i->customer?->name,
                    'at' => $i->created_at,
                    'url' => route('transport.invoices.show', $i->id),
                ]))
            ->sortByDesc(fn ($a) => $a['at']?->getTimestamp() ?? 0)->take(8)->values();

        return [
            'kpi' => [
                'revenue' => (float) $m->net, 'revenue_change' => $change((float) $m->net, (float) $p->net),
                'invoices' => (int) $m->cnt, 'total_incl' => (float) $m->total,
                'profit' => (float) $m->net - $mExp, 'profit_change' => $change((float) $m->net - $mExp, (float) $p->net - $pExp),
                'expenses' => $mExp,
                'loads' => $mLoads, 'loads_change' => $change($mLoads, $pLoads),
                'today_revenue' => (float) $todayInv->total, 'today_invoices' => (int) $todayInv->cnt,
                'unbilled_count' => $unbilled->count(), 'unbilled_value' => (float) $unbilled->sum(fn ($l) => $l->billing_price ?? 0),
                'cash' => $cash, 'receivables' => $receivables,
            ],
            'fleet' => $fleet,
            'overdue' => $overdue->take(5),
            'upcoming' => $upcoming,
            'emptyByRegion' => $emptyByRegion,
            'finance' => $finance,
            'loadsTrend' => $loadsTrend,
            'topCustomers' => $topCustomers,
            'topDestinations' => $topDestinations,
            'topTrucks' => $topTrucks,
            'alerts' => compact('docsAlert', 'docsExpired', 'zatcaFailed', 'zatcaPending', 'noPrice'),
            'activity' => $activity,
        ];
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

        // "المبيعات" هنا = فواتير النقليات المعتمدة (قسم المبيعات اتشال)
        $netTotalExpr = 'transport_invoices.total';

        $todaySales = TransportInvoice::query()->where('is_draft', false)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', $today)
            ->selectRaw("COUNT(*) as cnt, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->first();

        $monthSales = TransportInvoice::query()->where('is_draft', false)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', '>=', $monthStart)
            ->selectRaw("COUNT(*) as cnt, COALESCE(SUM({$netTotalExpr}), 0) as net")
            ->first();

        // الربح = إيراد النقليات قبل الضريبة - مصروفات/صيانة الشاحنات (سندات صرف مربوطة بشاحنة)
        $todaySalesProfit = $this->transportProfit($today, $today, $branchId);
        $monthSalesProfit = $this->transportProfit($monthStart, $today, $branchId);

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

        $todayStockTransfersCount = 0; // (تحويلات المخزون اتشالت)

        $todaySalesReturnsCount = 0; // (مرتجع المبيعات اتشال)

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

        // (سندات التسليم اتشالت) - الكارت ده بقى عدد الشاحنات المحمّلة دلوقتي
        $loadedTrucksCount = \Illuminate\Support\Facades\Schema::hasTable('truck_loads')
            ? \App\Models\TruckLoad::where('status', 'loaded')->count()
            : 0;

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
            'loaded_trucks_count' => $loadedTrucksCount,
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

        $salesByDay = TransportInvoice::query()->where('is_draft', false)
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
        return TransportInvoice::query()
            ->join('users', 'users.id', '=', 'transport_invoices.created_by')
            ->where('transport_invoices.is_draft', false)
            ->when($branchId, fn ($q) => $q->where('transport_invoices.branch_id', $branchId))
            ->whereDate('transport_invoices.issue_date', $today)
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
        return TransportInvoice::query()
            ->join('branches', 'branches.id', '=', 'transport_invoices.branch_id')
            ->where('transport_invoices.is_draft', false)
            ->whereDate('transport_invoices.issue_date', $today)
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
        // أكتر 5 شاحنات إيرادًا اليوم للفرع المختار
        return TransportInvoiceItem::query()
            ->join('transport_invoices', 'transport_invoices.id', '=', 'transport_invoice_items.transport_invoice_id')
            ->join('trucks', 'trucks.id', '=', 'transport_invoice_items.truck_id')
            ->where('transport_invoices.is_draft', false)
            ->where('transport_invoices.branch_id', $branchId)
            ->whereDate('transport_invoices.issue_date', $today)
            ->groupBy('trucks.id', 'trucks.plate_number')
            ->orderByDesc('net')
            ->limit(5)
            ->selectRaw('trucks.plate_number as name, COALESCE(SUM(transport_invoice_items.line_total), 0) as net')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'net' => round((float) $row->net, 2)])
            ->all();
    }

    /**
     * أكثر 5 منتجات مبيعاً بالكمية والقيمة
     */
    private function topSellingProductsDashboard(?int $branchId): array
    {
        // أكتر 5 شاحنات نقلات (العدد والإيراد)
        return TransportInvoiceItem::query()
            ->join('transport_invoices', 'transport_invoices.id', '=', 'transport_invoice_items.transport_invoice_id')
            ->join('trucks', 'trucks.id', '=', 'transport_invoice_items.truck_id')
            ->where('transport_invoices.is_draft', false)
            ->when($branchId, fn ($q) => $q->where('transport_invoices.branch_id', $branchId))
            ->groupBy('trucks.id', 'trucks.plate_number')
            ->orderByDesc('qty')
            ->limit(5)
            ->selectRaw('trucks.plate_number as name, COUNT(*) as qty, COALESCE(SUM(transport_invoice_items.line_total), 0) as net')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'qty' => (int) $row->qty,
                'net' => round((float) $row->net, 2),
            ])
            ->all();
    }

    /**
     * آخر 6 فواتير بيع (لجدول "أحدث العمليات" في الشاشة الرئيسية).
     */
    private function recentSales(?int $branchId): array
    {
        return TransportInvoice::query()
            ->where('is_draft', false)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('customer:id,name')
            ->latest('id')
            ->limit(6)
            ->get(['id', 'invoice_number', 'customer_id', 'total', 'payment_method', 'issue_date', 'created_at'])
            ->map(fn (TransportInvoice $invoice) => [
                'number' => $invoice->invoice_number ?: ('#' . $invoice->id),
                'customer' => $invoice->customer?->name,
                'total' => round((float) $invoice->total, 2),
                'payment_method' => $invoice->payment_method,
                'time' => optional($invoice->created_at)->diffForHumans(),
            ])
            ->all();
    }

    /** إيراد النقليات قبل الضريبة ناقص مصروفات الشاحنات في الفترة */
    private function transportProfit(string $from, string $to, ?int $branchId): float
    {
        $revenue = (float) TransportInvoice::query()
            ->where('is_draft', false)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to)
            ->sum('subtotal');

        $expenses = (float) DB::table('account_vouchers')
            ->join('account_voucher_lines', 'account_voucher_lines.account_voucher_id', '=', 'account_vouchers.id')
            ->whereNotNull('account_vouchers.truck_id')
            ->where('account_vouchers.type', AccountVoucher::TYPE_PAYMENT)
            ->when($branchId, fn ($q) => $q->where('account_vouchers.branch_id', $branchId))
            ->whereDate('account_vouchers.voucher_date', '>=', $from)
            ->whereDate('account_vouchers.voucher_date', '<=', $to)
            ->sum('account_voucher_lines.amount');

        return round($revenue - $expenses, 2);
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
