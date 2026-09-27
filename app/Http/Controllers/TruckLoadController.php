<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Support\SaudiRegions;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * حركة الشاحنات: لوحة الشاحنات (فاضية / محمّلة / متأخرة) + تحميل +
 * تفريغ + تحديد مكان الشاحنة الفاضية + تقرير الأحمال.
 *
 * القاعدة الأساسية: مينفعش تتحمّل شاحنة عليها حمل - لازم الأول تتسجل
 * "تم التفريغ". ده متأمّن على 3 مستويات: الشاشة (مفيش زرار تحميل
 * للمحمّلة)، الكنترولر (lockForUpdate + فحص)، وقاعدة البيانات (unique
 * على active_truck_id).
 */
class TruckLoadController extends Controller
{
    public function board(Request $request)
    {
        $this->authorize('truck_loads.view');

        $trucks = Truck::with(['driver', 'activeLoad.driver', 'activeLoad.customer'])
            ->where('status', '!=', 'inactive')
            ->orderBy('plate_number')
            ->get();

        // الإحصائيات قبل الفلترة
        $stats = [
            'total' => $trucks->count(),
            'empty' => $trucks->filter(fn ($t) => !$t->activeLoad && $t->status === 'active')->count(),
            'loaded' => $trucks->filter(fn ($t) => (bool) $t->activeLoad)->count(),
            'overdue' => $trucks->filter(fn ($t) => $t->activeLoad?->isOverdue())->count(),
            'maintenance' => $trucks->filter(fn ($t) => !$t->activeLoad && $t->status === 'maintenance')->count(),
        ];

        $status = $request->input('status');
        $region = $request->input('region');
        $search = trim((string) $request->input('search'));

        $filtered = $trucks->filter(function (Truck $t) use ($status, $region, $search) {
            $load = $t->activeLoad;

            if ($status === 'empty' && ($load || $t->status !== 'active')) return false;
            if ($status === 'loaded' && !$load) return false;
            if ($status === 'overdue' && !$load?->isOverdue()) return false;
            if ($status === 'maintenance' && ($load || $t->status !== 'maintenance')) return false;

            if ($region) {
                $inRegion = $load
                    ? in_array($region, [$load->from_region, $load->to_region], true)
                    : $t->current_region === $region;
                if (!$inRegion) return false;
            }

            if ($search !== '') {
                $driver = $load?->driver ?? $t->driver;
                $hay = mb_strtolower(implode(' ', array_filter([
                    $t->plate_number, $t->name, $t->type,
                    $driver?->name, $driver?->phone,
                    $load?->load_type, $load?->waybill_number, $load?->customer?->name,
                ])));
                if (!str_contains($hay, mb_strtolower($search))) return false;
            }

            return true;
        })
        // المتأخرة الأول، بعدين المحمّلة، بعدين الفاضية
        ->sortBy(fn ($t) => $t->activeLoad ? ($t->activeLoad->isOverdue() ? 0 : 1) : 2)
        ->values();

        return view('transport.loads.board', [
            'trucks' => $filtered,
            'stats' => $stats,
            'regions' => SaudiRegions::options(),
            'drivers' => Driver::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']),
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /** تحميل شاحنة */
    public function store(Request $request, Truck $truck)
    {
        $this->authorize('truck_loads.manage');

        $data = $request->validate([
            'from_region' => ['required', Rule::in(SaudiRegions::keys())],
            'from_city' => ['nullable', 'string', 'max:255'],
            'to_region' => ['required', Rule::in(SaudiRegions::keys())],
            'to_city' => ['nullable', 'string', 'max:255'],
            'load_type' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'waybill_number' => ['nullable', 'string', 'max:255'],
            'loaded_at' => ['required', 'date'],
            'expected_unload_at' => ['required', 'date', 'after_or_equal:loaded_at'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'notes' => ['nullable', 'string'],
        ], [
            'expected_unload_at.after_or_equal' => __('transport.unload_before_load'),
        ], [
            'from_region' => __('transport.from_region'),
            'to_region' => __('transport.to_region'),
            'load_type' => __('transport.load_type'),
            'loaded_at' => __('transport.loaded_at'),
            'expected_unload_at' => __('transport.expected_unload_at'),
        ]);

        try {
            DB::transaction(function () use ($truck, $data) {
                $locked = Truck::whereKey($truck->id)->lockForUpdate()->first();

                if (TruckLoad::where('active_truck_id', $locked->id)->exists()) {
                    abort(back()->withInput()->with('error', __('transport.truck_already_loaded', ['plate' => $locked->plate_number])));
                }
                if ($locked->status !== 'active') {
                    abort(back()->withInput()->with('error', __('transport.truck_not_available')));
                }

                $data['driver_id'] = $data['driver_id'] ?? $locked->driver_id;

                TruckLoad::create($data + [
                    'truck_id' => $locked->id,
                    'active_truck_id' => $locked->id,
                    'status' => 'loaded',
                    'created_by' => Auth::id(),
                ]);

                $locked->update(['current_region' => null]);
            });
        } catch (QueryException $e) {
            // حد تاني حمّل نفس الشاحنة في نفس اللحظة (unique على active_truck_id)
            return back()->withInput()->with('error', __('transport.truck_already_loaded', ['plate' => $truck->plate_number]));
        }

        return redirect()->route('transport.loads.board', $request->only('status', 'region', 'search'))
            ->with('success', __('transport.load_saved', ['plate' => $truck->plate_number]));
    }

    /** تم التفريغ */
    public function unload(Request $request, TruckLoad $load)
    {
        $this->authorize('truck_loads.manage');

        if ($load->status !== 'loaded') {
            return back()->with('error', __('transport.load_not_active'));
        }

        $data = $request->validate([
            'unloaded_at' => ['required', 'date'],
        ]);

        $unloadedAt = Carbon::parse($data['unloaded_at']);
        if ($unloadedAt->lt($load->loaded_at)) {
            return back()->with('error', __('transport.unload_before_loaded_at'));
        }

        DB::transaction(function () use ($load, $unloadedAt) {
            $load->update([
                'status' => 'unloaded',
                'active_truck_id' => null,
                'unloaded_at' => $unloadedAt,
                'unload_recorded_at' => now(),
                'unloaded_by' => Auth::id(),
            ]);

            // الشاحنة بقت فاضية في منطقة التنزيل
            $load->truck?->update(['current_region' => $load->to_region]);

            // لو الحمولة جاية من بوليصة شحن: البوليصة بتتقفل "تم التسليم" هي كمان
            \App\Models\Waybill::where('truck_load_id', $load->id)->where('status', 'open')
                ->update(['status' => 'delivered', 'delivered_at' => $unloadedAt]);
        });

        return back()->with('success', __('transport.unload_saved', ['plate' => $load->truck?->plate_number]));
    }

    /** شاشة تعديل حمل (محمّل أو اتفرّغ) - كل بيانات الحمل */
    public function edit(Request $request, TruckLoad $load)
    {
        $this->authorize('truck_loads.manage');

        if ($error = $this->editBlockedReason($load)) {
            return back()->with('error', $error);
        }

        $load->load(['truck', 'invoice']);

        return view('transport.loads.edit', [
            'load' => $load,
            'regions' => SaudiRegions::options(),
            'trucks' => Truck::with('activeLoad:id,active_truck_id')
                ->where(fn ($q) => $q->where('status', 'active')->orWhere('id', $load->truck_id))
                ->orderBy('plate_number')
                ->get(['id', 'plate_number', 'name', 'type', 'status']),
            'drivers' => Driver::orderBy('name')->get(['id', 'name', 'phone', 'status']),
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
            'returnTo' => $this->safeReturn($request->input('return_to', url()->previous())),
        ]);
    }

    public function update(Request $request, TruckLoad $load)
    {
        $this->authorize('truck_loads.manage');

        if ($error = $this->editBlockedReason($load)) {
            return back()->with('error', $error);
        }

        $isUnloaded = $load->status === 'unloaded';

        $data = $request->validate([
            'truck_id' => ['required', 'exists:trucks,id'],
            'from_region' => ['required', Rule::in(SaudiRegions::keys())],
            'from_city' => ['nullable', 'string', 'max:255'],
            'to_region' => ['required', Rule::in(SaudiRegions::keys())],
            'to_city' => ['nullable', 'string', 'max:255'],
            'load_type' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'waybill_number' => ['nullable', 'string', 'max:255'],
            'loaded_at' => ['required', 'date'],
            'expected_unload_at' => ['required', 'date', 'after_or_equal:loaded_at'],
            'unloaded_at' => [$isUnloaded ? 'required' : 'nullable', 'date', 'after_or_equal:loaded_at'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'notes' => ['nullable', 'string'],
        ], [
            'expected_unload_at.after_or_equal' => __('transport.unload_before_load'),
            'unloaded_at.after_or_equal' => __('transport.unload_before_loaded_at'),
        ], [
            'truck_id' => __('transport.truck'),
            'from_region' => __('transport.from_region'),
            'to_region' => __('transport.to_region'),
            'load_type' => __('transport.load_type'),
            'loaded_at' => __('transport.loaded_at'),
            'expected_unload_at' => __('transport.expected_unload_at'),
            'unloaded_at' => __('transport.unloaded_at'),
        ]);

        if (!$isUnloaded) {
            unset($data['unloaded_at']);
        }

        try {
            DB::transaction(function () use ($load, $data, $isUnloaded) {
                $oldTruckId = (int) $load->truck_id;
                $newTruckId = (int) $data['truck_id'];

                // تغيير الشاحنة لحمل لسه محمّل: الشاحنة الجديدة لازم تكون فاضية وشغالة
                if (!$isUnloaded && $newTruckId !== $oldTruckId) {
                    $newTruck = Truck::whereKey($newTruckId)->lockForUpdate()->first();
                    if (TruckLoad::where('active_truck_id', $newTruckId)->exists()) {
                        abort(back()->withInput()->with('error', __('transport.truck_already_loaded', ['plate' => $newTruck->plate_number])));
                    }
                    if ($newTruck->status !== 'active') {
                        abort(back()->withInput()->with('error', __('transport.truck_not_available')));
                    }
                    $data['active_truck_id'] = $newTruckId;
                }

                $load->update($data);

                if (!$isUnloaded && $newTruckId !== $oldTruckId) {
                    // الشاحنة القديمة بقت فاضية في مكان التحميل، والجديدة محمّلة
                    Truck::whereKey($oldTruckId)->update(['current_region' => $load->from_region]);
                    Truck::whereKey($newTruckId)->update(['current_region' => null]);
                }

                // حمل اتفرّغ: لو ده آخر حمل للشاحنة وهي فاضية، مكانها = منطقة التنزيل الجديدة
                if ($isUnloaded) {
                    $truck = Truck::find($newTruckId);
                    $latest = TruckLoad::where('truck_id', $newTruckId)->where('status', 'unloaded')
                        ->orderByDesc('unloaded_at')->orderByDesc('id')->value('id');
                    if ($truck && $latest === $load->id && !TruckLoad::where('active_truck_id', $newTruckId)->exists()) {
                        $truck->update(['current_region' => $load->to_region]);
                    }
                }

                // بوليصة الشحن المربوطة بالحمل بتتحدّث بنفس البيانات
                $wb = [
                    'truck_id' => $load->truck_id,
                    'driver_id' => $load->driver_id,
                    'customer_id' => $load->customer_id,
                    'from_region' => $load->from_region,
                    'from_city' => $load->from_city,
                    'to_region' => $load->to_region,
                    'to_city' => $load->to_city,
                    'loaded_at' => $load->loaded_at,
                    'expected_unload_at' => $load->expected_unload_at,
                ];
                if ($isUnloaded) {
                    $wb['delivered_at'] = $load->unloaded_at;
                }
                \App\Models\Waybill::where('truck_load_id', $load->id)->whereNull('transport_invoice_id')->update($wb);
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('error', __('transport.truck_already_loaded', ['plate' => Truck::find($request->input('truck_id'))?->plate_number]));
        }

        return redirect()->to($this->safeReturn($request->input('return_to')) ?? route('transport.loads.board'))
            ->with('success', __('transport.load_updated'));
    }

    /** الحمل الملغي أو اللي اتفوتر مينفعش يتعدّل */
    private function editBlockedReason(TruckLoad $load): ?string
    {
        if ($load->status === 'cancelled') {
            return __('transport.load_cancelled_locked');
        }
        if ($load->transport_invoice_id) {
            return __('transport.load_invoiced_locked', ['number' => $load->invoice?->invoice_number ?? ('#' . $load->transport_invoice_id)]);
        }

        return null;
    }

    /** رابط الرجوع لازم يكون جوه نفس الموقع */
    private function safeReturn(?string $url): ?string
    {
        if (!$url || !str_starts_with($url, url('/'))) {
            return null;
        }

        return str_contains($url, '/loads/') && str_contains($url, '/edit') ? null : $url;
    }

    /** إلغاء حمولة اتسجلت بالغلط */
    public function cancel(TruckLoad $load)
    {
        $this->authorize('truck_loads.manage');

        if ($load->status !== 'loaded') {
            return back()->with('error', __('transport.load_not_active'));
        }

        DB::transaction(function () use ($load) {
            $load->update(['status' => 'cancelled', 'active_truck_id' => null]);
            // البوليصة (لو فيه) بتفضل مفتوحة، بس من غير ربط بالحمولة الملغية
            \App\Models\Waybill::where('truck_load_id', $load->id)->update(['truck_load_id' => null]);
            // ترجع فاضية في المكان اللي كانت هتحمّل منه
            $load->truck?->update(['current_region' => $load->from_region]);
        });

        return back()->with('success', __('transport.load_cancelled'));
    }

    /** تحديد مكان شاحنة فاضية */
    public function setLocation(Request $request, Truck $truck)
    {
        $this->authorize('truck_loads.manage');

        $data = $request->validate([
            'current_region' => ['nullable', Rule::in(SaudiRegions::keys())],
        ]);

        if ($truck->activeLoad()->exists()) {
            return back()->with('error', __('transport.truck_already_loaded', ['plate' => $truck->plate_number]));
        }

        $truck->update(['current_region' => $data['current_region'] ?? null]);

        return back()->with('success', __('transport.location_saved'));
    }

    /** تقرير الأحمال */
    public function report(Request $request)
    {
        $this->authorize('truck_loads.report');

        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        $query = TruckLoad::with(['truck:id,plate_number,name', 'driver:id,name,phone', 'customer:id,name', 'creator:id,name'])
            ->whereDate('loaded_at', '>=', $from)
            ->whereDate('loaded_at', '<=', $to)
            ->when($request->filled('truck_id'), fn ($q) => $q->where('truck_id', $request->input('truck_id')))
            ->when($request->filled('driver_id'), fn ($q) => $q->where('driver_id', $request->input('driver_id')))
            ->when($request->filled('from_region'), fn ($q) => $q->where('from_region', $request->input('from_region')))
            ->when($request->filled('to_region'), fn ($q) => $q->where('to_region', $request->input('to_region')));

        $status = $request->input('status');
        if (in_array($status, ['loaded', 'unloaded', 'cancelled'], true)) {
            $query->where('status', $status);
        } elseif ($status === 'overdue') {
            $query->where('status', 'loaded')->where('expected_unload_at', '<', now());
        } elseif ($status !== 'all') {
            $query->where('status', '!=', 'cancelled');   // الافتراضي: من غير الملغية
        }

        $loads = $query->orderByDesc('loaded_at')->get();

        $unloaded = $loads->where('status', 'unloaded');
        $durations = $unloaded->map(fn ($l) => $l->loaded_at->diffInMinutes($l->unloaded_at))->filter(fn ($m) => $m > 0);

        $summary = [
            'total' => $loads->count(),
            'loaded' => $loads->where('status', 'loaded')->count(),
            'unloaded' => $unloaded->count(),
            'overdue' => $loads->filter->isOverdue()->count(),
            'late' => $unloaded->filter->wasLate()->count(),
            'avg_hours' => $durations->count() ? round($durations->avg() / 60, 1) : null,
            'weight' => (float) $loads->sum('weight'),
        ];

        $routes = $loads->groupBy(fn ($l) => $l->from_region . '|' . $l->to_region)
            ->map(fn ($g) => [
                'from' => SaudiRegions::name($g->first()->from_region),
                'to' => SaudiRegions::name($g->first()->to_region),
                'count' => $g->count(),
            ])
            ->sortByDesc('count')->take(10)->values();

        $loadTypes = $loads->groupBy(fn ($l) => trim($l->load_type))
            ->map(fn ($g, $type) => ['type' => $type, 'count' => $g->count(), 'weight' => (float) $g->sum('weight')])
            ->sortByDesc('count')->values();

        return view('transport.loads.report', [
            'loads' => $loads,
            'summary' => $summary,
            'routes' => $routes,
            'loadTypes' => $loadTypes,
            'from' => $from,
            'to' => $to,
            'regions' => SaudiRegions::options(),
            'trucks' => Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name']),
            'drivers' => Driver::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
