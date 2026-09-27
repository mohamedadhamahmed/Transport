<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Support\SaudiRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * شاشة المناطق: مناطق المملكة الأساسية (ثابتة) + إضافة/حذف مناطق مخصصة.
 * المنطقة المضافة بتظهر في كل اختيارات المناطق (التحميل من/إلى، مكان
 * الشاحنة، لوحة الشاحنات، البوالص، التقارير) من غير أي تعديل تاني.
 */
class RegionController extends Controller
{
    /** الجداول/الأعمدة اللي بتخزن مفتاح المنطقة - المنطقة المستخدمة فيها مينفعش تتحذف */
    private const USAGE = [
        'truck_loads' => ['from_region', 'to_region'],
        'trucks' => ['current_region'],
        'waybills' => ['from_region', 'to_region'],
        'transport_quotation_items' => ['from_region', 'to_region'],
    ];

    public function index()
    {
        $this->authorize('truck_loads.view');

        $stats = $this->stats();
        $make = fn ($key, $name, $isBase, $id = null) => [
            'id' => $id,
            'key' => $key,
            'name' => $name,
            'base' => $isBase,
            'trucks' => $stats['trucks'][$key] ?? 0,
            'loads' => $stats['loads'][$key] ?? 0,
        ];

        $options = SaudiRegions::options();
        $base = collect(SaudiRegions::base())->keys()->map(fn ($k) => $make($k, $options[$k] ?? $k, true));

        $custom = Region::orderBy('id')->get()->filter(fn ($r) => $r->key)
            ->map(fn ($r) => $make($r->key, $options[$r->key] ?? $r->name, false, $r->id))->values();

        return view('transport.regions.index', compact('base', 'custom'));
    }

    public function store(Request $request)
    {
        $this->authorize('truck_loads.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
        ], [], ['name' => __('transport.region_name')]);

        $name = trim(preg_replace('/\s+/u', ' ', $data['name']));

        $taken = collect(SaudiRegions::base() + SaudiRegions::custom())
            ->contains(fn ($r) => mb_strtolower(trim($r['ar'])) === mb_strtolower($name) || mb_strtolower(trim($r['en'] ?? '')) === mb_strtolower($name));
        if ($taken) {
            return back()->withInput()->with('error', __('transport.region_exists', ['name' => $name]));
        }

        DB::transaction(function () use ($name, $data) {
            $region = Region::create([
                'name' => $name,
                'name_en' => $data['name_en'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $region->update(['key' => 'region_' . $region->id]);
        });

        SaudiRegions::flush();

        return redirect()->route('transport.regions.index')->with('success', __('transport.region_added', ['name' => $name]));
    }

    public function destroy(Region $region)
    {
        $this->authorize('truck_loads.manage');

        if ($region->key && $this->isUsed($region->key)) {
            return back()->with('error', __('transport.region_in_use', ['name' => $region->name]));
        }

        $region->delete();
        SaudiRegions::flush();

        return back()->with('success', __('transport.region_deleted'));
    }

    // ------------------------------------------------------------------

    /** عدد الشاحنات في كل منطقة (الفاضية فيها + المحمّلة رايحة لها) وعدد الحمولات (من/إلى) */
    private function stats(): array
    {
        $trucks = Truck::where('status', '!=', 'inactive')->whereNotNull('current_region')
            ->groupBy('current_region')->selectRaw('current_region as r, COUNT(*) as c')->pluck('c', 'r')->all();

        $heading = TruckLoad::where('status', 'loaded')
            ->groupBy('to_region')->selectRaw('to_region as r, COUNT(*) as c')->pluck('c', 'r')->all();
        foreach ($heading as $r => $c) {
            $trucks[$r] = ($trucks[$r] ?? 0) + $c;
        }

        $loads = [];
        foreach (['from_region', 'to_region'] as $col) {
            $rows = TruckLoad::where('status', '!=', 'cancelled')
                ->groupBy($col)->selectRaw("$col as r, COUNT(*) as c")->pluck('c', 'r')->all();
            foreach ($rows as $r => $c) {
                $loads[$r] = ($loads[$r] ?? 0) + $c;
            }
        }
        // الحمولة اللي من نفس المنطقة لنفس المنطقة ماتتحسبش مرتين
        $same = TruckLoad::where('status', '!=', 'cancelled')->whereColumn('from_region', 'to_region')
            ->groupBy('from_region')->selectRaw('from_region as r, COUNT(*) as c')->pluck('c', 'r')->all();
        foreach ($same as $r => $c) {
            $loads[$r] -= $c;
        }

        return ['trucks' => $trucks, 'loads' => $loads];
    }

    private function isUsed(string $key): bool
    {
        foreach (self::USAGE as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $col) {
                if (Schema::hasColumn($table, $col) && DB::table($table)->where($col, $key)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
}
