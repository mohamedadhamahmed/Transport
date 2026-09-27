<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\TransportQuotation;
use App\Support\SaudiRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * عروض أسعار النقليات: بدل "منتج × كمية"، كل سطر = مسار (من منطقة إلى
 * منطقة) + نوع الشاحنة + نوع الحمولة + عدد النقلات × (سعر النقلة +
 * التحويلة). من غير أي قيود محاسبية. وممكن يتحول لفاتورة نقليات.
 */
class TransportQuotationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('transport_quotations.view');

        $quotations = TransportQuotation::with(['customer:id,name', 'creator:id,name', 'invoice:id,invoice_number'])
            ->withCount('items')
            ->when($request->filled('number'), fn ($q) => $q->where('quotation_number', 'like', '%' . $request->input('number') . '%'))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->input('customer_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->input('date_to')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::orderBy('name')->pluck('name', 'id');

        return view('transport.quotations.index', compact('quotations', 'customers'));
    }

    public function create()
    {
        $this->authorize('transport_quotations.create');

        return view('transport.quotations.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('transport_quotations.create');

        $data = $this->validated($request);

        $quotation = DB::transaction(function () use ($data) {
            $q = new TransportQuotation(['created_by' => Auth::id(), 'status' => 'draft']);
            $this->fillAndSave($q, $data);
            $q->update(['quotation_number' => 'QT-' . str_pad((string) $q->id, 6, '0', STR_PAD_LEFT)]);

            return $q;
        });

        return redirect()->route('transport.quotations.show', $quotation)
            ->with('success', __('transport.quotation_created'));
    }

    public function show(TransportQuotation $quotation)
    {
        $this->authorize('transport_quotations.view');

        $quotation->load(['customer', 'branch', 'creator', 'items', 'invoice']);

        return view('transport.quotations.show', compact('quotation'));
    }

    public function edit(TransportQuotation $quotation)
    {
        $this->authorize('transport_quotations.edit');

        if ($quotation->status === 'converted') {
            return redirect()->route('transport.quotations.show', $quotation)->with('error', __('transport.quotation_converted_locked'));
        }

        $quotation->load('items');

        return view('transport.quotations.edit', $this->formData() + ['quotation' => $quotation]);
    }

    public function update(Request $request, TransportQuotation $quotation)
    {
        $this->authorize('transport_quotations.edit');

        if ($quotation->status === 'converted') {
            return redirect()->route('transport.quotations.show', $quotation)->with('error', __('transport.quotation_converted_locked'));
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($quotation, $data) {
            $quotation->items()->delete();
            $this->fillAndSave($quotation, $data);
        });

        return redirect()->route('transport.quotations.show', $quotation)
            ->with('success', __('transport.quotation_updated'));
    }

    public function destroy(TransportQuotation $quotation)
    {
        $this->authorize('transport_quotations.delete');

        $quotation->delete();

        return redirect()->route('transport.quotations.index')->with('success', __('transport.quotation_deleted'));
    }

    /** تغيير الحالة: مسودة / مُرسل / مقبول / مرفوض */
    public function status(Request $request, TransportQuotation $quotation)
    {
        $this->authorize('transport_quotations.edit');

        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'rejected'])]]);

        if ($quotation->status === 'converted') {
            return back()->with('error', __('transport.quotation_converted_locked'));
        }

        $quotation->update($data);

        return back()->with('success', __('transport.status_updated'));
    }

    /** تحويل لفاتورة: بيفتح شاشة فاتورة النقليات جاهزة ببيانات العرض (وانت بتختار الشاحنات) */
    public function convert(TransportQuotation $quotation)
    {
        $this->authorize('transport_invoices.create');

        if ($quotation->status === 'converted' && $quotation->transport_invoice_id) {
            return redirect()->route('transport.invoices.show', $quotation->transport_invoice_id)
                ->with('error', __('transport.quotation_already_converted'));
        }

        return redirect()->route('transport.invoices.create', ['quotation' => $quotation->id]);
    }

    // ------------------------------------------------------------------

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'branches' => Branch::orderBy('name')->pluck('name', 'id'),
            'regions' => SaudiRegions::options(),
            'defaultBranchId' => Auth::user()->branch_id ?? Branch::orderBy('id')->value('id'),
        ];
    }

    private function validated(Request $request): array
    {
        $regionRule = Rule::in(SaudiRegions::keys());

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'tax_type' => ['nullable', 'in:standard,international,custom'],
            'tax_id' => ['nullable', 'required_if:tax_type,custom', 'exists:taxes,id'],   // "نسبة أخرى" من الإعدادات
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'terms' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.truck_type' => ['nullable', 'string', 'max:255'],
            'items.*.from_region' => ['required', $regionRule],
            'items.*.from_city' => ['nullable', 'string', 'max:255'],
            'items.*.to_region' => ['required', $regionRule],
            'items.*.to_city' => ['nullable', 'string', 'max:255'],
            'items.*.load_type' => ['nullable', 'string', 'max:255'],
            'items.*.trips_count' => ['required', 'integer', 'min:1', 'max:10000'],
            'items.*.trip_price' => ['required', 'numeric', 'min:0'],
            'items.*.has_transfer' => ['nullable', 'boolean'],
            'items.*.transfer_location' => ['nullable', 'string', 'max:255'],
            'items.*.transfer_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
        ], [], [
            'items' => __('transport.routes'),
            'items.*.from_region' => __('transport.from_region'),
            'items.*.to_region' => __('transport.to_region'),
            'items.*.trips_count' => __('transport.trips_count'),
            'items.*.trip_price' => __('transport.trip_price'),
        ]);

        $data['items'] = array_values($data['items']);

        return $data;
    }

    private function fillAndSave(TransportQuotation $q, array $data): void
    {
        $tripsTotal = 0;
        $transfersTotal = 0;
        $lines = [];

        foreach ($data['items'] as $item) {
            $count = (int) $item['trips_count'];
            $price = round((float) $item['trip_price'], 2);
            $hasTransfer = !empty($item['has_transfer']);
            $transfer = $hasTransfer ? round((float) ($item['transfer_price'] ?? 0), 2) : 0;

            $tripsTotal += $count * $price;
            $transfersTotal += $count * $transfer;

            $lines[] = [
                'truck_type' => $item['truck_type'] ?? null,
                'from_region' => $item['from_region'],
                'from_city' => $item['from_city'] ?? null,
                'to_region' => $item['to_region'],
                'to_city' => $item['to_city'] ?? null,
                'load_type' => $item['load_type'] ?? null,
                'trips_count' => $count,
                'trip_price' => $price,
                'has_transfer' => $hasTransfer,
                'transfer_location' => $hasTransfer ? ($item['transfer_location'] ?? null) : null,
                'transfer_price' => $transfer,
                'line_total' => round($count * ($price + $transfer), 2),
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
        $tax = round($subtotal * $taxRate, 2);

        $q->fill([
            'customer_id' => $data['customer_id'],
            'branch_id' => $data['branch_id'],
            'issue_date' => $data['issue_date'],
            'valid_until' => $data['valid_until'] ?? null,
            'trips_total' => $tripsTotal,
            'transfers_total' => $transfersTotal,
            'discount_amount' => $discount,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_type' => $taxType,
            'tax_amount' => $tax,
            'total' => round($subtotal + $tax, 2),
            'terms' => $data['terms'] ?? null,
            'note' => $data['note'] ?? null,
        ]);
        $q->save();

        $q->items()->createMany($lines);
    }
}
