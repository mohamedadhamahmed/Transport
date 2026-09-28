<?php

namespace App\Http\Middleware;

use App\Support\ClosedPeriod;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * بيرفض أي طلب حفظ/تعديل/حذف لمستند محاسبي تاريخه في سنة مالية مقفولة:
 *  - لو تاريخ المستند اللي في الفورم (تاريخ الفاتورة/القيد/السند...) جوه الفترة المقفولة.
 *  - لو بتعدّل أو تحذف مستند قديم (فاتورة، قيد، سند...) تاريخه جوه الفترة المقفولة.
 * (وفيه حماية تانية على مستوى الحركات نفسها في CreditTransaction.)
 */
class EnsurePeriodOpen
{
    /** حقول التاريخ في فورمات المستندات المحاسبية. */
    private const DATE_INPUTS = [
        'invoice_date', 'issue_date', 'entry_date', 'voucher_date', 'purchase_date',
        'return_date', 'posting_date', 'settlement_date', 'loan_date', 'transaction_date',
        'payment_date', 'note_date', 'credit_note_date',
    ];

    /** المستندات اللي ليها أثر محاسبي (تعديلها/حذفها بيغيّر الأرصدة). */
    private const DOCUMENT_MODELS = [
        'Invoice', 'InvoiceReturn', 'Purchase', 'PurchaseReturn', 'JournalEntry', 'AccountVoucher',
        'TransportInvoice', 'TransportCreditNote', 'PayrollPosting', 'EmployeeLoan',
        'EndOfServiceSettlement', 'KitchenMaterialIssue', 'CreditTransaction',
    ];

    /** تاريخ المستند نفسه (أول عمود موجود من دول). */
    private const DOCUMENT_DATES = [
        'issue_date', 'invoice_date', 'entry_date', 'voucher_date', 'purchase_date', 'return_date',
        'posting_date', 'settlement_date', 'loan_date', 'date_export', 'created_at',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || !ClosedPeriod::closedUntil()) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        if (str_starts_with($routeName, 'year-closing.') || in_array($routeName, ['login', 'logout'], true)) {
            return $next($request);
        }

        foreach (self::DATE_INPUTS as $key) {
            if ($request->filled($key) && is_scalar($request->input($key)) && ClosedPeriod::isClosed($request->input($key))) {
                return $this->reject($request);
            }
        }

        // تعديل/حذف مستند قديم.
        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'], true)) {
            foreach ((array) optional($request->route())->parameters() as $param) {
                if ($param instanceof Model
                    && in_array(class_basename($param), self::DOCUMENT_MODELS, true)
                    && ClosedPeriod::isClosed($this->documentDate($param))) {
                    return $this->reject($request);
                }
            }
        }

        return $next($request);
    }

    private function documentDate(Model $model)
    {
        $attributes = $model->getAttributes();
        foreach (self::DOCUMENT_DATES as $column) {
            if (!empty($attributes[$column])) {
                return $attributes[$column];
            }
        }

        return null;
    }

    private function reject(Request $request): Response
    {
        $message = ClosedPeriod::message();

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'errors' => ['closed_period' => [$message]]], 422);
        }

        return back()->withInput()->withErrors(['closed_period' => $message])->with('error', $message);
    }
}
