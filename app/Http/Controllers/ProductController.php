<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\Reports\ReportExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
public function index(Request $request, Branch $branch)
{
    $query = Product::where('branch_id', $branch->id);

    if ($request->filled('product_group')) {
        $query->where('product_group_id', $request->product_group);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        });
    }

    $products = $query->with('productGroup')->orderBy('name')->paginate(20)->withQueryString();
    if ($request->boolean('partial')) {
        return view('products._table', compact('products'));
    }

    $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

    return view('products.index', compact('products', 'branch', 'productGroups'));
}

    public function create()
    {
        $this->authorize('products.create');

        $branches = Branch::orderBy('name')->get();
        $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

        return view('products.create', compact('branches', 'productGroups'));
    }

    public function store(Request $request)
    {
        $this->authorize('products.create');

        $validated = $this->validated($request);
        $validated['created_by'] = Auth::id();

        $product = Product::create($validated);

        $this->syncAlternateLinks($request, $product);

        return redirect()->route('products.index', $product->branch_id)
            ->with('success', __('products.created_successfully'));
    }

    public function edit(Product $product)
    {
        $this->authorize('products.edit');

        $branches = Branch::orderBy('name')->get();
    $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

        return view('products.edit', compact('product', 'branches', 'productGroups'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('products.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'product_group_id' => ['required', 'exists:productgroup,id'],
            'status' => ['required'], // <-- إضافة قاعدة التحقق لحالة المنتج
        ]);

        // الحفاظ على الفرع الحالي للمنتج
        $validated['branch_id'] = $product->branch_id;

        $product->update($validated);

        $this->syncAlternateLinks($request, $product);

        return redirect()->route('products.index', $product->branch_id)
            ->with('success', __('products.updated_success'));

            }

    /**
     * ميزة "البدائل": لو تفعّل التوجل "هذا المنتج بديل لمنتج آخر" في فورم
     * إنشاء/تعديل المنتج، بنستبدل كل صفوف primaryProducts بتاعة المنتج ده
     * بالمنتجات الأساسية اللي اتحددت (sync = استبدال كامل، مش إضافة فوق
     * القديم) - ده بيغطي حالة إلغاء التفعيل أو شيل منتج أساسي من القايمة
     * برضه (sync بيمسح أي صف مش موجود في القايمة الجديدة). لو التوجل
     * متطفيش، بنصفّر كل الروابط (يرجع المنتج "أساسي" تلقائيًا).
     */
    private function syncAlternateLinks(Request $request, Product $product): void
    {
        if (! $request->boolean('is_alternate')) {
            $product->primaryProducts()->sync([]);
            return;
        }

        $primaryIds = collect((array) $request->input('primary_product_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $id === $product->id) // منتج مايبقاش بديل لنفسه
            ->values();

        // نتأكد إن كل الـ id فعلًا موجودة في جدول المنتجات قبل الـ sync -
        // أي id مش حقيقي (مثلاً جاي من تلاعب في الفورم) بيتجاهل بصمت بدل
        // ما يوقع استعلام الـ sync بخطأ foreign key.
        $validIds = Product::whereIn('id', $primaryIds)->pluck('id');

        $product->primaryProducts()->sync($validIds);
    }

    /**
     * بيانات مودال "العمليات" الخاص بمنتج واحد (JSON) - بيتفتح من زرار
     * "العمليات" في مودال اختيار منتج (الفواتير والمشتريات، نفس الـ
     * endpoint للاتنين) كمودال ثاني فوق مودال الاختيار من غير أي navigation.
     * بيجمع مبيعات + مشتريات + تحويلات المخزون الخاصة بالمنتج ده في جدول
     * واحد مرتب بالتاريخ تنازليًا، مفلتر اختياريًا بنوع العملية وبفترة
     * تاريخ - من غير ما نكرر منطق تقارير مركز التقارير (نفس صلاحياته
     * بالظبط: نوع العملية بيتشال تمامًا من النتيجة لو المستخدم مالوش
     * صلاحية عليه، مش بس بيتخفي في الواجهة).
     */
    public function operationsData(Request $request, Product $product)
    {
        $user = Auth::user();

        $canViewSales = false; // (قسم المبيعات اتشال)
        $canViewPurchases = (bool) $user?->hasPermission('reports_purchases.by_product');
        $canViewTransfers = false; // (تحويلات المخزون اتشالت)

        abort_unless($canViewSales || $canViewPurchases || $canViewTransfers, 403);

        $type = (string) $request->query('type', 'all');
        $dateFrom = $request->filled('date_from') ? $request->query('date_from') : null;
        $dateTo = $request->filled('date_to') ? $request->query('date_to') : null;

        $rows = collect();

        if ($canViewPurchases && in_array($type, ['all', 'purchases'], true)) {
            $rows = $rows->merge(
                PurchaseItem::query()
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->where('purchase_items.product_id', $product->id)
                    ->when($dateFrom, fn ($q) => $q->whereDate('purchases.issue_date', '>=', $dateFrom))
                    ->when($dateTo, fn ($q) => $q->whereDate('purchases.issue_date', '<=', $dateTo))
                    ->orderByDesc('purchases.issue_date')
                    ->orderByDesc('purchase_items.id')
                    ->limit(300)
                    ->get([
                        'purchases.purchase_number as document_number',
                        'purchases.issue_date as op_date',
                        'suppliers.name as entity_name',
                        'purchase_items.quantity as quantity',
                        'purchase_items.unit_price as price',
                    ])
                    ->map(fn ($r) => [
                        'document_number' => $r->document_number,
                        'product_name' => $product->name,
                        'date' => optional($r->op_date)->format('Y-m-d'),
                        'type_key' => 'purchases',
                        'type_label' => __('products.operations.type_purchases'),
                        'entity_name' => $r->entity_name ?? '-',
                        'quantity' => (float) $r->quantity,
                        'price' => (float) $r->price,
                    ])
            );
        }


        $rows = $rows->sortByDesc('date')->take(500)->values();

        // تصدير إكسيل لنفس الصفوف بالظبط اللي هترجع للمودال/تقرير "حركة
        // منتج" - نفس فلاتر النوع والتاريخ المطبّقة فوق (مفيش استعلام
        // تاني منفصل)، بنفس أسلوب ReportExcelExporter المستخدم في كل
        // تقارير مركز التقارير (ReportController) عشان نفضل على نفس
        // القالب من غير مكتبة/كنترولر تصدير جديد.
        if ($request->get('export') === 'excel') {
            return ReportExcelExporter::download(
                [
                    __('invoices.invoice_number'),
                    __('invoices.product'),
                    __('invoices.date'),
                    __('invoices.operation_type'),
                    __('invoices.operation_entity'),
                    __('invoices.quantity'),
                    __('invoices.unit_price'),
                ],
                $rows->map(fn ($r) => [
                    $r['document_number'] ?? '-',
                    $r['product_name'],
                    $r['date'] ?? '-',
                    $r['type_label'],
                    $r['entity_name'] ?? '-',
                    $r['quantity'],
                    $r['price'],
                ])->toArray(),
                'product-movement-' . ($product->code ?: $product->id) . '-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return response()->json(['rows' => $rows]);
    }

    /**
     * قايمة منتجات بديلة لمنتج معيّن (زرار "البدائل" في مودال اختيار
     * منتج) - نفس شكل بيانات pickProducts في InvoiceController عشان
     * الواجهة تقدر تعرضها بنفس شكل جدول الاختيار وتضيف أي بديل مباشرة.
     */
    public function alternatesData(Product $product)
    {
        $alternates = $product->alternates()
            ->with('branch:id,name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'branch_name' => $p->branch?->name,
                'location' => $p->location,
                'stock_quantity' => $p->stock_quantity,
                'purchase_price' => $p->purchase_price,
                'sale_price' => $p->sale_price,
                'notes' => $p->notes,
                'reference_number' => $p->reference_number,
            ])
            ->values();

        return response()->json($alternates);
    }

    /**
     * بحث Ajax عام (كل الفروع) بالاسم أو الكود - مستخدم في فورم إنشاء/
     * تعديل منتج عشان تختار المنتجات "الأساسية" اللي المنتج ده بديل ليها.
     * مختلف عن InvoiceController@searchProducts (اللي بيفلتر على فرع
     * المستخدم بس) لإن منتج ممكن يبقى بديل لمنتج في فرع تاني.
     */
    public function searchAlternates(Request $request)
    {
        // مستخدم من فورمي إنشاء وتعديل منتج مع بعض، فبنقبل أي صلاحية من
        // الاتنين (مش بس إنشاء) عشان مستخدم عنده صلاحية تعديل بس برضه
        // يقدر يستخدم البحث ده وهو بيعدّل منتج موجود.
        abort_unless(
            Auth::user()?->hasPermission('products.create') || Auth::user()?->hasPermission('products.edit'),
            403
        );

        $term = trim((string) $request->query('q', ''));
        $excludeId = $request->query('exclude_id');

        $products = Product::query()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                });
            })
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->with('branch:id,name')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'code', 'branch_id'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'text' => $p->name . ($p->code ? " ({$p->code})" : '') . ($p->branch ? ' - ' . $p->branch->name : ''),
            ]);

        return response()->json($products);
    }

    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $branchId = $product->branch_id;
        $product->delete();

        return redirect()->route('products.index', $branchId)
            ->with('success', __('products.deleted_successfully'));
    }

    private function validated(Request $request, $ignoreId = null): array
    {
        return $request->validate([
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
    }
}