<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\FinancialAccount;
use App\Models\InvoiceItem;
use App\Models\CreditTransaction;
use App\Models\Product;
use App\Models\DraftInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\InvoiceReturn;
use App\Services\Zatca\QRCode;
use App\Services\Zatca\QRCodeString;
use App\Services\Zatca\ZatcaConfig;
use App\Models\Setting;
use App\Models\SystemSetting;
// ZATCA Invoice Services
use App\Services\Zatca\Invoice\Client;
use App\Services\Zatca\Invoice\Supplier;
use App\Services\Zatca\Invoice\Delivery;
use App\Services\Zatca\Invoice\PaymentType;
use App\Services\Zatca\Invoice\PIH;
use App\Services\Zatca\Invoice\ReturnReason;
use App\Services\Zatca\Invoice\BillingReference;
use App\Services\Zatca\Invoice\AdditionalDocumentReference;
use App\Services\Zatca\Invoice\LegalMonetaryTotal;
use App\Services\Zatca\Invoice\TaxesTotal;
use App\Services\Zatca\Invoice\TaxSubtotal;
use App\Services\Zatca\Invoice\LineTaxCategory;
use App\Services\Zatca\Invoice\InvoiceLine;
use App\Services\Zatca\Invoice\AllowanceCharge;
use App\Services\Zatca\Invoice\InvoiceGenerator;
use Ramsey\Uuid\Uuid;
use DOMDocument;


class InvoiceController extends Controller
{


    function sent_to_zatca_return_items($request)
    {
        // - Then Call Invoice required data from database depend on your query statment and required company id

        $setting = Setting::where('branchs_id', 1)->first();

        $previous_invoice = null;
        $invoice = Invoice::find($request);



        if ($invoice->document_type == 'standard') {
            if (
                is_null($invoice->customer->name) || is_null($invoice->customer->postcode) || is_null($invoice->customer->address) || is_null($invoice->customer->sub_city) ||
                is_null($invoice->customer->plot_identification) || is_null($invoice->customer->building_number) || is_null($invoice->customer->street_name) || is_null($invoice->customer->tax_no) || strlen($invoice->customer->tax_no) != 15
            ) {
                return "  \n Please enter the full national address and tax number information. Thank you يرجل ادخال بيانات العنوان الوطني و الرقم الضريبيي كاملا وشكرا";

            }

        }
        $invprevious = $setting->previous_hash_invoice;

        if ($invprevious == null) {
            $previous_invoice = 'X+zrZv/IbzjZUnhsbWlsecLbwjndTpG0ZynXOif7V+k=';

        } else {
            $previous_invoice = $invprevious;
        }
        $myuuid = Uuid::uuid4();

        $created_at = \Carbon\Carbon::now();
        Invoice::find($request)->update(
            [
                'issue_date_return' => substr($created_at, 0, 10),
                'issue_time_return' => substr($created_at, 11),
                'uuid' => $myuuid
            ]
        );

        $invoice = Invoice::find($request);

        $rat_tax = 0;


        $total_withot_tax_sum = 0;
        $tax_sum = 0;
        $total_with_tax_sum = 0;
        $invoiceLines = [];



        foreach (InvoiceReturn::where("invoice_id", $request)->where('return_quantity', '!=', 0)->where('send_zatca', 0)->get() as $item) {
            InvoiceReturn::find($item->id)->update(['send_zatca' => 1]);
            $price_each_element_withoud_tax = number_format(($item->return_Unit_Price - ($item->discountvalue / $item->return_quantity)), 2, '.', '');
            $temp = ($item->return_Unit_Price - ($item->discountvalue / $item->return_quantity)) * $item->return_quantity;
            $total_withot_tax = number_format($temp, 2, '.', '');
            $temp = ($item->return_Added_Value) * $item->return_quantity;
            $tax = number_format($temp, 2, '.', '');
            $totlal_element = $total_withot_tax + $tax;

            $total_with_tax = number_format($totlal_element, 2, '.', '');
            $total_withot_tax_sum = $total_withot_tax_sum + $total_withot_tax * 1;
            $tax_sum = $tax_sum + $tax;
            $total_with_tax_sum = $total_with_tax_sum + $total_with_tax;
            $rat_tax = number_format($item->tax_rate*100, 2, '.', '');
            $taxCategoryCode = ($item->tax_rate == 0) ? 'E' : 'S';
            $itemTaxCategory = (new LineTaxCategory())
                ->setTaxCategory($taxCategoryCode)
                ->setTaxPercentage($rat_tax)
                ->getElement();
            $invoiceLines[] = (new InvoiceLine())
                ->setLineID($item->product_id)
                ->setLineName($item->productData->product_name)
                ->setLineCurrency('SAR')
                ->setLinePrice(number_format($price_each_element_withoud_tax, 2, '.', ''))
                ->setLineQuantity($item->return_quantity)
                ->setLineSubTotal($total_withot_tax)
                ->setLineTaxTotal($tax)
                ->setLineNetTotal($total_with_tax)
                ->setLineTaxCategories($itemTaxCategory)
                ->setLineDiscountReason('Discount on product')
                ->setLineDiscountAmount(0)
                ->getElement();
        }



        $total_withot_tax_sum = number_format($total_withot_tax_sum, 2, '.', '');
        $tax_sum = number_format($tax_sum, 2, '.', '');
        ;
        $total_with_tax_sum = number_format($total_with_tax_sum, 2, '.', '');






        // clients data


        $client = (new Client())
            ->setVatNumber($invoice->customer->tax_no)
            ->setStreetName($invoice->customer->street_name)
            ->setBuildingNumber($invoice->customer->building_number)
            ->setPlotIdentification($invoice->customer->plot_identification)
            ->setSubDivisionName($invoice->customer->sub_city)
            ->setCityName($invoice->customer->address)
            ->setPostalNumber($invoice->customer->postcode)
            ->setCountryName('SA')
            ->setClientName($invoice->customer->name);


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

        $delivery = (new Delivery())
            ->setDeliveryDateTime($invoice->issue_date);

        $paymentType = (new PaymentType())
            ->setPaymentType('10');

        $returnReason = (new ReturnReason())
            ->setReturnReason('InvoiceItem returns');

        $previous_hash = (new PIH())
            ->setPIH($previous_invoice);  // note this value it from step 3 , 4
        $billingReference = (new BillingReference())
            ->setBillingReference($request); // note this used when type credit or debit this value of parent invoice id

        $additionalDocumentReference = (new AdditionalDocumentReference())
            ->setInvoiceID($setting->Invoice_count + 1); // note this value it from step 1

        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setTotalCurrency('SAR')
            ->setLineExtensionAmount($total_withot_tax_sum)
            ->setTaxExclusiveAmount($total_withot_tax_sum)
            ->setTaxInclusiveAmount($total_with_tax_sum)
            ->setAllowanceTotalAmount(0)
            ->setPrepaidAmount(0)
            ->setPayableAmount($total_with_tax_sum);

        $taxesTotal = (new TaxesTotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxTotal($tax_sum);
        $current_item_tax_rate = ($tax_sum > 0) ? 15 : 0; // افترضنا 15، يمكن استبدالها بـ $item->tax_rate لو متوفر
        $taxCategoryCode = ($current_item_tax_rate == 0) ? 'E' : 'S';

        $taxSubtotal = (new TaxSubtotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxableAmount($total_withot_tax_sum)
            ->setTaxAmount($tax_sum)
            ->setTaxCategory($taxCategoryCode)
            ->setTaxPercentage($rat_tax)
            ->getElement();


        $allowanceCharge = (new AllowanceCharge())
            ->setAllowanceChargeCurrency('SAR')
            ->setAllowanceChargeIndex('1')
            ->setAllowanceChargeAmount(0)
            ->setAllowanceChargeTaxCategory($taxCategoryCode)
            ->setAllowanceChargeTaxPercentage($rat_tax)
            ->getElement();



        if (strlen($invoice->customer->tax_no) != 15) {



            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
                ->setZatcaLang('en')
                ->setInvoiceNumber($invoice->NOTICE_Number)
                ->setInvoiceUuid($myuuid) // this value from step 6
                ->setInvoiceIssueDate($invoice->issue_date_return)
                ->setInvoiceIssueTime($invoice->issue_time_return)
                ->setInvoiceType(($invoice->document_type == 'simplified') ? '0200000' : '0100000', "381")
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                ->setInvoiceBillingReference($billingReference) // use this when document type is credit or debit
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceupplier($supplier)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                ->setInvoiceReturnReason($returnReason) //use this when document type is credit or debit
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal($taxSubtotal)
                ->setInvoiceAllowanceCharges($allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($setting->production_certificate)
                ->setPrivateKeyEncoded($setting->private_key)
                ->setCertificateSecret($setting->production_secret)
                ->sendDocument(true); // when you use production certifiacte for (simulation , core) dont forget set sendDocument(true)


        } else {
            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
                ->setZatcaLang('en')
                ->setInvoiceNumber($invoice->NOTICE_Number)
                ->setInvoiceUuid($myuuid) // this value from step 6
                ->setInvoiceIssueDate($invoice->issue_date_return)
                ->setInvoiceIssueTime($invoice->issue_time_return)
                ->setInvoiceType(($invoice->document_type == 'simplified') ? '0200000' : '0100000', "381")
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                ->setInvoiceBillingReference($billingReference) // use this when document type is credit or debit
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceupplier($supplier)
                ->setInvoiceClient($client)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                ->setInvoiceReturnReason($returnReason) //use this when document type is credit or debit
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal($taxSubtotal)
                ->setInvoiceAllowanceCharges($allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($setting->production_certificate)
                ->setPrivateKeyEncoded($setting->private_key)
                ->setCertificateSecret($setting->production_secret)
                ->sendDocument(true); // when you use production certifiacte for (simulation , core) dont forget set sendDocument(true)
        }

        if ($response['success']) {
            Setting::where('branchs_id', 1)->update([
                'previous_hash_invoice' => $response['hash'],
                'Invoice_count' => $setting->Invoice_count + 1
            ]);
            Invoice::find($request)->update(
                [
                    'qr_zatca_return' => \Carbon\Carbon::now(),
                    'sent_to_zatca_status_return' => "PASS",
                    'xmltags_return' => $response['xml'],
                    'xml_return' => $invoice->document_type == 'simplified' ? NULL : $response['response']->clearedInvoice

                ]
            );
            return 1;

        } else {

            return '|||' . $response['response']->reportingStatus . '   ||| ERROR MESSAGE    :-   ' . $response['response']->validationResults->errorMessages[0]->message;
        }
    }

    function sent_to_zatca($request)
    {
        // - Then Call Invoice required data from database depend on your query statment and required company id

        $setting = Setting::where('branchs_id', 1)->first();

        ### Zatca Integration have two steps second : Send Invoice to zatca Step example :
        // - Add below line to start of controller file which used
        $previous_invoice = null;
        $invoice = Invoice::find($request);
        $invprevious = $setting->previous_hash_invoice;

        if ($invprevious == null) {
            $previous_invoice = 'X+zrZv/IbzjZUnhsbWlsecLbwjndTpG0ZynXOif7V+k=';

        } else {
            $previous_invoice = $invprevious;
        }

        $myuuid = Uuid::uuid4();

        Invoice::find($request)->update(
            [
                'invoice_counter' => $setting->Invoice_count + 1,
                'invoice_number' => $invoice->id,
                'invoiceUUid' => $myuuid,
                'document_type' => strlen($invoice->customer->tax_no) != 15 ? 'simplified' : 'standard',
                'invoice_type' => "388", //  "388" NORMAL INVOICE , "383"  DEBIT_NOTE , "381" CREDIT_NOTE
                'issue_date' => substr($invoice->created_at, 0, 10),
                'issue_time' => substr($invoice->created_at, 11),
            ]
        );


        $invoice = Invoice::find($request);

        if ($invoice->document_type == 'standard') {
            if (
                is_null($invoice->customer->name) || is_null($invoice->customer->postcode) || is_null($invoice->customer->address) || is_null($invoice->customer->sub_city) ||
                is_null($invoice->customer->plot_identification) || is_null($invoice->customer->building_number) || is_null($invoice->customer->street_name) || is_null($invoice->customer->tax_no) || strlen($invoice->customer->tax_no) != 15
            ) {
                return "  \n Please enter the full national address and tax number information. Thank you يرجل ادخال بيانات العنوان الوطني و الرقم الضريبيي كاملا وشكرا";

            }

        }



        $total_withot_tax_sum = 0;
        $tax_sum = 0;
        $total_with_tax_sum = 0;
        $invoiceLines = [];



        foreach (InvoiceItem::where("invoice_id", $request)->where('quantity', '!=', 0)->get() as $item) {
            $price_each_element_withoud_tax = number_format(($item->Unit_Price - ($item->Discount_Value / $item->quantity)), 2, '.', '');
            $temp = ($item->Unit_Price - ($item->Discount_Value / $item->quantity)) * $item->quantity;
            $total_withot_tax = number_format($temp, 2, '.', '');
            $temp = (($item->Added_Value)) * $item->quantity;
            $tax = number_format($temp, 2, '.', '');
            $totlal_element = $total_withot_tax + $tax;
            $current_item_tax_rate = ($item->Added_Value > 0) ? 15 : 0; // افترضنا 15، يمكن استبدالها بـ $item->tax_rate لو متوفر
            $taxCategoryCode = ($current_item_tax_rate == 0) ? 'E' : 'S';


            $total_with_tax = number_format($totlal_element, 2, '.', '');
            $total_withot_tax_sum = $total_withot_tax_sum + $total_withot_tax * 1;
            $tax_sum = $tax_sum + $tax;
            $total_with_tax_sum = $total_with_tax_sum + $total_with_tax;
            $rat_tax = number_format($item->tax_rate, 2, '.', '') * 100;

            $itemTaxCategory = (new LineTaxCategory())
                ->setTaxCategory($taxCategoryCode)
                ->setTaxPercentage($rat_tax)
                ->getElement();
            $invoiceLines[] = (new InvoiceLine())
                ->setLineID($item->product_id)
                ->setLineName($item->productData->product_name)
                ->setLineCurrency('SAR')
                ->setLinePrice(number_format($price_each_element_withoud_tax, 2, '.', ''))
                ->setLineQuantity($item->quantity)
                ->setLineSubTotal($total_withot_tax)
                ->setLineTaxTotal($tax)
                ->setLineNetTotal($total_with_tax)
                ->setLineTaxCategories($itemTaxCategory)
                ->setLineDiscountReason('Discount on product')
                ->setLineDiscountAmount(0)
                ->getElement();
        }
        //  return $invoiceLines;
        $total_withot_tax_sum = number_format($total_withot_tax_sum, 2, '.', '');
        $tax_sum = number_format($tax_sum, 2, '.', '');
        ;
        $total_with_tax_sum = number_format($total_with_tax_sum, 2, '.', '');



        // - If Invoice type is standard invoice (B2B) you must provide full buyer information as below :



        // clients data
        $client = (new Client())
            ->setVatNumber($invoice->customer->tax_no)
            ->setStreetName($invoice->customer->street_name)
            ->setBuildingNumber($invoice->customer->building_number)
            ->setPlotIdentification($invoice->customer->plot_identification)
            ->setSubDivisionName($invoice->customer->sub_city)
            ->setCityName($invoice->customer->address)
            ->setPostalNumber($invoice->customer->postcode)
            ->setCountryName('SA')
            ->setClientName($invoice->customer->name);


        //  return $client->getElement();
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
        // return $supplier->getElement();

        $delivery = (new Delivery())
            ->setDeliveryDateTime($invoice->issue_date);

        $paymentType = (new PaymentType())
            ->setPaymentType('10');

        $returnReason = (new ReturnReason())
            ->setReturnReason('SET_RETURN_REASON');

        $previous_hash = (new PIH())
            ->setPIH($previous_invoice);  // note this value it from step 3 , 4
        // $billingReference = (new BillingReference())
        // ->setBillingReference('23'); // note this used when type credit or debit this value of parent invoice id

        $additionalDocumentReference = (new AdditionalDocumentReference())
            ->setInvoiceID($setting->Invoice_count + 1); // note this value it from step 1

        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setTotalCurrency('SAR')
            ->setLineExtensionAmount($total_withot_tax_sum)
            ->setTaxExclusiveAmount($total_withot_tax_sum)
            ->setTaxInclusiveAmount($total_with_tax_sum)
            ->setAllowanceTotalAmount(0)
            ->setPrepaidAmount(0)
            ->setPayableAmount($total_with_tax_sum);

        $taxesTotal = (new TaxesTotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxTotal($tax_sum);
        $current_item_tax_rate = ($tax_sum > 0) ? 15 : 0; // افترضنا 15، يمكن استبدالها بـ $item->tax_rate لو متوفر
        $taxCategoryCode = ($current_item_tax_rate == 0) ? 'E' : 'S';

        $taxSubtotal = (new TaxSubtotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxableAmount($total_withot_tax_sum)
            ->setTaxAmount($tax_sum)
            ->setTaxCategory($taxCategoryCode)
            ->setTaxPercentage($rat_tax)
            ->getElement();


        $allowanceCharge = (new AllowanceCharge())
            ->setAllowanceChargeCurrency('SAR')
            ->setAllowanceChargeIndex('1')
            ->setAllowanceChargeAmount(0)
            ->setAllowanceChargeTaxCategory($taxCategoryCode)
            ->setAllowanceChargeTaxPercentage($rat_tax)
            ->getElement();
        if (strlen($invoice->customer->tax_no) != 15) {

            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
                ->setZatcaLang('en')
                ->setInvoiceNumber($request)
                ->setInvoiceUuid($invoice->invoiceUUid) // this value from step 6
                ->setInvoiceIssueDate($invoice->issue_date)
                ->setInvoiceIssueTime($invoice->issue_time)
                ->setInvoiceType(($invoice->document_type == 'simplified') ? '0200000' : '0100000', $invoice->invoice_type)
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                //->setInvoiceBillingReference($billingReference)  use this when document type is credit or debit
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceupplier($supplier)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                //->setInvoiceReturnReason($returnReason) use this when document type is credit or debit
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal($taxSubtotal)
                ->setInvoiceAllowanceCharges($allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($setting->production_certificate)
                ->setPrivateKeyEncoded($setting->private_key)
                ->setCertificateSecret($setting->production_secret)
                ->sendDocument(true); // when you use production certifiacte for (simulation , core) dont forget set sendDocument(true)
            //   return $response;


        } else {
            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
                ->setZatcaLang('en')
                ->setInvoiceNumber($request)
                ->setInvoiceUuid($invoice->invoiceUUid) // this value from step 6
                ->setInvoiceIssueDate($invoice->issue_date)
                ->setInvoiceIssueTime($invoice->issue_time)
                ->setInvoiceType(($invoice->document_type == 'simplified') ? '0200000' : '0100000', $invoice->invoice_type)
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                //->setInvoiceBillingReference($billingReference)  use this when document type is credit or debit
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceupplier($supplier)
                ->setInvoiceClient($client)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                //->setInvoiceReturnReason($returnReason) use this when document type is credit or debit
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal($taxSubtotal)
                ->setInvoiceAllowanceCharges($allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($setting->production_certificate)
                ->setPrivateKeyEncoded($setting->private_key)
                ->setCertificateSecret($setting->production_secret)
                ->sendDocument(true); // when you use production certifiacte for (simulation , core) dont forget set sendDocument(true)
        }

        if ($response['success']) {
            Setting::where('branchs_id', 1)->update([
                'previous_hash_invoice' => $response['hash'],
                'Invoice_count' => $setting->Invoice_count + 1
            ]);
            Invoice::find($request)->update(
                [
                    'signing_time' => \Carbon\Carbon::now(),
                    'hash' => $response['hash'],
                    'xml' => $response['xml'],
                    'sent_to_zatca_status' => "PASS",
                    'sent_to_zatca' => 1,
                    'clearedInvoice' => $invoice->document_type == 'simplified' ? NULL : $response['response']->clearedInvoice

                ]
            );

            return 1;
        } else {
            return $response;

            return '|||' . $response['response']->reportingStatus . '   ||| ERROR MESSAGE    :-   ' . $response['response']->validationResults->errorMessages[0]->message;
        }
    }











    function sendzatca_fromsale($request)
    {
        // - Then Call Invoice required data from database depend on your query statment and required company id

        $setting = Setting::where('branchs_id', 1)->first();

        ### Zatca Integration have two steps second : Send Invoice to zatca Step example :
        // - Add below line to start of controller file which used
        $previous_invoice = null;
        $invoice = Invoice::find($request);
        $invprevious = $setting->previous_hash_invoice;

        if ($invprevious == null) {
            $previous_invoice = 'X+zrZv/IbzjZUnhsbWlsecLbwjndTpG0ZynXOif7V+k=';

        } else {
            $previous_invoice = $invprevious;
        }

        $myuuid = Uuid::uuid4();

        Invoice::find($request)->update(
            [
                'invoice_counter' => $setting->Invoice_count + 1,
                'invoice_number' => $invoice->id,
                'invoiceUUid' => $myuuid,
                'document_type' => strlen($invoice->customer->tax_no) != 15 ? 'simplified' : 'standard',
                'invoice_type' => "388", //  "388" NORMAL INVOICE , "383"  DEBIT_NOTE , "381" CREDIT_NOTE
                'issue_date' => substr($invoice->created_at, 0, 10),
                'issue_time' => substr($invoice->created_at, 11),
            ]
        );


        $invoice = Invoice::find($request);

        if ($invoice->document_type == 'standard') {
            if (
                is_null($invoice->customer->name) || is_null($invoice->customer->postcode) || is_null($invoice->customer->address) || is_null($invoice->customer->sub_city) ||
                is_null($invoice->customer->plot_identification) || is_null($invoice->customer->building_number) || is_null($invoice->customer->street_name) || is_null($invoice->customer->tax_no) || strlen($invoice->customer->tax_no) != 15
            ) {
                return "  \n Please enter the full national address and tax number information. Thank you يرجل ادخال بيانات العنوان الوطني و الرقم الضريبيي كاملا وشكرا";

            }

        }



        $total_withot_tax_sum = 0;
        $tax_sum = 0;
        $total_with_tax_sum = 0;
        $invoiceLines = [];



        foreach (InvoiceItem::where("invoice_id", $request)->where('quantity', '!=', 0)->get() as $item) {
            $price_each_element_withoud_tax = number_format(($item->Unit_Price - ($item->Discount_Value / $item->quantity)), 2, '.', '');
            $temp = ($item->Unit_Price - ($item->Discount_Value / $item->quantity)) * $item->quantity;
            $total_withot_tax = number_format($temp, 2, '.', '');
            $temp = (($item->Added_Value)) * $item->quantity;
            $tax = number_format($temp, 2, '.', '');
            $totlal_element = $total_withot_tax + $tax;
            $current_item_tax_rate = ($item->Added_Value > 0) ? 15 : 0; // افترضنا 15، يمكن استبدالها بـ $item->tax_rate لو متوفر
            $taxCategoryCode = ($current_item_tax_rate == 0) ? 'E' : 'S';


            $total_with_tax = number_format($totlal_element, 2, '.', '');
            $total_withot_tax_sum = $total_withot_tax_sum + $total_withot_tax * 1;
            $tax_sum = $tax_sum + $tax;
            $total_with_tax_sum = $total_with_tax_sum + $total_with_tax;
            $rat_tax = number_format($item->tax_rate, 2, '.', '') * 100;

            $itemTaxCategory = (new LineTaxCategory())
                ->setTaxCategory($taxCategoryCode)
                ->setTaxPercentage($rat_tax)
                ->getElement();
            $invoiceLines[] = (new InvoiceLine())
                ->setLineID($item->product_id)
                ->setLineName($item->productData->product_name)
                ->setLineCurrency('SAR')
                ->setLinePrice(number_format($price_each_element_withoud_tax, 2, '.', ''))
                ->setLineQuantity($item->quantity)
                ->setLineSubTotal($total_withot_tax)
                ->setLineTaxTotal($tax)
                ->setLineNetTotal($total_with_tax)
                ->setLineTaxCategories($itemTaxCategory)
                ->setLineDiscountReason('Discount on product')
                ->setLineDiscountAmount(0)
                ->getElement();
        }
        //  return $invoiceLines;
        $total_withot_tax_sum = number_format($total_withot_tax_sum, 2, '.', '');
        $tax_sum = number_format($tax_sum, 2, '.', '');
        ;
        $total_with_tax_sum = number_format($total_with_tax_sum, 2, '.', '');



        // - If Invoice type is standard invoice (B2B) you must provide full buyer information as below :



        // clients data
        $client = (new Client())
            ->setVatNumber($invoice->customer->tax_no)
            ->setStreetName($invoice->customer->street_name)
            ->setBuildingNumber($invoice->customer->building_number)
            ->setPlotIdentification($invoice->customer->plot_identification)
            ->setSubDivisionName($invoice->customer->sub_city)
            ->setCityName($invoice->customer->address)
            ->setPostalNumber($invoice->customer->postcode)
            ->setCountryName('SA')
            ->setClientName($invoice->customer->name);


        //  return $client->getElement();
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
        // return $supplier->getElement();

        $delivery = (new Delivery())
            ->setDeliveryDateTime($invoice->issue_date);

        $paymentType = (new PaymentType())
            ->setPaymentType('10');

        $returnReason = (new ReturnReason())
            ->setReturnReason('SET_RETURN_REASON');

        $previous_hash = (new PIH())
            ->setPIH($previous_invoice);  // note this value it from step 3 , 4
        // $billingReference = (new BillingReference())
        // ->setBillingReference('23'); // note this used when type credit or debit this value of parent invoice id

        $additionalDocumentReference = (new AdditionalDocumentReference())
            ->setInvoiceID($setting->Invoice_count + 1); // note this value it from step 1

        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setTotalCurrency('SAR')
            ->setLineExtensionAmount($total_withot_tax_sum)
            ->setTaxExclusiveAmount($total_withot_tax_sum)
            ->setTaxInclusiveAmount($total_with_tax_sum)
            ->setAllowanceTotalAmount(0)
            ->setPrepaidAmount(0)
            ->setPayableAmount($total_with_tax_sum);

        $taxesTotal = (new TaxesTotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxTotal($tax_sum);
        $current_item_tax_rate = ($tax_sum > 0) ? 15 : 0; // افترضنا 15، يمكن استبدالها بـ $item->tax_rate لو متوفر
        $taxCategoryCode = ($current_item_tax_rate == 0) ? 'E' : 'S';

        $taxSubtotal = (new TaxSubtotal())
            ->setTaxCurrencyCode('SAR')
            ->setTaxableAmount($total_withot_tax_sum)
            ->setTaxAmount($tax_sum)
            ->setTaxCategory($taxCategoryCode)
            ->setTaxPercentage($rat_tax)
            ->getElement();


        $allowanceCharge = (new AllowanceCharge())
            ->setAllowanceChargeCurrency('SAR')
            ->setAllowanceChargeIndex('1')
            ->setAllowanceChargeAmount(0)
            ->setAllowanceChargeTaxCategory($taxCategoryCode)
            ->setAllowanceChargeTaxPercentage($rat_tax)
            ->getElement();
        if (strlen($invoice->customer->tax_no) != 15) {

            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
                ->setZatcaLang('en')
                ->setInvoiceNumber($request)
                ->setInvoiceUuid($invoice->invoiceUUid) // this value from step 6
                ->setInvoiceIssueDate($invoice->issue_date)
                ->setInvoiceIssueTime($invoice->issue_time)
                ->setInvoiceType(($invoice->document_type == 'simplified') ? '0200000' : '0100000', $invoice->invoice_type)
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                //->setInvoiceBillingReference($billingReference)  use this when document type is credit or debit
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceupplier($supplier)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                //->setInvoiceReturnReason($returnReason) use this when document type is credit or debit
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal($taxSubtotal)
                ->setInvoiceAllowanceCharges($allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($setting->production_certificate)
                ->setPrivateKeyEncoded($setting->private_key)
                ->setCertificateSecret($setting->production_secret)
                ->sendDocument(true); // when you use production certifiacte for (simulation , core) dont forget set sendDocument(true)
            //   return $response;


        } else {
            $response = (new InvoiceGenerator())
                ->setZatcaEnv($setting->is_production ? 'core' : 'simulation')
                ->setZatcaLang('en')
                ->setInvoiceNumber($request)
                ->setInvoiceUuid($invoice->invoiceUUid) // this value from step 6
                ->setInvoiceIssueDate($invoice->issue_date)
                ->setInvoiceIssueTime($invoice->issue_time)
                ->setInvoiceType(($invoice->document_type == 'simplified') ? '0200000' : '0100000', $invoice->invoice_type)
                ->setInvoiceCurrencyCode('SAR')
                ->setInvoiceTaxCurrencyCode('SAR')
                //->setInvoiceBillingReference($billingReference)  use this when document type is credit or debit
                ->setInvoiceAdditionalDocumentReference($additionalDocumentReference)
                ->setInvoicePIH($previous_hash)
                ->setInvoiceupplier($supplier)
                ->setInvoiceClient($client)
                ->setInvoiceDelivery($delivery)
                ->setInvoicePaymentType($paymentType)
                //->setInvoiceReturnReason($returnReason) use this when document type is credit or debit
                ->setInvoiceLegalMonetaryTotal($legalMonetaryTotal)
                ->setInvoiceTaxesTotal($taxesTotal)
                ->setInvoiceTaxSubTotal($taxSubtotal)
                ->setInvoiceAllowanceCharges($allowanceCharge)
                ->setInvoiceLines(...$invoiceLines)
                ->setCertificateEncoded($setting->production_certificate)
                ->setPrivateKeyEncoded($setting->private_key)
                ->setCertificateSecret($setting->production_secret)
                ->sendDocument(true); // when you use production certifiacte for (simulation , core) dont forget set sendDocument(true)
        }

        if ($response['success']) {
            Setting::where('branchs_id', 1)->update([
                'previous_hash_invoice' => $response['hash'],
                'Invoice_count' => $setting->Invoice_count + 1
            ]);
            Invoice::find($request)->update(
                [
                    'signing_time' => \Carbon\Carbon::now(),
                    'hash' => $response['hash'],
                    'xml' => $response['xml'],
                    'sent_to_zatca_status' => "PASS",
                    'sent_to_zatca' => 1,
                    'clearedInvoice' => $invoice->document_type == 'simplified' ? NULL : $response['response']->clearedInvoice

                ]
            );

            return 1;
        } else {
            return $response;

            return '|||' . $response['response']->reportingStatus . '   ||| ERROR MESSAGE    :-   ' . $response['response']->validationResults->errorMessages[0]->message;
        }
    }

    function dwonloadxml($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $xml = new DOMDocument;
        // Safe-fallback context matching cleared vs standard xml parameters
        $rawXml = base64_decode($invoice->clearedInvoice ?? $invoice->xml, true);

        if (!$rawXml) {
            return "Failed to decode XML content.";
        }

        $xml->loadXML($rawXml);
        $xml->formatOutput = true;

        $namefile = "invoice_" . $invoice->id . '_' . date("Y_m_d") . 'T' . date("H_i") . ".xml";
        $filepath = public_path('result.xml');
        $xml->save($filepath);

        $headers = [
            'Content-Type' => 'application/xml',
        ];

        return response()->download($filepath, $namefile, $headers);
    }



    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'branch', 'creator'])->latest();

        if ($request->filled('invoice_number')) {
            $invoiceNumber = $request->string('invoice_number');
            $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('issue_date', $request->date('date'));
        }

        $Invoice = $query->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('Invoice', 'customers'));
    }

public function create(Request $request)
{
    $customers = Customer::orderBy('name')->get();
    $branches = Branch::orderBy('name')->get();

    $maxDiscountPercent = DB::table('employee_discount_settings')
        ->where('user_id', auth()->user()->id)
        ->where('branchs_id', auth()->user()->branch_id)
        ->value('max_discount') ?? 0;

    // لو جاية من شاشة "المسودات السابقة" (?draft_id=xx) بنجيب المسودة
    // ونمررها للفورم عشان تتملى بيها كل الحقول (العميل، طريقة الدفع،
    // البنود...) - المسودة مالهاش رقم فاتورة رسمي لسه، هياخد لما تتأكد.
    $draft = null;
    if ($request->filled('draft_id')) {
        $draft = DraftInvoice::find($request->input('draft_id'));

        // إصلاح تعويضي للمسودات اللي اتحفظت قبل ما نضيف rules لاسم/كود/سعر
        // شراء المنتج في store() - كانت بتتخزن من غير الحقول دي خالص (كانت
        // بتتشال أوتوماتيك لإن مكانش ليها validation rule)، فكانت بتفضل
        // فاضية لما تتفتح المسودة تاني. هنا بنجيب أي بيانات ناقصة من جدول
        // المنتجات مباشرة (بالـ product_id) عشان الشاشة تبان صح حتى مع
        // مسودات قديمة اتحفظت قبل الإصلاح.
        if ($draft && !empty($draft->items)) {
            $productIds = collect($draft->items)->pluck('product_id')->filter()->unique();
            $productsById = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $draft->items = collect($draft->items)->map(function ($item) use ($productsById) {
                if (empty($item['name']) || empty($item['code'])) {
                    $product = $productsById->get($item['product_id'] ?? null);
                    if ($product) {
                        $item['name'] = $item['name'] ?? $product->name;
                        $item['code'] = $item['code'] ?? $product->code;
                        $item['purchase_price'] = $item['purchase_price'] ?? $product->purchase_price;
                    }
                }
                return $item;
            })->all();
        }
    }

    return view('invoices.create', compact('customers', 'branches', 'maxDiscountPercent', 'draft'));
}

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'branch', 'items.product', 'creator', 'returns']);

        // Map your new model attributes to the keys your Blade template uses
        $data = [
            'invoiceData' => $invoice,
            'totatextlriyales' => '', // Replace with your number-to-words logic if needed
            'totatextlrihalala' => '',
        ];
        // If your view still references old column names like $invoice->cashamount,
        // you can either update the Blade file or handle compatibility attributes.
        return view('invoices.show', compact('data'));
    }

    /**
     * تحميل PDF للفاتورة مباشرة - محتاج مكتبة barryvdh/laravel-dompdf:
     *   composer require barryvdh/laravel-dompdf
     */
    public function downloadPdf(Invoice $invoice)
    {
        $pdf = $this->buildInvoicePdf($invoice);

        return $pdf->download('invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf');
    }

    /**
     * اسم بديل (alias) - لو الراوت عندك بيستخدم ->pdf() بدل ->downloadPdf()
     * (زي الخطأ اللي ظهرلك: "Call to undefined method ...::pdf()")، الدالة
     * دي بتخليه يشتغل برضه من غير ما تغيّري اسم الراوت.
     */
    public function pdf(Invoice $invoice)
    {
        return $this->downloadPdf($invoice);
    }

    /**
     * نفس الـ PDF بس من غير تحميل إجباري (بيتفتح في المتصفح) - ده اللي
     * بيتفتح لما العميل يدوس على رابط واتساب. الراوت بتاعها لازم يكون
     * برا middleware('auth') وجوه middleware('signed') عشان العميل يقدر
     * يفتحها من غير ما يكون مسجل دخول في النظام، وفي نفس الوقت محدش يقدر
     * يشوف فاتورة غير بتاعته من غير التوقيع (signature) الصحيح في اللينك.
     */
    public function publicPdf(Invoice $invoice)
    {
        $pdf = $this->buildInvoicePdf($invoice);

        return $pdf->stream('invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf');
    }

    protected function buildInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'branch', 'items.product']);

        $html = view('invoices.pdf', compact('invoice'))->render();

        // dompdf مبيدعمش تشكيل الحروف العربية (وصل الحروف ببعض بشكلها
        // الصحيح: أول/وسط/آخر/منفصل) ولا اتجاه الكتابة (RTL) تلقائيًا -
        // من غيرها الحروف العربية بتطلع منفصلة عن بعض ومقلوبة (زي ما
        // شفتيه في اللقطة). مكتبة ArPHP بتظبط شكل النص العربي بس (من
        // غير ما تلمس الإنجليزي أو الأرقام أو أي HTML tags) قبل ما نسلّم
        // الصفحة لـ dompdf يحوّلها PDF.
        //   composer require khaled.alshamaa/arphp
        if (class_exists(\ArPHP\I18N\Arabic::class)) {
            $arabic = new \ArPHP\I18N\Arabic();
            $html = $arabic->utf8Glyphs($html);
        }

        return Pdf::loadHTML($html)->setPaper('a4');
    }

    /**
     * البحث عن منتجات لإضافتها لسطور الفاتورة (يستخدم من فورم إنشاء الفاتورة).
     * دلوقتي بيفلتر على فرع المستخدم فقط (لو اتبعت branch_id، وإلا بيرجع
     * لفرع المستخدم المسجل دخوله كـ fallback).
     */

    public function edit(Invoice $invoice)
    {
        // يمكنك جلب البيانات التي تحتاجها في صفحة التعديل مثل العملاء والمنتجات
        $customers = \App\Models\Customer::all();

        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // منطق تحديث الفاتورة هنا

        return redirect()->route('invoices.index')->with('success', 'تم تحديث الفاتورة بنجاح');
    }
    public function searchProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->where('status', 'active')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'code', 'sale_price', 'purchase_price', 'stock_quantity']);

        return response()->json($products);
    }

    /**
     * قائمة منتجات مقسّمة صفحات (20 في كل صفحة) لمودال "اختيار منتج" في
     * شاشة إنشاء الفاتورة - زي المودال في النظام القديم (بحث + صفحات تتحمل
     * بالـ ajax بدل ما تتحمل كل المنتجات مرة واحدة).
     * دلوقتي بيفلتر على فرع المستخدم فقط.
     */
    public function pickProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->with('branch:id,name')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $products->getCollection()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'branch_name' => $p->branch?->name,
                    'location' => $p->location,
                    'stock_quantity' => $p->stock_quantity,
                    'purchase_price' => $p->purchase_price,
                    'sale_price' => $p->sale_price,
                    'average_cost' => $p->average_cost,
                    'notes' => $p->notes,
                    'reference_number' => $p->reference_number,
                ];
            })->values(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * إضافة عميل سريعة من فورم الفاتورة (بدون مغادرة الصفحة).
     */
    public function quickStoreCustomer(Request $request)
    {
        // 1. التحقق من صحة البيانات (Validate)
        $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'name_en'                 => ['nullable', 'string', 'max:255'],
            'phone'                   => ['required', 'string', 'max:255'],
            'email'                   => ['nullable', 'email', 'max:255'],
            'tax_no'                  => ['nullable', 'numeric'], // أو Tax_Number حسب فورم الإرسال
            'balance'                 => ['nullable', 'numeric'],
            'credit_limit'            => ['nullable', 'numeric', 'min:0'],
            'grace_period_in_days'    => ['nullable', 'numeric'],
            'street_name'             => ['nullable', 'string', 'max:255'],
            'building_number'         => ['nullable', 'string', 'max:255'],
            'plot_identification'     => ['nullable', 'string', 'max:255'],
            'postcode'                => ['nullable', 'string', 'max:255'],
            'CRN'                     => ['nullable', 'string', 'max:255'],
            'notes'                   => ['nullable', 'string'],
        ]);

        // 2. تنفيذ العملية داخل Transaction لضمان السلامة المالية وقاعدة البيانات
        $customer = DB::transaction(function () use ($request) {

            // إنشاء العميل الجديد
            $newCustomer = Customer::create([
                'name'                 => $request->name,
                'name_en'              => $request->name_en ?? null,
                'comp_name'            => $request->company_name ?? $request->name,
                'tax_no'               => $request->tax_no ?? $request->input('TaxـNumber', 0),
                'Balance'              => $request->balance ?? $request->credit_limit ?? 0,
                'phone'                => $request->phone ?? '05----------',
                'email'                => $request->email ?? 'Email@gmail.com',
                'notes'                => $request->notes ?? $request->product_notes ?? "لا توجد ملاحظات",
                'Limit_credit'         => $request->credit_limit ?? 0,
                'grace_period_in_days' => $request->grace_period_in_days ?? 0,
                'street_name'          => $request->street_name ?? $request->StreetName ?? null,
                'building_number'      => $request->building_number ?? $request->buildnumber ?? null,
                'plot_identification'  => $request->plot_identification ?? null,
                'address'              => $request->city ?? "Client Address",
                'sub_city'             => $request->sub_city ?? "Client Address",
                'postcode'             => $request->postcode ?? null,
                'CRN'                  => $request->CRN ?? null,
            ]);

            // توليد رقم الحساب التالي في شجرة الحسابات للعملاء (Account Type: 1, Parent: 2)
            $nextAccountNumber = FinancialAccount::where('account_type', 1)
                ->where('orginal_type', 1)
                ->max('account_number') + 1;

            // إنشاء الحساب المالي المرتبط بالعميل في شجرة الحسابات
            FinancialAccount::create([
                'name'                  => $request->name,
                'account_type'          => 1,
                'parent_account_number' => 2, // الحساب الأب للعملاء
                'account_number'        => $nextAccountNumber,
                'start_balance'         => 0,
                'current_balance'       => 0,
                'start_balance_status'  => 3,
                'other_table_FK'        => NULL,
                'notes'                 => NULL,
                'added_by'              => Auth::id() ?? 1,
                'updated_by'            => NULL,
                'com_code'              => 1,
                'date'                  => Carbon::now('Asia/Riyadh'),
                'active'                => 1,
                'is_parent'             => 0,
                'orginal_id'            => $newCustomer->id,
                'orginal_type'          => 1, // نوع الأصل يعبر عن عميل
            ]);

            return $newCustomer;
        });

        // 3. طريقة الإرجاع (سواء كنت تفضل JSON أو Redirect)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $customer->id,
                'name' => $customer->name,
                'message' => __('تم اضافة العميل بنجاح')
            ]);
        }

        $message = app()->getLocale() == 'ar' ? 'تم اضافة العميل بنجاح' : 'Client added successfully';
        session()->flash('newcustomer', $message);

        return redirect()->back();
    }

    /**
     * إضافة منتج سريعة من فورم الفاتورة (بدون مغادرة الصفحة).
     * المنتج بيتربط تلقائيًا بفرع المستخدم الحالي (نفس منطق
     * ProductController@store، بس بدون مغادرة شاشة الفاتورة).
     */
    public function quickStoreProduct(Request $request)
    {
        $branchId = $request->input('branch_id') ?? Auth::user()?->branch_id;
        $request->merge(['branch_id' => $branchId, 'status' => 'active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = Auth::id();

        $product = Product::create($validated);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sale_price' => $product->sale_price,
            'purchase_price' => $product->purchase_price,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }

    /**
     * تحويل بيانات فاتورة (سواء جايه من فورم إنشاء فاتورة عادي، أو من
     * اعتماد مسودة مباشرة) لفاتورة رسمية فعلية: بتاخد رقم، بتتسجل في
     * جدول invoices، بتتخصم من المخزون، وبتتسجل كل القيود المحاسبية
     * المرتبطة بيها. مستخدمة من store() (لما تدوسي "حفظ الفاتورة") ومن
     * approveDraft() (لما تدوسي "اعتماد" على مسودة من غير ما تفتحيها).
     */
    protected function finalizeInvoice(array $validated): Invoice
    {
        return DB::transaction(function () use ($validated) {
    // إعادة حساب الإجماليات من السيرفر لضمان الدقة
    $subtotal = 0;
    $taxTotal = 0;
    $discountTotal = 0;
    $totalCost = 0; // لحساب تكلفة البضاعة المباعة

    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);
        $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
        $subtotal += $lineSubtotal;
        $taxTotal += $lineSubtotal * $item['tax_rate'];
        $discountTotal += $item['discount_amount'] ?? 0;

        // حساب التكلفة الإجمالية بناءً على سعر الشراء للمنتج
        if ($product) {
            $totalCost += ($product->purchase_price ?? 0) * $item['quantity'];
        }
    }

    $invoiceLevelDiscount = min($validated['invoice_level_discount'] ?? 0, $subtotal + $taxTotal);
    $grandTotal = $subtotal + $taxTotal - $invoiceLevelDiscount;
    $totalQuantity = array_sum(array_column($validated['items'], 'quantity'));

    $cashAmount = 0;
    $bankAmount = 0;
    $creditAmount = 0;

    switch ($validated['payment_method']) {
        case 'cash':
            $cashAmount = $grandTotal;
            break;
        case 'bank_transfer':
        case 'card':
            $bankAmount = $grandTotal;
            break;
        case 'credit':
            $creditAmount = $grandTotal;
            break;
        case 'split':
            $cashAmount = $validated['cash_amount'] ?? 0;
            $bankAmount = $validated['bank_amount'] ?? 0;
            $creditAmount = max(0, $grandTotal - ($cashAmount + $bankAmount));
            break;
    }

    // 1. إنشاء الفاتورة
    $invoice = Invoice::create([
        'customer_id' => $validated['customer_id'],
        'branch_id' => $validated['branch_id'],
        'created_by' => Auth::id(),
        'subtotal' => $subtotal,
        'tax_amount' => $taxTotal,
        'discount_amount' => $discountTotal,
        'total_quantity' => $totalQuantity,
        'payment_method' => $validated['payment_method'],
        'has_multiple_payment_methods' => $validated['payment_method'] === 'split',
        'cash_amount' => $cashAmount,
        'bank_amount' => $bankAmount,
        'credit_amount' => $creditAmount,
        'is_finalized' => $validated['is_finalized'],
        'note' => $validated['note'] ?? null,
        'purchase_order_number' => $validated['purchase_order_number'] ?? null,
        'invoice_level_discount' => $invoiceLevelDiscount,
        'issue_date' => now()->toDateString(),
        'issue_time' => now()->toTimeString(),
    ]);

    $invoice->update(['invoice_number' => (string) $invoice->id]);

    // 2. إنشاء تفاصيل الفاتورة وتحديث المخزون
    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);
        $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
        $lineTax = $lineSubtotal * $item['tax_rate'];

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $item['product_id'],
            'branch_id' => $validated['branch_id'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'discount_amount' => $item['discount_amount'] ?? 0,
            'tax_amount' => $lineTax,
            'tax_rate' => $item['tax_rate'],
            'product_name_snapshot' => $product?->name,
            'is_finalized' => $validated['is_finalized'],
            'created_by' => Auth::id(),
            'remaining_quantity' => $item['quantity'],
        ]);

        if ($product && $validated['is_finalized']) {
            $product->decrement('stock_quantity', $item['quantity']);
            $product->increment('total_sold', $item['quantity']);
        }
    }

    // 3. القيود المحاسبية والحركات المالية (في حال كانت الفاتورة معتمدة)
    if ($validated['is_finalized'] && $grandTotal > 0) {
        $confirmInvoice = $invoice;
        $customerId = $validated['customer_id'];
        $customerData = Customer::find($customerId);

        // تجهيز بيانات المبالغ لتتوافق مع القيود المضافة
        $pData = [
            'cashamount' => $cashAmount,
            'Bank_transfer' => ($validated['payment_method'] === 'bank_transfer' || $validated['payment_method'] === 'card') ? $bankAmount : 0,
            'bankamount' => ($validated['payment_method'] === 'split') ? $bankAmount : 0,
            'creaditamount' => $creditAmount,
            'created_at' => Carbon::now('Asia/Riyadh'),
        ];

        // أ. القيود المحاسبية لـ Cash
        if ($pData['cashamount']) {
            $financialAccount = FinancialAccount::where('parent_account_number', 5)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($financialAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $financialAccount->id,
                    'recive_amount' => $pData['cashamount'],
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $financialAccount->current_balance + $pData['cashamount'],
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $pData['cashamount'],
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }

        // ب. القيود المحاسبية للشبكة والتحويل البنكي
        $totalBank = $pData['Bank_transfer'] + $pData['bankamount'];
        if ($totalBank) {
            $financialAccount = FinancialAccount::where('parent_account_number', 4)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($financialAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $financialAccount->id,
                    'recive_amount' => $totalBank,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $financialAccount->current_balance + $totalBank,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $totalBank,
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }

        // ج. تحديث حساب ضريبة القيمة المضافة والإيرادات
        $totalValue = $pData['Bank_transfer'] + $pData['creaditamount'] + $pData['bankamount'] + $pData['cashamount'];
        $vatValue = $totalValue - ($totalValue * 100 / 115);
        $netRevenue = $totalValue * 100 / 115;

        // حساب الضريبة (102)
        $vatAccount = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
        if ($vatAccount) {
            $vatAccount->update([
                'current_balance' => $vatAccount->current_balance + $vatValue,
                'creditor_current' => $vatAccount->creditor_current + $vatValue,
            ]);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $vatAccount->id,
                'recive_amount' => $vatValue,
                'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                'pay_method' => $validated['payment_method'],
                'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                'currentblance' => $vatAccount->current_balance,
                'Pay_Method_Name' => ucfirst($validated['payment_method']),
                'created_at' => $pData['created_at'],
                'updated_at' => Carbon::now('Asia/Riyadh'),
                'creditor' => $vatValue,
                'vat' => 1,
                'name' => $customerData->name ?? '',
                'tax' => $customerData->tax_no ?? '',
                'operation_type' => 1,
                'invoice_number' => $confirmInvoice->invoice_number,
            ]);
        }

        // حساب المبيعات والإيرادات (112)
        $revenueAccount = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
        if ($revenueAccount) {
            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $revenueAccount->id,
                'recive_amount' => $netRevenue,
                'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                'pay_method' => $validated['payment_method'],
                'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                'currentblance' => $revenueAccount->current_balance + $netRevenue,
                'Pay_Method_Name' => ucfirst($validated['payment_method']),
                'created_at' => $pData['created_at'],
                'updated_at' => Carbon::now('Asia/Riyadh'),
                'creditor' => $netRevenue,
                'operation_type' => 1,
                'invoice_number' => $confirmInvoice->invoice_number,
            ]);
        }

        // د. حساب تكلفة البضاعة المباعة (183) والمخزن (181)
        if ($totalCost > 0) {
            $costAccount = FinancialAccount::where('parent_account_number', 183)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($costAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $costAccount->id,
                    'recive_amount' => $totalCost,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $costAccount->current_balance + $totalCost,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $totalCost,
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }

            $inventoryAccount = FinancialAccount::where('parent_account_number', 181)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($inventoryAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $inventoryAccount->id,
                    'recive_amount' => $totalCost,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $inventoryAccount->current_balance - $totalCost,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'creditor' => $totalCost,
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }

        // هـ. في حال وجود مبالغ آجلة يتم تحديث رصيد العميل المحاسبي
        if ($pData['creaditamount'] != 0 && $customerData) {
            $customerData->increment('Balance', $pData['creaditamount']);

            $customerFinancialAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerFinancialAccount) {
                $customerFinancialAccount->update([
                    'current_balance' => $customerFinancialAccount->current_balance + $pData['creaditamount'],
                    'debtor_current' => $customerFinancialAccount->debtor_current + $pData['creaditamount'],
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerFinancialAccount->id,
                    'recive_amount' => $pData['creaditamount'],
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $customerFinancialAccount->current_balance,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $pData['creaditamount'],
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }
    }

            return $invoice;
        });
    }

    public function store(Request $request)
    {
        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'draft_id' => ['nullable', 'integer', 'exists:draft_invoices,id'],
            'submission_token' => ['nullable', 'string', 'max:64'],
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,credit,split'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'purchase_order_number' => ['nullable', 'string', 'max:255'],
            'invoice_level_discount' => ['nullable', 'numeric', 'min:0'],
            'is_finalized' => ['required', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
            // اسم/كود/سعر شراء المنتج مش لازمين لحساب الفاتورة نفسها (بيتجابوا
            // من جدول المنتجات مباشرة وقت finalizeInvoice())، لكن لازم يكون
            // ليهم rule هنا عشان Laravel::validate() منكنش بيسيبهم أصلًا -
            // من غير rule، أي مفتاح متعرفش عليه validate() بيتشال تلقائيًا من
            // $validated["items"]. ده كان بالظبط سبب إن اسم/كود المنتج
            // بيختفوا لما نحفظ مسودة، وبيرجعوا فاضيين لما نفتحها تاني.
            'items.*.name' => ['nullable', 'string'],
            'items.*.code' => ['nullable', 'string'],
            'items.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        // حماية من تكرار الإرسال (لو حصل ضغط أكتر من مرة على زرار الحفظ):
        // كل تحميل لصفحة إنشاء الفاتورة بيجيله توكن عشوائي ثابت (submission_token)
        // في حقل مخفي. Cache::add() عملية ذرية (atomic) - أول طلب بس هو
        // اللي بينجح يسجل التوكن، وأي طلب تاني بنفس التوكن (يعني نفس
        // الضغطة المكررة) بيترفض فورًا من غير ما يعمل أي فاتورة تانية،
        // حتى لو الطلبين وصلوا للسيرفر في نفس اللحظة تقريبًا.
        $submissionToken = $validated['submission_token'] ?? null;
        if ($submissionToken) {
            $lockKey = 'invoice_submission_' . $submissionToken;
            if (!Cache::add($lockKey, true, now()->addMinutes(15))) {
                return redirect()->route('invoices.index')
                    ->with('error', __('invoices.duplicate_submission_prevented'));
            }
        }

        // مسودة (زرار "حفظ كمسودة"): منسجلهاش في جدول invoices خالص عشان
        // رقم الفاتورة الرسمي (invoice_number) میتحجزش لفاتورة ممكن
        // تتلغي بعدين - بنسجلها/بنعدلها في جدول draft_invoices المنفصل.
        // لو المسودة دي أصلاً كانت متفتحة من قايمة "المسودات السابقة"
        // (draft_id موجود)، بنعدل على نفس الصف بدل ما نعمل نسخة جديدة.
        if (!$validated['is_finalized']) {
            $draftData = [
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'created_by' => Auth::id(),
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $validated['cash_amount'] ?? 0,
                'bank_amount' => $validated['bank_amount'] ?? 0,
                'note' => $validated['note'] ?? null,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'invoice_level_discount' => $validated['invoice_level_discount'] ?? 0,
                'items' => $validated['items'],
            ];

            if (!empty($validated['draft_id'])) {
                DraftInvoice::where('id', $validated['draft_id'])->update($draftData);
            } else {
                DraftInvoice::create($draftData);
            }

            return redirect()->route('invoices.drafts.index')
                ->with('success', __('invoices.draft_saved_successfully'));
        }

        $invoice = $this->finalizeInvoice($validated);

        // لو الفاتورة دي كانت أصلاً مسودة اتفتحت من "المسودات السابقة"
        // (draft_id) وبقت دلوقتي فاتورة حقيقية معتمدة، نمسح المسودة
        // عشان ميفضلش نسخة مكررة في قايمة المسودات.
        if (!empty($validated['draft_id'])) {
            DraftInvoice::where('id', $validated['draft_id'])->delete();
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('invoices.created_successfully'));
    }

    /**
     * اعتماد مسودة كفاتورة رسمية مباشرة من قايمة "المسودات السابقة" -
     * من غير ما تفتحيها الأول في شاشة إنشاء الفاتورة. بتاخد بيانات
     * المسودة زي ما هي، وبتعمل بيها بالظبط نفس اللي بيحصل لما تدوسي
     * "حفظ الفاتورة" (فاتورة رسمية + رقم + كل القيود المحاسبية)،
     * وبعدين بتمسح المسودة.
     */
    public function approveDraft(DraftInvoice $draft)
    {
        $data = [
            'customer_id' => $draft->customer_id,
            'branch_id' => $draft->branch_id,
            'payment_method' => $draft->payment_method,
            'cash_amount' => $draft->cash_amount,
            'bank_amount' => $draft->bank_amount,
            'note' => $draft->note,
            'purchase_order_number' => $draft->purchase_order_number,
            'invoice_level_discount' => $draft->invoice_level_discount,
            'items' => $draft->items,
            // finalizeInvoice() بتفترض إن المفتاح ده موجود دايمًا (زي ما
            // بيجيله من store() جاي من حقل is_finalized المخفي في الفورم)
            // - هنا بنعتمد المسودة يعني بنفّذها كفاتورة رسمية فورًا.
            'is_finalized' => true,
        ];

        // نفس التحقق اللي بيحصل وقت إنشاء فاتورة عادية - عشان مسودة
        // ناقصة بيانات أساسية (مفيش عميل مثلًا، أو مفيش أصناف) متتحولش
        // لفاتورة رسمية غلط.
        $validator = Validator::make($data, [
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,credit,split'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('invoices.drafts.index')
                ->with('error', __('invoices.draft_missing_data'));
        }

        $invoice = $this->finalizeInvoice($data);

        $draft->delete();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('invoices.created_successfully'));
    }
}
