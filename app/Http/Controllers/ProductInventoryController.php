<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * قسم المنتجات والمخزون:
 * - قائمة "جميع المنتجات" مع فلترة إجبارية بالفرع أولاً، ثم فلترة اختيارية بالفئة/الرقم.
 * - تعديل بيانات منتج.
 * - تعديل كمية المخزون (منفصل عن تعديل البيانات العامة).
 * - رفع ملف إكسيل لتحديث كميات المخزون بالجملة عبر أرقام المنتجات.
 */
class ProductInventoryController extends Controller
{

// عرض فورم إضافة فئة جديدة
public function createGroup()
{
    $this->authorize('products.groups');

    return view('products.groups.create');
}

// حفظ الفئة الجديدة في جدول productgroup
public function storeGroup(Request $request)
{
    $this->authorize('products.groups');

    $validated = $request->validate([
        'group_ar' => ['required', 'string', 'max:255'],
        'group_en' => ['nullable', 'string', 'max:255'],
    ]);

    DB::table('productgroup')->insert([
        'group_ar' => $validated['group_ar'],
        'group_en' => $validated['group_en'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->back()->with('success', __('تم إنشاء الفئة بنجاح'));
}
    /**
     * الخطوة الأولى: اختيار الفرع (صفحة بسيطة بزرار/قائمة فروع)
     */

    public function chooseBranch()
    {
        $this->authorize('products.view');

        $branches = Branch::orderBy('name')->get();

        return view('products.choose-branch', compact('branches'));
    }

    /**
     * قائمة "جميع المنتجات" الخاصة بفرع معيّن + فلترة بالفئة/الرقم
     */
public function index(Request $request, Branch $branch)
{
    $this->authorize('products.view');

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

    // لو طلب partial (AJAX)، رجّع الجدول بس بدون الـ layout
    if ($request->boolean('partial')) {
        return view('products._table', compact('products'));
    }

    // التعديل الأول: استخدام productgroup
    $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();

    return view('products.index', compact('products', 'branch', 'productGroups'));
}
public function edit(Product $product)
{
    $this->authorize('products.edit');

    // جلب الفئات من جدول productgroup
    $productGroups = DB::table('productgroup')->orderBy('group_ar')->get();
    // التأكد من تمرير $productGroups عبر دالة compact
    return view('products.edit', compact('product', 'productGroups'));
}
public function update(Request $request, Product $product)
    {
        $this->authorize('products.edit');

    dd( $request);
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

        return redirect()->route('products.index', $product->branch_id)
            ->with('success', __('products.updated_success'));
    }
    /**
     * تعديل كمية المخزون فقط
     */
    public function updateStock(Request $request, Product $product)
    {
        $this->authorize('products.edit');

        $validated = $request->validate([
            'stock_quantity' => ['required', 'numeric'],
            'reason' => ['nullable', 'string'],
        ]);

        $product->update(['stock_quantity' => $validated['stock_quantity']]);

        return redirect()->back()->with('success', __('products.stock_updated_success'));
    }

    /**
     * عرض فورم رفع ملف إكسيل (تحديث كميات بالجملة / مخزون افتتاحي)
     */
    public function showImportForm(Branch $branch)
    {
        $this->authorize('products.edit');

        return view('products.import', compact('branch'));
    }

    /**
     * معالجة ملف الإكسيل
     */
    public function importExcel(Request $request, Branch $branch)
    {
        $this->authorize('products.edit');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'mode' => ['required', 'in:opening_stock,adjustment'],
        ]);

        $path = $request->file('file')->getRealPath();

        // قراءة الملف عبر مكتبة Maatwebsite Excel بالطريقة الصحيحة والمتوافقة
        $rows = Excel::toArray(
            new class implements \Maatwebsite\Excel\Concerns\ToCollection {
                public function collection(\Illuminate\Support\Collection $collection) {
                    return $collection;
                }
            }, 
            $path
        )[0] ?? [];

        $updated = 0;
        $notFound = [];

        DB::transaction(function () use ($rows, $branch, $request, &$updated, &$notFound) {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // تخطي صف العناوين
                }

                $productCode = trim((string) ($row[0] ?? ''));
                $quantity = (float) ($row[1] ?? 0);

                if ($productCode === '') {
                    continue;
                }

                $product = Product::where('branch_id', $branch->id)
                    ->where('code', $productCode)
                    ->first();

                if (!$product) {
                    $notFound[] = $productCode;
                    continue;
                }

                if ($request->mode === 'opening_stock') {
                    $product->update(['stock_quantity' => $quantity]);
                } else {
                    $product->increment('stock_quantity', $quantity);
                }

                $updated++;
            }
        });

        $message = __('products.import_success', ['count' => $updated]);
        if (!empty($notFound)) {
            $message .= ' - ' . __('products.import_not_found', ['codes' => implode(', ', array_slice($notFound, 0, 10))]);
        }

        return redirect()->route('products.index', $branch->id)->with('success', $message);
    }
}