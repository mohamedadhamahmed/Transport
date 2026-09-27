<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('drivers.view');

        $drivers = Driver::withCount('trucks')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->input('search');
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhere('id_number', 'like', "%{$s}%")
                        ->orWhere('license_number', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('transport.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $this->authorize('drivers.create');

        return view('transport.drivers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('drivers.create');

        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        Driver::create($data);

        return redirect()->route('transport.drivers.index')->with('success', __('transport.driver_created'));
    }

    public function edit(Driver $driver)
    {
        $this->authorize('drivers.edit');

        return view('transport.drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $this->authorize('drivers.edit');

        $driver->update($this->validated($request));

        return redirect()->route('transport.drivers.index')->with('success', __('transport.driver_updated'));
    }

    public function destroy(Driver $driver)
    {
        $this->authorize('drivers.delete');

        // الشاحنات المرتبطة بيه هتفضل موجودة، بس من غير سائق (nullOnDelete)
        $driver->delete();

        return redirect()->route('transport.drivers.index')->with('success', __('transport.driver_deleted'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'date'],
            'id_expiry' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['salary'] = $data['salary'] ?? 0;

        return $data;
    }
}
