<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\FinancialAccount;
use App\Models\TransportInvoice;
use App\Models\TransportQuotation;
use App\Models\Waybill;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Support\OperationType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        // أول فتح للشاشة: الشهر الحالي (المسودات بتظهر كلها)
        if (!$request->has('date_from') && !$request->has('date_to') && $request->input('status') !== 'draft') {
            $request->merge(['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]);
        }

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
        // بحث حر: رقم الفاتورة / اسم العميل / المرجع (رقم أمر الشراء)
        if ($request->filled('q')) {
            $term = '%' . trim($request->input('q')) . '%';
            $query->where(function ($w) use ($term) {
                $w->where('invoice_number', 'like', $term)
                    ->orWhere('po_number', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('tax_number', 'like', $term));
            });
        }
        // المسودات بتتعرض لوحدها (?status=draft)، والقايمة العادية = الفواتير المعتمدة
        $status = $request->input('status');
        if ($status === 'draft') {
            $query->where('is_draft', true);
        } elseif ($status === 'zatca_sent' || $status === 'zatca_pending') {
            $query->where('is_draft', false)->where('is_sent_to_zatca', $status === 'zatca_sent');
        } elseif ($status !== 'all') {
            $query->where('is_draft', false);
        }
        if ($request->filled('zatca')) {
            $query->where('is_sent_to_zatca', $request->input('zatca') === 'sent');
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

        $draftsCount = TransportInvoice::where('is_draft', true)->count();

        $invoices = $query->with(['customer:id,name,tax_number', 'branch:id,name', 'creator:id,name'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::orderBy('name')->pluck('name', 'id');
        $trucks = Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name']);

        return view('transport.invoices.index', compact('invoices', 'totals', 'customers', 'trucks', 'draftsCount'));
    }

    public function create(Request $request)
    {
        $this->authorize('transport_invoices.create');

        // جاي من "تحويل عرض سعر لفاتورة": نجهّز الفاتورة ببيانات العرض،
        // وكل نقلة في العرض بتبقى سطر (المستخدم بيختار الشاحنة لكل سطر).
        $prefill = null;
        if ($request->filled('quotation')) {
            $quotation = TransportQuotation::with('items')->find($request->input('quotation'));
            if ($quotation && $quotation->status !== 'converted') {
                $items = [];
                foreach ($quotation->items as $qi) {
                    for ($i = 0; $i < min((int) $qi->trips_count, 100); $i++) {
                        $items[] = [
                            'truck_id' => null,
                            'trip_date' => now()->toDateString(),
                            'from_location' => $qi->from_label,
                            'to_location' => $qi->to_label,
                            'trip_price' => (float) $qi->trip_price,
                            'has_transfer' => (bool) $qi->has_transfer,
                            'transfer_location' => $qi->transfer_location,
                            'transfer_price' => (float) $qi->transfer_price,
                            'note' => trim(($qi->load_type ?? '') . ' ' . ($qi->note ?? '')) ?: null,
                        ];
                    }
                }
                $prefill = [
                    'quotation' => $quotation,
                    'customer_id' => $quotation->customer_id,
                    'branch_id' => $quotation->branch_id,
                    'tax_rate' => round($quotation->tax_rate * 100, 2),
                    'tax_type' => $quotation->tax_type ?? 'standard',
                    'discount_amount' => (float) $quotation->discount_amount,
                    'items' => $items,
                ];
            }
        }

        // جاي من "إنشاء فاتورة" في بوليصة شحن: سطر واحد جاهز بالشاحنة والمسار والأجرة
        if (!$prefill && $request->filled('waybill')) {
            $wb = Waybill::find($request->input('waybill'));
            if ($wb && !$wb->transport_invoice_id) {
                $prefill = [
                    'waybill' => $wb,
                    'customer_id' => $wb->customer_id,
                    'items' => [[
                        'truck_id' => $wb->truck_id,
                        'trip_date' => $wb->departure_date?->toDateString() ?? $wb->loaded_at?->toDateString() ?? now()->toDateString(),
                        'from_location' => $wb->from_label !== '-' ? $wb->from_label : null,
                        'to_location' => $wb->to_label !== '-' ? $wb->to_label : null,
                        'waybill_number' => $wb->waybill_number,
                        'trip_price' => (float) $wb->freight_amount,
                        'has_transfer' => false,
                        'note' => $wb->goods_description,
                    ]],
                ];
            }
        }

        if ($prefill) {
            return view('transport.invoices.create', $this->formData() + ['prefill' => $prefill]);
        }

        // الشاشة الجديدة: عميل ← أحماله غير المفوترة ← فاتورة
        return view('transport.invoices.create-loads', $this->loadsFormData($request));
    }

    public function store(Request $request)
    {
        $this->authorize('transport_invoices.create');

        if ($request->input('form_type') === 'loads') {
            return $this->storeFromLoads($request);
        }

        $data = $this->validated($request);

        $isDraft = $request->input('save_as') === 'draft';

        $invoice = DB::transaction(function () use ($data, $isDraft) {
            $invoice = new TransportInvoice();
            $invoice->created_by = Auth::id();
            $invoice->is_draft = $isDraft;
            $this->fillAndSave($invoice, $data);

            // المسودة: من غير رقم فاتورة رسمي ومن غير أي قيود محاسبية
            if (!$isDraft) {
                $this->finalize($invoice);
            }

            if (!empty($data['transport_waybill_id'])) {
                Waybill::whereKey($data['transport_waybill_id'])->whereNull('transport_invoice_id')
                    ->update(['transport_invoice_id' => $invoice->id]);
            }

            if (!empty($data['transport_quotation_id'])) {
                TransportQuotation::whereKey($data['transport_quotation_id'])
                    ->update(['status' => 'converted', 'transport_invoice_id' => $invoice->id]);
            }

            return $invoice;
        });

        if ($isDraft) {
            return redirect()->route('transport.invoices.index', ['status' => 'draft'])
                ->with('success', __('transport.draft_saved'));
        }

        return redirect()->route('transport.invoices.show', ['invoice' => $invoice, 'saved' => 1])
            ->with('success', __('transport.invoice_created'));
    }

    public function show(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.view');

        $invoice->load(['customer', 'branch', 'creator', 'items.truck.driver']);

        return view('transport.invoices.show', [
            'invoice' => $invoice,
            // بعد الإرسال للزكاة: QR المرحلة التانية من الـ XML الموقّع، وقبلها QR المرحلة الأولى
            'qrData' => $invoice->zatcaQrFromXml() ?? $this->zatcaQrData($invoice),
        ]);
    }

    public function edit(Request $request, TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.edit');

        if ($invoice->is_sent_to_zatca) {
            return redirect()->route('transport.invoices.show', $invoice)->with('error', __('transport.zatca_locked'));
        }

        $invoice->load(['items', 'customer']);

        if ($invoice->usesLoadsForm()) {
            return view('transport.invoices.create-loads', $this->loadsFormData($request, $invoice));
        }

        return view('transport.invoices.edit', $this->formData() + ['invoice' => $invoice]);
    }

    public function update(Request $request, TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.edit');

        if ($invoice->is_sent_to_zatca) {
            return redirect()->route('transport.invoices.show', $invoice)->with('error', __('transport.zatca_locked'));
        }

        if ($request->input('form_type') === 'loads') {
            return $this->storeFromLoads($request, $invoice);
        }

        $data = $this->validated($request);
        $wasDraft = (bool) $invoice->is_draft;
        // المسودة ممكن تفضل مسودة أو تتحوّل لفاتورة، لكن الفاتورة المعتمدة مترجعش مسودة
        $keepDraft = $wasDraft && $request->input('save_as') === 'draft';

        DB::transaction(function () use ($invoice, $data, $wasDraft, $keepDraft) {
            if (!$wasDraft) {
                // نرجّع أثر الفاتورة القديمة بالكامل، وبعدين نسجلها من جديد
                $this->reverseAccounting($invoice);
            }
            $invoice->items()->delete();

            $this->fillAndSave($invoice, $data);

            if ($wasDraft && !$keepDraft) {
                $this->finalize($invoice->fresh());
            } elseif (!$wasDraft) {
                $this->recordAccounting($invoice->fresh());
            }
        });

        if ($keepDraft) {
            return redirect()->route('transport.invoices.index', ['status' => 'draft'])->with('success', __('transport.draft_saved'));
        }

        return redirect()->route('transport.invoices.show', ['invoice' => $invoice, 'saved' => $wasDraft ? 1 : null])
            ->with('success', $wasDraft ? __('transport.draft_converted') : __('transport.invoice_updated'));
    }

    public function destroy(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.delete');

        if ($invoice->is_sent_to_zatca) {
            return back()->with('error', __('transport.zatca_locked'));
        }

        DB::transaction(function () use ($invoice) {
            if (!$invoice->is_draft) {
                $this->reverseAccounting($invoice);
            }
            $invoice->items()->delete();

            Waybill::where('transport_invoice_id', $invoice->id)->update(['transport_invoice_id' => null]);

            // الأحمال ترجع "غير مفوترة"
            TruckLoad::where('transport_invoice_id', $invoice->id)->update(['transport_invoice_id' => null]);

            // عرض السعر اللي اتحوّل للفاتورة دي يرجع "مقبول" تاني
            TransportQuotation::where('transport_invoice_id', $invoice->id)
                ->update(['status' => 'accepted', 'transport_invoice_id' => null]);

            $invoice->delete();
        });

        return redirect()->route('transport.invoices.index')
            ->with('success', __('transport.invoice_deleted'));
    }

    /** تحويل مسودة لفاتورة معتمدة مباشرة من غير فتحها (رقم رسمي + قيود محاسبية) */
    public function approve(TransportInvoice $invoice)
    {
        $this->authorize('transport_invoices.create');

        if (!$invoice->is_draft) {
            return back()->with('error', __('transport.not_a_draft'));
        }

        DB::transaction(fn () => $this->finalize($invoice));

        return redirect()->route('transport.invoices.show', ['invoice' => $invoice, 'saved' => 1])
            ->with('success', __('transport.draft_converted'));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    // ------------------------------------------------------------------
    // فاتورة من الأحمال غير المفوترة (شاشة "فاتورة نقل جديدة")
    // ------------------------------------------------------------------

    /** بيانات شاشة الفاتورة: العميل + أحماله غير المفوترة في الفترة */
    private function loadsFormData(Request $request, ?TransportInvoice $invoice = null): array
    {
        $customerId = $request->input('customer_id', $invoice?->customer_id);
        $includeLoaded = $request->boolean('include_loaded');
        $customer = $customerId ? Customer::find($customerId) : null;

        $loads = collect();
        if ($customer) {
            $loads = TruckLoad::unbilled($invoice?->id)
                ->with(['truck:id,plate_number,name,default_trip_price', 'waybill:id,truck_load_id,waybill_number,freight_amount'])
                ->where('customer_id', $customer->id)
                ->when(!$includeLoaded, fn ($q) => $q->where(fn ($w) => $w->where('status', 'unloaded')
                    ->when($invoice, fn ($ww) => $ww->orWhere('transport_invoice_id', $invoice->id))))
                ->when($request->filled('date_from'), fn ($q) => $q->where(fn ($w) => $w->whereDate('loaded_at', '>=', $request->input('date_from'))
                    ->when($invoice, fn ($ww) => $ww->orWhere('transport_invoice_id', $invoice->id))))
                ->when($request->filled('date_to'), fn ($q) => $q->where(fn ($w) => $w->whereDate('loaded_at', '<=', $request->input('date_to'))
                    ->when($invoice, fn ($ww) => $ww->orWhere('transport_invoice_id', $invoice->id))))
                ->orderBy('loaded_at')
                ->get();
        }

        return [
            'invoice' => $invoice,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'tax_number', 'commercial_registration_number', 'address', 'phone']),
            'customer' => $customer,
            'loads' => $loads,
            'includeLoaded' => $includeLoaded,
            'branches' => Branch::orderBy('name')->pluck('name', 'id'),
            'defaultBranchId' => Auth::user()->branch_id ?? Branch::orderBy('id')->value('id'),
            'expectedNumber' => $invoice?->invoice_number
                ?? 'TR-' . str_pad((string) (((int) TransportInvoice::max('id')) + 1), 6, '0', STR_PAD_LEFT),
        ];
    }

    private function storeFromLoads(Request $request, ?TransportInvoice $invoice = null)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'issue_at' => ['required', 'date'],
            'supply_from' => ['nullable', 'date'],
            'supply_to' => ['nullable', 'date', 'after_or_equal:supply_from'],
            'customer_tax_number' => ['nullable', 'digits:15'],
            'customer_cr' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'tax_type' => ['required', 'in:standard,international'],
            'prices_include_tax' => ['nullable', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'loads' => ['nullable', 'array'],
            'loads.*' => ['integer', 'exists:truck_loads,id'],
            'load_prices' => ['nullable', 'array'],
            'load_prices.*' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['nullable', 'array'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'customer_tax_number' => __('transport.customer_vat'),
            'issue_at' => __('transport.invoice_date'),
            'supply_to' => __('transport.supply_to'),
        ]);

        $loadIds = array_values(array_unique(array_map('intval', $data['loads'] ?? [])));
        $manual = collect($data['lines'] ?? [])
            ->filter(fn ($l) => trim((string) ($l['description'] ?? '')) !== '' && (float) ($l['quantity'] ?? 0) > 0 && (float) ($l['unit_price'] ?? 0) > 0)
            ->values();

        if (!$loadIds && $manual->isEmpty()) {
            throw ValidationException::withMessages(['loads' => __('transport.pick_loads_or_lines')]);
        }

        $wasDraft = (bool) $invoice?->is_draft;
        $editing = (bool) $invoice;

        $invoice = DB::transaction(function () use ($data, $loadIds, $manual, $invoice, $wasDraft) {
            $loads = TruckLoad::with(['truck', 'waybill'])
                ->whereIn('id', $loadIds)
                ->where('customer_id', $data['customer_id'])
                ->unbilled($invoice?->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($loads->count() !== count($loadIds)) {
                throw ValidationException::withMessages(['loads' => __('transport.loads_already_billed')]);
            }

            $rate = $data['tax_type'] === 'international' ? 0.0 : 0.15;
            $inclusive = !empty($data['prices_include_tax']);
            $net = fn (float $v) => round($inclusive ? $v / (1 + $rate) : $v, 2);

            $lines = [];
            foreach ($loadIds as $id) {
                $load = $loads[$id];
                $price = round((float) ($data['load_prices'][$id] ?? $load->billing_price ?? 0), 2);
                if ($price <= 0) {
                    throw ValidationException::withMessages(['load_prices' => __('transport.load_needs_price', [
                        'plate' => $load->truck?->plate_number, 'date' => $load->loaded_at?->format('Y-m-d'),
                    ])]);
                }
                // السعر اللي اتكتب في الفاتورة بيتحفظ على الحمل
                if ((float) $load->price !== $price) {
                    $load->update(['price' => $price]);
                }
                $lineNet = $net($price);
                $lines[] = [
                    'truck_id' => $load->truck_id,
                    'truck_load_id' => $load->id,
                    'truck_snapshot' => $load->truck?->display_name,
                    'description' => null,
                    'quantity' => 1,
                    'unit_price' => $lineNet,
                    'trip_date' => $load->loaded_at?->toDateString(),
                    'from_location' => $load->from_label,
                    'to_location' => $load->to_label,
                    'waybill_number' => $load->waybill_number ?: $load->waybill?->waybill_number,
                    'trip_price' => $lineNet,
                    'has_transfer' => false,
                    'transfer_price' => 0,
                    'line_total' => $lineNet,
                    'note' => $load->load_type,
                ];
            }

            foreach ($manual as $m) {
                $qty = round((float) $m['quantity'], 2);
                $unit = $net((float) $m['unit_price']);
                $total = round($qty * $unit, 2);
                $lines[] = [
                    'truck_id' => null,
                    'truck_load_id' => null,
                    'truck_snapshot' => null,
                    'description' => trim($m['description']),
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'trip_date' => null,
                    'from_location' => null,
                    'to_location' => null,
                    'waybill_number' => null,
                    'trip_price' => $total,
                    'has_transfer' => false,
                    'transfer_price' => 0,
                    'line_total' => $total,
                    'note' => null,
                ];
            }

            $gross = round(array_sum(array_column($lines, 'line_total')), 2);
            // الخصم بيتكتب قبل الضريبة دايمًا
            $discount = min(round((float) ($data['discount_amount'] ?? 0), 2), $gross);
            $subtotal = round($gross - $discount, 2);
            $taxAmount = round($subtotal * $rate, 2);
            $total = round($subtotal + $taxAmount, 2);

            // بيانات العميل اللي اتكتبت في الفاتورة بتتحدّث في كارت العميل
            $customer = Customer::find($data['customer_id']);
            $customer->fill(array_filter([
                'tax_number' => $data['customer_tax_number'] ?? null,
                'commercial_registration_number' => $data['customer_cr'] ?? null,
                'address' => $data['customer_address'] ?? null,
                'phone' => $data['customer_phone'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''));
            if ($customer->isDirty()) {
                $customer->save();
            }

            $isNew = !$invoice;
            if ($isNew) {
                $invoice = new TransportInvoice();
                $invoice->created_by = Auth::id();
                $invoice->is_draft = false;
            } else {
                if (!$wasDraft) {
                    $this->reverseAccounting($invoice);
                }
                $invoice->items()->delete();
                TruckLoad::where('transport_invoice_id', $invoice->id)->update(['transport_invoice_id' => null]);
                Waybill::where('transport_invoice_id', $invoice->id)->whereNotNull('truck_load_id')->update(['transport_invoice_id' => null]);
            }

            $issueAt = Carbon::parse($data['issue_at']);

            $invoice->fill([
                'customer_id' => $customer->id,
                'branch_id' => $data['branch_id'],
                'issue_date' => $issueAt->toDateString(),
                'issue_time' => $issueAt->format('H:i:s'),
                'supply_from' => $data['supply_from'] ?? null,
                'supply_to' => $data['supply_to'] ?? null,
                'trips_count' => count($loadIds),
                'trips_total' => $gross,
                'transfers_total' => 0,
                'discount_amount' => $discount,
                'subtotal' => $subtotal,
                'tax_rate' => $rate,
                'tax_type' => $data['tax_type'],
                'prices_include_tax' => $inclusive,
                'tax_amount' => $taxAmount,
                'total' => $total,
                // القيد: العميل مدين بالإجمالي (آجل)
                'payment_method' => 'credit',
                'cash_amount' => 0,
                'bank_amount' => 0,
                'credit_amount' => $total,
                'po_number' => $data['po_number'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
            $invoice->save();
            $invoice->items()->createMany($lines);

            if ($loadIds) {
                TruckLoad::whereIn('id', $loadIds)->update(['transport_invoice_id' => $invoice->id]);
                Waybill::whereIn('truck_load_id', $loadIds)->whereNull('transport_invoice_id')
                    ->update(['transport_invoice_id' => $invoice->id]);
            }

            if ($isNew || $wasDraft) {
                $this->finalize($invoice);
            } else {
                $this->recordAccounting($invoice->fresh());
            }

            return $invoice;
        });

        return redirect()->route('transport.invoices.show', ['invoice' => $invoice, 'saved' => 1])
            ->with('success', $editing ? __('transport.invoice_updated') : __('transport.invoice_created'));
    }

    /** اعتماد الفاتورة: رقم رسمي TR-xxxxxx + القيود المحاسبية */
    private function finalize(TransportInvoice $invoice): void
    {
        $invoice->forceFill([
            'is_draft' => false,
            'invoice_number' => 'TR-' . str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT),
        ])->save();

        $this->recordAccounting($invoice->fresh());
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone', 'tax_number']),
            'branches' => Branch::orderBy('name')->pluck('name', 'id'),
            'trucks' => Truck::with(['driver:id,name', 'activeLoad'])
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
            'tax_type' => ['nullable', 'in:standard,international,custom'],
            'tax_id' => ['nullable', 'required_if:tax_type,custom', 'exists:taxes,id'],   // "نسبة أخرى" من الإعدادات
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],   // نسبة مئوية (15)
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'transport_quotation_id' => ['nullable', 'exists:transport_quotations,id'],
            'transport_waybill_id' => ['nullable', 'exists:waybills,id'],

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
        // نوع الضريبة: داخل المملكة 15% / خارج المملكة معفاة 0% / نسبة مخصّصة
        $taxType = $data['tax_type'] ?? 'standard';
        $taxRate = match ($taxType) {
            'international' => 0.0,
            'custom' => round(((float) (\App\Models\Tax::find($data['tax_id'] ?? null)?->rate ?? 0)) / 100, 4),
            default => 0.15,
        };
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
            'tax_type' => $taxType,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'payment_method' => $data['payment_method'],
            'cash_amount' => $cash,
            'bank_amount' => $bank,
            'credit_amount' => $credit,
            'po_number' => $data['po_number'] ?? null,
            'transport_quotation_id' => $data['transport_quotation_id'] ?? $invoice->transport_quotation_id,
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
