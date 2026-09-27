<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\Setting;
use App\Models\TransportInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use DOMDocument;
use Throwable;

// كلاسات باكدج الزاتكا - نفس الـ use statements اللي بعتيهالي بالظبط
use App\Services\Zatca\Invoice\Client;
use App\Services\Zatca\Invoice\Supplier;
use App\Services\Zatca\Invoice\Delivery;
use App\Services\Zatca\Invoice\PaymentType;
use App\Services\Zatca\Invoice\PIH;
use App\Services\Zatca\Invoice\AdditionalDocumentReference;
use App\Services\Zatca\Invoice\LegalMonetaryTotal;
use App\Services\Zatca\Invoice\TaxesTotal;
use App\Services\Zatca\Invoice\TaxSubtotal;
use App\Services\Zatca\Invoice\LineTaxCategory;
use App\Services\Zatca\Invoice\InvoiceLine;
use App\Services\Zatca\Invoice\AllowanceCharge;
use App\Services\Zatca\Invoice\InvoiceGenerator;
// إشعار دائن (مرتجع مبيعات) - نفس مكتبة الفواتير العادية، زائد كلاستين
// جديدين بس مخصوصين للإشعارات: BillingReference (بيربط الإشعار
// بالفاتورة الأصلية) و ReturnReason (سبب الإرجاع).
use App\Services\Zatca\Invoice\BillingReference;
use App\Services\Zatca\Invoice\ReturnReason;

/*
|--------------------------------------------------------------------------
| ZatcaController
|--------------------------------------------------------------------------
| شاشة "الفواتير المرسلة / الغير مرسلة للزكاة" - بديل صفحتين النظام
| القديم (اللي بتفلتر بالفرع، واللي بتجيب كل الفروع) في شاشة واحدة
| بتبديل (تاب) "غير مرسلة" / "مرسلة"، وفيها كمان زرار "إرسال الكل".
|
| ملحوظة واحدة باقية قبل ما ده يشتغل فعليًا:
|
|  $setting = Setting::query()->first() تحت - بعتيلي في الـ use
|  statements موديلين: App\Models\Setting و App\Models\SystemSetting
|  سوا. أنا مستخدمة Setting دلوقتي (نفس اسم "settings" في نظامك
|  القديم)، لكن لو بيانات المنشأة الحقيقية (الرقم الضريبي CRN/TRN،
|  الشهادة production_certificate، مفتاح التوقيع private_key،
|  previous_hash_invoice...) موجودة في SystemSetting بدل كده، قوللي
|  وأغيّر السطر ده بسرعة.
*/
class ZatcaController extends Controller
{
    /**
     * قايمة الفواتير - تاب "غير مرسلة" (افتراضي) أو "مرسلة" حسب ?sent=1
     */
    public function index(Request $request)
    {
        $this->authorize('zatca.view');
        $sent = $request->boolean('sent');

        $query = Invoice::with(['customer', 'branch', 'creator'])
            ->where('is_finalized', true)
            ->where('is_sent_to_zatca', $sent)
            ->latest('issue_date');

        // فلتر اختياري (?status=FAIL) - بيضيّق تاب "غير مرسلة" على الفواتير
        // اللي اتحاول إرسالها فعلاً وفشلت (zatca_status=FAIL) بس، مستبعد
        // اللي لسه ماتحاولش ترسل أصلاً (zatca_status=NULL) - مستخدم في
        // رابط إشعار "فواتير زاتكا فشلت" في الهيدر.
        if ($request->filled('status')) {
            $query->where('zatca_status', $request->input('status'));
        }

        // فلتر الفرع - زي ملحوظة "كل الفروع" في تقرير المبيعات: النظام
        // القديم كان بيقفل خيار "كل الفروع" على صلاحية معينة. النسخة
        // دي بتوريه لكل المستخدمين لحد ما تحدد اسم الصلاحية عندك.
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('start_at')) {
            $query->whereDate('issue_date', '>=', $request->date('start_at'));
        }
        if ($request->filled('end_at')) {
            $query->whereDate('issue_date', '<=', $request->date('end_at'));
        }

        $invoices = $query->get();

        $notSentCount = Invoice::where('is_finalized', true)->where('is_sent_to_zatca', false)->count();
        $sentCount = Invoice::where('is_finalized', true)->where('is_sent_to_zatca', true)->count();

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('zatca.index', compact('invoices', 'sent', 'branches', 'notSentCount', 'sentCount'));
    }

    /**
     * إرسال فاتورة واحدة للزكاة (AJAX)
     */
    public function send(Invoice $invoice)
    {
        $this->authorize('zatca.send');
        $setting = Setting::where('branchs_id', $invoice->branch_id)->first()
            ?? Setting::query()->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.settings_missing'),
            ], 422);
        }

        $result = $this->performSend($invoice, $setting);
return  $result;
        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    /**
     * إرسال إشعار دائن (Credit Note) لعملية مرتجع مبيعات كاملة (كل صفوف
     * invoice_returns اللي ليهم نفس reference_value - راجع
     * InvoiceReturnController@store/print) للزكاة (AJAX). نفس فكرة
     * send() بالظبط بس للمرتجعات بدل الفواتير.
     */
    public function sendReturn(string $referenceValue)
    {
        $this->authorize('zatca.send');
        $returns = InvoiceReturn::with('product')
            ->where('reference_value', $referenceValue)
            ->orderBy('id')
            ->get();

        if ($returns->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.credit_note_not_found'),
            ], 404);
        }

        if ($returns->first()->is_sent_to_zatca) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.already_sent'),
            ], 422);
        }

        $invoice = Invoice::with('customer')->find($returns->first()->invoice_id);
        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.credit_note_not_found'),
            ], 404);
        }

        $setting = Setting::where('branchs_id', $invoice->branch_id)->first()
            ?? Setting::query()->first();
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.settings_missing'),
            ], 422);
        }

        $result = $this->performSendReturn($returns, $invoice, $setting, $referenceValue);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    /**
     * إرسال كل الفواتير "الغير مرسلة" الظاهرة حاليًا (بنفس فلاتر الشاشة:
     * الفرع / من تاريخ / إلى تاريخ) دفعة واحدة (AJAX).
     *
     * مهم: بترسل الفواتير بترتيب التاريخ من الأقدم للأحدث، لإن كل فاتورة
     * محتاج الـ previous_hash بتاع اللي قبلها (سلسلة الـ PIH) - لو
     * الترتيب اتقلب هتفشل كل الفواتير اللي بعد أول واحدة غلط.
     *
     * الفاتورة اللي تفشل بيتم تخطيها وتكمل اللي بعدها، وفي الآخر بترجع
     * ملخص (كام اترسل / كام فشل ولية).
     */
    public function sendAll(Request $request)
    {
        $this->authorize('zatca.send');
        $setting = Setting::query()->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.settings_missing'),
            ], 422);
        }

        $query = Invoice::with(['customer'])
            ->where('is_finalized', true)
            ->where('is_sent_to_zatca', false)
            ->oldest('issue_date')
            ->oldest('id');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }
        if ($request->filled('start_at')) {
            $query->whereDate('issue_date', '>=', $request->date('start_at'));
        }
        if ($request->filled('end_at')) {
            $query->whereDate('issue_date', '<=', $request->date('end_at'));
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.no_invoices_to_send'),
            ], 422);
        }

        $sentCount = 0;
        $failed = [];

        foreach ($invoices as $invoice) {
            try {
                $result = $this->performSend($invoice, $setting);
            } catch (Throwable $e) {
                $result = ['success' => false, 'message' => $e->getMessage()];
            }

            if ($result['success'] ?? false) {
                $sentCount++;
                // performSend() بيعمل $setting->update(...) جوه، وده بيحدّث
                // القيم على نفس الـ $setting instance تلقائيًا، فمفيش داعي
                // نجيبه تاني من الداتابيز - الفاتورة اللي بعدها هتاخد
                // previous_hash_invoice الصح.
            } else {
                $failed[] = [
                    'invoice_number' => $invoice->invoice_number ?? $invoice->id,
                    'message' => $result['message'] ?? __('zatca.send_failed'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'total' => $invoices->count(),
            'sent_count' => $sentCount,
            'failed_count' => count($failed),
            'failed' => $failed,
        ]);
    }

    /**
     * المنطق الفعلي لإرسال فاتورة واحدة - مستخدم من send() و sendAll()
     * سوا. نسخة من دالة sent_to_zatca() اللي كانت شغالة في نظامك
     * القديم، بعد تعديلها لموديلات my-erp.
     *
     * @return array{success: bool, message?: string}
     */
    /**
     * إرسال فاتورة نقليات للزكاة بنفس كود إرسال فواتير المبيعات بالظبط
     * (performSend + buildAndSendInvoice) - الفرق الوحيد إن أسطر الفاتورة
     * بتتبني من النقلات (راجع zatcaItems()). مستخدم من TransportZatcaController.
     */
    public function sendTransportInvoice(TransportInvoice $invoice, Setting $setting): array
    {
        if ($invoice->is_draft) {
            return ['success' => false, 'message' => __('transport.zatca_draft_not_allowed')];
        }

        $invoice->loadMissing(['customer', 'items']);

        return $this->performSend($invoice, $setting);
    }

    protected function performSend(Invoice|TransportInvoice $invoice, Setting $setting): array
    {
        if ($invoice->is_sent_to_zatca) {
            return ['success' => false, 'message' => __('zatca.already_sent')];
        }

        $customer = $invoice->customer;
        // فاتورة "standard" (فيها بيانات عميل كاملة) لازم العنوان الوطني
        // والرقم الضريبي للعميل يكونوا مكتملين - زي نظامك القديم بالظبط
        $isFullTaxNumber = !empty($customer?->tax_number) && strlen((string) $customer->tax_number) === 15;
        $documentType = $isFullTaxNumber ? 'standard' : 'simplified';

        if ($documentType === 'standard') {
            if (
                empty($customer->name) || empty($customer->postal_code) || empty($customer->district) ||
                empty($customer->plot_identification) || empty($customer->building_number) ||
                empty($customer->street_name) || empty($customer->tax_number) ||
                strlen((string) $customer->tax_number) !== 15
            ) {
                return ['success' => false, 'message' => __('zatca.missing_customer_address')];
            }
        }

        // كل مرحلة تجهيز بيانات الفاتورة (لحد استدعاء sendDocument) بقت
        // دلوقتي ملفوفة جوه try/catch شامل واحد (مش بس حوالين sendDocument
        // زي قبل كده) - عن طريق تفويض كل ده لدالة منفصلة buildAndSendInvoice().
        // كده أي استثناء يحصل حتى قبل ما نكلم الزكاة أصلاً (زي حقل فاضي في
        // إعدادات الزاتكا بيتبعت null لمكان محتاج نص/رقم) هيترجم لرسالة
        // عربية واضحة بدل ما يبان كـ TypeError خام للمستخدم، وأهم حاجة إننا
        // مستحيل نرجع من غير array دلوقتي.
        try {
            $response = $this->buildAndSendInvoice($invoice, $setting, $customer, $documentType);

        } catch (Throwable $e) {
            Log::error('ZATCA send failed (exception before/while calling ZATCA)', [
                'invoice_id' => $invoice->id,
                'exception_class' => get_class($e),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $invoice->update(['zatca_status' => 'FAIL']);

            return ['success' => false, 'message' => $this->zatcaFriendlyErrorMessage($e)];
        }

        if ($response['success'] ?? false) {
            $setting->update([
                'previous_hash_invoice' => $response['hash'],
                'invoices_count' => ($setting->invoices_count ?? 0) + 1,
            ]);

            $invoice->update([
                'is_sent_to_zatca' => true,
                'zatca_status' => 'PASS',
                'zatca_signed_at' => now(),
                'zatca_hash' => $response['hash'],
                'zatca_invoice_xml' => $response['xml'],
                'zatca_cleared_invoice_xml' => $documentType === 'simplified' ? null : ($response['response']->clearedInvoice ?? null),
            ]);

            return ['success' => true];
        }

        $invoice->update(['zatca_status' => 'FAIL']);

        // نحاول نطلع الرسالة من كذا شكل محتمل لرد الزكاة (validation errors
        // القياسية، أو رسالة عامة، أو أي حاجة تانية) بدل ما نرجع رسالة عامة
        // "حصل خطأ" من غير أي تفاصيل - وبنسجل الرد الخام كامل في اللوج
        // (storage/logs/laravel.log) عشان لو الشكل مش من اللي احنا متوقعينه
        // يبقى ممكن تتشاف التفاصيل الحقيقية هناك.
        $rawResponse = $response['response'] ?? null;
        $statusCode = $response['status_code'] ?? null;
        $rawBody = $response['raw_body'] ?? null;

        $message = $rawResponse->validationResults->errorMessages[0]->message
            ?? $rawResponse->message
            ?? (is_string($rawResponse) ? $rawResponse : null)
            ?? ($statusCode ? $this->zatcaHttpFailureMessage($statusCode, $rawBody) : null)
            ?? __('zatca.send_failed');

        Log::error('ZATCA send failed (rejected by ZATCA / unexpected response shape)', [
            'invoice_id' => $invoice->id,
            'status_code' => $statusCode,
            'raw_response' => $rawResponse,
            'raw_body' => $rawBody,
        ]);

        return ['success' => false, 'message' => $message];
    }

    /**
     * بناء بيانات فاتورة الزاتكا كاملة (العميل/المنشأة/الأصناف/الإجماليات)
     * وإرسالها فعليًا لمكتبة sendDocument() - اتقسمت من جوه performSend()
     * عشان تتلف بالكامل جوه try/catch واحد شامل هناك (مش بس حوالين
     * sendDocument() زي قبل كده)، فأي TypeError يحصل هنا (زي حقل فاضي في
     * إعدادات الزاتكا) يترجع كـ Throwable عادي لـ performSend() يترجم
     * لرسالة عربية واضحة بدل ما يوصل خام للمستخدم.
     */
    /**
     * أسطر الفاتورة بالشكل اللي buildAndSendInvoice() مستنيه (quantity /
     * unit_price / discount_amount / tax_amount / tax_rate / product_id /
     * product_name_snapshot). فاتورة المبيعات بترجع أصنافها زي ما هي؛
     * فاتورة النقليات: كل نقلة سطر والتحويلة سطر، والخصم على مستوى
     * الفاتورة بيتوزّع على السطور بالنسبة (آخر سطر بياخد فرق التقريب).
     */
    protected function zatcaItems(Invoice|TransportInvoice $invoice)
    {
        if ($invoice instanceof Invoice) {
            return $invoice->items;
        }

        $raw = [];
        foreach ($invoice->items as $item) {
            $name = $item->description
                ? $item->description . ((float) $item->quantity != 1.0 ? ' × ' . rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') : '')
                : 'نقل ' . trim(($item->from_location ?? '') . ' - ' . ($item->to_location ?? ''), ' -')
                    . ' / ' . ($item->truck_snapshot ?? '') . ($item->trip_date ? ' / ' . $item->trip_date->format('Y-m-d') : '');
            $raw[] = ['name' => mb_substr($name, 0, 250), 'amount' => (float) $item->trip_price];
            if ($item->has_transfer && (float) $item->transfer_price > 0) {
                $raw[] = ['name' => mb_substr('تحويلة ' . ($item->transfer_location ?? '') . ' / ' . ($item->truck_snapshot ?? ''), 0, 250), 'amount' => (float) $item->transfer_price];
            }
        }
        $raw = array_values(array_filter($raw, fn ($l) => $l['amount'] > 0));

        $gross = array_sum(array_column($raw, 'amount'));
        $subtotal = round((float) $invoice->subtotal, 2);
        $rate = (float) $invoice->tax_rate;
        $allocated = 0;
        $rows = [];
        foreach ($raw as $idx => $l) {
            $net = $idx === array_key_last($raw)
                ? round($subtotal - $allocated, 2)
                : round($gross > 0 ? $l['amount'] * $subtotal / $gross : 0, 2);
            $allocated += $net;
            $rows[] = (object) [
                'quantity' => 1,
                'unit_price' => $net,
                'discount_amount' => 0,
                'tax_amount' => round($net * $rate, 2),
                'tax_rate' => $rate,
                'product_id' => $idx + 1,
                'product_name_snapshot' => $l['name'],
            ];
        }

        return collect($rows);
    }

    protected function buildAndSendInvoice(Invoice|TransportInvoice $invoice, Setting $setting, $customer, string $documentType)
    {
        $myUuid = Uuid::uuid4()->toString();
        $previousHash = $setting->previous_hash_invoice ?: 'X+zrZv/IbzjZUnhsbWlsecLbwjndTpG0ZynXOif7V+k=';

        $invoice->update([
            'zatca_invoice_uuid' => $myUuid,
            'zatca_document_type' => $documentType,
            'zatca_invoice_type' => $invoice->zatca_invoice_type ?: '388',
        ]);

        $totalWithoutTaxSum = 0;
        $taxSum = 0;
        $totalWithTaxSum = 0;
        $invoiceLines = [];
        $ratTax = 0;

        foreach ($this->zatcaItems($invoice) as $item) {
            $qty = (float) $item->quantity;
            if ($qty == 0) {
                continue;
            }

            $priceEach = number_format(((float) $item->unit_price - (((float) $item->discount_amount) / $qty)), 2, '.', '');
            $lineSubtotal = number_format(((float) $item->unit_price - (((float) $item->discount_amount) / $qty)) * $qty, 2, '.', '');
            $lineTax = number_format((float) $item->tax_amount, 2, '.', '');
            $lineTotal = number_format(((float) $lineSubtotal) + ((float) $lineTax), 2, '.', '');

            $totalWithoutTaxSum += (float) $lineSubtotal;
            $taxSum += (float) $lineTax;
            $totalWithTaxSum += (float) $lineTotal;

            $ratTax = number_format(((float) ($item->tax_rate ?? 0)) * 100, 2, '.', '');
            $taxCategoryCode = ($ratTax == 0) ? ($invoice instanceof TransportInvoice ? 'Z' : 'E') : 'S';

            $itemTaxCategory = (new LineTaxCategory())
                ->setTaxCategory($taxCategoryCode)
                ->setTaxPercentage($ratTax)
                ->getElement();

            $invoiceLines[] = (new InvoiceLine())
                ->setLineID($item->product_id)
                ->setLineName($item->product_name_snapshot)
                ->setLineCurrency('SAR')
                ->setLinePrice($priceEach)
                ->setLineQuantity($qty)
                ->setLineSubTotal($lineSubtotal)
                ->setLineTaxTotal($lineTax)
                ->setLineNetTotal($lineTotal)
                ->setLineTaxCategories($itemTaxCategory)
                ->setLineDiscountReason('Discount on product')
                ->setLineDiscountAmount(0)
                ->getElement();
        }

        $totalWithoutTaxSum = number_format($totalWithoutTaxSum, 2, '.', '');
        $taxSum = number_format($taxSum, 2, '.', '');
        $totalWithTaxSum = number_format($totalWithTaxSum, 2, '.', '');

        $client = (new Client())
            ->setVatNumber($customer->tax_number)
            ->setStreetName($customer->street_name)
            ->setBuildingNumber($customer->building_number)
            ->setPlotIdentification($customer->plot_identification)
            ->setSubDivisionName($customer->district)
            ->setCityName($customer->district)
            ->setPostalNumber($customer->postal_code)
            ->setCountryName('SA')
            ->setClientName($customer->name);

        $supplier = (new Supplier())
            ->setCrn($setting->crn)
            ->setStreetName($setting->street_name)
            ->setBuildingNumber($setting->building_number)
            ->setPlotIdentification($setting->plot_identification)
            ->setSubDivisionName($setting->region)
            ->setCityName($setting->city)
            ->setPostalNumber($setting->postal_number)
            ->setCountryName('SA')
            ->setVatNumber($setting->trn)
            ->setVatName($setting->name);
$delivery = (new Delivery())->setDeliveryDateTime(\Carbon\Carbon::parse($invoice->issue_date)->toDateString());        $paymentType = (new PaymentType())->setPaymentType('10');
        $previousHashObj = (new PIH())->setPIH($previousHash);
        $additionalDocumentReference = (new AdditionalDocumentReference())
            ->setInvoiceID(($setting->invoices_count ?? 0) + 1);

        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setTotalCurrency('SAR')
            ->setLineExtensionAmount($totalWithoutTaxSum)
            ->setTaxExclusiveAmount($totalWithoutTaxSum)
            ->setTaxInclusiveAmount($totalWithTaxSum)
            ->setAllowanceTotalAmount(0)
            ->setPrepaidAmount(0)
            ->setPayableAmount($totalWithTaxSum);

        $taxesTotal = (new TaxesTotal())->setTaxCurrencyCode('SAR')->setTaxTotal($taxSum);

        $taxCategoryCode = ($taxSum > 0) ? 'S' : ($invoice instanceof TransportInvoice ? 'Z' : 'E');
        $taxSubtotalObj = (new TaxSubtotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxableAmount($totalWithoutTaxSum)
            ->setTaxAmount($taxSum)
            ->setTaxCategory($taxCategoryCode)
            ->setTaxPercentage($ratTax);
        if ($taxCategoryCode === 'Z') {
            // فاتورة نقليات لشحنة خارج المملكة: نقل دولي للبضائع - نسبة صفر
            $taxSubtotalObj->setTaxExemptionReasonCode('VATEX-SA-34-1')
                ->setTaxExemptionReason('The international transport of Goods');
        }
        $taxSubtotal = $taxSubtotalObj->getElement();

        $allowanceCharge = (new AllowanceCharge())
            ->setAllowanceChargeCurrency('SAR')
            ->setAllowanceChargeIndex('1')
            ->setAllowanceChargeAmount(0)
            ->setAllowanceChargeTaxCategory($taxCategoryCode)
            ->setAllowanceChargeTaxPercentage($ratTax)
            ->getElement();

        $generator = (new InvoiceGenerator())
            ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
            ->setZatcaLang('en')
            ->setInvoiceNumber($invoice->invoice_number)
            ->setInvoiceUuid($myUuid)
            ->setInvoiceIssueDate(\Carbon\Carbon::parse($invoice->issue_date)->toDateString())
            ->setInvoiceIssueTime((string) $invoice->issue_time)
            ->setInvoiceType($documentType === 'simplified' ? '0200000' : '0100000', $invoice->zatca_invoice_type ?: '388')
            ->setInvoiceCurrencyCode('SAR')
            ->setInvoiceTaxCurrencyCode('SAR')
            ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
            ->setInvoicePIH($previousHashObj)
            ->setInvoiceSupplier($supplier)
            ->setInvoiceDelivery($delivery)
            ->setInvoicePaymentType($paymentType)
            ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
            ->setInvoiceTaxesTotal($taxesTotal)
            ->setInvoiceTaxSubTotal($taxSubtotal)
            ->setInvoiceAllowanceCharges($allowanceCharge)
            ->setInvoiceLines(...$invoiceLines)
            ->setCertificateEncoded($setting->production_certificate)
            ->setPrivateKeyEncoded($setting->private_key)
            ->setCertificateSecret($setting->production_secret);

        if ($documentType === 'standard') {
            $generator->setInvoiceClient($client);
        }

        return $generator->sendDocument(true);
    }

    /**
     * بنحول أي استثناء وقع أثناء تجهيز/إرسال فاتورة أو إشعار الزاتكا لرسالة
     * عربية واضحة ومحددة للمستخدم - بدل ما يشوف رسالة PHP تقنية زي
     * "TypeError: ... must be of type string, null given". بنسيب النص
     * التقني الأصلي في آخر الرسالة برضه (بين قوسين) عشان لو المستخدم
     * محتاج يبعتلنا التفاصيل بالظبط من غير ما يدور في اللوج.
     */
    private function zatcaFriendlyErrorMessage(Throwable $e): string
    {
        $raw = $e->getMessage();

        // TypeError من جوه مكتبة بناء الفاتورة (Client/Supplier/InvoiceGenerator)
        // بييجي غالبًا لما حقل مطلوب في إعدادات الزاتكا (الرقم الضريبي، السجل
        // التجاري، الشهادة، المفتاح الخاص...) يكون لسه فاضي (null) في جدول
        // settings - يعني إعدادات الزاتكا ناقصة، مش عيب في الفاتورة نفسها.
        if ($e instanceof \TypeError) {
            return 'إعدادات الزاتكا (الرقم الضريبي / السجل التجاري / الشهادة / المفتاح الخاص) لسه ناقصة أو فيها حقل فاضي - راجع صفحة الإعدادات وتأكد إن كل الحقول متعبّية. (تفاصيل تقنية: ' . $raw . ')';
        }

        if (str_contains($raw, 'Basic Auth')) {
            return 'بيانات الدخول (Basic Auth) الخاصة بربط حساب الزاتكا غير مكتملة في صفحة الإعدادات.';
        }

        if (
            $e instanceof \GuzzleHttp\Exception\ConnectException
            || str_contains($raw, 'cURL error')
            || str_contains($raw, 'timed out')
            || str_contains($raw, 'Could not resolve host')
        ) {
            return 'تعذر الاتصال بسيرفر هيئة الزكاة والضريبة (ZATCA) - تأكد من اتصال السيرفر بالإنترنت وحاول تاني.';
        }

        return __('zatca.send_failed') . ' (تفاصيل تقنية: ' . $raw . ')';
    }

    /**
     * لما رد الزكاة يكون خطأ HTTP (كود 400/401/403/...) من غير جسم JSON
     * قياسي فيه validationResults/message - بنكوّن رسالة توضح كود الحالة
     * ومعناه المرجّح، وبنرفق أول جزء من نص الرد الخام (لو موجود) عشان
     * التفاصيل الكاملة تكون واضحة قدام المستخدم من غير ما يدوّر في اللوج.
     */
    private function zatcaHttpFailureMessage(int $statusCode, ?string $rawBody): string
    {
        $hint = match (true) {
            $statusCode === 401 || $statusCode === 403 => 'بيانات اعتماد الاتصال بالزاتكا (Certificate/Secret) غير صحيحة أو منتهية - راجع صفحة الإعدادات.',
            $statusCode === 400 => 'الزاتكا رفضت شكل بيانات الفاتورة المرسلة.',
            $statusCode >= 500 => 'سيرفر هيئة الزكاة نفسه فيه مشكلة مؤقتة - جرب تاني بعد شوية.',
            default => 'الزاتكا رفضت الطلب.',
        };

        $bodyPreview = $rawBody ? mb_substr(trim($rawBody), 0, 300) : null;

        return __('zatca.send_failed') . " - كود الحالة: {$statusCode}. {$hint}"
            . ($bodyPreview ? " (نص الرد: {$bodyPreview})" : '');
    }

    /**
     * المنطق الفعلي لإرسال إشعار دائن (مرتجع) - نفس هيكل performSend()
     * تمامًا بفروق قليلة مطلوبة تقنيًا للإشعارات فقط:
     *
     *  - InvoiceTypeCode = 381 (إشعار دائن) بدل 388 (فاتورة ضريبية).
     *  - لازم BillingReference يحتوي على رقم الفاتورة الأصلية (invoice_number)
     *    عشان الزاتكا تربط الإشعار بيها محاسبيًا - ده منفصل تمامًا عن سلسلة
     *    التسلسل/الهاش (previous_hash_invoice/invoices_count) اللي بتفضل
     *    نفسها زي أي فاتورة عادية (كل المستندات - فواتير وإشعارات - بترقم
     *    وتترابط بالهاش في نفس التسلسل الزمني لمنشأتك، مش لكل نوع لوحده).
     *  - القيم (الكمية/الإجمالي/الضريبة) كلها موجبة زي أي فاتورة عادية -
     *    كود 381 وحده هو اللي بيقول للزكاة إن المستند ده "تخفيض"، مفيش
     *    قيم بالسالب (اتأكدت من ده قبل الكتابة عشان غلط شائع يسبب رفض
     *    الإشعار من الزكاة).
     *  - نتائج الإرسال بتتسجل على أعمدة credit_note_* في جدول الفاتورة
     *    الأصلية (مش على invoice_returns) لإن كل إشعار مرتبط بفاتورة
     *    وحدة أصلاً - وده بالظبط الشكل اللي جدول invoices متجهز بيه من
     *    قبل (أعمدة credit_note_zatca_xml/hash/status/issue_date/time).
     *
     * @return array{success: bool, message?: string}
     */
    protected function performSendReturn($returns, Invoice $invoice, Setting $setting, string $referenceValue): array
    {
        $customer = $invoice->customer;

        $isFullTaxNumber = !empty($customer?->tax_number) && strlen((string) $customer->tax_number) === 15;
        $documentType = $isFullTaxNumber ? 'standard' : 'simplified';

        if ($documentType === 'standard') {
            if (
                empty($customer->name) || empty($customer->postal_code) || empty($customer->district) ||
                empty($customer->plot_identification) || empty($customer->building_number) ||
                empty($customer->street_name) || empty($customer->tax_number) ||
                strlen((string) $customer->tax_number) !== 15
            ) {
                return ['success' => false, 'message' => __('zatca.missing_customer_address')];
            }
        }

        // نفس فكرة performSend(): كل مرحلة تجهيز إشعار الدائن (لحد استدعاء
        // sendDocument) اتفوّضت لدالة buildAndSendCreditNote() تتلف جوه
        // try/catch شامل هنا، عشان أي استثناء (حتى قبل الاتصال بالزكاة
        // نفسها) يترجع رسالة عربية واضحة بدل TypeError خام، وميرجعش أبدًا
        // من غير array.
        try {
            $response = $this->buildAndSendCreditNote($invoice, $setting, $customer, $documentType, $returns, $referenceValue);
        } catch (Throwable $e) {
            Log::error('ZATCA send (credit note) failed (exception before/while calling ZATCA)', [
                'reference_value' => $referenceValue,
                'exception_class' => get_class($e),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $invoice->update(['credit_note_zatca_status' => 'FAIL']);

            return ['success' => false, 'message' => $this->zatcaFriendlyErrorMessage($e)];
        }

        if ($response['success'] ?? false) {
            $setting->update([
                'previous_hash_invoice' => $response['hash'],
                'invoices_count' => ($setting->invoices_count ?? 0) + 1,
            ]);

            $invoice->update([
                'credit_note_issue_date' => now()->toDateString(),
                'credit_note_issue_time' => now()->toTimeString(),
                'credit_note_zatca_status' => 'PASS',
                'credit_note_zatca_hash' => $response['hash'],
                'credit_note_zatca_xml' => $response['xml'],
            ]);

            InvoiceReturn::where('reference_value', $referenceValue)
                ->update(['is_sent_to_zatca' => true]);

            return ['success' => true];
        }

        $invoice->update(['credit_note_zatca_status' => 'FAIL']);

        $rawResponse = $response['response'] ?? null;
        $statusCode = $response['status_code'] ?? null;
        $rawBody = $response['raw_body'] ?? null;

        $message = $rawResponse->validationResults->errorMessages[0]->message
            ?? $rawResponse->message
            ?? (is_string($rawResponse) ? $rawResponse : null)
            ?? ($statusCode ? $this->zatcaHttpFailureMessage($statusCode, $rawBody) : null)
            ?? __('zatca.send_failed');

        Log::error('ZATCA send (credit note) failed (rejected by ZATCA / unexpected response shape)', [
            'reference_value' => $referenceValue,
            'status_code' => $statusCode,
            'raw_response' => $rawResponse,
            'raw_body' => $rawBody,
        ]);

        return ['success' => false, 'message' => $message];
    }

    /**
     * بناء بيانات إشعار الدائن (مرتجع) كاملة وإرسالها فعليًا - اتقسمت من
     * جوه performSendReturn() لنفس سبب buildAndSendInvoice(): تتلف بالكامل
     * جوه try/catch شامل هناك.
     */
    protected function buildAndSendCreditNote(Invoice $invoice, Setting $setting, $customer, string $documentType, $returns, string $referenceValue)
    {
        $myUuid = Uuid::uuid4()->toString();
        $previousHash = $setting->previous_hash_invoice ?: 'X+zrZv/IbzjZUnhsbWlsecLbwjndTpG0ZynXOif7V+k=';

        $totalWithoutTaxSum = 0;
        $taxSum = 0;
        $totalWithTaxSum = 0;
        $invoiceLines = [];
        $ratTax = 0;

        foreach ($returns as $row) {
            $qty = (float) $row->quantity;
            if ($qty == 0) {
                continue;
            }

            $lineDiscount = round((float) $row->discount_amount + (float) $row->invoice_discount_amount, 2);
            $priceEach = number_format(((float) $row->unit_price - ($lineDiscount / $qty)), 2, '.', '');
            $lineSubtotal = number_format(((float) $row->unit_price * $qty) - $lineDiscount, 2, '.', '');
            $lineTax = number_format((float) $row->tax_amount, 2, '.', '');
            $lineTotal = number_format(((float) $lineSubtotal) + ((float) $lineTax), 2, '.', '');

            $totalWithoutTaxSum += (float) $lineSubtotal;
            $taxSum += (float) $lineTax;
            $totalWithTaxSum += (float) $lineTotal;

            $ratTax = number_format(((float) ($row->tax_rate ?? 0)) * 100, 2, '.', '');
            $taxCategoryCode = ($ratTax == 0) ? 'E' : 'S';

            $itemTaxCategory = (new LineTaxCategory())
                ->setTaxCategory($taxCategoryCode)
                ->setTaxPercentage($ratTax)
                ->getElement();

            $invoiceLines[] = (new InvoiceLine())
                ->setLineID($row->product_id)
                ->setLineName(optional($row->product)->name ?? ('#' . $row->product_id))
                ->setLineCurrency('SAR')
                ->setLinePrice($priceEach)
                ->setLineQuantity($qty)
                ->setLineSubTotal($lineSubtotal)
                ->setLineTaxTotal($lineTax)
                ->setLineNetTotal($lineTotal)
                ->setLineTaxCategories($itemTaxCategory)
                ->setLineDiscountReason('Sales return')
                ->setLineDiscountAmount(0)
                ->getElement();
        }

        $totalWithoutTaxSum = number_format($totalWithoutTaxSum, 2, '.', '');
        $taxSum = number_format($taxSum, 2, '.', '');
        $totalWithTaxSum = number_format($totalWithTaxSum, 2, '.', '');

        $client = (new Client())
            ->setVatNumber($customer->tax_number)
            ->setStreetName($customer->street_name)
            ->setBuildingNumber($customer->building_number)
            ->setPlotIdentification($customer->plot_identification)
            ->setSubDivisionName($customer->district)
            ->setCityName($customer->district)
            ->setPostalNumber($customer->postal_code)
            ->setCountryName('SA')
            ->setClientName($customer->name);

        $supplier = (new Supplier())
            ->setCrn($setting->crn)
            ->setStreetName($setting->street_name)
            ->setBuildingNumber($setting->building_number)
            ->setPlotIdentification($setting->plot_identification)
            ->setSubDivisionName($setting->region)
            ->setCityName($setting->city)
            ->setPostalNumber($setting->postal_number)
            ->setCountryName('SA')
            ->setVatNumber($setting->trn)
            ->setVatName($setting->name);

        $delivery = (new Delivery())->setDeliveryDateTime(now()->toDateString());
        $paymentType = (new PaymentType())->setPaymentType('10');
        $previousHashObj = (new PIH())->setPIH($previousHash);
        $additionalDocumentReference = (new AdditionalDocumentReference())
            ->setInvoiceID(($setting->invoices_count ?? 0) + 1);

        $billingReference = (new BillingReference())
            ->setBillingReference((string) $invoice->invoice_number);
        $returnReason = (new ReturnReason())
            ->setReturnReason('Sales return for invoice ' . $invoice->invoice_number);

        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setTotalCurrency('SAR')
            ->setLineExtensionAmount($totalWithoutTaxSum)
            ->setTaxExclusiveAmount($totalWithoutTaxSum)
            ->setTaxInclusiveAmount($totalWithTaxSum)
            ->setAllowanceTotalAmount(0)
            ->setPrepaidAmount(0)
            ->setPayableAmount($totalWithTaxSum);

        $taxesTotal = (new TaxesTotal())->setTaxCurrencyCode('SAR')->setTaxTotal($taxSum);

        $taxCategoryCode = ($taxSum > 0) ? 'S' : ($invoice instanceof TransportInvoice ? 'Z' : 'E');
        $taxSubtotalObj = (new TaxSubtotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxableAmount($totalWithoutTaxSum)
            ->setTaxAmount($taxSum)
            ->setTaxCategory($taxCategoryCode)
            ->setTaxPercentage($ratTax);
        if ($taxCategoryCode === 'Z') {
            // فاتورة نقليات لشحنة خارج المملكة: نقل دولي للبضائع - نسبة صفر
            $taxSubtotalObj->setTaxExemptionReasonCode('VATEX-SA-34-1')
                ->setTaxExemptionReason('The international transport of Goods');
        }
        $taxSubtotal = $taxSubtotalObj->getElement();

        $allowanceCharge = (new AllowanceCharge())
            ->setAllowanceChargeCurrency('SAR')
            ->setAllowanceChargeIndex('1')
            ->setAllowanceChargeAmount(0)
            ->setAllowanceChargeTaxCategory($taxCategoryCode)
            ->setAllowanceChargeTaxPercentage($ratTax)
            ->getElement();

        $generator = (new InvoiceGenerator())
            ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
            ->setZatcaLang('en')
            ->setInvoiceNumber($referenceValue)
            ->setInvoiceUuid($myUuid)
            ->setInvoiceIssueDate(now()->toDateString())
            ->setInvoiceIssueTime(now()->toTimeString())
            ->setInvoiceType($documentType === 'simplified' ? '0200000' : '0100000', '381')
            ->setInvoiceCurrencyCode('SAR')
            ->setInvoiceTaxCurrencyCode('SAR')
            ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
            ->setInvoiceBillingReference($billingReference)
            ->setInvoiceReturnReason($returnReason)
            ->setInvoicePIH($previousHashObj)
            ->setInvoiceSupplier($supplier)
            ->setInvoiceDelivery($delivery)
            ->setInvoicePaymentType($paymentType)
            ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
            ->setInvoiceTaxesTotal($taxesTotal)
            ->setInvoiceTaxSubTotal($taxSubtotal)
            ->setInvoiceAllowanceCharges($allowanceCharge)
            ->setInvoiceLines(...$invoiceLines)
            ->setCertificateEncoded($setting->production_certificate)
            ->setPrivateKeyEncoded($setting->private_key)
            ->setCertificateSecret($setting->production_secret);

        if ($documentType === 'standard') {
            $generator->setInvoiceClient($client);
        }

        return $generator->sendDocument(true);
    }

    /**
     * تنزيل ملف الـ XML بتاع فاتورة اترسلت بنجاح - نسخة من dwonloadxml()
     */
    public function downloadXml(Invoice $invoice)
    {
        $this->authorize('zatca.view');
        if (!$invoice->is_sent_to_zatca || $invoice->zatca_status !== 'PASS') {
            abort(404);
        }

        $rawXml = base64_decode($invoice->zatca_cleared_invoice_xml ?? $invoice->zatca_invoice_xml, true);

        if (!$rawXml) {
            abort(404, __('zatca.xml_not_found'));
        }

        $xml = new DOMDocument();
        $xml->loadXML($rawXml);
        $xml->formatOutput = true;

        $fileName = 'invoice_' . $invoice->invoice_number . '_' . now()->format('Y_m_d_His') . '.xml';
        $filePath = storage_path('app/tmp_' . $fileName);
        $xml->save($filePath);

        return response()->download($filePath, $fileName, ['Content-Type' => 'application/xml'])
            ->deleteFileAfterSend(true);
    }

    /**
     * تنزيل XML إشعار الدائن (المرتجع) - نفس downloadXml() بس من أعمدة
     * credit_note_* على الفاتورة الأصلية.
     *
     * ⚠️ ملحوظة مهمة: أعمدة credit_note_* في جدول invoices فيها مكان
     * لإشعار واحد بس لكل فاتورة. لو نفس الفاتورة اترجع منها أكتر من مرة
     * (أكتر من reference_value)، الإشعار التاني هيكتب فوق بيانات الأول
     * (XML/hash/status) وهيفضل بس آخر إشعار متسجل. لو محتاج سجل كامل
     * لكل إشعارات فاتورة واحدة، ده محتاج عمود/جدول منفصل مرتبط بـ
     * reference_value بدل ما يتسجل على الفاتورة نفسها - قوللي لو عايز
     * أضيفه.
     */
    public function downloadCreditNoteReturnXml(string $referenceValue)
    {
        $this->authorize('zatca.view');
        $firstReturn = InvoiceReturn::where('reference_value', $referenceValue)->first();
        abort_if(!$firstReturn, 404);

        $invoice = Invoice::find($firstReturn->invoice_id);
        abort_if(!$invoice || $invoice->credit_note_zatca_status !== 'PASS', 404);

        $rawXml = base64_decode($invoice->credit_note_zatca_xml, true);
        if (!$rawXml) {
            abort(404, __('zatca.xml_not_found'));
        }

        $xml = new DOMDocument();
        $xml->loadXML($rawXml);
        $xml->formatOutput = true;

        $fileName = 'credit_note_' . $referenceValue . '_' . now()->format('Y_m_d_His') . '.xml';
        $filePath = storage_path('app/tmp_' . $fileName);
        $xml->save($filePath);

        return response()->download($filePath, $fileName, ['Content-Type' => 'application/xml'])
            ->deleteFileAfterSend(true);
    }
}
