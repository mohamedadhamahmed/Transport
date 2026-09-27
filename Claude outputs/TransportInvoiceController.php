<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\FinancialAccount;
use App\Models\TransportInvoice;
use App\Models\Truck;
use App\Support\OperationType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * فواتير النقليات: نفس فكرة فاتورة المبيعات بالظبط (عميل + فرع + طريقة
 * دفع + ضريبة + قيود محاسبية + رصيد العميل الآجل)، بس بدل ما السطر يبقى
 * "منتج × كمية" بقى "نقلة": تختار الشاحنة، تكتب من/إلى وسعر النقلة، ولو
 * فيه تحويلة تعلّم عليها وتكتب مكانها ومبلغها (بيتضاف على سعر النقلة).
 * مفيش مخزون ولا تكلفة بضاعة مباعة هنا.
 */
class TransportInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('transport_invoices.view');

        $query = TransportInvoice::query();

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->input('invoice_number') . '%');
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if ($request->filled('truck_id')) {
            $truckId = $request->input('truck_id');
            $query->whereHas('items', fn ($q) => $q->where('truck_id', $truckId));
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->input('date_to'));
        }

        // إجماليات الفواتير اللي طالعة في البحث الحالي (كل الصفحات مش الصفحة الحالية بس)
        $totals = (clone $query)->toBase()->selectRaw('
            COUNT(*) as invoices_count,
            COALESCE(SUM(trips_count),0) as trips_count,
            COALESCE(SUM(subtotal),0) as subtotal,
            COALESCE(SUM(tax_amount),0) as tax_amount,
            COALESCE(SUM(total),0) as total,
            COALESCE(SUM(credit_amount),0) as credit_amount
        ')->first();

        $invoices = $query->with(['customer:id,name', 'branch:id,name', 'creator:id,name'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::orderBy('name')->pluck('name', 'id');
        $trucks = Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name']);

        return view('transport.invoices.index', compact('invoices', 'totals', 'customers', 'trucks'));
    }

    public function create()
    {
        $this->authorize('transport_invoices.create');

        return view('transport.invoices.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('transport_invoices.create');

        $data = $this->validated($request);

        $invoice = DB::transaction(function () use ($data) {
            $invoice = new TransportInvoice();
            $invoice->created_by = Auth::id();
            $this->fillAndSave($invoice, $data);
            $invoice->update(['invoice_number' => 'TR-' . str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT)]);
            $this->recordAccounting($invoice);

            return $invoice;
        });

        return redirect()->route('transport.invoices.show', ['invoice' => $invoice, 'saved' => 1])
            ->with('success', __('transport.invoice_created'));
    }

    public function show(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.view');

        $invoice->load(['customer', 'branch', 'creator', 'items.truck.driver']);

        return view('transport.invoices.show', [
            'invoice' => $invoice,
            'qrData' => $this->zatcaQrData($invoice),
        ]);
    }

    public function edit(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.edit');

        $invoice->load('items');

        return view('transport.invoices.edit', $this->formData() + ['invoice' => $invoice]);
    }

    public function update(Request $request, TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.edit');

        $data = $this->validated($request);

        DB::transaction(function () use ($invoice, $data) {
            // نرجّع أثر الفاتورة القديمة بالكامل، وبعدين نسجلها من جديد
            $this->reverseAccounting($invoice);
            $invoice->items()->delete();

            $this->fillAndSave($invoice, $data);
            $this->recordAccounting($invoice->fresh());
        });

        return redirect()->route('transport.invoices.show', $invoice)
            ->with('success', __('transport.invoice_updated'));
    }

    public function destroy(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.delete');

        DB::transaction(function () use ($invoice) {
            $this->reverseAccounting($invoice);
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('transport.invoices.index')
            ->with('success', __('transport.invoice_deleted'));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone', 'tax_number']),
            'branches' => Branch::orderBy('name')->pluck('name', 'id'),
            'trucks' => Truck::with('driver:id,name')
                ->where('status', '!=', 'inactive')
                ->orderBy('plate_number')
                ->get(['id', 'plate_number', 'name', 'type', 'default_trip_price', 'driver_id']),
            'defaultBranchId' => Auth::user()->branch_id ?? Branch::orderBy('id')->value('id'),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'issue_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,credit,split'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],   // نسبة مئوية (15)
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.truck_id' => ['required', 'exists:trucks,id'],
            'items.*.trip_date' => ['required', 'date'],   // تاريخ الحمل لكل حمل
            'items.*.from_location' => ['nullable', 'string', 'max:255'],
            'items.*.to_location' => ['nullable', 'string', 'max:255'],
            'items.*.waybill_number' => ['nullable', 'string', 'max:255'],
            'items.*.trip_price' => ['required', 'numeric', 'min:0'],
            'items.*.has_transfer' => ['nullable', 'boolean'],
            'items.*.transfer_location' => ['nullable', 'string', 'max:255'],
            'items.*.transfer_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
        ], [], [
            'items' => __('transport.trips'),
            'items.*.truck_id' => __('transport.truck'),
            'items.*.trip_price' => __('transport.trip_price'),
            'items.*.trip_date' => __('transport.load_date'),
        ]);

        $data['items'] = array_values($data['items']);

        return $data;
    }

    /**
     * بتحسب الإجماليات وتحفظ الفاتورة + سطور النقلات.
     */
    private function fillAndSave(TransportInvoice $invoice, array $data): void
    {
        $tripsTotal = 0;
        $transfersTotal = 0;
        $lines = [];

        foreach ($data['items'] as $item) {
            $hasTransfer = !empty($item['has_transfer']);
            $tripPrice = round((float) $item['trip_price'], 2);
            $transferPrice = $hasTransfer ? round((float) ($item['transfer_price'] ?? 0), 2) : 0;

            $tripsTotal += $tripPrice;
            $transfersTotal += $transferPrice;

            $truck = Truck::find($item['truck_id']);

            $lines[] = [
                'truck_id' => $item['truck_id'],
                'truck_snapshot' => $truck?->display_name,
                'trip_date' => $item['trip_date'] ?? null,
                'from_location' => $item['from_location'] ?? null,
                'to_location' => $item['to_location'] ?? null,
                'waybill_number' => $item['waybill_number'] ?? null,
                'trip_price' => $tripPrice,
                'has_transfer' => $hasTransfer,
                'transfer_location' => $hasTransfer ? ($item['transfer_location'] ?? null) : null,
                'transfer_price' => $transferPrice,
                'line_total' => $tripPrice + $transferPrice,
                'note' => $item['note'] ?? null,
            ];
        }

        $gross = $tripsTotal + $transfersTotal;
        $discount = min(round((float) ($data['discount_amount'] ?? 0), 2), $gross);
        $subtotal = round($gross - $discount, 2);
        $taxRate = round(((float) $data['tax_rate']) / 100, 4);
        $taxAmount = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $taxAmount, 2);

        [$cash, $bank, $credit] = $this->paymentSplit(
            $data['payment_method'],
            $total,
            (float) ($data['cash_amount'] ?? 0),
            (float) ($data['bank_amount'] ?? 0)
        );

        $now = Carbon::now('Asia/Riyadh');

        $invoice->fill([
            'customer_id' => $data['customer_id'],
            'branch_id' => $data['branch_id'],
            'issue_date' => $data['issue_date'],
            'issue_time' => $invoice->issue_time ?? $now->toTimeString(),
            'trips_count' => count($lines),
            'trips_total' => $tripsTotal,
            'transfers_total' => $transfersTotal,
            'discount_amount' => $discount,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'payment_method' => $data['payment_method'],
            'cash_amount' => $cash,
            'bank_amount' => $bank,
            'credit_amount' => $credit,
            'po_number' => $data['po_number'] ?? null,
            'note' => $data['note'] ?? null,
        ]);
        $invoice->save();

        $invoice->items()->createMany($lines);
    }

    private function paymentSplit(string $method, float $total, float $cashIn, float $bankIn): array
    {
        return match ($method) {
            'cash' => [$total, 0, 0],
            'bank_transfer' => [0, $total, 0],
            'credit' => [0, 0, $total],
            'split' => (function () use ($total, $cashIn, $bankIn) {
                $cash = min($cashIn, $total);
                $bank = min($bankIn, $total - $cash);
                return [$cash, $bank, round(max(0, $total - $cash - $bank), 2)];
            })(),
            default => [$total, 0, 0],
        };
    }

    /**
     * القيود المحاسبية - نفس حسابات فاتورة المبيعات (InvoiceController::
     * recordInvoiceAccounting) بالظبط: خزينة (5) / بنك (4) / ضريبة (102)
     * / إيرادات (112) / حساب العميل للآجل - بس بنوع عملية مستقل
     * (TRANSPORT_INVOICE) عشان تتفرق عن فواتير المبيعات في كشوف الحساب.
     * مفيش تكلفة بضاعة ولا مخزون.
     */
    private function recordAccounting(TransportInvoice $invoice): void
    {
        if ((float) $invoice->total <= 0) {
            return;
        }

        $branchId = $invoice->branch_id;
        $customer = Customer::find($invoice->customer_id);
        $note = 'فاتورة نقليات رقم : ' . $invoice->invoice_number;
        $now = Carbon::now('Asia/Riyadh');

        $base = [
            'user_id' => Auth::id(),
            'branchs_id' => $branchId,
            'pay_method' => $invoice->payment_method,
            'Pay_Method_Name' => ucfirst($invoice->payment_method),
            'note' => $note,
            'operation_type' => OperationType::TRANSPORT_INVOICE,
            'invoice_number' => $invoice->invoice_number,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // أ. الخزينة
        if ((float) $invoice->cash_amount > 0) {
            $cashAccount = FinancialAccount::where('parent_account_number', 5)->where('branchs_id', $branchId)->first();
            if ($cashAccount) {
                CreditTransaction::create($base + [
                    'customer_id' => $cashAccount->id,
                    'recive_amount' => $invoice->cash_amount,
                    'currentblance' => $cashAccount->current_balance + $invoice->cash_amount,
                    'debtor' => $invoice->cash_amount,
                ]);
            }
        }

        // ب. البنك
        if ((float) $invoice->bank_amount > 0) {
            $bankAccount = FinancialAccount::where('parent_account_number', 4)->where('branchs_id', $branchId)->first();
            if ($bankAccount) {
                CreditTransaction::create($base + [
                    'customer_id' => $bankAccount->id,
                    'recive_amount' => $invoice->bank_amount,
                    'currentblance' => $bankAccount->current_balance + $invoice->bank_amount,
                    'debtor' => $invoice->bank_amount,
                ]);
            }
        }

        // ج. ضريبة القيمة المضافة (102)
        if ((float) $invoice->tax_amount > 0) {
            $vatAccount = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', $branchId)->first();
            if ($vatAccount) {
                $vatAccount->update([
                    'current_balance' => $vatAccount->current_balance + $invoice->tax_amount,
                    'creditor_current' => $vatAccount->creditor_current + $invoice->tax_amount,
                ]);

                CreditTransaction::create($base + [
                    'customer_id' => $vatAccount->id,
                    'recive_amount' => $invoice->tax_amount,
                    'currentblance' => $vatAccount->current_balance,
                    'creditor' => $invoice->tax_amount,
                    'vat' => 1,
                    'name' => $customer->name ?? '',
                ]);
            }
        }

        // د. الإيرادات (112)
        $revenueAccount = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', $branchId)->first();
        if ($revenueAccount) {
            CreditTransaction::create($base + [
                'customer_id' => $revenueAccount->id,
                'recive_amount' => $invoice->subtotal,
                'currentblance' => $revenueAccount->current_balance + $invoice->subtotal,
                'creditor' => $invoice->subtotal,
            ]);
        }

        // هـ. الآجل على العميل
        if ((float) $invoice->credit_amount > 0 && $customer) {
            $customer->increment('balance', $invoice->credit_amount);

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customer->id)->first();
            if ($customerAccount) {
                $customerAccount->update([
                    'current_balance' => $customerAccount->current_balance + $invoice->credit_amount,
                    'debtor_current' => $customerAccount->debtor_current + $invoice->credit_amount,
                ]);

                CreditTransaction::create($base + [
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => $invoice->credit_amount,
                    'currentblance' => $customerAccount->current_balance,
                    'debtor' => $invoice->credit_amount,
                ]);
            }
        }
    }

    /**
     * بترجع كل أثر محاسبي للفاتورة (قبل التعديل أو الحذف).
     */
    private function reverseAccounting(TransportInvoice $invoice): void
    {
        $branchId = $invoice->branch_id;

        if ((float) $invoice->tax_amount > 0) {
            $vatAccount = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', $branchId)->first();
            if ($vatAccount) {
                $vatAccount->update([
                    'current_balance' => $vatAccount->current_balance - $invoice->tax_amount,
                    'creditor_current' => $vatAccount->creditor_current - $invoice->tax_amount,
                ]);
            }
        }

        if ((float) $invoice->credit_amount > 0) {
            $customer = Customer::find($invoice->customer_id);
            if ($customer) {
                $customer->decrement('balance', $invoice->credit_amount);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $invoice->customer_id)->first();
            if ($customerAccount) {
                $customerAccount->update([
                    'current_balance' => $customerAccount->current_balance - $invoice->credit_amount,
                    'debtor_current' => $customerAccount->debtor_current - $invoice->credit_amount,
                ]);
            }
        }

        CreditTransaction::where('invoice_number', $invoice->invoice_number)
            ->where('operation_type', OperationType::TRANSPORT_INVOICE)
            ->delete();
    }

    /**
     * QR الزكاة (المرحلة الأولى - TLV base64): اسم البائع، الرقم الضريبي،
     * وقت الإصدار، الإجمالي شامل الضريبة، قيمة الضريبة.
     */
    private function zatcaQrData(TransportInvoice $invoice): string
    {
        $seller = defined('sallerQrCode') ? (string) constant('sallerQrCode') : (string) config('app.name');
        $vatNo = defined('TaxQrCode') ? (string) constant('TaxQrCode') : '';
        $time = $invoice->issue_date->format('Y-m-d') . 'T' . ($invoice->issue_time ?: '00:00:00');

        $tlv = '';
        foreach ([
            1 => $seller,
            2 => $vatNo,
            3 => $time,
            4 => number_format((float) $invoice->total, 2, '.', ''),
            5 => number_format((float) $invoice->tax_amount, 2, '.', ''),
        ] as $tag => $value) {
            $tlv .= chr($tag) . chr(strlen($value)) . $value;
        }

        return base64_encode($tlv);
    }
}
