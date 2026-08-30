<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * مرتجع تسليمات - تعديل بحت على الكميات المعلّقة (رجّعها للمخزون
 * منطقيًا)، بدون أي قيد محاسبي عكسي، لأن التسليم الأصلي مكانش عليه أي
 * قيد من الأساس (شوف DeliveryNoteController للتفاصيل).
 */
class DeliveryNoteReturnController extends Controller
{
    /**
     * عرض فورم إنشاء مرتجع لسند تسليم معين
     */
    public function create($id)
    {
        $invoice = DeliveryNote::with('customer')->findOrFail($id);
        $items = DeliveryNoteItem::where('invoice_id', $id)
            ->where('save', 1)
            ->with('product')
            ->get();

        return view('delivery-note.return', compact('invoice', 'items'));
    }

    /**
     * تنفيذ عملية المرتجع - تحديث الكميات فقط، بدون أي أثر محاسبي.
     */
    public function store(Request $request, $id)
    {
        $items = json_decode((string) $request->input('items_json'), true) ?: [];
        $request->merge(['items' => $items]);

        $validated = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.return_qty' => ['nullable', 'numeric', 'min:0'],
            'return_note' => ['nullable', 'string'],
        ])->validate();

        $items = $validated['items'];

        DB::beginTransaction();

        try {
            $invoice = DeliveryNote::findOrFail($id);
            $hasReturn = false;

            foreach ($items as $itemData) {
                $returnQty = floatval($itemData['return_qty'] ?? 0);

                if ($returnQty <= 0) {
                    continue;
                }

                $item = DeliveryNoteItem::findOrFail($itemData['id']);

                // حماية: منع إرجاع كمية أكبر من المتاح (بعد استبعاد
                // المرتجع سابقًا والمُفوتَر بالفعل - لا يجوز إرجاع كمية
                // اتحولت لفاتورة حقيقية بالفعل من هنا، لازم يبقى إرجاعها
                // عن طريق نظام مرتجعات الفواتير الأصلي).
                $available = $item->quantity - $item->quantityreturn - $item->invoiced_quantity;
                if ($returnQty > $available) {
                    throw new \Exception(__('deliverynote.return_error_exceed') . " ({$available})");
                }

                $item->quantityreturn += $returnQty;
                $item->save();

                $hasReturn = true;

                // إرجاع الكمية للمخزون (اختياري - فعّله لو كنت فعّلت
                // النقص التلقائي وقت التسليم في DeliveryNoteController)
                // Product::where('id', $item->product_id)->increment('stock_quantity', $returnQty);
            }

            if (!$hasReturn) {
                throw new \Exception(__('deliverynote.return_error_none'));
            }

            // تحديث حالة السند: 1 = مرتجع بالكامل، 2 = مرتجع جزئيًا، يفضل
            // 0 لو لسه فيه كمية متاحة (للإرجاع أو للتحويل لفاتورة).
            $allItems = DeliveryNoteItem::where('invoice_id', $id)->get();
            $fullyResolved = $allItems->every(fn($i) => ($i->quantityreturn + $i->invoiced_quantity) >= $i->quantity);
            $partiallyReturned = $allItems->contains(fn($i) => $i->quantityreturn > 0);

            if ($fullyResolved && $allItems->every(fn($i) => $i->invoiced_quantity <= 0)) {
                $invoice->status = 1; // مرتجع بالكامل (ولا حاجة اتحولت لفاتورة)
            } elseif ($partiallyReturned) {
                $invoice->status = 2; // مرتجع جزئيًا
            }
            $invoice->save();

            DB::commit();

            return redirect()->route('deliverynote.history')->with('success', __('deliverynote.return_success_simple'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
