<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;            // الموديل الصحيح لجدول المنتجات (اسمه Product مفرد)
use App\Models\Customer;          // عدّل اسم الموديل حسب موديل العملاء عندك
use App\Models\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * "دفتر التسليم" (Delivery Note) - قسم لتسجيل قطع بتتسلّم للعميل بشكل
 * متكرر (زبون بياخد كل شوية قطعة) بدون أي أثر مالي أو محاسبي وقت
 * التسليم نفسه. القيود المحاسبية الحقيقية بتتسجل فقط لما يتم "اعتماد"
 * جزء أو كل الكميات المعلّقة وتحويلها لفاتورة ضريبية حقيقية عبر
 * DeliveryNoteConvertController.
 */
class DeliveryNoteController extends Controller
{
    /**
     * عرض فورم تسجيل تسليم جديد (بدون أي بيانات دفع - مجرد تسجيل قطع)
     */
    public function create()
    {
        $this->authorize('delivery_note.create');

        // منحملش كل جدول العملاء هنا (بحث Ajax حي في الفورم نفسه).
        $Customer = [];

        // نسبة الضريبة هنا للمعاينة/التقدير بس (القيمة التقديرية المعالة) -
        // سند التسليم نفسه لسه من غير ضريبة فعلية في الداتابيز (مفيش عمود
        // tax_rate على delivery_notes)، والضريبة الحقيقية بتتحدد وقت
        // "الاعتماد وتحويل لفاتورة" زي ما هو معمول في DeliveryNoteConvertController.
        $taxes = Tax::orderBy('priority', 'asc')->where('is_active', 1)->get();
        $defaultTaxRate = Tax::defaultRateFraction();

        return view('delivery-note.create', compact('Customer', 'taxes', 'defaultTaxRate'));
    }

    /**
     * البحث اللحظي عن منتجات بالاسم/الكود
     *
     * ملحوظة: مبنفلترش على عمود status عمدًا - قيم status في بيانات
     * المنتجات الحالية مش كلها 'active' حرفيًا، وكانت الفلترة عليه بتخفي
     * منتجات موجودة فعلاً وفيها مخزون من صندوق البحث السريع.
     */
    public function searchProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
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
     * إضافة عميل سريعة من فورم التسليم (بدون مغادرة الصفحة)
     */
    public function quickStoreCustomer(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_no' => ['nullable', 'numeric'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'district' => ['nullable', 'string', 'max:255'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:255'],
            'plot_identification' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'CRN' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'comp_name' => $request->company_name ?? $request->name,
            'tax_no' => $request->tax_no ?? 0,
            'Balance' => 0,
            'phone' => $request->phone,
            'email' => $request->email ?? 'Email@gmail.com',
            'notes' => $request->notes ?? 'لا توجد ملاحظات',
            'Limit_credit' => $request->credit_limit ?? 0,
            'sub_city' => $request->district ?? null,
            'street_name' => $request->street_name ?? null,
            'building_number' => $request->building_number ?? null,
            'plot_identification' => $request->plot_identification ?? null,
            'postcode' => $request->postal_code ?? null,
            'CRN' => $request->CRN ?? null,
        ]);

        // إنشاء الحساب المالي المرتبط بالعميل في شجرة الحسابات - مفيد
        // لاحقًا وقت التحويل لفاتورة حقيقية (نظام الفواتير محتاج الحساب
        // ده موجود مسبقًا للعميل).
        // parent_account_number + orginal_type بدل account_type القديم -
        // account_type بقى بيحمل تصنيف محاسبي (أصول/خصوم/...) مش نوع
        // الكيان بعد ميجريشن 2026_09_02_000028.
        $nextAccountNumber = \App\Models\FinancialAccount::where('parent_account_number', 2)
            ->where('orginal_type', 1)
            ->max('account_number') + 1;

        // account_type و account_category_id بيتورثوا مع بعض من نفس
        // تصنيف حساب العملاء الأب.
        $inheritedCategoryId = \App\Models\FinancialAccount::inheritedCategoryId(2);

        \App\Models\FinancialAccount::create([
            'name' => $customer->name,
            'account_type' => $inheritedCategoryId,
            'account_category_id' => $inheritedCategoryId,
            'parent_account_number' => 2,
            'account_number' => $nextAccountNumber,
            'start_balance' => 0,
            'current_balance' => 0,
            'start_balance_status' => 3,
            'added_by' => Auth::id() ?? 1,
            'com_code' => 1,
            'date' => Carbon::now('Asia/Riyadh'),
            'active' => 1,
            'is_parent' => 0,
            'orginal_id' => $customer->id,
            'orginal_type' => 1,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $customer->id,
                'name' => $customer->name,
            ]);
        }

        return redirect()->back()->with('success', __('deliverynote.customer_added_success'));
    }

    /**
     * إضافة منتج سريعة من فورم التسليم
     */
    public function quickStoreProduct(Request $request)
    {
        $branchId = Auth::user()?->branch_id;
        $request->merge(['branch_id' => $branchId, 'status' => 'active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
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
        ]);
    }

    /**
     * قائمة منتجات مقسّمة صفحات (20 في كل صفحة) لمودال "اختيار منتج"
     */
    public function pickProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
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
                    'location' => $p->location ?? null,
                    'stock_quantity' => $p->stock_quantity ?? null,
                    'purchase_price' => $p->purchase_price,
                    'sale_price' => $p->sale_price,
                ];
            })->values(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * حفظ سند تسليم جديد - تسجيل بحت، بدون أي قيد محاسبي أو طريقة دفع.
     * القطع دي بتفضل "معلّقة" لحد ما يتم اعتمادها وتحويلها لفاتورة حقيقية
     * (أو ترجع كمرتجع) عبر DeliveryNoteConvertController / DeliveryNoteReturnController.
     */
    public function store(Request $request)
    {
        $this->authorize('delivery_note.create');

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'submission_token' => ['nullable', 'string', 'max:64'],
            'customer_id' => ['required'],
            'note' => ['nullable', 'string'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.name' => ['nullable', 'string'],
            'items.*.code' => ['nullable', 'string'],
        ])->validate();

        // حماية ذرية من تكرار الإرسال (ضغط مزدوج / إعادة إرسال)
        $submissionToken = $validated['submission_token'] ?? null;
        if ($submissionToken) {
            $lockKey = 'delivery_submission_' . $submissionToken;
            if (!Cache::add($lockKey, true, now()->addMinutes(15))) {
                return redirect()->route('deliverynote.history')
                    ->with('error', __('deliverynote.duplicate_submission_prevented'));
            }
        }

        DB::transaction(function () use ($validated) {
            // ⚠️ إصلاح: كان هنا Auth::user()->branchs_id (بحرف "s" زيادة) وهي
            // خاصية مش موجودة أصلاً على موديل User (اسم العمود الصحيح هناك
            // هو branch_id من غير "s")، فكانت النتيجة دايمًا null والقيمة
            // الافتراضية 1 هي اللي بتتسجل - يعني كل سندات التسليم اتسجلت
            // تاريخيًا بفرع "1" بغض النظر عن الفرع الحقيقي بتاع الموظف.
            // اسم عمود الفرع في جدول delivery_note نفسه فعلاً "branchs_id"
            // (بالـ s - تسمية قديمة من الجدول الأصلي)، والمشكلة كانت بس في
            // قراءة فرع المستخدم الحالي من branch_id الصحيح.
            $branchId = Auth::user()->branch_id ?? 1;

            $totalQuantity = 0;
            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $totalQuantity += $item['quantity'];
                $totalPrice += ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            }

            // رأس سند التسليم - مجرد تسجيل، status = 0 يعني "معلّق/غير مفوتَر بعد"
            $invoice = DeliveryNote::create([
                'customer_id' => $validated['customer_id'],
                'user_id' => Auth::id(),
                'Price' => $totalPrice,
                'Number_of_Quantity' => $totalQuantity,
                'Pay' => '-',
                'branchs_id' => $branchId,
                'note' => $validated['note'] ?? '-',
                'status' => 0,
                'save' => 1,
            ]);

            foreach ($validated['items'] as $item) {
                DeliveryNoteItem::create([
                    'product_id' => $item['product_id'],
                    'invoice_id' => $invoice->id,
                    'Discount_Value' => $item['discount'] ?? 0,
                    'branch_id' => $branchId,
                    'Unit_Price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'save' => 1,
                ]);

                // نقص المخزون (اختياري) - فعّله لو عايز الكمية تتحدث فورًا
                // لحظة التسليم (بغض النظر عن الفوترة لاحقًا).
                // Product::where('id', $item['product_id'])->decrement('stock_quantity', $item['quantity']);
            }
        });

        return redirect()->route('deliverynote.history')->with('success', __('deliverynote.save_success'));
    }

    /**
     * عرض قائمة التسليمات السابقة مع فلترة بالتاريخ والعميل
     */
    public function history(Request $request)
    {
        $this->authorize('delivery_note.view');

        $start_at = $request->start_at ?? date('Y-m-01');
        $end_at   = $request->end_at ?? date('Y-m-d');

        $query = DeliveryNote::with(['customer', 'user'])
            ->whereDate('created_at', '>=', $start_at)
            ->whereDate('created_at', '<=', $end_at)
            ->where('save', 1);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->orderByDesc('created_at')->paginate(20);

        // فلتر العميل بقى بحث Ajax حي (منحملش كل جدول العملاء).
        $selectedCustomerId = $request->filled('customer_id') ? (int) $request->input('customer_id') : null;
        $Customer = $selectedCustomerId
            ? [$selectedCustomerId => optional(Customer::find($selectedCustomerId))->name]
            : [];

        return view('delivery-note.history', compact('invoices', 'Customer'));
    }

    /**
     * عرض/طباعة سند تسليم واحد
     */
    public function show($id)
    {
        $this->authorize('delivery_note.view');

        $invoice = DeliveryNote::with(['customer', 'user'])->findOrFail($id);
        $items = DeliveryNoteItem::where('invoice_id', $id)
            ->where('save', 1)
            ->with('product')
            ->get();

        return view('delivery-note.show', compact('invoice', 'items'));
    }

    /**
     * فورم تعديل سند تسليم - متاحة بس لو السند لسه "معلّق بالكامل" (زي
     * ما هو مُعرَّف في DeliveryNote::isEditable()): status = 0 ومفيش أي
     * بند منه اتحول جزئيًا/كليًا لفاتورة أو اترجع. غير كده التعديل ممنوع
     * تمامًا لتجنب تعارض مع كميات محولة/مرتجعة بالفعل مبنية على القيم
     * الحالية.
     */
    public function edit($id)
    {
        $this->authorize('delivery_note.edit');

        $invoice = DeliveryNote::with(['customer'])->findOrFail($id);

        if (!$invoice->isEditable()) {
            return redirect()->route('deliverynote.show', $invoice->id)
                ->with('error', __('deliverynote.not_editable'));
        }

        $items = DeliveryNoteItem::where('invoice_id', $id)
            ->where('save', 1)
            ->with('product')
            ->get();

        // منحملش كل جدول العملاء (بحث Ajax حي زي شاشة الإنشاء)، بس
        // بنبعت العميل الحالي بتاع السند عشان يظهر محدد مسبقًا.
        $Customer = [$invoice->customer_id => optional($invoice->customer)->name];

        $taxes = Tax::orderBy('priority', 'asc')->where('is_active', 1)->get();
        $defaultTaxRate = Tax::defaultRateFraction();

        // نجهز شكل الأصناف بنفس البنية اللي بيتوقعها addProduct() في
        // الفرونت (product_id/name/code/quantity/unit_price/purchase_price/
        // discount) عشان جدول الأصناف يبان مليان من أول ما الصفحة تفتح.
        $existingItems = $items->map(function (DeliveryNoteItem $item) {
            return [
                'product_id' => $item->product_id,
                'name' => optional($item->product)->name,
                'code' => optional($item->product)->code,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->Unit_Price,
                'purchase_price' => (float) (optional($item->product)->purchase_price ?? 0),
                'discount' => (float) $item->Discount_Value,
            ];
        })->values();

        return view('delivery-note.edit', compact('invoice', 'Customer', 'taxes', 'defaultTaxRate', 'existingItems'));
    }

    /**
     * حفظ التعديل: نفس منطق الحساب المستخدم في store() بالظبط، ومفيش
     * أي حاجة نرجعها لأن سند التسليم المعلّق (زي عروض الأسعار) مالوش
     * أي أثر على المخزون أو القيود المحاسبية أو رصيد العميل - فبنعلّم
     * البنود القديمة save=0 (نفس أسلوب "الحذف الناعم" المتّبع في باقي
     * الموديول ده) ونضيف البنود الجديدة save=1 جوه ترانزاكشن واحدة.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('delivery_note.edit');

        $invoice = DeliveryNote::findOrFail($id);

        if (!$invoice->isEditable()) {
            return redirect()->route('deliverynote.show', $invoice->id)
                ->with('error', __('deliverynote.not_editable'));
        }

        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'customer_id' => ['required'],
            'note' => ['nullable', 'string'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.name' => ['nullable', 'string'],
            'items.*.code' => ['nullable', 'string'],
        ])->validate();

        // بنتأكد تاني جوه الترانزاكشن إن السند لسه قابل للتعديل - عشان
        // نقفل احتمال (نادر لكن وارد) إن حد يعتمد جزء منه أو يرجّع منه
        // في نفس اللحظة اللي المستخدم فاتح فيها فورم التعديل وضاغط حفظ.
        $stillEditable = DB::transaction(function () use ($validated, $invoice) {
            $invoice->refresh();

            if (!$invoice->isEditable()) {
                return false;
            }

            $totalQuantity = 0;
            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $totalQuantity += $item['quantity'];
                $totalPrice += ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            }

            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'Price' => $totalPrice,
                'Number_of_Quantity' => $totalQuantity,
                'note' => $validated['note'] ?? '-',
            ]);

            $invoice->items()->where('save', 1)->update(['save' => 0]);

            foreach ($validated['items'] as $item) {
                DeliveryNoteItem::create([
                    'product_id' => $item['product_id'],
                    'invoice_id' => $invoice->id,
                    'Discount_Value' => $item['discount'] ?? 0,
                    'branch_id' => $invoice->branchs_id,
                    'Unit_Price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'save' => 1,
                ]);
            }

            return true;
        });

        if (!$stillEditable) {
            return redirect()->route('deliverynote.show', $invoice->id)
                ->with('error', __('deliverynote.not_editable'));
        }

        return redirect()->route('deliverynote.show', $invoice->id)
            ->with('success', __('deliverynote.updated_successfully'));
    }
}
