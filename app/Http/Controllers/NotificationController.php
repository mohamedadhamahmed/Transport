<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\JournalEntry;
use App\Models\TransportInvoice;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\EmployeeContract;

/*
|--------------------------------------------------------------------------
| NotificationController
|--------------------------------------------------------------------------
| مفيش نظام إشعارات مخزّن في الداتابيز في المشروع أصلاً (زي الموجود في
| dashboard.blade.php اللي بيجيب أرقام الكروت لايف عن طريق endpoint واحد
| بيترجع JSON). هنا نفس الفكرة بالظبط: endpoint واحد خفيف بيرجع عدّادين
| (فواتير زاتكا فشلت + منتجات ناقصة عن حد التنبيه)، كل عداد متفلتر على
| صلاحية المستخدم، ومعاه رابط "اذهب للمراجعة" لكل عداد. الجرس في الهيدر
| بيستدعي الراوت ده مرة واحدة لما الصفحة تفتح.
|
| بالإضافة كمان لقايمة "عمليات اليوم" - كل عمليات البيع/الشراء/القبض/
| الصرف اللي حصلت "النهارده" بس (مش آخر 5 من كل الأزمنة زي أول نسخة) -
| مدموجين مع بعض ومترتبين بالأحدث، كل نوع متفلتر على صلاحيته. رقم الجرس
| الأحمر بقى = عدّادات التنبيهات (زاتكا فشلت + مخزون ناقص) + إجمالي عدد
| عمليات اليوم، عشان يبقى مؤشر نشاط حقيقي مش بس "فيه مشكلة".
|
| القايمة دي بتتحمّل صفحة صفحة (PAGE_SIZE في المرة) عن طريق زرار "عرض
| المزيد" في الفرونت (AJAX) بدل ما تتحمل كلها مرة واحدة - راجع
| recentOperationsPage() تحت.
*/
class NotificationController extends Controller
{

    private const PAGE_SIZE = 5;

    // حد أقصى دفاعي لكل نوع عملية في اليوم الواحد - عشان يوم مزدحم جدًا
    // (مئات العمليات) متعملش استعلام وترتيب لكل حاجة من غير أي حد. القيمة
    // دي كبيرة كفاية إنها متأثرش على الاستخدام العادي.
    private const MAX_PER_TYPE = 200;
 public function index()
    {
        $days = 30; // نطاق التنبيه بالأيام - عدّله زي ما تحب

        $contracts = EmployeeContract::with('employee')
            ->expiringWithin($days)
            ->get();

        $today = now()->startOfDay();
        $alerts = collect();

        foreach ($contracts as $c) {
            foreach ([
                'end_date'           => __('notifications.contract_end'),
                'residency_expiry'   => __('notifications.residency'),
                'work_permit_expiry' => __('notifications.work_permit'),
            ] as $field => $label) {
                if ($c->$field && $c->$field->between($today, $today->copy()->addDays($days))) {
                    $alerts->push([
                        'employee'  => $c->employee,
                        'type'      => $label,
                        'date'      => $c->$field,
                        'days_left' => $today->diffInDays($c->$field, false),
                        'contract'  => $c,
                    ]);
                }
            }
        }

        $alerts = $alerts->sortBy('date')->values();

        return view('notifications.index', compact('alerts', 'days'));
    }
    public function summary(Request $request)
    {
        $user = auth()->user();
        $items = [];

        if ($user?->hasPermission('zatca.view')) {
            $zatcaFailedCount = TransportInvoice::where('is_draft', false)
                ->where('is_sent_to_zatca', false)
                ->where('zatca_status', 'FAIL')
                ->count();

            if ($zatcaFailedCount > 0) {
                $items[] = [
                    'type' => 'zatca_failed',
                    'count' => $zatcaFailedCount,
                    'message' => __('messages.zatca_failed_notification', ['count' => $zatcaFailedCount]),
                    'url' => route('transport.zatca.index', ['sent' => 0, 'status' => 'FAIL']),
                ];
            }
        }

        // وثائق الشاحنات: الاستمارة / التأمين / كرت التشغيل / الفحص الدوري
        if ($user?->hasPermission('trucks.view')) {
            $today = now()->toDateString();
            $limit = now()->addDays(Truck::EXPIRY_ALERT_DAYS)->toDateString();
            foreach (Truck::DOCUMENTS as $col => $key) {
                $row = Truck::query()
                    ->where('status', '!=', 'inactive')
                    // الوثائق الاختيارية للشاحنات الخارجية مالهاش تنبيه
                    ->when(in_array($col, Truck::OPTIONAL_FOR_EXTERNAL, true), fn ($q) => $q->where(fn ($o) => $o->where('ownership', '!=', 'external')->orWhereNull('ownership')))
                    ->whereNotNull($col)
                    ->whereDate($col, '<=', $limit)
                    ->selectRaw('COUNT(*) as total, SUM(CASE WHEN ' . $col . ' < ? THEN 1 ELSE 0 END) as expired', [$today])
                    ->first();
                $total = (int) ($row->total ?? 0);
                if ($total > 0) {
                    $expired = (int) $row->expired;
                    $items[] = [
                        'type' => 'truck_doc_' . $col,
                        'level' => $expired > 0 ? 'danger' : 'warning',
                        'count' => $total,
                        'message' => __('transport.notif_truck_doc', [
                            'doc' => __('transport.' . $key),
                            'expired' => $expired,
                            'soon' => $total - $expired,
                            'days' => Truck::EXPIRY_ALERT_DAYS,
                        ]),
                        'url' => route('transport.trucks.index', ['docs' => $col]),
                    ];
                }
            }
        }

        // شاحنات متأخرة عن التنزيل
        if ($user?->hasPermission('truck_loads.view')) {
            $overdue = TruckLoad::where('status', 'loaded')->where('expected_unload_at', '<', now())->count();
            if ($overdue > 0) {
                $items[] = [
                    'type' => 'trucks_overdue',
                    'level' => 'danger',
                    'count' => $overdue,
                    'message' => __('transport.notif_trucks_overdue', ['count' => $overdue]),
                    'url' => route('transport.loads.board'),
                ];
            }
        }

        if ($user?->hasPermission('reports_products.low_stock')) {
            $lowStockCount = Product::where('low_stock_alert_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')
                ->count();

            if ($lowStockCount > 0) {
                $items[] = [
                    'type' => 'low_stock',
                    'count' => $lowStockCount,
                    'message' => __('messages.low_stock_notification', ['count' => $lowStockCount]),
                    'url' => route('reports.products.low-stock'),
                ];
            }
        }

        $todayOperations = $this->todayOperations($user);
        $todayCount = count($todayOperations);
        $newCount = count(array_filter($todayOperations, fn ($op) => $op['is_new']));
        $page = array_slice($todayOperations, 0, self::PAGE_SIZE);

        return response()->json([
            // الرقم الأحمر = التنبيهات (مشاكل قايمة) + العمليات الجديدة من آخر فتح للجرس
            'count' => array_sum(array_column($items, 'count')) + $newCount,
            'items' => $items,
            'recent' => $page,
            // أحدث 20 عملية بمفاتيح ثابتة - الفرونت بيطلع منها إشعار متصفح للجديد
            'latest' => array_slice($todayOperations, 0, 20),
            'today_count' => $todayCount,
            'new_count' => $newCount,
            'has_more' => $todayCount > count($page),
        ]);
    }

    /** المستخدم فتح الجرس: كل اللي قبل دلوقتي بقى "متشاف" */
    public function markSeen()
    {
        $user = auth()->user();
        if ($user && Schema::hasColumn('users', 'notifications_seen_at')) {
            DB::table('users')->where('id', $user->id)->update(['notifications_seen_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * صفحة تانية (وتالتة...) من "عمليات اليوم" - بتتنادى من زرار "عرض
     * المزيد" في الفرونت.
     */
    public function recentOperationsPage(Request $request)
    {
        $user = auth()->user();
        $offset = max(0, (int) $request->input('offset', 0));

        $todayOperations = $this->todayOperations($user);
        $page = array_slice($todayOperations, $offset, self::PAGE_SIZE);

        return response()->json([
            'recent' => $page,
            'has_more' => ($offset + count($page)) < count($todayOperations),
        ]);
    }

    /**
     * عمليات اليوم (بتوقيت الرياض): فواتير النقل، السندات (قبض/صرف/صيانة)،
     * القيود اليومية، تحميل الشاحنات، تفريغ الشاحنات - مدموجة ومترتبة
     * بالأحدث، وكل نوع متفلتر على صلاحيته.
     *
     * كل عملية ليها key ثابت (نوع|id) عشان إشعار المتصفح ميتكررش، و
     * is_new = حصلت بعد آخر مرة المستخدم فتح الجرس.
     *
     * created_at متخزّن UTC، فبنحوّل بداية/نهاية يوم الرياض لمدى UTC.
     */
    private function todayOperations($user): array
    {
        $startUtc = Carbon::now('Asia/Riyadh')->startOfDay()->utc();
        $endUtc = Carbon::now('Asia/Riyadh')->endOfDay()->utc();
        $seenAt = $user?->notifications_seen_at ? Carbon::parse($user->notifications_seen_at) : null;
        $operations = collect();

        $op = function (string $type, $id, string $label, $number, $party, $total, $at, string $url) {
            return [
                'key' => $type . '|' . $id,
                'type' => $type,
                'label' => $label,
                'number' => (string) $number,
                'party' => $party,
                'total' => $total,
                'time' => $at ? Carbon::parse($at)->diffForHumans() : '',
                'at' => $at ? Carbon::parse($at) : null,
                'url' => $url,
            ];
        };

        // فواتير النقل
        if ($user?->hasPermission('transport_invoices.view')) {
            $operations = $operations->concat(
                TransportInvoice::query()
                    ->where('is_draft', false)
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->with('customer:id,name')
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get(['id', 'invoice_number', 'customer_id', 'total', 'created_at'])
                    ->map(fn (TransportInvoice $i) => $op(
                        'sale', $i->id, __('transport.notif_transport_invoice'),
                        $i->invoice_number ?: ('#' . $i->id), $i->customer?->name,
                        round((float) $i->total, 2), $i->created_at, route('transport.invoices.show', $i->id)
                    ))
            );
        }

        // المشتريات (لو الموديول لسه موجود)
        if ($user?->hasPermission('purchases.view') && Schema::hasTable('purchases')) {
            $operations = $operations->concat(
                Purchase::query()
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->with('supplier:id,name')
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get(['id', 'purchase_number', 'supplier_id', 'grand_total', 'created_at'])
                    ->map(fn (Purchase $p) => $op(
                        'purchase', $p->id, __('messages.recent_operation_purchase'),
                        $p->purchase_number ?: ('#' . $p->id), $p->supplier?->name,
                        round((float) $p->grand_total, 2), $p->created_at, route('purchases.show', $p->id)
                    ))
            );
        }

        // السندات: كل السندات (vouchers.view) أو سندات صيانة الشاحنات بس (maintenance.view)
        $allVouchers = $user?->hasPermission('vouchers.view');
        if ($allVouchers || $user?->hasPermission('maintenance.view')) {
            $operations = $operations->concat(
                AccountVoucher::query()
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->when(!$allVouchers, fn ($q) => $q->whereNotNull('truck_id'))
                    ->with(['lines.counterpartAccount:id,name', 'truck:id,plate_number', 'creator:id,name'])
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get()
                    ->map(function (AccountVoucher $v) use ($op) {
                        $isReceipt = $v->type === AccountVoucher::TYPE_RECEIPT;
                        $partyNames = $v->lines->pluck('counterpartAccount.name')->filter()->unique();
                        $party = $partyNames->count() > 1
                            ? $partyNames->take(2)->implode('، ') . __('messages.recent_operation_more_parties')
                            : $partyNames->first();
                        if ($v->truck) {
                            $party = trim('🚚 ' . $v->truck->plate_number . ($party ? ' - ' . $party : ''));
                        }
                        $label = $v->truck_id && !$isReceipt
                            ? __('transport.notif_maintenance_voucher')
                            : ($isReceipt ? __('messages.recent_operation_receipt') : __('messages.recent_operation_payment'));

                        return $op(
                            $isReceipt ? 'receipt' : 'payment', $v->id, $label,
                            $v->voucher_number ?: ('#' . $v->id), $party,
                            round((float) $v->total_amount, 2), $v->created_at, route('vouchers.show', $v->id)
                        );
                    })
            );
        }

        // القيود اليومية
        if ($user?->hasPermission('journal_entries.view')) {
            $operations = $operations->concat(
                JournalEntry::query()
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get(['id', 'entry_number', 'entry_type', 'description', 'total_debit', 'created_at'])
                    ->map(fn (JournalEntry $e) => $op(
                        'journal', $e->id,
                        $e->isOpening() ? __('transport.notif_opening_entry') : __('transport.notif_journal_entry'),
                        $e->entry_number ?: ('#' . $e->id), mb_strimwidth((string) $e->description, 0, 60, '…'),
                        round((float) $e->total_debit, 2), $e->created_at, route('journal-entries.show', $e->id)
                    ))
            );
        }

        // حركة الشاحنات النهارده: اتحمّلت / فضيت
        if ($user?->hasPermission('truck_loads.view')) {
            $board = route('transport.loads.board');

            $operations = $operations->concat(
                TruckLoad::with(['truck:id,plate_number', 'customer:id,name'])
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->latest('id')->limit(self::MAX_PER_TYPE)->get()
                    ->map(fn (TruckLoad $l) => $op(
                        'truck_loaded', $l->id, __('transport.notif_truck_loaded'),
                        $l->truck?->plate_number ?? ('#' . $l->truck_id),
                        $l->from_label . ' ← ' . $l->to_label . ($l->customer ? ' · ' . $l->customer->name : ''),
                        null, $l->created_at, $board
                    ))
            );

            // التفريغ: بوقت تسجيله على السيستم (unload_recorded_at) - مش updated_at
            // اللي بيتغير مع أي تعديل أو فوترة للحمل فكان بيكرر الإشعار
            $hasRecorded = Schema::hasColumn('truck_loads', 'unload_recorded_at');
            $todayLocal = Carbon::now('Asia/Riyadh');
            $operations = $operations->concat(
                TruckLoad::with('truck:id,plate_number')
                    ->where('status', 'unloaded')
                    ->where(function ($q) use ($hasRecorded, $startUtc, $endUtc, $todayLocal) {
                        if ($hasRecorded) {
                            $q->whereBetween('unload_recorded_at', [$startUtc, $endUtc])
                                ->orWhere(fn ($qq) => $qq->whereNull('unload_recorded_at')
                                    ->whereBetween('unloaded_at', [$todayLocal->copy()->startOfDay()->format('Y-m-d H:i:s'), $todayLocal->copy()->endOfDay()->format('Y-m-d H:i:s')]));
                        } else {
                            $q->whereBetween('unloaded_at', [$todayLocal->copy()->startOfDay()->format('Y-m-d H:i:s'), $todayLocal->copy()->endOfDay()->format('Y-m-d H:i:s')]);
                        }
                    })
                    ->latest('id')->limit(self::MAX_PER_TYPE)->get()
                    ->map(function (TruckLoad $l) use ($op, $board, $hasRecorded) {
                        // unloaded_at مكتوب بتوقيت الرياض - نحوّله لـ UTC عشان الترتيب والمقارنة
                        $at = ($hasRecorded && $l->unload_recorded_at)
                            ? $l->unload_recorded_at
                            : Carbon::parse($l->unloaded_at->format('Y-m-d H:i:s'), 'Asia/Riyadh')->utc();

                        return $op(
                            'truck_unloaded', $l->id, __('transport.notif_truck_unloaded'),
                            $l->truck?->plate_number ?? ('#' . $l->truck_id),
                            $l->to_label . ' - ' . __('transport.notif_now_empty'),
                            null, $at, $board
                        );
                    })
            );
        }

        return $operations
            ->sortByDesc(fn ($o) => $o['at']?->getTimestamp() ?? 0)
            ->values()
            ->map(function ($o) use ($seenAt) {
                $o['is_new'] = !$seenAt || ($o['at'] && $o['at']->gt($seenAt));
                unset($o['at']);

                return $o;
            })
            ->all();
    }
}
