<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * إدارة الموردين - إضافة/تعديل/عرض قائمة
 * ⚠️ أسماء الحقول هنا مطابقة تمامًا لهيكلة جدول suppliers الفعلية
 * (suppliers.sql) - لاحظ إن أسماء بعض الأعمدة مختلفة عن جدول customers
 * رغم تشابه المعنى (tax_no وليس tax_number، crn حروف صغيرة وليس CRN،
 * sub_city وليس district، postcode وليس postal_code):
 * name, name_en, company_name, phone, email, tax_no, crn, balance,
 * credit_limit, address, city, sub_city, street_name, building_number,
 * plot_identification, postcode, notes, created_by.
 */
class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $supplier = new Supplier();

        return view('suppliers.create', compact('supplier'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_no' => ['nullable', 'string', 'max:255'],
            'crn' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'sub_city' => ['nullable', 'string', 'max:255'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:255'],
            'plot_identification' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier = DB::transaction(function () use ($validated) {
            $supplier = Supplier::create([
                'name' => $validated['name'],
                'name_en' => $validated['name_en'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'tax_no' => $validated['tax_no'] ?? null,
                'crn' => $validated['crn'] ?? null,
                'balance' => 0,
                'credit_limit' => $validated['credit_limit'] ?? 0,
                'address' => null,
                'city' => $validated['city'] ?? null,
                'sub_city' => $validated['sub_city'] ?? null,
                'street_name' => $validated['street_name'] ?? null,
                'building_number' => $validated['building_number'] ?? null,
                'plot_identification' => $validated['plot_identification'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // حساب مالي مرتبط بالمورد في شجرة الحسابات
            // ⚠️ parent_account_number = 3 افتراض تخميني للحساب الأب
            // الخاص بالموردين - راجعه حسب شجرة حساباتك الفعلية.
            $nextAccountNumber = FinancialAccount::where('account_type', 2)
                ->where('orginal_type', 2)
                ->max('account_number') + 1;

            FinancialAccount::create([
                'name' => $supplier->name,
                'account_type' => 2,
                'parent_account_number' => 3,
                'account_number' => $nextAccountNumber,
                'start_balance' => 0,
                'current_balance' => 0,
                'start_balance_status' => 3,
                'added_by' => Auth::id() ?? 1,
                'com_code' => 1,
                'date' => Carbon::now('Asia/Riyadh'),
                'active' => 1,
                'is_parent' => 0,
                'orginal_id' => $supplier->id,
                'orginal_type' => 2,
            ]);

            return $supplier;
        });

        return redirect()->route('suppliers.index')->with('success', __('suppliers.created_success'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_no' => ['nullable', 'string', 'max:255'],
            'crn' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'sub_city' => ['nullable', 'string', 'max:255'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:255'],
            'plot_identification' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier->update([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? $supplier->name_en,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? $supplier->email,
            'company_name' => $validated['company_name'] ?? $supplier->company_name,
            'tax_no' => $validated['tax_no'] ?? $supplier->tax_no,
            'crn' => $validated['crn'] ?? $supplier->crn,
            'credit_limit' => $validated['credit_limit'] ?? $supplier->credit_limit,
            'notes' => $validated['notes'] ?? $supplier->notes,
            'city' => $validated['city'] ?? $supplier->city,
            'sub_city' => $validated['sub_city'] ?? $supplier->sub_city,
            'street_name' => $validated['street_name'] ?? $supplier->street_name,
            'building_number' => $validated['building_number'] ?? $supplier->building_number,
            'plot_identification' => $validated['plot_identification'] ?? $supplier->plot_identification,
            'postcode' => $validated['postcode'] ?? $supplier->postcode,
        ]);

        FinancialAccount::where('orginal_type', 2)->where('orginal_id', $supplier->id)
            ->update(['name' => $supplier->name]);

        return redirect()->route('suppliers.index')->with('success', __('suppliers.updated_success'));
    }
}
