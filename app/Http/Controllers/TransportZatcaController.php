<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TransportInvoice;
use Illuminate\Http\Request;
use Throwable;

/**
 * شاشة إرسال فواتير النقليات للزكاة. الإرسال نفسه بيتم بنفس كود إرسال
 * فواتير المبيعات (ZatcaController::sendTransportInvoice → performSend →
 * buildAndSendInvoice) - نفس إعدادات الربط ونفس عدّاد الفواتير والـ hash.
 * المسودات مش بتتبعت.
 */
class TransportZatcaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('zatca.view');

        $sent = $request->boolean('sent');

        $invoices = TransportInvoice::with(['customer:id,name,tax_number', 'branch:id,name'])
            ->where('is_draft', false)
            ->where('is_sent_to_zatca', $sent)
            ->when($request->filled('status'), fn ($q) => $q->where('zatca_status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->input('date_to')))
            ->orderBy('id', $sent ? 'desc' : 'asc')
            ->paginate(30)
            ->withQueryString();

        return view('transport.zatca.index', [
            'invoices' => $invoices,
            'sent' => $sent,
            'notSentCount' => TransportInvoice::where('is_draft', false)->where('is_sent_to_zatca', false)->count(),
            'sentCount' => TransportInvoice::where('is_sent_to_zatca', true)->count(),
        ]);
    }

    public function send(TransportInvoice $invoice)
    {
        $this->authorize('zatca.send');

        $setting = $this->setting($invoice);
        if (!$setting) {
            return $this->reply(['success' => false, 'message' => __('zatca.settings_missing')]);
        }

        return $this->reply($this->sendOne($invoice, $setting));
    }

    /** إرسال كل الفواتير المعتمدة اللي لسه متبعتتش (الأقدم الأول) */
    public function sendAll()
    {
        $this->authorize('zatca.send');

        $invoices = TransportInvoice::where('is_draft', false)->where('is_sent_to_zatca', false)->orderBy('id')->get();
        if ($invoices->isEmpty()) {
            return $this->reply(['success' => false, 'message' => __('zatca.no_invoices_to_send')]);
        }

        $sent = 0;
        $failed = [];
        foreach ($invoices as $invoice) {
            $setting = $this->setting($invoice);
            $result = $setting ? $this->sendOne($invoice, $setting) : ['success' => false, 'message' => __('zatca.settings_missing')];
            if ($result['success'] ?? false) {
                $sent++;
            } else {
                $failed[] = $invoice->invoice_number . ': ' . ($result['message'] ?? '');
            }
        }

        $msg = __('transport.zatca_bulk_result', ['sent' => $sent, 'failed' => count($failed)]);
        if ($failed) {
            $msg .= ' — ' . implode(' | ', array_slice($failed, 0, 5));
        }

        return $this->reply(['success' => $sent > 0, 'message' => $msg]);
    }

    public function downloadXml(TransportInvoice $invoice)
    {
        $this->authorize('zatca.view');

        $xml = $invoice->zatca_cleared_invoice_xml
            ? (base64_decode($invoice->zatca_cleared_invoice_xml, true) ?: $invoice->zatca_cleared_invoice_xml)
            : $invoice->zatca_invoice_xml;

        abort_unless($xml, 404);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $invoice->invoice_number . '.xml"',
        ]);
    }

    // ------------------------------------------------------------------

    private function sendOne(TransportInvoice $invoice, Setting $setting): array
    {
        try {
            $result = app(ZatcaController::class)->sendTransportInvoice($invoice, $setting);
        } catch (Throwable $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        if (!($result['success'] ?? false)) {
            $invoice->update(['zatca_message' => $result['message'] ?? null]);
        } else {
            $invoice->update(['zatca_message' => null]);
            $result['message'] = __('transport.zatca_sent_ok');
        }

        return $result;
    }

    private function setting(TransportInvoice $invoice): ?Setting
    {
        return Setting::where('branchs_id', $invoice->branch_id)->first() ?? Setting::query()->first();
    }

    private function reply(array $result)
    {
        if (request()->expectsJson()) {
            return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
        }

        return back()->with(($result['success'] ?? false) ? 'success' : 'error', $result['message'] ?? '');
    }
}
