<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Truck;
use App\Services\TruckAssetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TruckController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('trucks.view');

        $trucks = Truck::with('driver:id,name,phone')
            ->withCount('trips')
            ->withSum('trips', 'line_total')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->input('search');
                $q->where(function ($qq) use ($s) {
                    $qq->where('plate_number', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%")
                        ->orWhere('registration_number', 'like', "%{$s}%")
                        ->orWhere('chassis_number', 'like', "%{$s}%")
                        ->orWhere('type', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            // تنبيهات الوثائق: docs=alert (أي وثيقة) أو docs=<عمود> (وثيقة معيّنة)، expired=1 للمنتهية بس
            ->when($request->filled('docs'), fn ($q) => $q->documentsAlert(
                $request->input('docs') === 'alert' ? null : $request->input('docs'),
                $request->boolean('expired') ? 'expired' : 'any'
            ))
            ->orderBy('plate_number')
            ->paginate(20)
            ->withQueryString();

        return view('transport.trucks.index', compact('trucks'));
    }

    public function create()
    {
        $this->authorize('trucks.create');

        $drivers = $this->driverOptions();

        return view('transport.trucks.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $this->authorize('trucks.create');

        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        DB::transaction(function () use ($data) {
            $truck = Truck::create($data);
            // شاحنة ملك الشركة: قيمتها بتتسجل أصل ثابت تحت "الشاحنات"
            app(TruckAssetService::class)->sync($truck);
        });

        return redirect()->route('transport.trucks.index')->with('success', __('transport.truck_created'));
    }

    public function edit(Truck $truck)
    {
        $this->authorize('trucks.edit');

        $drivers = $this->driverOptions();

        return view('transport.trucks.edit', compact('truck', 'drivers'));
    }

    public function update(Request $request, Truck $truck)
    {
        $this->authorize('trucks.edit');

        DB::transaction(function () use ($request, $truck) {
            $truck->update($this->validated($request, $truck));
            app(TruckAssetService::class)->sync($truck->fresh());
        });

        // لو جاي من لوحة الشاحنات يرجع لها
        $back = (string) $request->input('return_to');
        if ($back !== '' && str_starts_with($back, url('/')) && !str_contains($back, '/edit')) {
            return redirect()->to($back)->with('success', __('transport.truck_updated'));
        }

        return redirect()->route('transport.trucks.index')->with('success', __('transport.truck_updated'));
    }

    public function destroy(Truck $truck)
    {
        $this->authorize('trucks.delete');

        // شاحنة عليها نقلات في فواتير مينفعش تتمسح (عشان الفواتير
        // القديمة متبوظش) - الحل إنها تتحول لـ "موقوفة".
        if ($truck->trips()->exists() || $truck->loads()->exists() || $truck->expenseVouchers()->exists()) {
            return back()->with('error', __('transport.truck_in_use'));
        }

        if ($truck->financial_account_id) {
            $acc = \App\Models\FinancialAccount::find($truck->financial_account_id);
            if ($acc && !$acc->creditTransactions()->exists()) {
                $acc->delete();
            } elseif ($acc) {
                $acc->update(['active' => 0]);
            }
        }

        $truck->delete();

        return redirect()->route('transport.trucks.index')->with('success', __('transport.truck_deleted'));
    }

    /** إضافة شاحنة سريعة (Ajax) من البوليصة */
    public function quick(Request $request)
    {
        $this->authorize('trucks.create');

        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', Rule::unique('trucks', 'plate_number')],
            'type' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'ownership' => ['required', 'in:owned,external'],
            'owner_name' => ['nullable', 'required_if:ownership,external', 'string', 'max:255'],
        ]);

        $truck = Truck::create($data + ['status' => 'active', 'default_trip_price' => 0, 'created_by' => Auth::id()]);

        return response()->json([
            'id' => $truck->id,
            'plate_number' => $truck->plate_number,
            'label' => $truck->display_name . ($truck->type ? ' (' . $truck->type . ')' : ''),
            'type' => $truck->type,
            'capacity' => $truck->capacity,
            'owner' => $truck->ownership === 'external' ? $truck->owner_name : null,
            'driver_id' => null,
        ]);
    }

    private function driverOptions()
    {
        return Driver::where('status', 'active')->orderBy('name')->pluck('name', 'id');
    }

    private function validated(Request $request, ?Truck $truck = null): array
    {
        if ($request->filled('owner_phone')) {
            $request->merge(['owner_phone' => \App\Support\SaudiPhone::normalize($request->input('owner_phone'))]);
        }

        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', Rule::unique('trucks', 'plate_number')->ignore($truck?->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'ownership' => ['required', 'in:owned,external'],
            'purchase_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'owner_name' => ['nullable', 'required_if:ownership,external', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'regex:/^05\d{8}$/'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model_year' => ['nullable', 'string', 'max:10'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'default_trip_price' => ['nullable', 'numeric', 'min:0'],
            'registration_expiry' => ['nullable', 'date'],
            'insurance_expiry' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'insurance_company' => ['nullable', 'string', 'max:255'],
            'insurance_policy_number' => ['nullable', 'string', 'max:100'],
            'operating_card_number' => ['nullable', 'string', 'max:100'],
            'operating_card_expiry' => ['nullable', 'date'],
            'inspection_expiry' => ['nullable', 'date'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'current_region' => ['nullable', Rule::in(\App\Support\SaudiRegions::keys())],
            'notes' => ['nullable', 'string'],
        ]);

        $data['default_trip_price'] = $data['default_trip_price'] ?? 0;
        if ($data['ownership'] === 'external') {
            $data['purchase_value'] = null;
            $data['purchase_date'] = null;
        } else {
            $data['owner_name'] = null;
            $data['owner_phone'] = null;
        }

        return $data;
    }
}
