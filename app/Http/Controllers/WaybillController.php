<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Models\Waybill;
use App\Support\SaudiPhone;
use App\Support\SaudiRegions;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * بوالص الشحن. البوليصة ممكن (اختياري) تسجّل تحميل الشاحنة على لوحة
 * الشاحنات: لو الشاحنة فاضية بيتعمل TruckLoad مربوط بالبوليصة، ولما
 * البوليصة تتسلّم ("تم التسليم") الشاحنة بتتفرّغ تلقائي.
 * لو الشاحنة عليها حمل، البوليصة بتتعمل عادي (تنبيه بس) لكن من غير
 * تسجيل تحميل جديد - عشان مفيش حمولتين على نفس الشاحنة.
 */
class WaybillController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('waybills.view');

        $waybills = Waybill::with(['truck:id,plate_number,name', 'driver:id,name,phone', 'customer:id,name', 'invoice:id,invoice_number'])
            ->when($request->filled('number'), fn ($q) => $q->where('waybill_number', 'like', '%' . $request->input('number') . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('truck_id'), fn ($q) => $q->where('truck_id', $request->input('truck_id')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->input('customer_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->input('date_to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->input('search');
                $q->where(fn ($qq) => $qq->where('shipper_name', 'like', "%{$s}%")
                    ->orWhere('consignee_name', 'like', "%{$s}%")
                    ->orWhere('goods_description', 'like', "%{$s}%"));
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('transport.waybills.index', [
            'waybills' => $waybills,
            'trucks' => Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name']),
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create()
    {
        $this->authorize('waybills.create');

        return view('transport.waybills.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('waybills.create');

        $data = $this->validated($request);
        $registerLoad = $request->boolean('register_load');
        $warning = null;

        try {
            $waybill = DB::transaction(function () use ($data, $registerLoad, &$warning) {
                $waybill = Waybill::create($data + ['status' => 'open', 'created_by' => Auth::id()]);
                $waybill->update(['waybill_number' => 'WB-' . str_pad((string) $waybill->id, 6, '0', STR_PAD_LEFT)]);

                if ($registerLoad) {
                    $warning = $this->registerLoad($waybill);
                }

                return $waybill;
            });
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'active_truck_id')) {
                return back()->withInput()->with('error', __('transport.truck_already_loaded', ['plate' => Truck::find($data['truck_id'])?->plate_number]));
            }
            throw $e;
        }

        return redirect()->route('transport.waybills.show', $waybill)
            ->with('success', __('transport.waybill_created'))
            ->with('error', $warning);
    }

    public function show(Waybill $waybill)
    {
        $this->authorize('waybills.view');

        $waybill->load(['truck', 'driver', 'customer', 'truckLoad', 'invoice', 'creator']);

        return view('transport.waybills.show', [
            'waybill' => $waybill,
            'contact' => Waybill::companyContact(),
        ]);
    }

    public function edit(Waybill $waybill)
    {
        $this->authorize('waybills.edit');

        if ($waybill->status !== 'open') {
            return redirect()->route('transport.waybills.show', $waybill)->with('error', __('transport.waybill_closed'));
        }

        return view('transport.waybills.edit', $this->formData() + ['waybill' => $waybill]);
    }

    public function update(Request $request, Waybill $waybill)
    {
        $this->authorize('waybills.edit');

        if ($waybill->status !== 'open') {
            return redirect()->route('transport.waybills.show', $waybill)->with('error', __('transport.waybill_closed'));
        }

        $data = $this->validated($request);
        $warning = null;

        DB::transaction(function () use ($waybill, $data, $request, &$warning) {
            $truckChanged = (int) $waybill->truck_id !== (int) $data['truck_id'];
            $waybill->update($data);

            $load = $waybill->truckLoad;
            if ($load && $load->status === 'loaded') {
                if ($truckChanged) {
                    // الشاحنة اتغيّرت: نلغي حمولة الشاحنة القديمة ونسجل على الجديدة
                    $load->update(['status' => 'cancelled', 'active_truck_id' => null]);
                    $load->truck?->update(['current_region' => $load->from_region]);
                    $waybill->update(['truck_load_id' => null]);
                    $warning = $this->registerLoad($waybill->fresh());
                } else {
                    $load->update($this->loadData($waybill));
                }
            } elseif (!$load && $request->boolean('register_load')) {
                $warning = $this->registerLoad($waybill);
            }
        });

        return redirect()->route('transport.waybills.show', $waybill)
            ->with('success', __('transport.waybill_updated'))
            ->with('error', $warning);
    }

    public function destroy(Waybill $waybill)
    {
        $this->authorize('waybills.delete');

        DB::transaction(function () use ($waybill) {
            $this->cancelLinkedLoad($waybill);
            $waybill->delete();
        });

        return redirect()->route('transport.waybills.index')->with('success', __('transport.waybill_deleted'));
    }

    /** تم التسليم: البوليصة بتتقفل والشاحنة بتتفرّغ (لو التحميل متسجل من البوليصة) */
    public function deliver(Request $request, Waybill $waybill)
    {
        $this->authorize('waybills.edit');

        if ($waybill->status !== 'open') {
            return back()->with('error', __('transport.waybill_closed'));
        }

        $at = Carbon::parse($request->validate(['delivered_at' => ['required', 'date']])['delivered_at']);
        if ($at->lt($waybill->loaded_at)) {
            return back()->with('error', __('transport.unload_before_loaded_at'));
        }

        DB::transaction(function () use ($waybill, $at) {
            $waybill->update(['status' => 'delivered', 'delivered_at' => $at]);

            $load = $waybill->truckLoad;
            if ($load && $load->status === 'loaded') {
                $load->update(['status' => 'unloaded', 'active_truck_id' => null, 'unloaded_at' => $at, 'unloaded_by' => Auth::id()]);
                $load->truck?->update(['current_region' => $load->to_region]);
            }
        });

        return back()->with('success', __('transport.waybill_delivered'));
    }

    /** إلغاء البوليصة (والحمولة المربوطة بيها لو لسه شغالة) */
    public function cancel(Waybill $waybill)
    {
        $this->authorize('waybills.edit');

        if ($waybill->status !== 'open') {
            return back()->with('error', __('transport.waybill_closed'));
        }

        DB::transaction(function () use ($waybill) {
            $this->cancelLinkedLoad($waybill);
            $waybill->update(['status' => 'cancelled']);
        });

        return back()->with('success', __('transport.waybill_cancelled'));
    }

    /** إنشاء فاتورة نقليات من البوليصة */
    public function toInvoice(Waybill $waybill)
    {
        $this->authorize('transport_invoices.create');

        if ($waybill->transport_invoice_id) {
            return redirect()->route('transport.invoices.show', $waybill->transport_invoice_id)
                ->with('error', __('transport.waybill_already_invoiced'));
        }

        return redirect()->route('transport.invoices.create', ['waybill' => $waybill->id]);
    }

    // ------------------------------------------------------------------

    /**
     * تسجيل تحميل الشاحنة على لوحة الشاحنات. بيرجع رسالة تنبيه (بدل ما
     * يفشل) لو الشاحنة عليها حمل أو مش متاحة.
     */
    private function registerLoad(Waybill $waybill): ?string
    {
        $truck = Truck::whereKey($waybill->truck_id)->lockForUpdate()->first();

        if (!$truck || TruckLoad::where('active_truck_id', $truck->id)->exists()) {
            return __('transport.waybill_load_skipped_busy', ['plate' => $truck?->plate_number]);
        }
        if ($truck->status !== 'active') {
            return __('transport.truck_not_available');
        }

        $load = TruckLoad::create($this->loadData($waybill) + [
            'truck_id' => $truck->id,
            'active_truck_id' => $truck->id,
            'status' => 'loaded',
            'created_by' => Auth::id(),
        ]);

        $truck->update(['current_region' => null]);
        $waybill->update(['truck_load_id' => $load->id]);

        return null;
    }

    private function loadData(Waybill $w): array
    {
        return [
            'driver_id' => $w->driver_id ?? $w->truck?->driver_id,
            'customer_id' => $w->customer_id,
            'from_region' => $w->from_region,
            'from_city' => $w->from_city,
            'to_region' => $w->to_region,
            'to_city' => $w->to_city,
            'load_type' => $w->goods_description,
            'weight' => $w->weight,
            'waybill_number' => $w->waybill_number,
            'loaded_at' => $w->loaded_at,
            'expected_unload_at' => $w->expected_unload_at,
        ];
    }

    private function cancelLinkedLoad(Waybill $waybill): void
    {
        $load = $waybill->truckLoad;
        if ($load && $load->status === 'loaded') {
            $load->update(['status' => 'cancelled', 'active_truck_id' => null]);
            $load->truck?->update(['current_region' => $load->from_region]);
        }
    }

    private function formData(): array
    {
        return [
            'trucks' => Truck::with(['driver:id,name,phone', 'activeLoad'])
                ->where('status', '!=', 'inactive')
                ->orderBy('plate_number')
                ->get(),
            'drivers' => Driver::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'regions' => SaudiRegions::options(),
        ];
    }

    private function validated(Request $request): array
    {
        // تنظيف أرقام الجوال قبل الفحص (أرقام عربية / مسافات / +966)
        foreach (['shipper_phone', 'consignee_phone'] as $f) {
            if ($request->filled($f)) {
                $request->merge([$f => SaudiPhone::normalize($request->input($f))]);
            }
        }

        $regions = Rule::in(SaudiRegions::keys());

        $data = $request->validate([
            'issue_date' => ['required', 'date'],
            'truck_id' => ['required', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'shipper_name' => ['required', 'string', 'max:255'],
            'shipper_phone' => ['nullable', 'regex:/^05\d{8}$/'],
            'consignee_name' => ['required', 'string', 'max:255'],
            'consignee_phone' => ['nullable', 'regex:/^05\d{8}$/'],
            'from_region' => ['required', $regions],
            'from_city' => ['nullable', 'string', 'max:255'],
            'from_address' => ['nullable', 'string', 'max:255'],
            'to_region' => ['required', $regions],
            'to_city' => ['nullable', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:255'],
            'goods_description' => ['required', 'string', 'max:255'],
            'packages_count' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'loaded_at' => ['required', 'date'],
            'expected_unload_at' => ['required', 'date', 'after_or_equal:loaded_at'],
            'freight_amount' => ['nullable', 'numeric', 'min:0'],
            'freight_payer' => ['required', 'in:shipper,consignee,customer'],
            'notes' => ['nullable', 'string'],
        ], [
            'shipper_phone.regex' => __('transport.phone_bad'),
            'consignee_phone.regex' => __('transport.phone_bad'),
            'expected_unload_at.after_or_equal' => __('transport.unload_before_load'),
        ], [
            'shipper_name' => __('transport.shipper_name'),
            'consignee_name' => __('transport.consignee_name'),
            'from_region' => __('transport.from_region'),
            'to_region' => __('transport.to_region'),
            'goods_description' => __('transport.goods_description'),
            'loaded_at' => __('transport.loaded_at'),
            'expected_unload_at' => __('transport.expected_unload_at'),
        ]);

        $data['freight_amount'] = $data['freight_amount'] ?? 0;

        return $data;
    }
}
