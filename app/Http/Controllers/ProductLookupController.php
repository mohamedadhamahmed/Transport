<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * بحث/اختيار المنتجات (Ajax) المستخدم في شاشات المشتريات وأوامر الشراء
 * وتقرير حركة صنف. كان جوه InvoiceController قبل ما قسم المبيعات يتشال،
 * وأسماء المسارات فضلت زي ما هي (invoices.products.search / pick).
 */
class ProductLookupController extends Controller implements HasMiddleware
{
    // البحث عن المنتجات بيستخدمه أكتر من شاشة، فمسموح لأي حد معاه
    // صلاحية من الصلاحيات اللي بتفتح الشاشات دي.
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                abort_unless(\Illuminate\Support\Facades\Auth::user()?->hasAnyPermission([
                    'products.view', 'purchases.create', 'purchases.orders',
                    'reports_purchases.by_product', 'reports_products.stock',
                ]), 403);

                return $next($request);
            },
        ];
    }

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
}
