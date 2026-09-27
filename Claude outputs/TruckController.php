<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Truck;
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
                        ->orWhere('type', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
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

        Truck::create($data);

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

        $truck->update($this->validated($request, $truck));

        return redirect()->route('transport.trucks.index')->with('success', __('transport.truck_updated'));
    }

    public function destroy(Truck $truck)
    {
        $this->authorize('trucks.delete');

        // شاحنة عليها نقلات في فواتير مينفعش تتمسح (عشان الفواتير
        // القديمة متبوظش) - الحل إنها تتحول لـ "موقوفة".
        if ($truck->trips()->exists()) {
            return back()->with('error', __('transport.truck_in_use'));
        }

        $truck->delete();

        return redirect()->route('transport.trucks.index')->with('success', __('transport.truck_deleted'));
    }

    private function driverOptions()
    {
        return Driver::where('status', 'active')->orderBy('name')->pluck('name', 'id');
    }

    private function validated(Request $request, ?Truck $truck = null): array
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', Rule::unique('trucks', 'plate_number')->ignore($truck?->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model_year' => ['nullable', 'string', 'max:10'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'default_trip_price' => ['nullable', 'numeric', 'min:0'],
            'registration_expiry' => ['nullable', 'date'],
            'insurance_expiry' => ['nullable', 'date'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['default_trip_price'] = $data['default_trip_price'] ?? 0;

        return $data;
    }
}
