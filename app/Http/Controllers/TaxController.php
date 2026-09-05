<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $this->authorize('settings.taxes');

        // جلب الضرائب مرتبة حسب الأولوية تصاعدياً (الأول فالأول)
        $taxes = Tax::orderBy('priority', 'asc')->get();
        return view('taxes.index', compact('taxes'));
    }

public function store(Request $request)
{
    $this->authorize('settings.taxes');

    $request->validate([
        'name' => 'required|string|max:255',
        'rate' => 'required|numeric|min:0|max:100',
        'priority' => 'required|integer',
    ]);

    Tax::create([
        'name' => $request->name,
        'rate' => $request->rate,
        'priority' => $request->priority,
        'is_active' => true,
    ]);

    return redirect()->route('taxes.index')->with('success', __('taxes.success_add'));
}

public function destroy(Tax $tax)
{
    $this->authorize('settings.taxes');

    $tax->delete();
    return redirect()->route('taxes.index')->with('success', __('taxes.success_delete'));
}
}