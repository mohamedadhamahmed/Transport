<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * قسم المستودعات: تحويل منتجات بين فروع الشركة (سند صرف من فرع + سند
 * استلام في فرع تاني) - ميزة جديدة تمامًا، منفصلة عن DeliveryNote
 * (تسليم لعميل) وعن Invoice/receiving_branch_id (فواتير ضريبية حقيقية).
 *
 * كل فرع في my-erp عنده صفوف Product منفصلة بتاعته (مفيش "منتج" واحد
 * مشترك بين الفروع بكمية لكل فرع)، فلما يتم الاستلام بندوّر على منتج
 * بنفس الكود في الفرع المستلم ونزوّد كميته، ولو مش موجود بننشئه هناك من
 * بيانات السند (snapshot) بتاعت لحظة الصرف.
 */
class StockTransferController extends Controller
{
    /**
     * صفحة اختيار الفرع قبل فتح فورم صرف أو استلام (نفس أسلوب
     * products.choose_branch المتبع فعلاً في قسم المنتجات).
     */
    public function chooseBranch(Request $request)
    {
        $this->authorize('stock_transfers.view');

        $mode = $request->query('mode') === 'receive' ? 'receive' : 'dispatch';
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('stock-transfers.choose-branch', compact('branches', 'mode'));
    }

    /**
     * فورم سند الصرف (إرسال منتجات من $branch لفرع تاني)
     */
    public function create(Branch $branch)
    {
        $this->authorize('stock_transfers.create');

        $branches = Branch::where('id', '!=', $branch->id)->orderBy('name')->get(['id', 'name']);

        return view('stock-transfers.create', ['fromBranch' => $branch, 'branches' => $branches]);
    }

    /**
     * حفظ سند الصرف - إما مسودة (من غير خصم مخزون) أو صرف فعلي (بيخصم
     * فورًا من مخزون الفرع المرسل).
     */
    public function store(Request $request)
    {
        $this->authorize('stock_transfers.create');

        $validated = $request->validate([
            'from_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'to_branch_id' => ['required', 'integer', 'exists:branches,id', 'different:from_branch_id'],
            'receiver_user_id' => ['required', 'integer', 'exists:users,id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'action' => ['required', 'in:draft,send'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $receiverBelongsToBranch = User::where('id', $validated['receiver_user_id'])
            ->where('branch_id', $validated['to_branch_id'])
            ->exists();
        if (! $receiverBelongsToBranch) {
            abort(422, __('stock_transfers.invalid_receiver'));
        }

        $fromBranchId = (int) $validated['from_branch_id'];
        $isSending = $validated['action'] === 'send';

        // تحقق مبدئي قبل فتح الـ transaction: كل المنتجات فعلاً تابعة
        // للفرع المرسل، ولو صرف فعلي (مش مسودة) الكمية المطلوبة متاحة.
        $products = Product::where('branch_id', $fromBranchId)
            ->whereIn('id', collect($validated['items'])->pluck('product_id'))
            ->get()
            ->keyBy('id');

        foreach ($validated['items'] as $line) {
            $product = $products->get($line['product_id']);
            if (! $product) {
                abort(422, __('stock_transfers.product_not_in_branch'));
            }
            if ($isSending && (float) $product->stock_quantity < (float) $line['quantity']) {
                abort(422, __('stock_transfers.insufficient_stock', ['product' => $product->name]));
            }
        }

        $stockTransfer = DB::transaction(function () use ($validated, $fromBranchId, $isSending) {
            $nextSequence = (int) StockTransfer::max('id') + 1;
            $transferNumber = 'ST-' . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);

            $stockTransfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $validated['to_branch_id'],
                'sender_user_id' => Auth::id(),
                'receiver_user_id' => $validated['receiver_user_id'],
                'status' => $isSending ? StockTransfer::STATUS_SENT : StockTransfer::STATUS_DRAFT,
                'notes' => $validated['notes'] ?? null,
                'transfer_date' => $validated['transfer_date'],
                'sent_at' => $isSending ? now() : null,
            ]);

            foreach ($validated['items'] as $line) {
                $product = Product::where('id', $line['product_id'])
                    ->where('branch_id', $fromBranchId)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    abort(422, __('stock_transfers.product_not_in_branch'));
                }

                $quantity = round((float) $line['quantity'], 2);

                if ($isSending && (float) $product->stock_quantity < $quantity) {
                    abort(422, __('stock_transfers.insufficient_stock', ['product' => $product->name]));
                }

                $stockTransfer->items()->create([
                    'from_product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'product_code_snapshot' => $product->code,
                    'unit_snapshot' => $product->unit,
                    'quantity' => $quantity,
                    'unit_cost_snapshot' => $product->average_cost > 0 ? $product->average_cost : $product->purchase_price,
                ]);

                if ($isSending) {
                    $product->decrement('stock_quantity', $quantity);
                }
            }

            return $stockTransfer;
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', $isSending
                ? __('stock_transfers.sent_successfully')
                : __('stock_transfers.draft_saved_successfully'));
    }

    /**
     * قائمة السندات (تبويبات: مُرسلة / مُستلمة / مسودات)
     */
    public function index(Request $request)
    {
        $this->authorize('stock_transfers.view');

        $box = in_array($request->query('box'), ['sent', 'received', 'draft']) ? $request->query('box') : 'sent';

        $query = StockTransfer::with(['fromBranch', 'toBranch', 'senderUser', 'receiverUser']);

        if ($box === 'draft') {
            $query->where('status', StockTransfer::STATUS_DRAFT);
        } elseif ($box === 'received') {
            $query->where('status', StockTransfer::STATUS_RECEIVED);
        } else {
            $query->where('status', StockTransfer::STATUS_SENT);
        }

        $transfers = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('stock-transfers.index', compact('transfers', 'box'));
    }

    public function show(StockTransfer $stockTransfer)
    {
        $this->authorize('stock_transfers.view');

        $stockTransfer->load(['fromBranch', 'toBranch', 'senderUser', 'receiverUser', 'items']);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    /**
     * فورم سند الاستلام (استلام منتجات في $branch من فرع تاني)
     */
    public function receiveForm(Branch $branch)
    {
        $this->authorize('stock_transfers.create');

        $pendingTransfers = StockTransfer::where('to_branch_id', $branch->id)
            ->where('status', StockTransfer::STATUS_SENT)
            ->orderByDesc('id')
            ->get(['id', 'transfer_number']);

        return view('stock-transfers.receive', ['toBranch' => $branch, 'pendingTransfers' => $pendingTransfers]);
    }

    /**
     * تفاصيل سند مُرسل (AJAX) - بيستخدمها فورم الاستلام لما تتحدد قيمة
     * "رقم الفاتورة" عشان يعرض الفرع المرسل والموظف المرسل والأصناف.
     */
    public function transferDetails(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromBranch', 'senderUser', 'items']);

        return response()->json([
            'from_branch' => $stockTransfer->fromBranch?->name,
            'sender' => $stockTransfer->senderUser?->name,
            'transfer_date' => optional($stockTransfer->transfer_date)->format('Y-m-d'),
            'notes' => $stockTransfer->notes,
            'items' => $stockTransfer->items->map(fn ($item) => [
                'name' => $item->product_name_snapshot,
                'code' => $item->product_code_snapshot,
                'unit' => $item->unit_snapshot,
                'quantity' => (float) $item->quantity,
            ]),
        ]);
    }

    /**
     * تأكيد الاستلام: بيدوّر على منتج بنفس الكود (أو الاسم لو مفيش كود)
     * في الفرع المستلم ويزوّد كميته، ولو مش موجود بينشئه من بيانات
     * السند (snapshot).
     */
    public function confirmReceive(Request $request, StockTransfer $stockTransfer)
    {
        $this->authorize('stock_transfers.create');

        if (! $stockTransfer->isSent()) {
            abort(422, __('stock_transfers.not_pending'));
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load('items.fromProduct');

            foreach ($stockTransfer->items as $item) {
                $destinationProduct = Product::where('branch_id', $stockTransfer->to_branch_id)
                    ->where(function ($q) use ($item) {
                        if ($item->product_code_snapshot) {
                            $q->where('code', $item->product_code_snapshot);
                        } else {
                            $q->where('name', $item->product_name_snapshot);
                        }
                    })
                    ->lockForUpdate()
                    ->first();

                if ($destinationProduct) {
                    $destinationProduct->increment('stock_quantity', (float) $item->quantity);
                } else {
                    $sourceProduct = $item->fromProduct;

                    $destinationProduct = Product::create([
                        'name' => $item->product_name_snapshot,
                        'name_en' => $sourceProduct->name_en ?? null,
                        'branch_id' => $stockTransfer->to_branch_id,
                        'code' => $item->product_code_snapshot,
                        'unit' => $item->unit_snapshot ?? 'piece',
                        'purchase_price' => $item->unit_cost_snapshot,
                        'average_cost' => $item->unit_cost_snapshot,
                        'sale_price' => $sourceProduct->sale_price ?? 0,
                        'wholesale_price' => $sourceProduct->wholesale_price ?? 0,
                        'stock_quantity' => (float) $item->quantity,
                        'status' => 'active',
                        'created_by' => Auth::id(),
                        'product_group_id' => $sourceProduct->product_group_id ?? 1,
                    ]);
                }

                $item->update(['to_product_id' => $destinationProduct->id]);
            }

            $stockTransfer->update([
                'status' => StockTransfer::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', __('stock_transfers.received_successfully'));
    }

    /**
     * بحث AJAX عن منتجات فرع معيّن - بيستخدمها كل من صندوق البحث السريع
     * (اقتراحات فورية أثناء الكتابة) ومودال "اختيار منتج" (بحث + صفحات)
     * في سند الصرف. نفس شكل استجابة InvoiceController::pickProducts()
     * (data + صفحات) عشان يبقى بنفس أسلوب اختيار المنتج المستخدم في
     * المبيعات والمشتريات والتسليمات.
     *
     * ملحوظة: مبنفلترش هنا لا على status ولا على stock_quantity > 0 -
     * نفس أسلوب InvoiceController::searchProducts()/pickProducts() -
     * عشان تظهر كل منتجات الفرع في الاقتراحات حتى لو مخزونها صفر (المستخدم
     * قادر يشوف المنتج ويختاره، والتحقق من كفاية الكمية بيحصل عند الحفظ).
     */
    public function searchProducts(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ]);

        $search = (string) $request->query('q', '');

        $products = Product::where('branch_id', $validated['branch_id'])
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
                    'unit' => $p->unit,
                    'stock_quantity' => $p->stock_quantity,
                    'purchase_price' => $p->purchase_price,
                    'average_cost' => $p->average_cost,
                ];
            })->values(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * بحث AJAX عن موظفي فرع معيّن (لاختيار "الموظف المستلم" في سند
     * الصرف بعد تحديد الفرع المستلم).
     */
    public function branchUsers(Branch $branch)
    {
        $users = User::where('branch_id', $branch->id)->orderBy('name')->get(['id', 'name']);

        return response()->json($users);
    }
}
