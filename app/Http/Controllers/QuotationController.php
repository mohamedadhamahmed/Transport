<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use ArPHP\I18N\Arabic; // الاستدعاء الصحيح لمكتبة اللغة العربية
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    /**
     * قايمة كل التسعيرات - فيها فلترة بالعميل والتاريخ والحالة (قيد
     * المراجعة / معتمدة / مرفوضة).
     */
    public function downloadPdf(Quotation $quotation)
    {
        $pdf = $this->buildQuotationPdf($quotation);
        $fileName = 'Quote_No_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($fileName);
    }

    public function pdf(Quotation $quotation)
    {
        $pdf = $this->buildQuotationPdf($quotation);
        $fileName = 'Quote_No_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        // يمكن استخدام stream بدلاً من download إذا كنت ترغب في عرض الملف في المتصفح
        return $pdf->stream($fileName);
    }

    protected function buildQuotationPdf(Quotation $quotation)
    {
        // 1. تحميل العلاقات المرتبطة
        $quotation->load(['customer', 'branch', 'creator', 'approver', 'items.product']);

        // 2. تحويل الـ View إلى نص HTML
        $htmlContent = view('quotations.pdf', compact('quotation'))->render();

        // 3. معالجة النص العربي باستخدام مكتبة ar-php
        $arabic = new Arabic();
        $html = $arabic->utf8Glyphs($htmlContent);

        // 4. إرجاع كائن الـ PDF (بدون output) لكي نستطيع استخدام download أو stream لاحقاً
        return PDF::loadHTML($html);
    }

    public function showQuotation(Quotation $quotation)
{
    $quotation->load(['customer', 'branch', 'creator', 'approver', 'items.product']);
    return view('quotations.print', compact('quotation'));
}

public function downloadQuotationPdf(Quotation $quotation)
{
    $quotation->load(['customer', 'branch', 'creator', 'approver', 'items.product']);

    $html = view('quotations.print', compact('quotation'))
        ->with('isPdf', true)
        ->render();

    $pdf = Browsershot::html($html)
        ->setNodeBinary(env('NODE_BINARY', '/usr/bin/node'))
        ->setNpmBinary(env('NPM_BINARY', '/usr/bin/npm'))
        ->noSandbox()               // ضروري على أغلب السيرفرات Linux
        ->showBackground()          // عشان الألوان والخلفيات تطلع
        ->emulateMedia('print')     // يفعّل قواعد @media print بتاعتك (إخفاء التولبار)
        ->format('A4')
        ->margins(10, 8, 10, 8)
        ->pdf();

    return response($pdf, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="quotation-' . $quotation->id . '.pdf"',
    ]);
}

    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'branch', 'creator'])->latest();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date('date'));
        }

        $quotations = $query->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->get();

        return view('quotations.index', compact('quotations', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        $maxDiscountPercent = DB::table('employee_discount_settings')
            ->where('user_id', Auth::id())
            ->where('branchs_id', Auth::user()->branch_id ?? null)
            ->value('max_discount') ?? 0;

        return view('quotations.create', compact('customers', 'branches', 'maxDiscountPercent'));
    }

    /**
     * كل التسعيرات السابقة لعميل معيّن (مهما كان المنتج) - بتتستخدم بالـ
     * ajax في شاشة "تسعيرة جديدة" (تظهر تلقائي لما تختار العميل) وكمان
     * في شاشة عرض تسعيرة واحدة (تحت البيانات، للمقارنة مع تسعيرات قديمة
     * لنفس العميل).
     */
    public function customerHistory(Request $request, Customer $customer)
    {
        $excludeId = $request->integer('exclude');

        $quotations = Quotation::with(['items'])
            ->where('customer_id', $customer->id)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->latest()
            ->limit(30)
            ->get()
            ->map(function (Quotation $quotation) {
                return [
                    'id' => $quotation->id,
                    'date' => $quotation->created_at->format('Y-m-d'),
                    'status' => $quotation->status,
                    'items_count' => $quotation->items->count(),
                    'items_summary' => $quotation->items->pluck('product_name_snapshot')->filter()->implode('، '),
                    'grand_total' => $quotation->grand_total,
                    'url' => route('quotations.show', $quotation),
                ];
            });

        return response()->json($quotations);
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'branch', 'creator', 'approver', 'items.product', 'invoice']);

        $previousQuotations = Quotation::with(['items'])
            ->where('customer_id', $quotation->customer_id)
            ->where('id', '!=', $quotation->id)
            ->latest()
            ->limit(30)
            ->get();

        return view('quotations.show', compact('quotation', 'previousQuotations'));
    }

    public function store(Request $request)
    {
        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,credit,split'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'purchase_order_number' => ['nullable', 'string', 'max:255'],
            'invoice_level_discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
        ])->validate();

        $quotation = DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            foreach ($validated['items'] as $item) {
                $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
                $subtotal += $lineSubtotal;
                $taxTotal += $lineSubtotal * $item['tax_rate'];
                $discountTotal += $item['discount_amount'] ?? 0;
            }

            $invoiceLevelDiscount = min($validated['invoice_level_discount'] ?? 0, $subtotal + $taxTotal);
            $grandTotal = $subtotal + $taxTotal - $invoiceLevelDiscount;
            $totalQuantity = array_sum(array_column($validated['items'], 'quantity'));

            $quotation = Quotation::create([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'created_by' => Auth::id(),
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $validated['cash_amount'] ?? 0,
                'bank_amount' => $validated['bank_amount'] ?? 0,
                'subtotal' => $subtotal,
                'discount_amount' => $discountTotal,
                'invoice_level_discount' => $invoiceLevelDiscount,
                'tax_amount' => $taxTotal,
                'grand_total' => $grandTotal,
                'total_quantity' => $totalQuantity,
                'note' => $validated['note'] ?? null,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
                $lineTax = $lineSubtotal * $item['tax_rate'];

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $lineTax,
                    'product_name_snapshot' => $product?->name,
                    'product_code_snapshot' => $product?->code,
                    'created_by' => Auth::id(),
                ]);
            }

            return $quotation;
        });

        return redirect()->route('quotations.show', $quotation)
            ->with('success', __('quotations.created_successfully'));
    }

    /**
     * اعتماد التسعيرة: بتتحول لفاتورة رسمية فعلية على طول - نفس بيانات
     * التسعيرة (العميل، الأصناف، طريقة الدفع) بتتبعت لـ
     * InvoiceController@createInvoiceFromData اللي بينادي نفس
     * finalizeInvoice() المستخدمة في شاشة الفواتير العادية، عشان الفاتورة
     * الناتجة تتسجل بنفس القيود المحاسبية وخصم المخزون بالظبط زي أي
     * فاتورة تانية. أي موظف عنده صلاحية دخول الشاشة يقدر يعتمد.
     */
    public function approve(Quotation $quotation)
    {
        if (!$quotation->isPending()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', __('quotations.already_processed'));
        }

        $quotation->load('items');

        $data = [
            'customer_id' => $quotation->customer_id,
            'branch_id' => $quotation->branch_id,
            'payment_method' => $quotation->payment_method,
            'cash_amount' => $quotation->cash_amount,
            'bank_amount' => $quotation->bank_amount,
            'note' => $quotation->note,
            'purchase_order_number' => $quotation->purchase_order_number,
            'invoice_level_discount' => $quotation->invoice_level_discount,
            'is_finalized' => true,
            'items' => $quotation->items->map(function (QuotationItem $item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'tax_rate' => $item->tax_rate,
                ];
            })->all(),
        ];

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
            return redirect()->route('quotations.show', $quotation)
                ->with('error', __('quotations.missing_data'));
        }

        $invoice = app(InvoiceController::class)->createInvoiceFromData($data);

        $quotation->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'invoice_id' => $invoice->id,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', __('quotations.approved_and_converted'));
    }

    public function reject(Quotation $quotation)
    {
        if (!$quotation->isPending()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', __('quotations.already_processed'));
        }

        $quotation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', __('quotations.rejected_successfully'));
    }
}