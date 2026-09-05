<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public function summary(Request $request)
    {
        $user = auth()->user();
        $items = [];

        if ($user?->hasPermission('zatca.view')) {
            $zatcaFailedCount = Invoice::where('is_finalized', true)
                ->where('is_sent_to_zatca', false)
                ->where('zatca_status', 'FAIL')
                ->count();

            if ($zatcaFailedCount > 0) {
                $items[] = [
                    'type' => 'zatca_failed',
                    'count' => $zatcaFailedCount,
                    'message' => __('messages.zatca_failed_notification', ['count' => $zatcaFailedCount]),
                    'url' => route('zatca.index', ['sent' => 0, 'status' => 'FAIL']),
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
        $page = array_slice($todayOperations, 0, self::PAGE_SIZE);

        return response()->json([
            'count' => array_sum(array_column($items, 'count')) + $todayCount,
            'items' => $items,
            'recent' => $page,
            'today_count' => $todayCount,
            'has_more' => $todayCount > count($page),
        ]);
    }

    /**
     * صفحة تانية (وتالتة...) من "عمليات اليوم" - بتتنادى من زرار "عرض
     * المزيد" في الفرونت. الـ offset بيتحسب على القايمة المدموجة
     * والمترتبة بالكامل (مش لكل نوع لوحده) عشان الترتيب الزمني يفضل صح
     * حتى لو النوع اتغير من عملية للتانية.
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
     * كل عمليات البيع/الشراء/القبض/الصرف اللي حصلت "النهارده" (بتوقيت
     * الرياض - نفس التوقيت المستخدم في التقرير الختامي اليومي)، مدموجين
     * مع بعض ومترتبين بالأحدث. كل نوع متفلتر على صلاحيته زي الداشبورد.
     *
     * ملحوظة توقيت مهمة: created_at متخزّن بتوقيت UTC (راجع
     * config('app.timezone'))، فمينفعش نقارنه بـ whereDate() ضد تاريخ
     * نص بالرياض (زي ما بيحصل في dailyClosingReport() اللي بيقارن ضد
     * عمود تاريخ عادي issue_date/voucher_date مش created_at) - كان ده
     * هيسبب فجوة 3 ساعات (من 00:00 لـ 03:00 بتوقيت الرياض) العمليات
     * فيها متتحسبش "النهارده" غلط. الحل: نحول بداية/نهاية يوم الرياض
     * لمدى UTC ونقارن بيه.
     */
    private function todayOperations($user): array
    {
        $startUtc = Carbon::now('Asia/Riyadh')->startOfDay()->utc();
        $endUtc = Carbon::now('Asia/Riyadh')->endOfDay()->utc();
        $operations = collect();

        if ($user?->hasPermission('invoices.view')) {
            $operations = $operations->concat(
                Invoice::query()
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->with('customer:id,name')
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get(['id', 'invoice_number', 'customer_id', 'subtotal', 'tax_amount', 'discount_amount', 'created_at'])
                    ->map(fn (Invoice $invoice) => [
                        'type' => 'sale',
                        'label' => __('messages.recent_operation_sale'),
                        'number' => $invoice->invoice_number ?: ('#' . $invoice->id),
                        'party' => $invoice->customer?->name,
                        'total' => round((float) ($invoice->subtotal + $invoice->tax_amount - $invoice->discount_amount), 2),
                        'time' => optional($invoice->created_at)->diffForHumans(),
                        'created_at' => $invoice->created_at,
                        'url' => route('invoices.show', $invoice->id),
                    ])
            );
        }

        if ($user?->hasPermission('purchases.view')) {
            $operations = $operations->concat(
                Purchase::query()
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->with('supplier:id,name')
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get(['id', 'purchase_number', 'supplier_id', 'grand_total', 'created_at'])
                    ->map(fn (Purchase $purchase) => [
                        'type' => 'purchase',
                        'label' => __('messages.recent_operation_purchase'),
                        'number' => $purchase->purchase_number ?: ('#' . $purchase->id),
                        'party' => $purchase->supplier?->name,
                        'total' => round((float) $purchase->grand_total, 2),
                        'time' => optional($purchase->created_at)->diffForHumans(),
                        'created_at' => $purchase->created_at,
                        'url' => route('purchases.show', $purchase->id),
                    ])
            );
        }

        if ($user?->hasPermission('vouchers.view')) {
            $operations = $operations->concat(
                AccountVoucher::query()
                    ->whereBetween('created_at', [$startUtc, $endUtc])
                    ->with('lines.counterpartAccount:id,name')
                    ->latest('id')
                    ->limit(self::MAX_PER_TYPE)
                    ->get(['id', 'voucher_number', 'type', 'created_at'])
                    ->map(function (AccountVoucher $voucher) {
                        $isReceipt = $voucher->type === AccountVoucher::TYPE_RECEIPT;

                        // السند ممكن يكون له أكتر من بند/طرف (سند متعدد
                        // البنود) - نجمع أسماء الأطراف المميزة كلها بدل
                        // ما ناخد بند واحد بس ونتجاهل الباقي.
                        $partyNames = $voucher->lines->pluck('counterpartAccount.name')->filter()->unique();
                        $party = $partyNames->count() > 1
                            ? $partyNames->take(2)->implode('، ') . __('messages.recent_operation_more_parties')
                            : $partyNames->first();

                        return [
                            'type' => $isReceipt ? 'receipt' : 'payment',
                            'label' => $isReceipt ? __('messages.recent_operation_receipt') : __('messages.recent_operation_payment'),
                            'number' => $voucher->voucher_number ?: ('#' . $voucher->id),
                            'party' => $party,
                            'total' => $voucher->total_amount,
                            'time' => optional($voucher->created_at)->diffForHumans(),
                            'created_at' => $voucher->created_at,
                            'url' => route('vouchers.show', $voucher->id),
                        ];
                    })
            );
        }

        return $operations
            ->sortByDesc('created_at')
            ->values()
            ->map(fn ($op) => collect($op)->except('created_at')->all())
            ->all();
    }
}
