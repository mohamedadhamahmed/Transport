<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\CreditTransaction;
use App\Models\FinancialAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseAttachment;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tax;
use App\Services\JournalEntryService;
use App\Services\Purchases\PurchaseItemsImporter;
use App\Services\Purchases\PurchaseItemsTemplateExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * *** رقم حساب مورّد جديد (parent_account_number) في شجرة الحسابات ***
     * لقيت في كود المشتريات القديم اللي بعتيهولي إن حساب المورد بيتلاقى
     * بـ FinancialAccount::where('orginal_type', 2)->where('orginal_id', $supplierId)
     * - نفس فكرة العميل بالظبط (orginal_type=1) لكن بـ 2 للمورد. المشكلة
     * إن الكود القديم مكانش بيعمل إنشاء الحساب ده من الأساس (كان
     * موجود مسبقًا في نظامك)، فمقدرش أعرف الرقم الأب (parent_account_number)
     * الصحيح للموردين عندك زي ما عرفت رقم العملاء (2) من
     * InvoiceController@quickStoreCustomer. حطيت 3 كافتراض (مباشرة بعد
     * العملاء) - *** لازم تتأكد منه قبل ما تشغل النظام على بيانات
     * حقيقية *** وتغيّريه هنا لو مختلف عندك.
     */
    // الموردين = id 1 في شجرة الحسابات ("الموردين" [2100] - خصوم). الـ 3
    // القديم كان "الحساب الرئيسي للمناديب" تحت الأصول المتداولة.
    const SUPPLIER_PARENT_ACCOUNT_NUMBER = 1;

    /**
     * *** رقم حساب مصروف الشحن *** - في الكود القديم كان FinancialAccount::find(133)
     * ثابت. لو رقم حساب "مصروفات الشحن" عندك مختلف، غيّري الرقم هنا.
     */
    const SHIPPING_EXPENSE_ACCOUNT_ID = 133;

    public function index(Request $request)
    {
        $this->authorize('purchases.view');

        $query = Purchase::with(['supplier', 'branch', 'creator'])->latest();

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('issue_date', $request->date('date'));
        }

        $purchases = $query->paginate(15)->withQueryString();

        // فلتر المورد بقى بحث Ajax حي (منحملش كل جدول الموردين).
        $selectedSupplierId = $request->filled('supplier_id') ? (int) $request->input('supplier_id') : null;
        $suppliers = $selectedSupplierId
            ? [$selectedSupplierId => optional(Supplier::find($selectedSupplierId))->name]
            : [];

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(Request $request)
    {
        $this->authorize('purchases.create');

        $branches = Branch::orderBy('name')->get();
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        // حسابات الدفع الفوري (نقدي/بنك/شبكة) الخاصة بالفرع الافتراضي
        // بس (فرع المستخدم الحالي، أو أول فرع لو مفيش) - بتتفلتر حسب
        // الفرع عشان لما تختار فرع تاني من الفورم، تشوف خزينة/بنك
        // الفرع ده بالظبط مش كل حسابات كل الفروع مع بعض. تحديث القايمة
        // ده بيحصل لايف بالـ JS (راوت purchases.payment-accounts) لما
        // تغيّر الفرع في الفورم؛ هنا بس بنجهز القايمة الأولية.
        $defaultBranchId = $request->input('branch_id', auth()->user()->branch_id ?? $branches->first()?->id);
        $paymentAccounts = $this->paymentAccountsQuery($defaultBranchId)->get();

        // لو الفاتورة جاية من تحويل "أمر شراء" (?from_po=ID) - بنجيب
        // أمر الشراء ده عشان نعبي بيه الفورم تلقائيًا (المورد + الأصناف
        // + المخزن + مركز التكلفة + رسوم الشحن + الملاحظات). لو الأمر
        // مش لسه "قيد الانتظار" (يعني اتحول أو اتلغى قبل كده) بنتجاهله
        // تمامًا عشان محدش يحول نفس الأمر مرتين.
        $sourcePurchaseOrder = null;
        if ($request->filled('from_po')) {
            $candidate = PurchaseOrder::with('items.product')->find($request->input('from_po'));
            if ($candidate && $candidate->isPending()) {
                $sourcePurchaseOrder = $candidate;
            }
        }

        // منحملش كل جدول الموردين هنا (بحث Ajax حي في الفورم نفسه) - لو
        // الفورم جاي من تحويل أمر شراء، بس محتاجين اسم المورد بتاعه عشان
        // يظهر كخيار مبدئي.
        $suppliers = $sourcePurchaseOrder
            ? [$sourcePurchaseOrder->supplier_id => optional(Supplier::find($sourcePurchaseOrder->supplier_id))->name]
            : [];

        // نسبة الضريبة الافتراضية اللي المفروض تتحدد تلقائيًا لكل بند جديد
        // بتتحدد حسب أولوية الضريبة في جدول الضرائب (Tax::defaultRateFraction())
        // مش رقم ثابت 15% زي ما كان بيحصل قبل كده.
        $defaultTaxRate = Tax::defaultRateFraction();

        return view('purchases.create', compact('suppliers', 'branches', 'paymentAccounts', 'costCenters', 'sourcePurchaseOrder', 'defaultTaxRate'));
    }

    /**
     * استعلام حسابات الدفع الفوري (نقدي/بنك/شبكة) الخاصة بفرع معيّن -
     * الفلترة بعمود branchs_id في financialaccount، بنفس الاسم
     * المستخدم في finalizePurchase تحت (حساب المخزون/الضريبة).
     */
    protected function paymentAccountsQuery($branchId)
    {
        return FinancialAccount::whereIn('parent_account_number', [4, 5])
            ->where('branchs_id', $branchId)
            ->orderBy('name')
            ->select(['id', 'name']);
    }

    /**
     * بيرجع حسابات الدفع الفوري (خزينة/بنك) الخاصة بفرع معيّن بس - بتتنادى
     * بالـ ajax لما تغيّر الفرع في فورم فاتورة المشتريات الجديدة، عشان
     * دروب داون "طريقة الدفع" يفضل مقصور على حسابات الفرع المختار بالظبط
     * ومايجيبش حسابات فروع تانية.
     */
    public function paymentAccountsForBranch(Request $request, $branchId)
    {
        return response()->json($this->paymentAccountsQuery($branchId)->get());
    }

    /**
     * إضافة مركز تكلفة جديد من فورم فاتورة المشتريات (بدون مغادرة
     * الصفحة) - جدول cost_centers موجود بالفعل عندك (cost_center_ar،
     * cost_center_en)، فبس بنضيف صف جديد فيه من غير أي قيد محاسبي (مركز
     * التكلفة نفسه مجرد تصنيف/تسمية، مش حساب مالي).
     */
    public function quickStoreCostCenter(Request $request)
    {
        $request->validate([
            'cost_center_ar' => ['required', 'string', 'max:255'],
            'cost_center_en' => ['nullable', 'string', 'max:250'],
        ]);

        $costCenter = CostCenter::create([
            'cost_center_ar' => $request->cost_center_ar,
            'cost_center_en' => $request->cost_center_en ?: '-',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $costCenter->id,
                'name' => $costCenter->cost_center_ar,
            ]);
        }

        return redirect()->back()->with('success', __('purchases.cost_center_added'));
    }

    /**
     * تحميل قالب إكسيل فاضي (نفس شكل الملف اللي بعتّه لي بالظبط:
     * product_name_ar, product_name_en, product_code, sale_price,
     * price, quantity, location, refnumber) - بتتعباه إنتِ بره النظام
     * وبعدين ترفعه تاني في importItems() تحت عشان يتضاف كل صف كصنف في
     * جدول فاتورة المشتريات دفعة واحدة، بدل ما تختار منتج منتج.
     *
     * منطق بناء الملف نفسه منقول لكلاس منفصل: PurchaseItemsTemplateExporter
     * (app/Services/Purchases) - نفس السلوك بالظبط، بس بتنظيم أوضح.
     */
    public function downloadItemsTemplate(PurchaseItemsTemplateExporter $exporter)
    {
        return $exporter->download();
    }

    /**
     * استيراد أصناف فاتورة المشتريات من ملف إكسيل بنفس شكل القالب فوق -
     * كل صف بيتحول لصنف في جدول الأصناف بالفورم (بدل الإضافة يدويًا منتج
     * منتج). المطابقة بتتم بـ "الكود" (product_code) أولاً، ولو مش
     * موجود بنجرب بالاسم العربي (product_name_ar). لو المنتج مش موجود
     * عندك خالص (لا بالكود ولا بالاسم)، بيتضاف **تلقائيًا كمنتج جديد**
     * في جدول المنتجات فورًا (حسب اختيارك) ببيانات الصف (الاسم، الكود،
     * الموقع، الرقم المرجعي، سعر الشراء، سعر البيع) - ده بيحصل فور رفع
     * الملف، حتى لو لسه مكملتش حفظ فاتورة المشتريات نفسها.
     *
     * منطق القراءة/المطابقة نفسه منقول لكلاس منفصل: PurchaseItemsImporter
     * (app/Services/Purchases) - نفس السلوك بالظبط، بس بتنظيم أوضح.
     *
     * محتاج مكتبة phpoffice/phpspreadsheet مركبة عندك (composer require
     * phpoffice/phpspreadsheet) عشان الميثود دي تشتغل - تفاصيل التركيب
     * في ملف التعليمات المرفق.
     */
    public function importItems(Request $request, PurchaseItemsImporter $importer)
    {
        $validated = $request->validate([
            'items_excel' => ['required', 'file', 'mimes:xlsx,xls'],
            // إجباري: عمود branch_id في جدول المنتجات عندك إجباري (بدون
            // قيمة افتراضية)، فأي منتج جديد بيتضاف تلقائيًا من صفوف
            // الإكسيل (غير موجود بالكود ولا بالاسم) لازم يترتبط بفرع.
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        return response()->json($importer->import($request->file('items_excel'), (int) $validated['branch_id']));
    }

    public function show(Purchase $purchase)
    {
        $this->authorize('purchases.view');

        $purchase->load(['supplier', 'branch', 'creator', 'items.product', 'attachments', 'paymentAccount', 'costCenter']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * تحميل فاتورة المشتريات PDF - نفس فكرة InvoiceController::downloadPdf()
     * و buildInvoicePdf() بالظبط، بس على قالب purchases.pdf.
     */
    public function downloadPdf(Purchase $purchase)
    {
        $this->authorize('purchases.view');

        $pdf = $this->buildPurchasePdf($purchase);

        return $pdf->download('purchase-' . ($purchase->purchase_number ?? $purchase->id) . '.pdf');
    }

    protected function buildPurchasePdf(Purchase $purchase)
    {
        $purchase->load(['supplier', 'branch', 'creator', 'items.product', 'paymentAccount', 'costCenter']);

        return Pdf::loadView('purchases.pdf', compact('purchase'))->setPaper('a4');
    }

    /**
     * تعديل فاتورة مشتريات - مسموح بس للفواتير "القابلة للتعديل"
     * (Purchase::isEditable()، راجعي تعليقها للتفاصيل). الفورم هنا نفس
     * فورم الإنشاء بالظبط، بس معبّى ببيانات الفاتورة الحالية.
     */
    public function edit(Purchase $purchase)
    {
        $this->authorize('purchases.edit');

        if (!$purchase->isEditable()) {
            abort(403, __('purchases.not_editable'));
        }

        $purchase->load(['items.product', 'supplier']);

        $branches = Branch::orderBy('name')->get();
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();
        // حسابات الدفع الخاصة بفرع الفاتورة الحالي (زي create()) - بتتحدّث
        // لايف بالـ JS لو المستخدم غيّر الفرع.
        $paymentAccounts = $this->paymentAccountsQuery($purchase->branch_id)->get();
        // منحملش كل جدول الموردين (بحث Ajax حي) - بس محتاجين اسم مورد
        // الفاتورة الحالي عشان يظهر كخيار مبدئي في الدروب داون.
        $suppliers = [$purchase->supplier_id => optional($purchase->supplier)->name];
        $defaultTaxRate = Tax::defaultRateFraction();

        // بيانات الفاتورة الحالية بشكل مبسّط عشان نعبي بيه فورم Alpine.js
        // (نفس شكل $sourcePurchaseOrderData في create() بالظبط).
        $existingPurchaseData = [
            'supplier_id' => $purchase->supplier_id,
            'branch_id' => $purchase->branch_id,
            'payment_account_id' => $purchase->payment_account_id,
            'cost_center_id' => $purchase->cost_center_id,
            'shipping_fee' => (float) $purchase->shipping_fee,
            'invoice_level_discount' => (float) $purchase->invoice_level_discount,
            'items' => $purchase->items->map(function (PurchaseItem $item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name_snapshot ?? $item->product?->name,
                    'code' => $item->product_code_snapshot ?? $item->product?->code,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'sale_price' => (float) ($item->sale_price ?? $item->product?->sale_price ?? 0),
                    'discount_amount' => (float) $item->discount_amount,
                    'tax_rate' => (float) $item->tax_rate,
                ];
            })->values(),
        ];

        return view('purchases.edit', compact(
            'purchase',
            'suppliers',
            'branches',
            'paymentAccounts',
            'costCenters',
            'defaultTaxRate',
            'existingPurchaseData'
        ));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorize('purchases.edit');

        if (!$purchase->isEditable()) {
            abort(403, __('purchases.not_editable'));
        }

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payment_account_id' => ['nullable', 'exists:financialaccount,id'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:255'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'invoice_level_discount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ])->validate();

        DB::transaction(function () use ($purchase, $validated) {
            // نتأكد تاني من قابلية التعديل جوه الـ transaction - لو حصل
            // مرتجع على الفاتورة في نفس اللحظة (سباق نادر)، نوقف فورًا
            // قبل ما نلمس أي بيانات.
            $purchase->refresh();
            if (!$purchase->isEditable()) {
                throw ValidationException::withMessages([
                    'items' => __('purchases.not_editable'),
                ]);
            }

            $this->reversePurchaseEffects($purchase);

            $totals = $this->computeTotals(
                $validated['items'],
                (float) ($validated['invoice_level_discount'] ?? 0),
                (float) ($validated['shipping_fee'] ?? 0)
            );
            $branchId = $validated['branch_id'];
            $paymentAccountId = $validated['payment_account_id'] ?? null;

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $branchId,
                'payment_account_id' => $paymentAccountId,
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'warehouse_name' => $validated['warehouse_name'] ?? null,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'shipping_fee' => $totals['shippingFee'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discountTotal'],
                'invoice_level_discount' => $totals['invoiceLevelDiscount'],
                'tax_amount' => $totals['taxTotal'],
                'grand_total' => $totals['grandTotal'],
                'total_quantity' => $totals['totalQuantity'],
                'note' => $validated['note'] ?? null,
                'issue_date' => $validated['issue_date'] ?? $purchase->issue_date,
            ]);

            $this->applyItemsToProducts($purchase, $validated['items'], $totals['shippingFee']);
            $this->recordPurchaseAccounting(
                $purchase,
                $totals['subtotal'],
                $totals['taxTotal'],
                $totals['invoiceLevelDiscount'],
                $totals['shippingFee'],
                $paymentAccountId,
                $branchId
            );
        });

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $path = $file->store('purchase-attachments/' . $purchase->id, 'public');
                PurchaseAttachment::create([
                    'purchase_id' => $purchase->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('purchases.show', $purchase)
            ->with('success', __('purchases.updated_successfully'));
    }

    /**
     * آخر N سعر شراء لمنتج معيّن ("التكلفات السابقة للمنتج" في الشاشة
     * القديمة) - بتتستخدم بالـ ajax لما تختار منتج في جدول الأصناف،
     * عشان تشوف كنتي بتشتريه بكام آخر مرة قبل ما تحدد السعر الجديد.
     */
    public function productCostHistory(Request $request, Product $product)
    {
        $history = PurchaseItem::with('purchase:id,created_at,supplier_id')
            ->with('purchase.supplier:id,name')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (PurchaseItem $item) {
                return [
                    'date' => $item->created_at->format('Y-m-d'),
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'supplier' => $item->purchase?->supplier?->name,
                    'purchase_id' => $item->purchase_id,
                ];
            });

        return response()->json($history);
    }

    /**
     * إضافة مورد سريع من فورم فاتورة المشتريات (بدون مغادرة الصفحة) -
     * نفس فكرة InvoiceController@quickStoreCustomer بالظبط، بس بـ
     * orginal_type = 2 (مورد) بدل 1 (عميل).
     */
    public function quickStoreSupplier(Request $request)
    {
        $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'name_en' => ['nullable', 'string', 'max:255'],
    'phone' => ['nullable', 'string', 'max:255'],
    'email' => ['nullable', 'email', 'max:255'],
    'company_name' => ['nullable', 'string', 'max:255'],
    'tax_no' => ['nullable', 'string', 'max:255'],
    'crn' => ['nullable', 'string', 'max:255'],
    'credit_limit' => ['nullable', 'numeric', 'min:0'],
    'notes' => ['nullable', 'string'],
    'city' => ['nullable', 'string', 'max:255'],
    'district' => ['nullable', 'string', 'max:255'],
    'street_name' => ['nullable', 'string', 'max:255'],
    'building_number' => ['nullable', 'string', 'max:255'],
    'plot_identification' => ['nullable', 'string', 'max:255'],
    'postal_code' => ['nullable', 'string', 'max:255'],
]);

$supplier = DB::transaction(function () use ($request) {
    $newSupplier = Supplier::create([
        'name' => $request->name,
        'name_en' => $request->name_en,
        'company_name' => $request->company_name ?? $request->name,
        'phone' => $request->phone,
        'email' => $request->email,
        'tax_no' => $request->tax_no,
        'crn' => $request->crn,
        'credit_limit' => $request->credit_limit ?? 0,
        'notes' => $request->notes,
        'created_by' => Auth::id(),
        'city' => $request->city,
        'sub_city' => $request->district,          // district في الفورم -> sub_city في الجدول
        'street_name' => $request->street_name,
        'building_number' => $request->building_number,
        'plot_identification' => $request->plot_identification,
        'postcode' => $request->postal_code,        // postal_code في الفورم -> postcode في الجدول
    ]);

    // parent_account_number + orginal_type بدل account_type القديم -
    // account_type بقى بيحمل تصنيف محاسبي (أصول/خصوم/...) مش نوع الكيان
    // بعد ميجريشن 2026_09_02_000028.
    $nextAccountNumber = FinancialAccount::where('parent_account_number', self::SUPPLIER_PARENT_ACCOUNT_NUMBER)
        ->where('orginal_type', 2)
        ->max('account_number') + 1;

    // account_type و account_category_id بيتورثوا مع بعض من نفس تصنيف
    // حساب الموردين الأب.
    $inheritedCategoryId = FinancialAccount::inheritedCategoryId(self::SUPPLIER_PARENT_ACCOUNT_NUMBER);

    FinancialAccount::create([
        'name' => $request->name,
        'account_type' => $inheritedCategoryId,
        'account_category_id' => $inheritedCategoryId,
        'parent_account_number' => self::SUPPLIER_PARENT_ACCOUNT_NUMBER,
        'account_number' => $nextAccountNumber,
        'start_balance' => 0,
        'current_balance' => 0,
        'start_balance_status' => 3,
        'other_table_FK' => null,
        'notes' => null,
        'added_by' => Auth::id() ?? 1,
        'updated_by' => null,
        'com_code' => 1,
        'date' => Carbon::now('Asia/Riyadh'),
        'active' => 1,
        'is_parent' => 0,
        'orginal_id' => $newSupplier->id,
        'orginal_type' => 2,
    ]);

    return $newSupplier;
});

if ($request->expectsJson() || $request->ajax()) {
    return response()->json([
        'id' => $supplier->id,
        'name' => $supplier->name,
    ]);
}

return redirect()->back()->with('success', __('purchases.supplier_added'));
}

    public function store(Request $request)
    {
        $this->authorize('purchases.create');

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            // فاضي = آجل، غير كده لازم يكون ID حساب موجود فعلاً.
            'payment_account_id' => ['nullable', 'exists:financialaccount,id'],
            // موجود بس لو الفاتورة دي جاية من تحويل أمر شراء - مش إجباري.
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:255'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'invoice_level_discount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ])->validate();

        $purchase = DB::transaction(function () use ($validated) {
            $purchase = $this->finalizePurchase($validated);

            // لو الفاتورة دي جاية من تحويل أمر شراء - بنقفل أمر الشراء
            // ده ونربطه بالفاتورة الناتجة، عشان محدش يقدر يحوله تاني.
            if (!empty($validated['purchase_order_id'])) {
                $sourcePurchaseOrder = PurchaseOrder::find($validated['purchase_order_id']);
                if ($sourcePurchaseOrder && $sourcePurchaseOrder->isPending()) {
                    $sourcePurchaseOrder->update([
                        'status' => 'converted',
                        'converted_purchase_id' => $purchase->id,
                        'converted_by' => Auth::id(),
                        'converted_at' => now(),
                    ]);
                }
            }

            return $purchase;
        });

        // المرفقات (PDF/صورة) - بتتحفظ بعد إنشاء الفاتورة عشان نعرف
        // الـ purchase_id بتاعها. بتتخزن على disk('public') جوه
        // storage/app/public/purchase-attachments/{id}/... - لازم يكون
        // شغال عندك: php artisan storage:link
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $path = $file->store('purchase-attachments/' . $purchase->id, 'public');
                PurchaseAttachment::create([
                    'purchase_id' => $purchase->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('purchases.show', $purchase)
            ->with('success', __('purchases.created_successfully'));
    }

    /**
     * القلب المحاسبي لفاتورة المشتريات: بيسجل الفاتورة والبنود، بيحدّث
     * تكلفة المنتج (بمتوسط مرجّح بعد توزيع رسوم الشحن على كل صنف حسب
     * نسبته من إجمالي الفاتورة)، ويعمل كل القيود المحاسبية المرتبطة
     * (مصروف الشحن، حساب المورد أو حساب الدفع الفوري، ضريبة القيمة
     * المضافة المدخلة، والمخزون). ده منقول بالظبط من نفس منطق شاشة
     * المشتريات القديمة اللي بعتيهالي - فقط بأسماء الأعمدة الجديدة
     * (purchase_price / stock_quantity / average_cost بدل الأسماء
     * القديمة اللي كانت فيها غلطات إملائية).
     */
    protected function finalizePurchase(array $validated): Purchase
    {
        $totals = $this->computeTotals(
            $validated['items'],
            (float) ($validated['invoice_level_discount'] ?? 0),
            (float) ($validated['shipping_fee'] ?? 0)
        );
        $branchId = $validated['branch_id'];
        $paymentAccountId = $validated['payment_account_id'] ?? null;

        // 1. إنشاء فاتورة المشتريات
        $purchase = Purchase::create([
            'supplier_id' => $validated['supplier_id'],
            'branch_id' => $branchId,
            'created_by' => Auth::id(),
            'payment_account_id' => $paymentAccountId,
            'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
            'warehouse_name' => $validated['warehouse_name'] ?? null,
            'cost_center_id' => $validated['cost_center_id'] ?? null,
            'shipping_fee' => $totals['shippingFee'],
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discountTotal'],
            'invoice_level_discount' => $totals['invoiceLevelDiscount'],
            'tax_amount' => $totals['taxTotal'],
            'grand_total' => $totals['grandTotal'],
            'total_quantity' => $totals['totalQuantity'],
            'note' => $validated['note'] ?? null,
            'issue_date' => $validated['issue_date'] ?? now()->toDateString(),
        ]);
        $purchase->update(['purchase_number' => (string) $purchase->id]);

        // 2. تحديث تكلفة كل منتج (متوسط مرجّح) + المخزون + بنود الفاتورة
        $this->applyItemsToProducts($purchase, $validated['items'], $totals['shippingFee']);

        // 3-7. كل القيود المحاسبية المرتبطة (شحن/مورد أو دفع فوري/مخزون/ضريبة)
        $this->recordPurchaseAccounting(
            $purchase,
            $totals['subtotal'],
            $totals['taxTotal'],
            $totals['invoiceLevelDiscount'],
            $totals['shippingFee'],
            $paymentAccountId,
            $branchId
        );

        return $purchase;
    }

    /**
     * حساب إجماليات فاتورة المشتريات (الإجمالي قبل الضريبة، الضريبة،
     * الخصم، الإجمالي النهائي...) من مصفوفة الأصناف - نفس الحسابات
     * المستخدمة في finalizePurchase() بالظبط، بس منقولة هنا عشان
     * update() يقدر يستخدمها هي كمان من غير تكرار.
     */
    protected function computeTotals(array $items, float $invoiceLevelDiscountInput, float $shippingFee): array
    {
        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $subtotal += $lineSubtotal;
            $taxTotal += $lineSubtotal * $item['tax_rate'];
            $discountTotal += $item['discount_amount'] ?? 0;
        }

        $invoiceLevelDiscount = min($invoiceLevelDiscountInput, $subtotal + $taxTotal);
        $grandTotal = $subtotal + $taxTotal - $invoiceLevelDiscount + $shippingFee;
        $totalQuantity = array_sum(array_column($items, 'quantity'));

        return compact('subtotal', 'taxTotal', 'discountTotal', 'invoiceLevelDiscount', 'shippingFee', 'grandTotal', 'totalQuantity');
    }

    /**
     * بتنشئ بنود الفاتورة (PurchaseItem) وتحدّث تكلفة/مخزون كل منتج
     * (متوسط مرجّح بعد توزيع رسوم الشحن) - مستخدمة في الإنشاء وفي إعادة
     * التطبيق بعد التعديل (update()) بنفس المنطق بالظبط. بتسجّل كمان
     * قيم المنتج "قبل" التعديل (stock_before/purchase_price_before/
     * average_cost_before) على كل بند، عشان لو الفاتورة دي اتعدلت
     * تاني بعدين نقدر نرجّع التكلفة لقيمتها الصح.
     */
    protected function applyItemsToProducts(Purchase $purchase, array $items, float $shippingFee): void
    {
        $totalInvoiceValue = array_sum(array_map(
            fn ($item) => $item['unit_price'] * $item['quantity'],
            $items
        ));

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
            $lineTax = $lineSubtotal * $item['tax_rate'];

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'unit_price' => $item['unit_price'],
                'sale_price' => $item['sale_price'] ?? null,
                'quantity' => $item['quantity'],
                'discount_amount' => $item['discount_amount'] ?? 0,
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $lineTax,
                'product_name_snapshot' => $product?->name,
                'product_code_snapshot' => $product?->code,
                'created_by' => Auth::id(),
                'stock_before' => $product?->stock_quantity ?? 0,
                'purchase_price_before' => $product?->purchase_price ?? 0,
                'average_cost_before' => $product?->average_cost ?? 0,
            ]);

            if ($product) {
                $grossLineTotal = $item['unit_price'] * $item['quantity'];
                $itemRatio = $totalInvoiceValue > 0 ? ($grossLineTotal / $totalInvoiceValue) : 0;
                $lineExtraCost = $itemRatio * $shippingFee;
                $extraCostPerUnit = $item['quantity'] > 0 ? ($lineExtraCost / $item['quantity']) : 0;

                $newQuantity = $product->stock_quantity + $item['quantity'];
                if ($newQuantity <= 0) {
                    $newAverageCost = $item['unit_price'] + $extraCostPerUnit;
                } else {
                    $currentStockValue = ($product->purchase_price ?? 0) * $product->stock_quantity;
                    $newItemsValue = ($item['unit_price'] + $extraCostPerUnit) * $item['quantity'];
                    $newAverageCost = round(($newItemsValue + $currentStockValue) / $newQuantity, 2);
                }

                $product->update([
                    'purchase_price' => $item['unit_price'] + $extraCostPerUnit,
                    'average_cost' => $newAverageCost,
                    'sale_price' => (($item['sale_price'] ?? 0) > 0) ? $item['sale_price'] : $product->sale_price,
                    'stock_quantity' => $newQuantity,
                ]);
            }
        }
    }

    /**
     * كل القيود المحاسبية المرتبطة بفاتورة مشتريات (مصروف الشحن، حساب
     * المورد أو حساب الدفع الفوري، المخزون، ضريبة القيمة المضافة
     * المدخلة) - نفس منطق finalizePurchase() الأصلي بالظبط، بس منقول
     * هنا عشان update() يقدر يعيد تسجيلها بعد التعديل من غير تكرار.
     */
    protected function recordPurchaseAccounting(
        Purchase $purchase,
        float $subtotal,
        float $taxTotal,
        float $invoiceLevelDiscount,
        float $shippingFee,
        ?int $paymentAccountId,
        int $branchId
    ): void {
        $purchaseNote = 'فاتورة مشتريات رقم :' . $purchase->id;
        $payMethodName = $paymentAccountId ? 'Immediate' : 'Credit';

        // مصروف الشحن (لو فيه) - بيتسجل كمديونية على حساب مصروف الشحن
        // الثابت، بغض النظر عن كون الفاتورة آجل أو فورية.
        if ($shippingFee > 0) {
            $shippingAccount = FinancialAccount::lockForUpdate()->find(self::SHIPPING_EXPENSE_ACCOUNT_ID);
            if ($shippingAccount) {
                $shippingAccount->update([
                    'current_balance' => $shippingAccount->current_balance + $shippingFee,
                    'debtor_current' => $shippingAccount->debtor_current + $shippingFee,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $shippingAccount->id,
                    'recive_amount' => $shippingFee,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $purchaseNote,
                    'currentblance' => $shippingAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'debtor' => $shippingFee,
                    'creditor' => 0,
                    'invoice_number' => $purchase->purchase_number,
                    'operation_type' => 3,
                ]);
            }
        }

        // مبلغ الفاتورة بدون رسوم الشحن (المديونية الأساسية على قيمة
        // البضاعة نفسها فقط، شامل الضريبة).
        $goodsTotal = $subtotal + $taxTotal - $invoiceLevelDiscount;

        if (is_null($paymentAccountId)) {
            // رسوم الشحن بتتضاف على المورد كمان (زي الدفع الفوري)، وإلا قيد الشحن
            // المدين بيفضل من غير طرف دائن والميزان يطلع غير متزن.
            $goodsTotal += $shippingFee;
            // آجل: يزيد رصيد المورد (المديونية عليه) + حسابه المالي.
            $supplier = Supplier::find($purchase->supplier_id);
            if ($supplier) {
                $supplier->increment('balance', $goodsTotal);
            }

            $supplierAccount = FinancialAccount::where('orginal_type', 2)
                ->where('orginal_id', $purchase->supplier_id)
                ->first();
            if ($supplierAccount) {
                $supplierAccount->update([
                    'current_balance' => $supplierAccount->current_balance + $goodsTotal,
                    'creditor_current' => $supplierAccount->creditor_current + $goodsTotal,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $supplierAccount->id,
                    'recive_amount' => $goodsTotal,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $purchaseNote,
                    'currentblance' => $supplierAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'creditor' => $goodsTotal,
                    'debtor' => 0,
                    'invoice_number' => $purchase->purchase_number,
                    'operation_type' => 3,
                ]);
            }
        } else {
            // دفع فوري: بينخصم من حساب الدفع المختار (نقدي/بنك/شبكة).
            $payValue = $goodsTotal + $shippingFee;
            $paymentAccount = FinancialAccount::lockForUpdate()->find($paymentAccountId);
            if ($paymentAccount) {
                $paymentAccount->update([
                    'current_balance' => $paymentAccount->current_balance - $payValue,
                    'creditor_current' => $paymentAccount->creditor_current + $payValue,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $paymentAccount->id,
                    'recive_amount' => $payValue,
                    'branchs_id' => Auth::user()->branchs_id ?? $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $purchaseNote,
                    'currentblance' => $paymentAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'debtor' => 0,
                    'creditor' => $payValue,
                    'invoice_number' => $purchase->purchase_number,
                    'operation_type' => 3,
                ]);
            }
        }

        // قيد المخزون (حساب 181) - بيزيد بقيمة البضاعة بدون الضريبة.
        $costWithoutTax = $subtotal - $invoiceLevelDiscount;
        $inventoryAccount = FinancialAccount::where('parent_account_number', 181)
            ->where('branchs_id', $branchId)
            ->first();
        if ($inventoryAccount) {
            $inventoryAccount->update([
                'current_balance' => $inventoryAccount->current_balance + $costWithoutTax,
                'debtor_current' => $inventoryAccount->debtor_current + $costWithoutTax,
            ]);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $inventoryAccount->id,
                'recive_amount' => $costWithoutTax,
                'branchs_id' => $branchId,
                'pay_method' => $payMethodName,
                'note' => $purchaseNote,
                'currentblance' => $inventoryAccount->current_balance,
                'Pay_Method_Name' => $payMethodName,
                'debtor' => $costWithoutTax,
                'creditor' => 0,
                'invoice_number' => $purchase->purchase_number,
                'operation_type' => 3,
            ]);
        }

        // ضريبة القيمة المضافة المدخلة (حساب 102) - بتزيد رصيدها المدين
        // (ضريبة مستردة من المشتريات).
        if ($taxTotal > 0) {
            $vatAccount = FinancialAccount::where('parent_account_number', 102)
                ->where('branchs_id', $branchId)
                ->first();
            if ($vatAccount) {
                $supplierData = Supplier::find($purchase->supplier_id);
                $vatAccount->update([
                    'current_balance' => $vatAccount->current_balance + $taxTotal,
                    'debtor_current' => $vatAccount->debtor_current + $taxTotal,
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $vatAccount->id,
                    'recive_amount' => $taxTotal,
                    'branchs_id' => $branchId,
                    'pay_method' => $payMethodName,
                    'note' => $purchaseNote,
                    'currentblance' => $vatAccount->current_balance,
                    'Pay_Method_Name' => $payMethodName,
                    'debtor' => $taxTotal,
                    'creditor' => 0,
                    'vat' => 1,
                    'name' => $supplierData->name ?? '',
                    'tax' => $supplierData->tax_no ?? '',
                    'invoice_number' => $purchase->purchase_number,
                    'operation_type' => 3,
                ]);
            }
        }

        // إنشاء أو تحديث القيد المحاسبي الآلي الموحد لفاتورة المشتريات وربطه بحركاتها
        $entryDescription = 'قيد فاتورة مشتريات رقم ' . ($purchase->purchase_number ?: $purchase->id);
        JournalEntryService::syncForSource(
            $purchase,
            $entryDescription,
            $purchase->purchase_date ?: now(),
            $branchId,
            Auth::id(),
            $purchase->cost_center_id
        );
    }

    /**
     * بترجع تأثير فاتورة مشتريات بالكامل (استعدادًا لتعديلها): بترجّع
     * تكلفة/سعر شراء كل منتج لقيمتها بالظبط قبل الفاتورة دي (snapshot)،
     * وتشيل الكمية اللي الفاتورة دي كانت زودتها بس من المخزون الحالي
     * (delta - مش استبدال كامل، عشان لو حصلت مبيعات على المنتج بعد
     * الفاتورة دي تفضل متسجلة صح)، وبترجع كل القيود المحاسبية المرتبطة
     * (شحن/مورد أو دفع فوري/مخزون/ضريبة) وتمسحها، وبترجع رصيد المورد
     * (لو آجل) وتمسح بنود الفاتورة القديمة. مسموح نستخدمها بس على فاتورة
     * "قابلة للتعديل" (Purchase::isEditable()) - راجعي تعليقها لتفاصيل
     * الشروط.
     */
    protected function reversePurchaseEffects(Purchase $purchase): void
    {
        $purchase->load('items.product');

        foreach ($purchase->items as $item) {
            if ($item->product) {
                $item->product->update([
                    'stock_quantity' => $item->product->stock_quantity - $item->quantity,
                    'purchase_price' => $item->purchase_price_before,
                    'average_cost' => $item->average_cost_before,
                ]);
            }
        }

        $this->reversePurchaseAccounting($purchase);

        $purchase->items()->delete();
    }

    /**
     * بترجع القيود المحاسبية (credittransactions) المرتبطة بفاتورة
     * مشتريات معيّنة - بتتعرف عليها بـ (invoice_number = رقم الفاتورة +
     * operation_type = 3)، وده مضمون يكون فريد لكل فاتورة (purchase_number
     * = ID الفاتورة نفسه)، ومضمون يشمل بس حركات الفاتورة دي (مش أي
     * مرتجع عليها) لإن Purchase::isEditable() بيرفض التعديل أصلاً لو
     * فيه أي كمية اترجعت. كل حركة بترجع لحسابها المالي بعكس الاتجاه
     * اللي اتسجلت بيه بالظبط (حسب نوعها: مصروف/مخزون/ضريبة "مدين"،
     * حساب مورد آجل "دائن"، حساب دفع فوري "مدين بس بعكس اتجاه الرصيد
     * الحالي")، بعدين بتتمسح كلها. رصيد المورد نفسه (جدول suppliers)
     * بيترجع كمان لو الفاتورة كانت آجلة.
     */
    protected function reversePurchaseAccounting(Purchase $purchase): void
    {
        $transactions = CreditTransaction::where('invoice_number', $purchase->purchase_number)
            ->where('operation_type', 3)
            ->lockForUpdate()
            ->get();

        foreach ($transactions as $transaction) {
            $account = FinancialAccount::lockForUpdate()->find($transaction->customer_id);
            if (!$account) {
                continue;
            }

            $isPaymentAccountRow = !is_null($purchase->payment_account_id)
                && (int) $transaction->customer_id === (int) $purchase->payment_account_id
                && (float) $transaction->debtor > 0;

            $isNewPaymentAccountRow = !is_null($purchase->payment_account_id)
                && (int) $transaction->customer_id === (int) $purchase->payment_account_id
                && (float) $transaction->creditor > 0;

            if ($isNewPaymentAccountRow) {
                // حساب الدفع الفوري (التسجيل الجديد - دائن): نرجّع الفلوس.
                $account->update([
                    'current_balance' => $account->current_balance + $transaction->creditor,
                    'creditor_current' => $account->creditor_current - $transaction->creditor,
                ]);
            } elseif ($isPaymentAccountRow) {
                // حساب الدفع الفوري: الرصيد كان نقص وقت الدفع (فلوس
                // خرجت) - بنرجعه زي ما كان.
                $account->update([
                    'current_balance' => $account->current_balance + $transaction->debtor,
                    'debtor_current' => $account->debtor_current - $transaction->debtor,
                ]);
            } elseif ((float) $transaction->creditor > 0) {
                // حساب المورد (آجل): الرصيد كان زاد (مديونية علينا) -
                // بننقصه.
                $account->update([
                    'current_balance' => $account->current_balance - $transaction->creditor,
                    'creditor_current' => $account->creditor_current - $transaction->creditor,
                ]);
            } else {
                // النمط العادي (شحن / مخزون / ضريبة): الرصيد كان زاد -
                // بننقصه.
                $account->update([
                    'current_balance' => $account->current_balance - $transaction->debtor,
                    'debtor_current' => $account->debtor_current - $transaction->debtor,
                ]);
            }
        }

        JournalEntryService::deleteForSource($purchase);

        CreditTransaction::where('invoice_number', $purchase->purchase_number)
            ->where('operation_type', 3)
            ->delete();

        if (is_null($purchase->payment_account_id)) {
            // نفس المبلغ اللي اتسجل على المورد بالظبط (من حركة حسابه).
            $supplierAccountIds = FinancialAccount::where('orginal_type', 2)
                ->where('orginal_id', $purchase->supplier_id)
                ->pluck('id');
            $goodsTotal = (float) $transactions->whereIn('customer_id', $supplierAccountIds->all())->sum('creditor');
            if ($goodsTotal == 0.0) {
                $goodsTotal = (float) $purchase->subtotal + (float) $purchase->tax_amount - (float) $purchase->invoice_level_discount;
            }
            $supplier = Supplier::find($purchase->supplier_id);
            if ($supplier) {
                $supplier->decrement('balance', $goodsTotal);
            }
        }
    }
}