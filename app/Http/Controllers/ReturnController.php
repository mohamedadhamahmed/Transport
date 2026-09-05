<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReturnController extends Controller
{
    /**
     * شاشة "مرتجع المبيعات": البحث عن فاتورة برقمها، ولو لقيناها بنعرض
     * أصنافها. الكمية "المتاح للإرجاع" بتتحسب من invoice_items.quantity
     * ناقص invoice_items.returned_quantity (مش من جدول تاني منفصل).
     */
    public function index(Request $request)
    {
        $this->authorize('invoices.returns');

        $invoice = null;
        $notFound = false;

        if ($request->filled('invoice_number')) {
            $invoice = Invoice::with(['customer', 'branch', 'items.product'])
                ->where('invoice_number', $request->string('invoice_number'))
                ->first();

            if (! $invoice) {
                $notFound = true;
            }
        }

        $previousReturns = InvoiceReturn::with(['invoice', 'product'])
            ->latest()
            ->limit(15)
            ->get();

        return view('returns.index', compact('invoice', 'notFound', 'previousReturns'));
    }

    public function store(Request $request)
    {
        $this->authorize('invoices.returns');

        $validated = Validator::make($request->all(), [
            'invoice_id' => ['required', 'exists:invoices,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_item_id' => ['required', 'exists:invoice_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ])->validate();

        $invoice = Invoice::with('items')->findOrFail($validated['invoice_id']);

        // نستبعد أي سطر الكمية فيه صفر (يعني المستخدم مسيبه من غير إرجاع)
        $requestedItems = collect($validated['items'])->filter(fn ($row) => $row['quantity'] > 0)->values();

        if ($requestedItems->isEmpty()) {
            return back()->withErrors(['items' => __('returns.no_items_to_return')])->withInput();
        }

        foreach ($requestedItems as $row) {
            $invoiceItem = $invoice->items->firstWhere('id', (int) $row['invoice_item_id']);

            if (! $invoiceItem) {
                return back()->withErrors(['items' => __('returns.invalid_item')])->withInput();
            }

            // بنحسب المتاح من quantity - returned_quantity مباشرة (مش من
            // عمود remaining_quantity المخزّن) عشان نضمن الرقم صح حتى لو
            // كانت الفاتورة اتعملت قبل ما نظبط تسجيل remaining_quantity.
            $remaining = $invoiceItem->quantity - $invoiceItem->returned_quantity;

            if ($row['quantity'] > $remaining) {
                return back()->withErrors(['items' => __('returns.max_return_error')])->withInput();
            }
        }

        DB::transaction(function () use ($invoice, $requestedItems) {
            foreach ($requestedItems as $row) {
                $invoiceItem = $invoice->items->firstWhere('id', (int) $row['invoice_item_id']);
                $quantity = (float) $row['quantity'];

                // متوسط سعر الوحدة بعد خصم السطر الأصلي، وخصم متناسب مع
                // الكمية المرتجعة (لو الإرجاع جزئي)
                $unitNet = $invoiceItem->quantity > 0
                    ? (($invoiceItem->unit_price * $invoiceItem->quantity) - $invoiceItem->discount_amount) / $invoiceItem->quantity
                    : $invoiceItem->unit_price;

                $lineDiscount = $invoiceItem->quantity > 0
                    ? ($invoiceItem->discount_amount / $invoiceItem->quantity) * $quantity
                    : 0;

                $taxRate = (float) $invoiceItem->tax_rate;
                $lineTax = $unitNet * $quantity * $taxRate;
                $lineTotal = ($unitNet * $quantity) + $lineTax;

                InvoiceReturn::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $invoiceItem->product_id,
                    'branch_id' => $invoice->branch_id,
                    'unit_price' => round($unitNet, 2),
                    'quantity' => $quantity,
                    'tax_amount' => round($lineTax, 2),
                    'tax_rate' => $invoiceItem->tax_rate,
                    'discount_amount' => round($lineDiscount, 2),
                    'invoice_discount_amount' => 0,
                    'card_refund_amount' => $invoice->payment_method === 'card' ? round($lineTotal, 2) : 0,
                    'is_sent_to_zatca' => false,
                    'created_by' => Auth::id(),
                ]);

                $newReturnedQuantity = $invoiceItem->returned_quantity + $quantity;
                $invoiceItem->update([
                    'returned_quantity' => $newReturnedQuantity,
                    'remaining_quantity' => $invoiceItem->quantity - $newReturnedQuantity,
                ]);

                // نرجع الكمية للمخزون بس لو الفاتورة الأصلية كانت نهائية
                if ($invoice->is_finalized) {
                    $product = Product::find($invoiceItem->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $quantity);
                        $product->decrement('total_sold', $quantity);
                    }
                }
            }
        });

        return redirect()->route('returns.index', ['invoice_number' => $invoice->invoice_number])
            ->with('success', __('returns.saved_successfully'));
    }
}
