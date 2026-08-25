<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use DOMDocument;

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

/*
|--------------------------------------------------------------------------
| ZatcaController
|--------------------------------------------------------------------------
| شاشة "الفواتير المرسلة / الغير مرسلة للزكاة" - بديل صفحتين النظام
| القديم (اللي بتفلتر بالفرع، واللي بتجيب كل الفروع) في شاشة واحدة
| بتبديل (تاب) "غير مرسلة" / "مرسلة".
|
| ملحوظة واحدة باقية قبل ما ده يشتغل فعليًا:
|
|  $setting = Setting::query()->first() تحت في send() - بعتيلي في
|  الـ use statements موديلين: App\Models\Setting و
|  App\Models\SystemSetting سوا. أنا مستخدمة Setting دلوقتي (نفس
|  اسم "settings" في نظامك القديم)، لكن لو بيانات المنشأة الحقيقية
|  (الرقم الضريبي CRN/TRN، الشهادة production_certificate، مفتاح
|  التوقيع private_key، previous_hash_invoice...) موجودة في
|  SystemSetting بدل كده، قوليلي وأغيّر السطر ده بسرعة.
*/
class ZatcaController extends Controller
{
    /**
     * قايمة الفواتير - تاب "غير مرسلة" (افتراضي) أو "مرسلة" حسب ?sent=1
     */
    public function index(Request $request)
    {
        $sent = $request->boolean('sent');

        $query = Invoice::with(['customer', 'branch', 'creator'])
            ->where('is_finalized', true)
            ->where('is_sent_to_zatca', $sent)
            ->latest('issue_date');

        // فلتر الفرع - زي ملحوظة "كل الفروع" في تقرير المبيعات: النظام
        // القديم كان بيقفل خيار "كل الفروع" على صلاحية معينة. النسخة
        // دي بتوريه لكل المستخدمين لحد ما تحددي اسم الصلاحية عندك.
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
     * إرسال فاتورة واحدة للزكاة (AJAX) - نسخة من sent_to_zatca() في
     * نظامك القديم، بعد تعديلها لموديلات my-erp.
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->is_sent_to_zatca) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.already_sent'),
            ], 422);
        }

        // ملحوظة: لو بيانات المنشأة الحقيقية موجودة في SystemSetting
        // بدل Setting، غيّري السطر ده لـ SystemSetting::query()->first()
        // (شوفي الملحوظة فوق أول الملف).
        $setting = Setting::query()->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => __('zatca.settings_missing'),
            ], 422);
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
                return response()->json([
                    'success' => false,
                    'message' => __('zatca.missing_customer_address'),
                ], 422);
            }
        }

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

        foreach ($invoice->items as $item) {
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
            $taxCategoryCode = ($ratTax == 0) ? 'E' : 'S';

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

        $delivery = (new Delivery())->setDeliveryDateTime($invoice->issue_date);
        $paymentType = (new PaymentType())->setPaymentType('10');
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

        $taxCategoryCode = ($taxSum > 0) ? 'S' : 'E';
        $taxSubtotal = (new TaxSubtotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxableAmount($totalWithoutTaxSum)
            ->setTaxAmount($taxSum)
            ->setTaxCategory($taxCategoryCode)
            ->setTaxPercentage($ratTax)
            ->getElement();

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
            ->setInvoiceIssueDate((string) $invoice->issue_date)
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

        $response = $generator->sendDocument(true);

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

            return response()->json(['success' => true]);
        }

        $invoice->update(['zatca_status' => 'FAIL']);

        $message = $response['response']->validationResults->errorMessages[0]->message
            ?? __('zatca.send_failed');

        return response()->json(['success' => false, 'message' => $message], 422);
    }

    /**
     * تنزيل ملف الـ XML بتاع فاتورة اترسلت بنجاح - نسخة من dwonloadxml()
     */
    public function downloadXml(Invoice $invoice)
    {
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
}
