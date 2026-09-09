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
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\InvoiceReturn;
use App\Services\Zatca\QRCode;
use App\Services\Zatca\QRCodeString;
use App\Services\Zatca\ZatcaConfig;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Models\Tax;
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
        $this->authorize('invoices.view');

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

        // فلتر العميل بقى بحث Ajax حي (منحملش كل جدول العملاء) - كل
        // اللي محتاجينه هنا هو اسم العميل المختار حاليًا في الفلتر لو فيه.
        $selectedCustomerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $customers = $selectedCustomerId
            ? [$selectedCustomerId => optional(Customer::find($selectedCustomerId))->name]
            : [];

        return view('invoices.index', compact('Invoice', 'customers'));
    }

public function create(Request $request)
{
    $this->authorize('invoices.create');

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

    // منحملش كل جدول العملاء هنا (ممكن يبقى فيه عشرات الآلاف من الصفوف) -
    // قايمة اختيار العميل بقت بحث Ajax حي (شوف customer_select في
    // invoices/create.blade.php)، وكل اللي محتاجينه هنا هو اسم العميل
    // المختار مسبقًا لو الفورم جاي من مسودة محفوظة.
    $selectedCustomer = $draft && $draft->customer_id
        ? Customer::where('id', $draft->customer_id)->first(['id', 'name'])
        : null;
    $customers = $selectedCustomer ? [$selectedCustomer->id => $selectedCustomer->name] : [];

    // نسبة الضريبة الافتراضية اللي المفروض تتحدد تلقائيًا في شاشة الإنشاء
    // بتتحدد حسب أولوية الضريبة في جدول الضرائب (Tax::defaultRateFraction())
    // مش رقم ثابت 15% زي ما كان بيحصل قبل كده.
    $defaultTaxRate = Tax::defaultRateFraction();

    return view('invoices.create', compact('customers', 'branches', 'maxDiscountPercent', 'draft', 'defaultTaxRate'));
}

    public function show(Invoice $invoice)
    {
        $this->authorize('invoices.view');

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
        $this->authorize('invoices.view');

        $pdf = $this->buildInvoicePdf($invoice);

        return $pdf->download('invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf');
    }
public function createInvoiceFromData(array $validated): Invoice
{
    return $this->finalizeInvoice($validated);
}
    /**
     * اسم بديل (alias) - لو الراوت عندك بيستخدم ->pdf() بدل ->downloadPdf()
     * (زي الخطأ اللي ظهرلك: "Call to undefined method ...::pdf()")، الدالة
     * دي بتخليه يشتغل برضه من غير ما تغيّر اسم الراوت.
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

        // العربي بيظهر صح من غير أي مكتبة إضافية (ArPHP اتشالت) طالما
        // القالب نفسه بيحدد dir="rtl" صراحة على كل جدول/عنصر، وبيفرض
        // خط DejaVu Sans بـ * { font-family: DejaVu Sans !important; }
        // - بالظبط زي تقنية القالب الشغال عندك (translation.blade.php).
        return Pdf::loadView('invoices.pdf', compact('invoice'))->setPaper('a4');
    }

    /**
     * البحث عن منتجات لإضافتها لسطور الفاتورة (يستخدم من فورم إنشاء الفاتورة).
     * دلوقتي بيفلتر على فرع المستخدم فقط (لو اتبعت branch_id، وإلا بيرجع
     * لفرع المستخدم المسجل دخوله كـ fallback).
     */

    public function edit(Invoice $invoice)
    {
        $this->authorize('invoices.edit');

        if (!$invoice->isEditable()) {
            abort(403, __('invoices.not_editable'));
        }

        $invoice->load(['items.product', 'customer']);

        // قايمة اختيار العميل بقت بحث Ajax حي (منحملش كل جدول العملاء) -
        // كل اللي محتاجينه هنا هو اسم العميل الحالي للفاتورة عشان يظهر
        // كخيار مبدئي في القايمة.
        $customers = [$invoice->customer_id => optional($invoice->customer)->name];

        $maxDiscountPercent = DB::table('employee_discount_settings')
            ->where('user_id', auth()->user()->id)
            ->where('branchs_id', auth()->user()->branch_id)
            ->value('max_discount') ?? 0;

        $defaultTaxRate = Tax::defaultRateFraction();

        // بيانات الفاتورة الحالية بشكل مبسّط عشان نعبي بيه فورم Alpine.js
        // (نفس شكل $draftForJs في create.blade.php بالظبط).
        $existingInvoiceData = [
            'customer_id' => $invoice->customer_id,
            'payment_method' => $invoice->payment_method,
            'cash_amount' => (float) $invoice->cash_amount,
            'bank_amount' => (float) $invoice->bank_amount,
            'extra_discount' => (float) $invoice->invoice_level_discount,
            'items' => $invoice->items->map(function (InvoiceItem $item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name_snapshot ?? $item->product?->name,
                    'code' => $item->product?->code,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'purchase_price' => (float) ($item->product?->purchase_price ?? 0),
                    'discount_amount' => (float) $item->discount_amount,
                    'tax_rate' => (float) $item->tax_rate,
                ];
            })->values(),
        ];

        return view('invoices.edit', compact('invoice', 'customers', 'maxDiscountPercent', 'defaultTaxRate', 'existingInvoiceData'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('invoices.edit');

        if (!$invoice->isEditable()) {
            abort(403, __('invoices.not_editable'));
        }

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'customer_id' => ['required', 'exists:customers,id'],
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
            'items.*.name' => ['nullable', 'string'],
            'items.*.code' => ['nullable', 'string'],
            'items.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        DB::transaction(function () use ($invoice, $validated) {
            // نتأكد تاني من قابلية التعديل جوه الـ transaction - لو حصل
            // مرتجع على الفاتورة في نفس اللحظة (سباق نادر)، نوقف فورًا
            // قبل ما نلمس أي بيانات.
            $invoice->refresh();
            if (!$invoice->isEditable()) {
                throw ValidationException::withMessages([
                    'items' => __('invoices.not_editable'),
                ]);
            }

            // الفرع بيفضل زي ما هو (فرع الفاتورة الأصلي) - التعديل مبيغيرش
            // فرع الفاتورة عشان القيود المحاسبية القديمة (والحسابات
            // المرتبطة بيها) كلها مسجلة على الفرع ده بالظبط.
            $branchId = $invoice->branch_id;

            $this->reverseInvoiceEffects($invoice);

            $totals = $this->computeInvoiceTotals($validated['items'], (float) ($validated['invoice_level_discount'] ?? 0));
            $split = $this->computePaymentSplit(
                $validated['payment_method'],
                $totals['grandTotal'],
                (float) ($validated['cash_amount'] ?? 0),
                (float) ($validated['bank_amount'] ?? 0)
            );

            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['taxTotal'],
                'discount_amount' => $totals['discountTotal'],
                'total_quantity' => $totals['totalQuantity'],
                'payment_method' => $validated['payment_method'],
                'has_multiple_payment_methods' => $validated['payment_method'] === 'split',
                'cash_amount' => $split['cashAmount'],
                'bank_amount' => $split['bankAmount'],
                'credit_amount' => $split['creditAmount'],
                'note' => $validated['note'] ?? null,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'invoice_level_discount' => $totals['invoiceLevelDiscount'],
            ]);

            $this->applyInvoiceItemsToProducts($invoice, $validated['items'], true);

            if ($totals['grandTotal'] > 0) {
                $this->recordInvoiceAccounting(
                    $invoice,
                    $split['cashAmount'],
                    $split['bankAmount'],
                    $split['creditAmount'],
                    $totals['totalCost'],
                    $validated['payment_method'],
                    $validated['customer_id'],
                    $branchId
                );
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', __('invoices.updated_successfully'));
    }
    /**
     * ملحوظة: مبنفلترش هنا على عمود status عمدًا - قيم status في بيانات
     * المنتجات الحالية مش كلها 'active' حرفيًا، وكانت الفلترة عليه بتخفي
     * منتجات موجودة فعلاً وفيها مخزون من صندوق البحث السريع (نفس السبب
     * اللي ظهر في شاشة تحويلات المخزون بين الفروع). الراوت ده كمان بيستخدمه
     * البحث السريع في شاشتي المشتريات والتسعيرات (بيعملوا fetch على
     * invoices.products.search مباشرة).
     */
    public function searchProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            // البحث بيغطي: اسم الصنف، الكود، الملاحظات، والرقم المرجعي
            // (طلب العميل يبحث بالاسم أو الكود أو الملاحظات أو الأرقام
            // البديلة - مفيش جدول منفصل للأرقام البديلة حاليًا فاعتمدنا
            // على عمود reference_number الموجود بالفعل).
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
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
            // withCount بيدينا عدد البدائل المرتبطة بالمنتج ده (لو هو
            // "أساسي" وليه بدائل) من غير ما نعمل استعلام منفصل لكل صف -
            // مستخدم في الواجهة لإظهار/إخفاء زرار "البدائل".
            ->withCount('alternates')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            // البحث هنا (مودال "اختيار منتج" الكامل) بيغطي نفس حقول
            // searchProducts فوق: الاسم، الكود، الملاحظات، والرقم المرجعي.
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%");
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
                    'alternates_count' => $p->alternates_count,
                ];
            })->values(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * آخر سعر بيع اتسجل بيه كل منتج (من الأصناف المطلوبة) لعميل معيّن -
     * مستخدمة في شاشة إنشاء الفاتورة عشان تعرض بادچ صغير جنب المنتج في
     * مودال الاختيار وتحت خانة سعر الوحدة في جدول الأصناف المضافة، تساعد
     * الموظف يتذكر آخر سعر باعه بيه لنفس العميل ده تحديدًا. بترجع
     * object بسيط {product_id: price} عشان تبقى سهلة الدمج في الواجهة.
     * بترجّع بس أحدث سعر لكل منتج (مش تاريخ كامل - ده موجود بالفعل في
     * تقرير "المبيعات حسب الصنف" لو حد محتاج التفاصيل الكاملة).
     */
    public function lastCustomerPrices(Request $request)
    {
        $customerId = $request->query('customer_id');
        $productIds = collect((array) $request->query('product_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (! $customerId || $productIds->isEmpty()) {
            return response()->json([]);
        }

        $rows = InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.customer_id', $customerId)
            ->whereIn('invoice_items.product_id', $productIds)
            ->orderByDesc('invoices.issue_date')
            ->orderByDesc('invoice_items.id')
            ->get(['invoice_items.product_id', 'invoice_items.unit_price']);

        // أول صف نشوفه لكل product_id هو الأحدث فعلًا (بفضل الـ order by
        // فوق) - فبنسيب أول قيمة نلاقيها بس ونتجاهل أي تكرار بعد كده.
        $lastPrices = [];
        foreach ($rows as $row) {
            if (! array_key_exists($row->product_id, $lastPrices)) {
                $lastPrices[$row->product_id] = (float) $row->unit_price;
            }
        }

        return response()->json($lastPrices);
    }

    /**
     * إضافة عميل سريعة من فورم الفاتورة (بدون مغادرة الصفحة).
     */
    public function quickStoreCustomer(Request $request)
    {// 1. التحقق من صحة البيانات (Validate)
$request->validate([
    'name'                            => ['required', 'string', 'max:255'],
    'phone'                           => ['required', 'string', 'max:255'],
    'email'                           => ['nullable', 'email', 'max:255'],
    'company_name'                    => ['nullable', 'string', 'max:255'],
    'tax_number'                      => ['nullable', 'string', 'max:255'],
    'commercial_registration_number'  => ['nullable', 'string', 'max:255'],
    'credit_limit'                    => ['nullable', 'numeric', 'min:0'],
    'grace_period_days'               => ['nullable', 'integer', 'min:0'],
    'opening_balance'                 => ['nullable', 'numeric'],
    'notes'                           => ['nullable', 'string'],
    'city'                            => ['nullable', 'string', 'max:255'],
    'district'                        => ['nullable', 'string', 'max:255'],
    'street_name'                     => ['nullable', 'string', 'max:255'],
    'building_number'                 => ['nullable', 'string', 'max:255'],
    'plot_identification'             => ['nullable', 'string', 'max:255'],
    'postal_code'                     => ['nullable', 'string', 'max:255'],
]);

// 2. تنفيذ العملية داخل Transaction
$customer = DB::transaction(function () use ($request) {

    $newCustomer = Customer::create([
        'name'                            => $request->name,
        'phone'                           => $request->phone,
        'email'                           => $request->email ?? null,
        'company_name'                    => $request->company_name ?? null,
        'address'                         => null, // العنوان العام - غير مستخدم من الفورم حاليًا
        'city'                            => $request->city ?? null,
        'notes'                           => $request->notes ?? null,
        'credit_limit'                    => $request->credit_limit ?? 10000,
        'balance'                         => $request->opening_balance ?? 0,
        'grace_period_days'               => $request->grace_period_days ?? 30,
        'tax_number'                      => $request->tax_number ?? null,
        'opening_balance'                 => $request->opening_balance ?? 0,
        'postal_code'                     => $request->postal_code ?? null,
        'district'                        => $request->district ?? null,
        'street_name'                     => $request->street_name ?? null,
        'building_number'                 => $request->building_number ?? null,
        'plot_identification'             => $request->plot_identification ?? null,
        'commercial_registration_number'  => $request->commercial_registration_number ?? null,
    ]);

    // توليد رقم الحساب التالي في شجرة الحسابات للعملاء - بنستخدم
    // parent_account_number + orginal_type بدل account_type القديم، لإن
    // account_type بقى بيحمل تصنيف محاسبي (أصول/خصوم/...) مش نوع الكيان
    // (عميل/مورد) بعد ميجريشن 2026_09_02_000028.
    $nextAccountNumber = FinancialAccount::where('parent_account_number', 2)
        ->where('orginal_type', 1)
        ->max('account_number') + 1;

    // account_type و account_category_id بيتورثوا مع بعض من نفس تصنيف
    // الحساب الأب (2 = حساب "العملاء") بدل قيمة ثابتة قديمة.
    $inheritedCategoryId = FinancialAccount::inheritedCategoryId(2);

    FinancialAccount::create([
        'name'                  => $request->name,
        'account_type'          => $inheritedCategoryId,
        'account_category_id'   => $inheritedCategoryId,
        'parent_account_number' => 2,
        'account_number'        => $nextAccountNumber,
        'start_balance'         => 0,
        'current_balance'       => 0,
        'start_balance_status'  => 3,
        'added_by'              => Auth::id() ?? 1,
        'com_code'              => 1,
        'date'                  => Carbon::now('Asia/Riyadh'),
        'active'                => 1,
        'is_parent'             => 0,
        'orginal_id'            => $newCustomer->id,
        'orginal_type'          => 1,
    ]);

    $newCustomer->update(['accounting_account_id' => $account->id ?? null]);

    return $newCustomer;
});

// 3. طريقة الإرجاع
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
     * المرتبطة بيها. مستخدمة من store() (لما تدوس "حفظ الفاتورة") ومن
     * approveDraft() (لما تدوس "اعتماد" على مسودة من غير ما تفتحها).
     */
    public  function finalizeInvoice(array $validated): Invoice
    {
        return DB::transaction(function () use ($validated) {
            $totals = $this->computeInvoiceTotals($validated['items'], (float) ($validated['invoice_level_discount'] ?? 0));
            $split = $this->computePaymentSplit(
                $validated['payment_method'],
                $totals['grandTotal'],
                (float) ($validated['cash_amount'] ?? 0),
                (float) ($validated['bank_amount'] ?? 0)
            );

            // 1. إنشاء الفاتورة
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'],
                'created_by' => Auth::id(),
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['taxTotal'],
                'discount_amount' => $totals['discountTotal'],
                'total_quantity' => $totals['totalQuantity'],
                'payment_method' => $validated['payment_method'],
                'has_multiple_payment_methods' => $validated['payment_method'] === 'split',
                'cash_amount' => $split['cashAmount'],
                'bank_amount' => $split['bankAmount'],
                'credit_amount' => $split['creditAmount'],
                'is_finalized' => $validated['is_finalized'],
                'note' => $validated['note'] ?? null,
                'purchase_order_number' => $validated['purchase_order_number'] ?? null,
                'invoice_level_discount' => $totals['invoiceLevelDiscount'],
                'issue_date' => now()->toDateString(),
                'issue_time' => now()->toTimeString(),
            ]);

            $invoice->update(['invoice_number' => (string) $invoice->id]);

            // 2. إنشاء تفاصيل الفاتورة وتحديث المخزون
            $this->applyInvoiceItemsToProducts($invoice, $validated['items'], (bool) $validated['is_finalized']);

            // 3. القيود المحاسبية والحركات المالية (في حال كانت الفاتورة معتمدة)
            if ($validated['is_finalized'] && $totals['grandTotal'] > 0) {
                $this->recordInvoiceAccounting(
                    $invoice,
                    $split['cashAmount'],
                    $split['bankAmount'],
                    $split['creditAmount'],
                    $totals['totalCost'],
                    $validated['payment_method'],
                    $validated['customer_id'],
                    $validated['branch_id']
                );
            }

            return $invoice;
        });
    }

    /**
     * حساب إجماليات الفاتورة (الإجمالي قبل الضريبة، الضريبة، الخصم،
     * الإجمالي النهائي، تكلفة البضاعة المباعة...) من مصفوفة الأصناف -
     * نفس الحسابات المستخدمة في finalizeInvoice() بالظبط، بس منقولة هنا
     * عشان update() يقدر يستخدمها هي كمان من غير تكرار.
     */
    protected function computeInvoiceTotals(array $items, float $invoiceLevelDiscountInput): array
    {
        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;
        $totalCost = 0;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $subtotal += $lineSubtotal;
            $taxTotal += $lineSubtotal * $item['tax_rate'];
            $discountTotal += $item['discount_amount'] ?? 0;

            if ($product) {
                $totalCost += ($product->purchase_price ?? 0) * $item['quantity'];
            }
        }

        $invoiceLevelDiscount = min($invoiceLevelDiscountInput, $subtotal + $taxTotal);
        $grandTotal = $subtotal + $taxTotal - $invoiceLevelDiscount;
        $totalQuantity = array_sum(array_column($items, 'quantity'));

        return compact('subtotal', 'taxTotal', 'discountTotal', 'invoiceLevelDiscount', 'grandTotal', 'totalQuantity', 'totalCost');
    }

    /**
     * توزيع الإجمالي النهائي على طرق الدفع (كاش/بنك/آجل) حسب طريقة
     * الدفع المختارة - نفس منطق finalizeInvoice() بالظبط.
     */
    protected function computePaymentSplit(string $paymentMethod, float $grandTotal, float $cashAmountInput, float $bankAmountInput): array
    {
        $cashAmount = 0;
        $bankAmount = 0;
        $creditAmount = 0;

        switch ($paymentMethod) {
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
                $cashAmount = $cashAmountInput;
                $bankAmount = $bankAmountInput;
                $creditAmount = max(0, $grandTotal - ($cashAmount + $bankAmount));
                break;
        }

        return compact('cashAmount', 'bankAmount', 'creditAmount');
    }

    /**
     * بتنشئ بنود الفاتورة (InvoiceItem) وتحدّث مخزون كل منتج - مستخدمة
     * في الإنشاء وفي إعادة التطبيق بعد التعديل (update()) بنفس المنطق
     * بالظبط. على عكس المشتريات، البيع مبيغيرش تكلفة/متوسط تكلفة المنتج
     * (purchase_price/average_cost) خالص - بس بيخصم من المخزون، فمفيش
     * داعي لأي "snapshot" هنا؛ الإرجاع (reverseInvoiceEffects) بيكتفي
     * بإضافة نفس الكمية تاني (delta)، وده آمن حتى لو حصلت فواتير/مشتريات
     * تانية على نفس المنتج في الوسط.
     */
    protected function applyInvoiceItemsToProducts(Invoice $invoice, array $items, bool $isFinalized): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $lineTax = $lineSubtotal * $item['tax_rate'];

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'],
                'branch_id' => $invoice->branch_id,
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'discount_amount' => $item['discount_amount'] ?? 0,
                'tax_amount' => $lineTax,
                'tax_rate' => $item['tax_rate'],
                // لو الكاشير عدّل اسم الصنف في سطر الفاتورة (زي إضافة
                // ملاحظة أو وصف مختلف عن اسم المنتج في الكتالوج)، الاسم
                // المُدخل هو اللي المفروض يتحفظ ويتطبع - مش اسم المنتج
                // الأصلي. لو السطر جاي فاضي أو من غير حقل name خالص (أي
                // مسار قديم قبل الميزة دي)، بنرجع لاسم المنتج زي ما كان.
                'product_name_snapshot' => !empty(trim((string) ($item['name'] ?? '')))
                    ? trim((string) $item['name'])
                    : $product?->name,
                'is_finalized' => $isFinalized,
                'created_by' => Auth::id(),
                'remaining_quantity' => $item['quantity'],
            ]);

            if ($product && $isFinalized) {
                $product->decrement('stock_quantity', $item['quantity']);
                $product->increment('total_sold', $item['quantity']);
            }
        }
    }

    /**
     * كل القيود المحاسبية والحركات المالية المرتبطة بفاتورة مبيعات
     * معتمدة (كاش/بنك/ضريبة/إيرادات/تكلفة البضاعة المباعة/رصيد العميل
     * الآجل) - نفس منطق finalizeInvoice() الأصلي بالظبط (بما في ذلك إن
     * حسابات الكاش/البنك/الإيرادات/التكلفة/المخزون هنا بتتسجل كسطر
     * تسجيل (CreditTransaction) بس من غير تحديث فعلي لرصيدها - ده سلوك
     * الكود الأصلي، مش غلط جديد مني)، منقولة هنا عشان update() يقدر
     * يعيد تسجيلها بعد التعديل من غير تكرار.
     */
    protected function recordInvoiceAccounting(
        Invoice $invoice,
        float $cashAmount,
        float $bankAmount,
        float $creditAmount,
        float $totalCost,
        string $paymentMethod,
        int $customerId,
        int $branchId
    ): void {
        $customerData = Customer::find($customerId);

        $pData = [
            'cashamount' => $cashAmount,
            'Bank_transfer' => ($paymentMethod === 'bank_transfer' || $paymentMethod === 'card') ? $bankAmount : 0,
            'bankamount' => ($paymentMethod === 'split') ? $bankAmount : 0,
            'creaditamount' => $creditAmount,
            'created_at' => Carbon::now('Asia/Riyadh'),
        ];

        // أ. القيود المحاسبية لـ Cash
        if ($pData['cashamount']) {
            $financialAccount = FinancialAccount::where('parent_account_number', 5)->where('branchs_id', Auth::user()->branchs_id ?? $branchId)->first();
            if ($financialAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $financialAccount->id,
                    'recive_amount' => $pData['cashamount'],
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $financialAccount->current_balance + $pData['cashamount'],
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $pData['cashamount'],
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }
        }

        // ب. القيود المحاسبية للشبكة والتحويل البنكي
        $totalBank = $pData['Bank_transfer'] + $pData['bankamount'];
        if ($totalBank) {
            $financialAccount = FinancialAccount::where('parent_account_number', 4)->where('branchs_id', Auth::user()->branchs_id ?? $branchId)->first();
            if ($financialAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $financialAccount->id,
                    'recive_amount' => $totalBank,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $financialAccount->current_balance + $totalBank,
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $totalBank,
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }
        }

        // ج. تحديث حساب ضريبة القيمة المضافة والإيرادات
        $totalValue = $pData['Bank_transfer'] + $pData['creaditamount'] + $pData['bankamount'] + $pData['cashamount'];
        $vatValue = $totalValue - ($totalValue * 100 / 115);
        $netRevenue = $totalValue * 100 / 115;

        // حساب الضريبة (102)
        $vatAccount = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', Auth::user()->branchs_id ?? $branchId)->first();
        if ($vatAccount) {
            $vatAccount->update([
                'current_balance' => $vatAccount->current_balance + $vatValue,
                'creditor_current' => $vatAccount->creditor_current + $vatValue,
            ]);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $vatAccount->id,
                'recive_amount' => $vatValue,
                'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                'pay_method' => $paymentMethod,
                'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                'currentblance' => $vatAccount->current_balance,
                'Pay_Method_Name' => ucfirst($paymentMethod),
                'created_at' => $pData['created_at'],
                'updated_at' => Carbon::now('Asia/Riyadh'),
                'creditor' => $vatValue,
                'vat' => 1,
                'name' => $customerData->name ?? '',
                'tax' => $customerData->tax_no ?? '',
                'operation_type' => 1,
                'invoice_number' => $invoice->invoice_number,
            ]);
        }

        // حساب المبيعات والإيرادات (112)
        $revenueAccount = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', Auth::user()->branchs_id ?? $branchId)->first();
        if ($revenueAccount) {
            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $revenueAccount->id,
                'recive_amount' => $netRevenue,
                'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                'pay_method' => $paymentMethod,
                'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                'currentblance' => $revenueAccount->current_balance + $netRevenue,
                'Pay_Method_Name' => ucfirst($paymentMethod),
                'created_at' => $pData['created_at'],
                'updated_at' => Carbon::now('Asia/Riyadh'),
                'creditor' => $netRevenue,
                'operation_type' => 1,
                'invoice_number' => $invoice->invoice_number,
            ]);
        }

        // د. حساب تكلفة البضاعة المباعة (183) والمخزن (181)
        if ($totalCost > 0) {
            $costAccount = FinancialAccount::where('parent_account_number', 183)->where('branchs_id', Auth::user()->branchs_id ?? $branchId)->first();
            if ($costAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $costAccount->id,
                    'recive_amount' => $totalCost,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $costAccount->current_balance + $totalCost,
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $totalCost,
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }

            $inventoryAccount = FinancialAccount::where('parent_account_number', 181)->where('branchs_id', Auth::user()->branchs_id ?? $branchId)->first();
            if ($inventoryAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $inventoryAccount->id,
                    'recive_amount' => $totalCost,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $inventoryAccount->current_balance - $totalCost,
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'creditor' => $totalCost,
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }
        }

        // هـ. في حال وجود مبالغ آجلة يتم تحديث رصيد العميل المحاسبي
        if ($pData['creaditamount'] != 0 && $customerData) {
            $customerData->increment('balance', $pData['creaditamount']);

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
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $paymentMethod,
                    'note' => 'فاتورة مبيعات رقم :' . $invoice->id,
                    'currentblance' => $customerFinancialAccount->current_balance,
                    'Pay_Method_Name' => ucfirst($paymentMethod),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $pData['creaditamount'],
                    'operation_type' => 1,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }
        }
    }

    /**
     * بترجع تأثير فاتورة مبيعات بالكامل (استعدادًا لتعديلها): بترجع
     * الكمية لكل منتج (delta - بترجع نفس الكمية اللي اتباعت بس، مش
     * snapshot، عشان تفضل صح حتى لو حصلت عمليات تانية على المنتج بعد
     * الفاتورة دي)، وبترجع كل القيود المحاسبية المرتبطة (كاش/بنك/ضريبة/
     * إيرادات/تكلفة/رصيد العميل الآجل) وتمسحها، وبتمسح بنود الفاتورة
     * القديمة. مسموح نستخدمها بس على فاتورة "قابلة للتعديل"
     * (Invoice::isEditable()).
     */
    protected function reverseInvoiceEffects(Invoice $invoice): void
    {
        $invoice->load('items.product');

        foreach ($invoice->items as $item) {
            if ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
                $item->product->decrement('total_sold', $item->quantity);
            }
        }

        $this->reverseInvoiceAccounting($invoice);

        $invoice->items()->delete();
    }

    /**
     * بترجع القيود المحاسبية المرتبطة بفاتورة مبيعات معيّنة. الحسابات
     * الوحيدة اللي فعليًا بيتحدّث رصيدها في recordInvoiceAccounting()
     * هي حساب الضريبة (102) وحساب العميل المحاسبي (لو فيه مبلغ آجل) -
     * باقي الحسابات (كاش/بنك/إيرادات/تكلفة/مخزون) بس بيتسجلها سطر
     * تسجيل (CreditTransaction) من غير تحديث فعلي لرصيدها، فمفيش داعي
     * نرجعها (نفس سلوك الكود الأصلي بالظبط). كل صفوف CreditTransaction
     * الخاصة بالفاتورة (invoice_number + operation_type=1) بتتمسح في
     * الآخر بغض النظر.
     */
    protected function reverseInvoiceAccounting(Invoice $invoice): void
    {
        $branchId = $invoice->branch_id;

        $totalValue = (float) $invoice->cash_amount + (float) $invoice->bank_amount + (float) $invoice->credit_amount;
        if ($totalValue > 0) {
            $vatValue = $totalValue - ($totalValue * 100 / 115);
            $vatAccount = FinancialAccount::where('parent_account_number', 102)
                ->where('branchs_id', $branchId)
                ->first();
            if ($vatAccount) {
                $vatAccount->update([
                    'current_balance' => $vatAccount->current_balance - $vatValue,
                    'creditor_current' => $vatAccount->creditor_current - $vatValue,
                ]);
            }
        }

        if ((float) $invoice->credit_amount != 0) {
            $customerFinancialAccount = FinancialAccount::where('orginal_type', 1)
                ->where('orginal_id', $invoice->customer_id)
                ->first();
            if ($customerFinancialAccount) {
                $customerFinancialAccount->update([
                    'current_balance' => $customerFinancialAccount->current_balance - $invoice->credit_amount,
                    'debtor_current' => $customerFinancialAccount->debtor_current - $invoice->credit_amount,
                ]);
            }

            $customer = Customer::find($invoice->customer_id);
            if ($customer) {
                $customer->decrement('balance', $invoice->credit_amount);
            }
        }

        CreditTransaction::where('invoice_number', $invoice->invoice_number)
            ->where('operation_type', 1)
            ->delete();
    }

    public function store(Request $request)
    {
        $this->authorize('invoices.create');

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

        // ?saved=1 بتخلي صفحة invoices.show تعرض مودال "إرسال للزكاة /
        // طباعة" مرة واحدة بس فور إنشاء الفاتورة، بدل ما تطبع أوتوماتيك
        // على طول زي ما كان بيحصل قبل كده - مش بتظهر لو رجعنا لنفس
        // الفاتورة تاني بعدين من قايمة الفواتير.
        return redirect()->route('invoices.show', ['invoice' => $invoice, 'saved' => 1])
            ->with('success', __('invoices.created_successfully'));
    }

    /**
     * اعتماد مسودة كفاتورة رسمية مباشرة من قايمة "المسودات السابقة" -
     * من غير ما تفتحها الأول في شاشة إنشاء الفاتورة. بتاخد بيانات
     * المسودة زي ما هي، وبتعمل بيها بالظبط نفس اللي بيحصل لما تدوس
     * "حفظ الفاتورة" (فاتورة رسمية + رقم + كل القيود المحاسبية)،
     * وبعدين بتمسح المسودة.
     */
    public function approveDraft(DraftInvoice $draft)
    {
        $this->authorize('invoices.create');

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

        return redirect()->route('invoices.show', ['invoice' => $invoice, 'saved' => 1])
            ->with('success', __('invoices.created_successfully'));
    }
}
