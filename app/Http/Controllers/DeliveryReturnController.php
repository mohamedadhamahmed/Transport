<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\delivery_to_customer_withoud_tax_invoices;
use App\Models\sales_withoud_taxes;
use App\Models\Product;          // عدّل اسم الموديل حسب موديل المنتجات عندك
use App\Models\Customer;         // عدّل اسم الموديل حسب موديل العملاء عندك
use App\Models\FinancialAccount;  // موديل الحسابات المالية (للقيود المحاسبية)
use App\Models\CreditTransaction; // موديل حركات القيد المحاسبي
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DeliveryReturnController extends Controller
{
    /**
     * عرض فورم إنشاء مرتجع لفاتورة تسليم معينة
     */
    public function create($id)
    {
        $this->authorize('delivery.view');

        $invoice = delivery_to_customer_withoud_tax_invoices::with('customer')->findOrFail($id);
        $items = sales_withoud_taxes::where('invoice_id', $id)
            ->where('save', 1)
            ->with('product')
            ->get();

        return view('delivery.return', compact('invoice', 'items'));
    }

    /**
     * تنفيذ عملية المرتجع + تحديث الكميات + عكس القيود المحاسبية (بدون ضريبة)
     */
    public function store(Request $request, $id)
    {
        $this->authorize('delivery.view');

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
            $invoice = delivery_to_customer_withoud_tax_invoices::findOrFail($id);
            $branchId = $invoice->branchs_id;
            $now = Carbon::now('Asia/Riyadh');

            $totalReturn = 0;   // إجمالي قيمة البيع للمرتجع (Unit_Price × الكمية)
            $totalReturnCost = 0; // إجمالي تكلفة الشراء للمرتجع (لعكس قيد المخزون/التكلفة)
            $hasReturn = false;

            $productIds = collect($items)->pluck('id')->map(function ($itemId) {
                return sales_withoud_taxes::find($itemId)->product_id ?? null;
            })->filter()->unique();
            $ProductData = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $itemData) {
                $returnQty = floatval($itemData['return_qty'] ?? 0);

                if ($returnQty <= 0) {
                    continue;
                }

                $item = sales_withoud_taxes::findOrFail($itemData['id']);

                // حماية جانب السيرفر: منع إرجاع كمية أكبر من المتاح
                $available = $item->quantity - $item->quantityreturn;
                if ($returnQty > $available) {
                    throw new \Exception(__('delivery.return_error_exceed') . " ({$available})");
                }

                $item->quantityreturn += $returnQty;
                $item->save();

                $lineReturnValue = $returnQty * $item->Unit_Price;
                $totalReturn += $lineReturnValue;

                $purchasePrice = optional($ProductData->get($item->product_id))->purchase_price ?? 0;
                $totalReturnCost += $purchasePrice * $returnQty;

                $hasReturn = true;

                // إرجاع الكمية للمخزون (اختياري)
                // Product::where('id', $item->product_id)->increment('stock_quantity', $returnQty);
            }

            if (!$hasReturn) {
                throw new \Exception(__('delivery.return_error_none'));
            }

            // ===== تحديث حالة السند الأصلي =====
            $allItems = sales_withoud_taxes::where('invoice_id', $id)->get();
            $fullyReturned = $allItems->every(fn($i) => $i->quantityreturn >= $i->quantity);
            $partiallyReturned = $allItems->contains(fn($i) => $i->quantityreturn > 0);

            if ($fullyReturned) {
                $invoice->status = 1; // مرتجعة بالكامل
            } elseif ($partiallyReturned) {
                $invoice->status = 2; // مرتجعة جزئيًا
            }
            $invoice->save();

            // ===== القيود المحاسبية العكسية (بدون ضريبة) =====
            $customerId = $invoice->customer_id;
            $customerData = Customer::find($customerId);
            $noteText = 'مرتجع سند تسليم رقم :' . $invoice->id;

            // أ. عكس الإيراد (خصم من حساب المبيعات/الإيرادات - مدين هذه المرة)
            $revenueAccount = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', $branchId)->first();
            if ($revenueAccount && $totalReturn > 0) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $revenueAccount->id,
                    'recive_amount' => $totalReturn,
                    'branchs_id' => $branchId,
                    'pay_method' => 'cash',
                    'note' => $noteText,
                    'currentblance' => $revenueAccount->current_balance - $totalReturn,
                    'Pay_Method_Name' => 'Cash',
                    'created_at' => $now,
                    'updated_at' => $now,
                    'debtor' => $totalReturn,
                    'operation_type' => 6, // نوع عملية مختلف يميّز أنه مرتجع
                    'invoice_number' => $invoice->id,
                ]);
                $revenueAccount->update([
                    'current_balance' => $revenueAccount->current_balance - $totalReturn,
                    'debtor_current' => $revenueAccount->debtor_current + $totalReturn,
                ]);
            }

            // ب. عكس تكلفة البضاعة المباعة والمخزون (إرجاع البضاعة للمخزن)
            if ($totalReturnCost > 0) {
                $costAccount = FinancialAccount::where('parent_account_number', 183)->where('branchs_id', $branchId)->first();
                if ($costAccount) {
                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $costAccount->id,
                        'recive_amount' => $totalReturnCost,
                        'branchs_id' => $branchId,
                        'pay_method' => 'cash',
                        'note' => $noteText,
                        'currentblance' => $costAccount->current_balance - $totalReturnCost,
                        'Pay_Method_Name' => 'Cash',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'creditor' => $totalReturnCost,
                        'operation_type' => 6,
                        'invoice_number' => $invoice->id,
                    ]);
                    $costAccount->update([
                        'current_balance' => $costAccount->current_balance - $totalReturnCost,
                        'creditor_current' => $costAccount->creditor_current + $totalReturnCost,
                    ]);
                }

                $inventoryAccount = FinancialAccount::where('parent_account_number', 181)->where('branchs_id', $branchId)->first();
                if ($inventoryAccount) {
                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $inventoryAccount->id,
                        'recive_amount' => $totalReturnCost,
                        'branchs_id' => $branchId,
                        'pay_method' => 'cash',
                        'note' => $noteText,
                        'currentblance' => $inventoryAccount->current_balance + $totalReturnCost,
                        'Pay_Method_Name' => 'Cash',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'debtor' => $totalReturnCost,
                        'operation_type' => 6,
                        'invoice_number' => $invoice->id,
                    ]);
                    $inventoryAccount->update([
                        'current_balance' => $inventoryAccount->current_balance + $totalReturnCost,
                        'debtor_current' => $inventoryAccount->debtor_current + $totalReturnCost,
                    ]);
                }
            }

            // ج. عكس أثر الدفع (نفترض المرتجع بيرجع نقدًا من الخزينة، أو
            // يُخصم من رصيد العميل الآجل لو كان جزء من الفاتورة على الحساب)
            $wasFullyCash = $invoice->creaditamount <= 0;

            if ($wasFullyCash) {
                $cashAccount = FinancialAccount::where('parent_account_number', 5)->where('branchs_id', $branchId)->first();
                if ($cashAccount) {
                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $cashAccount->id,
                        'recive_amount' => $totalReturn,
                        'branchs_id' => $branchId,
                        'pay_method' => 'cash',
                        'note' => $noteText,
                        'currentblance' => $cashAccount->current_balance - $totalReturn,
                        'Pay_Method_Name' => 'Cash',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'creditor' => $totalReturn,
                        'operation_type' => 6,
                        'invoice_number' => $invoice->id,
                    ]);
                    $cashAccount->update([
                        'current_balance' => $cashAccount->current_balance - $totalReturn,
                        'creditor_current' => $cashAccount->creditor_current + $totalReturn,
                    ]);
                }
            } else {
                // كان جزء من الفاتورة آجل - نخصم من مديونية العميل
                if ($customerData) {
                    $customerData->decrement('Balance', min($totalReturn, $customerData->Balance));
                }

                $customerFinancialAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
                if ($customerFinancialAccount) {
                    $customerFinancialAccount->update([
                        'current_balance' => $customerFinancialAccount->current_balance - $totalReturn,
                        'creditor_current' => $customerFinancialAccount->creditor_current + $totalReturn,
                    ]);

                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $customerFinancialAccount->id,
                        'recive_amount' => $totalReturn,
                        'branchs_id' => $branchId,
                        'pay_method' => 'credit',
                        'note' => $noteText,
                        'currentblance' => $customerFinancialAccount->current_balance,
                        'Pay_Method_Name' => 'Credit',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'creditor' => $totalReturn,
                        'operation_type' => 6,
                        'invoice_number' => $invoice->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('delivery.history')
                ->with('success', __('delivery.return_success') . ' ' . number_format($totalReturn, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
