<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * إدارة العملاء - إضافة/تعديل/عرض قائمة
 * ⚠️ أسماء الحقول هنا مطابقة تمامًا لهيكلة جدول customers الفعلية
 * (customers.sql) - وليس افتراضات قديمة. الأعمدة الفعلية:
 * name, phone, email, company_name, address, city, notes, credit_limit,
 * balance, grace_period_days, tax_number, opening_balance, postal_code,
 * district, street_name, building_number, plot_identification,
 * commercial_registration_number.
 */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $customer = new Customer();

        return view('customers.create', compact('customer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'commercial_registration_number' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'grace_period_days' => ['nullable', 'integer', 'min:0'],
            'opening_balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:255'],
            'plot_identification' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = DB::transaction(function () use ($validated) {
            $customer = Customer::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'address' => null, // العنوان العام - غير مستخدم من الفورم حاليًا
                'city' => $validated['city'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'credit_limit' => $validated['credit_limit'] ?? 10000,
                'balance' => $validated['opening_balance'] ?? 0,
                'grace_period_days' => $validated['grace_period_days'] ?? 30,
                'tax_number' => $validated['tax_number'] ?? null,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'postal_code' => $validated['postal_code'] ?? null,
                'district' => $validated['district'] ?? null,
                'street_name' => $validated['street_name'] ?? null,
                'building_number' => $validated['building_number'] ?? null,
                'plot_identification' => $validated['plot_identification'] ?? null,
                'commercial_registration_number' => $validated['commercial_registration_number'] ?? null,
            ]);

            // إنشاء الحساب المالي المرتبط بالعميل في شجرة الحسابات (نفس منطق
            // InvoiceController::quickStoreCustomer() - طبقة محاسبية منفصلة
            // عن عمود customers.balance نفسه)
            $nextAccountNumber = FinancialAccount::where('account_type', 1)
                ->where('orginal_type', 1)
                ->max('account_number') + 1;

            $account = FinancialAccount::create([
                'name' => $customer->name,
                'account_type' => 1,
                'parent_account_number' => 2,
                'account_number' => $nextAccountNumber,
                'start_balance' => 0,
                'current_balance' => 0,
                'start_balance_status' => 3,
                'added_by' => Auth::id() ?? 1,
                'com_code' => 1,
                'date' => Carbon::now('Asia/Riyadh'),
                'active' => 1,
                'is_parent' => 0,
                'orginal_id' => $customer->id,
                'orginal_type' => 1,
            ]);

            $customer->update(['accounting_account_id' => $account->id]);

            return $customer;
        });

        return redirect()->route('customers.index')->with('success', __('customers.created_success'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'commercial_registration_number' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'grace_period_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:255'],
            'plot_identification' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
        ]);

        $customer->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? $customer->email,
            'company_name' => $validated['company_name'] ?? $customer->company_name,
            'tax_number' => $validated['tax_number'] ?? $customer->tax_number,
            'commercial_registration_number' => $validated['commercial_registration_number'] ?? $customer->commercial_registration_number,
            'credit_limit' => $validated['credit_limit'] ?? $customer->credit_limit,
            'grace_period_days' => $validated['grace_period_days'] ?? $customer->grace_period_days,
            'notes' => $validated['notes'] ?? $customer->notes,
            'city' => $validated['city'] ?? $customer->city,
            'district' => $validated['district'] ?? $customer->district,
            'street_name' => $validated['street_name'] ?? $customer->street_name,
            'building_number' => $validated['building_number'] ?? $customer->building_number,
            'plot_identification' => $validated['plot_identification'] ?? $customer->plot_identification,
            'postal_code' => $validated['postal_code'] ?? $customer->postal_code,
        ]);

        // تحديث اسم الحساب المالي المرتبط لو الاسم اتغيّر
        FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customer->id)
            ->update(['name' => $customer->name]);

        return redirect()->route('customers.index')->with('success', __('customers.updated_success'));
    }
}
