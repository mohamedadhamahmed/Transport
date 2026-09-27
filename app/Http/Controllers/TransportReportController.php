<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\TransportInvoice;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\FinancialAccount;
use App\Models\TransportInvoiceItem;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Services\Hr\HrAccountService;
use App\Support\SaudiRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportReportController extends Controller
{
    /**
     * تقرير الشاحنات: أكثر الشاحنات صيانة ومصروفات، أكثر الوجهات للأحمال،
     * وأكبر الشاحنات أحمالًا - مع إيراد كل شاحنة وصافيها.
     */
    public function fleet(Request $request)
    {
        $this->authorize('transport_reports.fleet');

        $from = $request->input('date_from', now()->startOfYear()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        $trucks = Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name', 'type', 'ownership']);

        // المصروفات (سندات الصيانة = سندات صرف مربوطة بشاحنة)
        $expenseRows = DB::table('account_vouchers as v')
            ->join('account_voucher_lines as l', 'l.account_voucher_id', '=', 'v.id')
            ->whereNotNull('v.truck_id')
            ->where('v.type', AccountVoucher::TYPE_PAYMENT)
            ->whereDate('v.voucher_date', '>=', $from)
            ->whereDate('v.voucher_date', '<=', $to)
            ->groupBy('v.truck_id', 'v.expense_category')
            ->selectRaw('v.truck_id, v.expense_category, COUNT(DISTINCT v.id) as vouchers, SUM(l.amount) as total')
            ->get();

        $expByTruck = $expenseRows->groupBy('truck_id')->map(fn ($g) => [
            'total' => (float) $g->sum('total'),
            'vouchers' => (int) $g->sum('vouchers'),
            'maintenance' => (float) $g->whereIn('expense_category', ['maintenance', 'spare_parts', 'tires', 'oil'])->sum('total'),
        ]);
        $expByCategory = $expenseRows->groupBy('expense_category')
            ->map(fn ($g, $cat) => ['label' => AccountVoucher::EXPENSE_CATEGORIES[$cat] ?? ($cat ?: 'أخرى'), 'total' => (float) $g->sum('total')])
            ->sortByDesc('total')->values();

        // الأحمال (حركة الشاحنات) - من غير الملغية
        $loads = TruckLoad::where('status', '!=', 'cancelled')
            ->whereDate('loaded_at', '>=', $from)
            ->whereDate('loaded_at', '<=', $to)
            ->get(['id', 'truck_id', 'from_region', 'to_region', 'weight']);
        $loadsByTruck = $loads->groupBy('truck_id')->map(fn ($g) => ['count' => $g->count(), 'weight' => (float) $g->sum('weight')]);
        $destinations = $loads->groupBy('to_region')
            ->map(fn ($g, $r) => ['label' => SaudiRegions::name($r), 'count' => $g->count()])
            ->sortByDesc('count')->values();
        $routes = $loads->groupBy(fn ($l) => $l->from_region . '|' . $l->to_region)
            ->map(fn ($g) => ['label' => SaudiRegions::name($g->first()->from_region) . ' ← ' . SaudiRegions::name($g->first()->to_region), 'count' => $g->count()])
            ->sortByDesc('count')->take(10)->values();

        // الإيراد (نقلات فواتير النقليات)
        $revenue = TransportInvoiceItem::query()
            ->whereHas('invoice', fn ($q) => $q->where('is_draft', false))
            ->whereDate('trip_date', '>=', $from)
            ->whereDate('trip_date', '<=', $to)
            ->groupBy('truck_id')
            ->selectRaw('truck_id, COUNT(*) as trips, SUM(line_total) as total')
            ->get()->keyBy('truck_id');

        $rows = $trucks->map(function ($t) use ($expByTruck, $loadsByTruck, $revenue) {
            $exp = $expByTruck[$t->id] ?? ['total' => 0, 'vouchers' => 0, 'maintenance' => 0];
            $ld = $loadsByTruck[$t->id] ?? ['count' => 0, 'weight' => 0];
            $rev = (float) ($revenue[$t->id]->total ?? 0);

            return [
                'truck' => $t,
                'expenses' => $exp['total'],
                'maintenance' => $exp['maintenance'],
                'vouchers' => $exp['vouchers'],
                'loads' => $ld['count'],
                'weight' => $ld['weight'],
                'trips' => (int) ($revenue[$t->id]->trips ?? 0),
                'revenue' => $rev,
                'net' => $rev - $exp['total'],
            ];
        });

        return view('transport.reports.fleet', [
            'from' => $from,
            'to' => $to,
            'rows' => $rows->sortByDesc('expenses')->values(),
            'topExpenses' => $rows->where('expenses', '>', 0)->sortByDesc('expenses')->take(10)->values(),
            'topLoads' => $rows->where('loads', '>', 0)->sortByDesc('loads')->take(10)->values(),
            'destinations' => $destinations,
            'routes' => $routes,
            'expByCategory' => $expByCategory,
            'totals' => [
                'expenses' => $rows->sum('expenses'),
                'revenue' => $rows->sum('revenue'),
                'loads' => $rows->sum('loads'),
                'vouchers' => $rows->sum('vouchers'),
            ],
        ]);
    }

    /**
     * مخطط عُهد الموظفين: رصيد حساب ذمة/عهدة كل موظف تحت "ذمم الموظفين"
     * في شجرة الحسابات + العُهد المفتوحة المسجلة من شاشة السلف والعُهد.
     */
    public function custody(Request $request)
    {
        $this->authorize('employee_custody.view');

        $parentId = HrAccountService::DUES_PARENT_ACCOUNT_ID;
        $parent = FinancialAccount::find($parentId);

        $accounts = FinancialAccount::where('parent_account_number', $parentId)
            ->orderByDesc('current_balance')
            ->get(['id', 'name', 'current_balance', 'orginal_id', 'orginal_type', 'debtor_current', 'creditor_current']);

        $openCustody = EmployeeLoan::where('type', EmployeeLoan::TYPE_CUSTODY)
            ->where('status', EmployeeLoan::STATUS_ACTIVE)
            ->selectRaw('employee_id, COUNT(*) as docs, SUM(amount - COALESCE(paid_amount,0)) as remaining, SUM(amount) as total')
            ->groupBy('employee_id')
            ->get()->keyBy('employee_id');

        $employees = Employee::whereIn('id', $accounts->pluck('orginal_id')->filter()->merge($openCustody->keys()))
            ->get(['id', 'name', 'job_title'])->keyBy('id');

        $rows = $accounts->map(function ($a) use ($openCustody, $employees) {
            $emp = $a->orginal_id ? ($employees[$a->orginal_id] ?? null) : null;
            $c = $emp ? ($openCustody[$emp->id] ?? null) : null;

            return [
                'account' => $a,
                'employee' => $emp,
                'name' => $emp?->name ?? $a->name,
                'job' => $emp?->job_title,
                'balance' => round((float) $a->current_balance, 2),
                'open_custody' => round((float) ($c->remaining ?? 0), 2),
                'open_docs' => (int) ($c->docs ?? 0),
            ];
        });

        if (!$request->boolean('all')) {
            $rows = $rows->filter(fn ($r) => abs($r['balance']) > 0.009 || $r['open_custody'] > 0.009);
        }
        $rows = $rows->sortByDesc('balance')->values();

        return view('transport.reports.custody', [
            'rows' => $rows,
            'parent' => $parent,
            'total' => round($rows->sum('balance'), 2),
            'totalOpen' => round($rows->sum('open_custody'), 2),
        ]);
    }

    /**
     * الأحمال غير المفوترة: أحمال (حركة الشاحنات) لسه مدخلتش في أي فاتورة
     * نقل. الافتراضي "تم التفريغ" = جاهزة للفوترة. من هنا بتفتح فاتورة
     * جديدة للعميل بالأحمال بتاعته.
     */
    public function unbilled(Request $request)
    {
        $this->authorize('transport_invoices.view');

        $status = $request->input('status', 'unloaded');

        $query = TruckLoad::unbilled()
            ->with(['truck:id,plate_number,name', 'customer:id,name', 'waybill:id,truck_load_id,waybill_number,freight_amount'])
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('loaded_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('loaded_at', '<=', $request->input('date_to')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->input('customer_id')))
            ->when(in_array($status, ['unloaded', 'loaded'], true), fn ($q) => $q->where('status', $status));

        $loads = $query->orderBy('customer_id')->orderBy('loaded_at')->get();

        $summary = [
            'count' => $loads->count(),
            'value' => (float) $loads->sum(fn ($l) => $l->billing_price ?? 0),
            'customers' => $loads->pluck('customer_id')->filter()->unique()->count(),
            'no_price' => $loads->filter(fn ($l) => $l->billing_price === null)->count(),
        ];

        if ($request->input('export') === 'excel') {
            return $this->unbilledCsv($loads);
        }

        return view('transport.reports.unbilled', [
            'groups' => $loads->groupBy(fn ($l) => (int) $l->customer_id),
            'summary' => $summary,
            'status' => $status,
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    private function unbilledCsv($loads)
    {
        $filename = 'unbilled-loads-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($loads) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM عشان العربي يظهر صح في Excel
            fputcsv($out, [
                __('transport.customer'), __('transport.load_date'), __('transport.plate_number'),
                __('transport.from'), __('transport.to'), __('transport.load_type'),
                __('transport.waybill_ref'), __('transport.status'), __('transport.price'),
            ]);
            foreach ($loads as $l) {
                fputcsv($out, [
                    $l->customer?->name ?? __('transport.no_customer'),
                    $l->loaded_at?->format('Y-m-d'),
                    $l->truck?->plate_number,
                    $l->from_label,
                    $l->to_label,
                    $l->load_type,
                    $l->waybill_number ?: $l->waybill?->waybill_number,
                    __('transport.load_status_' . $l->status),
                    $l->billing_price !== null ? number_format($l->billing_price, 2, '.', '') : '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ==================================================================
    // مركز تقارير النقليات + تقارير الشحنات/الفواتير/العملاء/المسارات/السائقين/الشاحنة
    // ==================================================================

    /** صلاحيات تقارير النقليات - أي واحدة منها تفتح مركز التقارير */
    public const HUB_PERMISSIONS = [
        'truck_loads.report', 'transport_reports.fleet', 'transport_invoices.view', 'employee_custody.view',
    ];

    /** مركز تقارير النقليات والشاحنات */
    public function index()
    {
        $user = auth()->user();
        abort_unless($user && collect(self::HUB_PERMISSIONS)->contains(fn ($p) => $user->hasPermission($p)), 403);

        return view('transport.reports.index');
    }

    /** الفترة من الطلب (الافتراضي: من أول السنة لحد النهارده) */
    private function period(Request $request, string $defaultFrom = 'year'): array
    {
        $from = $request->input('date_from') ?: ($defaultFrom === 'month'
            ? now()->startOfMonth()->toDateString()
            : now()->startOfYear()->toDateString());
        $to = $request->input('date_to') ?: now()->toDateString();

        return [$from, $to];
    }

    /** CSV بيفتح في Excel بالعربي */
    private function csv(string $name, array $head, iterable $rows)
    {
        return response()->streamDownload(function () use ($head, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $head);
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, $name . '-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * تقرير فواتير النقل (المبيعات): الإجماليات، شهر بشهر، حسب العميل،
     * وقايمة الفواتير.
     */
    public function sales(Request $request)
    {
        $this->authorize('transport_invoices.view');
        [$from, $to] = $this->period($request, 'month');

        $invoices = TransportInvoice::with('customer:id,name,tax_number')
            ->where('is_draft', false)
            ->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to)
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->input('customer_id')))
            ->orderByDesc('issue_date')->orderByDesc('id')
            ->get(['id', 'invoice_number', 'customer_id', 'issue_date', 'trips_count', 'subtotal', 'tax_amount', 'total',
                'credit_amount', 'payment_method', 'is_sent_to_zatca', 'zatca_status']);

        if ($request->input('export') === 'excel') {
            return $this->csv('transport-sales', [
                __('transport.invoice_number'), __('transport.date'), __('transport.customer'), __('transport.vat_number'),
                __('transport.trips_count'), __('transport.before_tax'), __('transport.tax_amount'), __('transport.grand_total'),
            ], $invoices->map(fn ($i) => [
                $i->invoice_number, $i->issue_date?->format('Y-m-d'), $i->customer?->name, $i->customer?->tax_number,
                $i->trips_count, $i->subtotal, $i->tax_amount, $i->total,
            ]));
        }

        $count = $invoices->count();
        $summary = [
            'count' => $count,
            'subtotal' => (float) $invoices->sum('subtotal'),
            'tax' => (float) $invoices->sum('tax_amount'),
            'total' => (float) $invoices->sum('total'),
            'credit' => (float) $invoices->sum('credit_amount'),
            'trips' => (int) $invoices->sum('trips_count'),
            'avg' => $count ? (float) $invoices->avg('total') : 0,
            'zatca_pending' => $invoices->where('is_sent_to_zatca', false)->count(),
        ];

        $monthly = $invoices->groupBy(fn ($i) => $i->issue_date->format('Y-m'))
            ->map(fn ($g, $m) => ['month' => $m, 'count' => $g->count(), 'subtotal' => (float) $g->sum('subtotal'),
                'tax' => (float) $g->sum('tax_amount'), 'total' => (float) $g->sum('total')])
            ->sortKeys()->values();

        $byCustomer = $invoices->groupBy('customer_id')
            ->map(fn ($g) => ['customer' => $g->first()->customer, 'count' => $g->count(), 'trips' => (int) $g->sum('trips_count'),
                'subtotal' => (float) $g->sum('subtotal'), 'tax' => (float) $g->sum('tax_amount'), 'total' => (float) $g->sum('total')])
            ->sortByDesc('total')->values();

        return view('transport.reports.sales', [
            'from' => $from, 'to' => $to,
            'summary' => $summary, 'monthly' => $monthly, 'byCustomer' => $byCustomer,
            'invoices' => $invoices->take(300),
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /** أكثر العملاء شحنًا: أحمال + وزن + إيراد مفوتر + أحمال لسه متفوترتش */
    public function customers(Request $request)
    {
        $this->authorize('transport_invoices.view');
        [$from, $to] = $this->period($request);

        $loads = TruckLoad::where('status', '!=', 'cancelled')->whereNotNull('customer_id')
            ->whereDate('loaded_at', '>=', $from)->whereDate('loaded_at', '<=', $to)
            ->get(['id', 'customer_id', 'weight', 'status', 'loaded_at', 'to_region']);

        $revenue = TransportInvoice::where('is_draft', false)
            ->whereDate('issue_date', '>=', $from)->whereDate('issue_date', '<=', $to)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, COUNT(*) as invoices, SUM(subtotal) as subtotal, SUM(total) as total')
            ->get()->keyBy('customer_id');

        $unbilled = TruckLoad::unbilled()->with('waybill:id,truck_load_id,freight_amount')
            ->whereNotNull('customer_id')->get(['id', 'customer_id', 'price', 'status'])
            ->groupBy('customer_id')
            ->map(fn ($g) => ['count' => $g->count(), 'value' => (float) $g->sum(fn ($l) => $l->billing_price ?? 0)]);

        $ids = $loads->pluck('customer_id')->merge($revenue->keys())->merge($unbilled->keys())->unique()->filter();
        $names = Customer::whereIn('id', $ids)->pluck('name', 'id');

        $rows = $ids->map(function ($id) use ($loads, $revenue, $unbilled, $names) {
            $l = $loads->where('customer_id', $id);
            $topDest = $l->groupBy('to_region')->sortByDesc(fn ($g) => $g->count())->keys()->first();

            return [
                'id' => $id,
                'name' => $names[$id] ?? ('#' . $id),
                'loads' => $l->count(),
                'weight' => (float) $l->sum('weight'),
                'last' => $l->max('loaded_at'),
                'top_dest' => $topDest ? SaudiRegions::name($topDest) : '-',
                'invoices' => (int) ($revenue[$id]->invoices ?? 0),
                'subtotal' => (float) ($revenue[$id]->subtotal ?? 0),
                'total' => (float) ($revenue[$id]->total ?? 0),
                'unbilled_count' => $unbilled[$id]['count'] ?? 0,
                'unbilled_value' => $unbilled[$id]['value'] ?? 0,
            ];
        });

        $sort = in_array($request->input('sort'), ['loads', 'total', 'weight', 'unbilled_value'], true) ? $request->input('sort') : 'loads';
        $rows = $rows->sortByDesc($sort)->values();

        if ($request->input('export') === 'excel') {
            return $this->csv('top-customers', [
                __('transport.customer'), __('transport.loads_count'), __('transport.total_weight'), __('transport.top_destination'),
                __('transport.invoices_count'), __('transport.before_tax'), __('transport.grand_total'), __('transport.unbilled_count'), __('transport.unbilled_value'),
            ], $rows->map(fn ($r) => [$r['name'], $r['loads'], $r['weight'], $r['top_dest'], $r['invoices'], $r['subtotal'], $r['total'], $r['unbilled_count'], $r['unbilled_value']]));
        }

        return view('transport.reports.customers', compact('from', 'to', 'rows', 'sort'));
    }

    /** أكثر مناطق التحميل والتنزيل والمسارات (عدد + وزن + متوسط المدة + التأخير + الإيراد) */
    public function routes(Request $request)
    {
        $this->authorize('truck_loads.report');
        [$from, $to] = $this->period($request);

        $loads = TruckLoad::where('status', '!=', 'cancelled')
            ->whereDate('loaded_at', '>=', $from)->whereDate('loaded_at', '<=', $to)
            ->get(['id', 'from_region', 'to_region', 'from_city', 'to_city', 'weight', 'status', 'loaded_at', 'expected_unload_at', 'unloaded_at']);

        $revByLoad = TransportInvoiceItem::whereIn('truck_load_id', $loads->pluck('id'))
            ->whereHas('invoice', fn ($q) => $q->where('is_draft', false))
            ->pluck('line_total', 'truck_load_id');

        $stats = function ($g) use ($revByLoad) {
            $done = $g->where('status', 'unloaded')->filter(fn ($l) => $l->unloaded_at);
            $mins = $done->map(fn ($l) => $l->loaded_at->diffInMinutes($l->unloaded_at))->filter(fn ($m) => $m > 0);

            return [
                'count' => $g->count(),
                'weight' => (float) $g->sum('weight'),
                'avg_hours' => $mins->count() ? round($mins->avg() / 60, 1) : null,
                'late' => $done->filter->wasLate()->count() + $g->filter->isOverdue()->count(),
                'revenue' => (float) $g->sum(fn ($l) => $revByLoad[$l->id] ?? 0),
            ];
        };

        $origins = $loads->groupBy('from_region')->map(fn ($g, $r) => ['label' => SaudiRegions::name($r)] + $stats($g))->sortByDesc('count')->values();
        $destinations = $loads->groupBy('to_region')->map(fn ($g, $r) => ['label' => SaudiRegions::name($r)] + $stats($g))->sortByDesc('count')->values();
        $routes = $loads->groupBy(fn ($l) => $l->from_region . '|' . $l->to_region)
            ->map(fn ($g) => ['label' => SaudiRegions::name($g->first()->from_region) . ' ← ' . SaudiRegions::name($g->first()->to_region)] + $stats($g))
            ->sortByDesc('count')->values();
        $cities = $loads->filter(fn ($l) => $l->to_city)->groupBy(fn ($l) => trim($l->to_city))
            ->map(fn ($g, $c) => ['label' => $c . ' (' . SaudiRegions::name($g->first()->to_region) . ')', 'count' => $g->count()])
            ->sortByDesc('count')->take(15)->values();

        return view('transport.reports.routes', [
            'from' => $from, 'to' => $to,
            'total' => $loads->count(),
            'origins' => $origins, 'destinations' => $destinations, 'routes' => $routes, 'cities' => $cities,
        ]);
    }

    /** أداء السائقين: عدد الأحمال، الالتزام بالمواعيد، متوسط مدة الرحلة */
    public function drivers(Request $request)
    {
        $this->authorize('truck_loads.report');
        [$from, $to] = $this->period($request);

        $loads = TruckLoad::where('status', '!=', 'cancelled')->whereNotNull('driver_id')
            ->whereDate('loaded_at', '>=', $from)->whereDate('loaded_at', '<=', $to)
            ->get(['id', 'driver_id', 'truck_id', 'weight', 'status', 'loaded_at', 'expected_unload_at', 'unloaded_at']);

        $drivers = Driver::whereIn('id', $loads->pluck('driver_id')->unique())->get(['id', 'name', 'phone'])->keyBy('id');

        $rows = $loads->groupBy('driver_id')->map(function ($g, $id) use ($drivers) {
            $done = $g->where('status', 'unloaded')->filter(fn ($l) => $l->unloaded_at);
            $late = $done->filter->wasLate()->count();
            $mins = $done->map(fn ($l) => $l->loaded_at->diffInMinutes($l->unloaded_at))->filter(fn ($m) => $m > 0);

            return [
                'driver' => $drivers[$id] ?? null,
                'loads' => $g->count(),
                'done' => $done->count(),
                'on_road' => $g->where('status', 'loaded')->count(),
                'overdue' => $g->filter->isOverdue()->count(),
                'late' => $late,
                'on_time_pct' => $done->count() ? round(($done->count() - $late) * 100 / $done->count()) : null,
                'avg_hours' => $mins->count() ? round($mins->avg() / 60, 1) : null,
                'weight' => (float) $g->sum('weight'),
                'trucks' => $g->pluck('truck_id')->unique()->count(),
            ];
        })->sortByDesc('loads')->values();

        return view('transport.reports.drivers', compact('from', 'to', 'rows'));
    }

    /** كشف حساب شاحنة: أحمالها + إيرادها + مصروفاتها وصيانتها + صافي الربح + نسبة التشغيل */
    public function truck(Request $request)
    {
        $this->authorize('transport_reports.fleet');
        [$from, $to] = $this->period($request);

        $trucks = Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name', 'type', 'ownership', 'status']);
        $truck = $request->filled('truck_id') ? Truck::with('driver:id,name,phone')->find($request->input('truck_id')) : null;

        $data = null;
        if ($truck) {
            $loads = TruckLoad::with(['customer:id,name', 'driver:id,name'])
                ->where('truck_id', $truck->id)->where('status', '!=', 'cancelled')
                ->whereDate('loaded_at', '>=', $from)->whereDate('loaded_at', '<=', $to)
                ->orderByDesc('loaded_at')->get();

            $items = TransportInvoiceItem::with('invoice:id,invoice_number,customer_id,issue_date,is_draft', 'invoice.customer:id,name')
                ->where('truck_id', $truck->id)
                ->whereHas('invoice', fn ($q) => $q->where('is_draft', false)
                    ->whereDate('issue_date', '>=', $from)->whereDate('issue_date', '<=', $to))
                ->get();

            $vouchers = AccountVoucher::with('lines')
                ->where('truck_id', $truck->id)->where('type', AccountVoucher::TYPE_PAYMENT)
                ->whereDate('voucher_date', '>=', $from)->whereDate('voucher_date', '<=', $to)
                ->orderByDesc('voucher_date')->get();

            // نسبة التشغيل = ساعات الشاحنة وهي محمّلة ÷ ساعات الفترة
            $periodStart = \Carbon\Carbon::parse($from)->startOfDay();
            $periodEnd = \Carbon\Carbon::parse($to)->endOfDay()->min(now());
            $periodHours = max(1, $periodStart->diffInHours($periodEnd));
            $loadedHours = $loads->sum(function ($l) use ($periodStart, $periodEnd) {
                $s = $l->loaded_at->copy()->max($periodStart);
                $e = ($l->unloaded_at ?? now())->copy()->min($periodEnd);

                return $e->gt($s) ? $s->diffInMinutes($e) / 60 : 0;
            });

            $revenue = (float) $items->sum('line_total');
            $expenses = (float) $vouchers->sum(fn ($v) => $v->total_amount);
            $byCategory = $vouchers->groupBy(fn ($v) => $v->expenseCategoryLabel() ?: __('transport.other'))
                ->map(fn ($g, $label) => ['label' => $label, 'count' => $g->count(), 'total' => (float) $g->sum(fn ($v) => $v->total_amount)])
                ->sortByDesc('total')->values();

            $data = [
                'loads' => $loads,
                'items' => $items,
                'vouchers' => $vouchers,
                'byCategory' => $byCategory,
                'summary' => [
                    'loads' => $loads->count(),
                    'weight' => (float) $loads->sum('weight'),
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                    'maintenance' => (float) $vouchers->whereIn('expense_category', ['maintenance', 'spare_parts', 'tires', 'oil'])->sum(fn ($v) => $v->total_amount),
                    'net' => $revenue - $expenses,
                    'utilization' => round(min(100, $loadedHours * 100 / $periodHours)),
                    'late' => $loads->filter->wasLate()->count(),
                ],
            ];
        }

        return view('transport.reports.truck', compact('from', 'to', 'trucks', 'truck', 'data'));
    }
}
