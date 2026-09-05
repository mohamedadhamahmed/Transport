<?php

namespace App\Http\Controllers;

use App\Models\AssetCustody;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * عهدة الأصول (لابتوب، عربية، موبايل...) - راجع تعليق ميجريشن
 * create_asset_custodies_table لسبب فصلها عن EmployeeLoanController
 * (السلف/العهد المالية). تسجيل تشغيلي بحت من غير أي أثر محاسبي تلقائي.
 */
class AssetCustodyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('asset_custodies.view');

        $query = AssetCustody::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $assets = $query->orderByDesc('issued_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_number']);

        return view('asset-custodies.index', compact('assets', 'employees'));
    }

    public function store(Request $request)
    {
        $this->authorize('asset_custodies.view');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'condition_on_issue' => ['required', 'in:new,good,used,damaged'],
            'issued_date' => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        AssetCustody::create(array_merge($validated, [
            'status' => AssetCustody::STATUS_WITH_EMPLOYEE,
            'created_by' => Auth::id(),
        ]));

        return redirect()->route('asset-custodies.index')->with('success', __('asset_custodies.created_success'));
    }

    public function returnAsset(Request $request, AssetCustody $assetCustody)
    {
        $this->authorize('asset_custodies.view');

        $validated = $request->validate([
            'condition_on_return' => ['required', 'in:good,used,damaged,lost'],
            'returned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $assetCustody->update([
            'condition_on_return' => $validated['condition_on_return'],
            'returned_date' => $validated['returned_date'],
            'status' => $validated['condition_on_return'] === 'lost' ? AssetCustody::STATUS_LOST : AssetCustody::STATUS_RETURNED,
            'notes' => $validated['notes'] ?? $assetCustody->notes,
        ]);

        return redirect()->route('asset-custodies.index')->with('success', __('asset_custodies.returned_success'));
    }

    public function destroy(AssetCustody $assetCustody)
    {
        $this->authorize('asset_custodies.view');

        $assetCustody->delete();

        return redirect()->route('asset-custodies.index')->with('success', __('asset_custodies.deleted_success'));
    }
}
