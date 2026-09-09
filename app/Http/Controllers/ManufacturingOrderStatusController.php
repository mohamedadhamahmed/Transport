<?php

namespace App\Http\Controllers;

use App\Models\ManufacturingOrderStatus;
use Illuminate\Http\Request;

class ManufacturingOrderStatusController extends Controller
{
    public function index()
    {
        $statuses = ManufacturingOrderStatus::orderBy('sort_order')->get();

        return view('manufacturing.statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'type' => 'required|in:open,in_progress,closed,cancelled',
        ]);

        ManufacturingOrderStatus::create([
            ...$validated,
            'branch_id' => $request->user()->branch_id ?? null,
            'sort_order' => ManufacturingOrderStatus::max('sort_order') + 1,
        ]);

        return back()->with('success', __('manufacturing.status_added'));
    }

    public function update(Request $request, ManufacturingOrderStatus $status)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'type' => 'required|in:open,in_progress,closed,cancelled',
        ]);

        $status->update($validated);

        return back()->with('success', __('manufacturing.status_updated'));
    }

    public function destroy(ManufacturingOrderStatus $status)
    {
        if ($status->is_default) {
            return back()->with('error', __('manufacturing.cannot_delete_default_status'));
        }

        $status->delete();

        return back()->with('success', __('manufacturing.status_deleted'));
    }
}
