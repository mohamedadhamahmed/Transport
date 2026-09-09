<?php

namespace App\Http\Controllers;

use App\Models\Workstation;
use Illuminate\Http\Request;

class WorkstationController extends Controller
{
    public function index()
    {
        $workstations = Workstation::orderByDesc('id')->paginate(20);

        return view('manufacturing.workstations.index', compact('workstations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
        ]);

        Workstation::create([
            ...$validated,
            'branch_id' => $request->user()->branch_id ?? null,
            'code' => Workstation::generateCode(),
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', __('manufacturing.workstation_added'));
    }

    public function update(Request $request, Workstation $workstation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $workstation->update($validated);

        return back()->with('success', __('manufacturing.workstation_updated'));
    }

    public function destroy(Workstation $workstation)
    {
        $workstation->delete();

        return back()->with('success', __('manufacturing.workstation_deleted'));
    }
}
