<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Customer;
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
}
