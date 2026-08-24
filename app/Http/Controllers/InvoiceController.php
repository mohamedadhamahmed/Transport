<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\FinancialAccount;
use App\Models\InvoiceItem;
use App\Models\CreditTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'branch', 'creator'])->latest();

        if ($request->filled('invoice_number')) {
            $invoiceNumber = $request->string('invoice_number');
            $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('issue_date', $request->date('date'));
        }

        $invoices = $query->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

public function create()
{
    $customers = Customer::orderBy('name')->get();
    $branches = Branch::orderBy('name')->get();

    $maxDiscountPercent = DB::table('employee_discount_settings')
        ->where('user_id', auth()->user()->id)
        ->where('branchs_id', auth()->user()->branch_id)
        ->value('max_discount') ?? 0;

    return view('invoices.create', compact('customers', 'branches', 'maxDiscountPercent'));
}

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'branch', 'items.product', 'creator', 'returns']);

        // Map your new model attributes to the keys your Blade template uses
        $data = [
            'invoiceData' => $invoice,
            'totatextlriyales' => '', // Replace with your number-to-words logic if needed
            'totatextlrihalala' => '',
        ];
        // If your view still references old column names like $invoice->cashamount, 
        // you can either update the Blade file or handle compatibility attributes.
        return view('invoices.show', compact('data'));
    }
    /**
     * البحث عن منتجات لإضافتها لسطور الفاتورة (يستخدم من فورم إنشاء الفاتورة).
     * دلوقتي بيفلتر على فرع المستخدم فقط (لو اتبعت branch_id، وإلا بيرجع
     * لفرع المستخدم المسجل دخوله كـ fallback).
     */

    public function edit(Invoice $invoice)
    {
        // يمكنك جلب البيانات التي تحتاجها في صفحة التعديل مثل العملاء والمنتجات
        $customers = \App\Models\Customer::all();

        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // منطق تحديث الفاتورة هنا

        return redirect()->route('invoices.index')->with('success', 'تم تحديث الفاتورة بنجاح');
    }
    public function searchProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->where('status', 'active')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'code', 'sale_price', 'purchase_price', 'stock_quantity']);

        return response()->json($products);
    }

    /**
     * قائمة منتجات مقسّمة صفحات (20 في كل صفحة) لمودال "اختيار منتج" في
     * شاشة إنشاء الفاتورة - زي المودال في النظام القديم (بحث + صفحات تتحمل
     * بالـ ajax بدل ما تتحمل كل المنتجات مرة واحدة).
     * دلوقتي بيفلتر على فرع المستخدم فقط.
     */
    public function pickProducts(Request $request)
    {
        $search = (string) $request->query('q', '');
        $branchId = $request->query('branch_id', Auth::user()?->branch_id);

        $products = Product::query()
            ->with('branch:id,name')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $products->getCollection()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'branch_name' => $p->branch?->name,
                    'location' => $p->location,
                    'stock_quantity' => $p->stock_quantity,
                    'purchase_price' => $p->purchase_price,
                    'sale_price' => $p->sale_price,
                    'average_cost' => $p->average_cost,
                    'notes' => $p->notes,
                    'reference_number' => $p->reference_number,
                ];
            })->values(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * إضافة عميل سريعة من فورم الفاتورة (بدون مغادرة الصفحة).
     */
    public function quickStoreCustomer(Request $request)
    {
        // 1. التحقق من صحة البيانات (Validate)
        $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'name_en'                 => ['nullable', 'string', 'max:255'],
            'phone'                   => ['required', 'string', 'max:255'],
            'email'                   => ['nullable', 'email', 'max:255'],
            'tax_no'                  => ['nullable', 'numeric'], // أو Tax_Number حسب فورم الإرسال
            'balance'                 => ['nullable', 'numeric'],
            'credit_limit'            => ['nullable', 'numeric', 'min:0'],
            'grace_period_in_days'    => ['nullable', 'numeric'],
            'street_name'             => ['nullable', 'string', 'max:255'],
            'building_number'         => ['nullable', 'string', 'max:255'],
            'plot_identification'     => ['nullable', 'string', 'max:255'],
            'postcode'                => ['nullable', 'string', 'max:255'],
            'CRN'                     => ['nullable', 'string', 'max:255'],
            'notes'                   => ['nullable', 'string'],
        ]);

        // 2. تنفيذ العملية داخل Transaction لضمان السلامة المالية وقاعدة البيانات
        $customer = DB::transaction(function () use ($request) {

            // إنشاء العميل الجديد
            $newCustomer = Customer::create([
                'name'                 => $request->name,
                'name_en'              => $request->name_en ?? null,
                'comp_name'            => $request->company_name ?? $request->name,
                'tax_no'               => $request->tax_no ?? $request->input('TaxـNumber', 0),
                'Balance'              => $request->balance ?? $request->credit_limit ?? 0,
                'phone'                => $request->phone ?? '05----------',
                'email'                => $request->email ?? 'Email@gmail.com',
                'notes'                => $request->notes ?? $request->product_notes ?? "لا توجد ملاحظات",
                'Limit_credit'         => $request->credit_limit ?? 0,
                'grace_period_in_days' => $request->grace_period_in_days ?? 0,
                'street_name'          => $request->street_name ?? $request->StreetName ?? null,
                'building_number'      => $request->building_number ?? $request->buildnumber ?? null,
                'plot_identification'  => $request->plot_identification ?? null,
                'address'              => $request->city ?? "Client Address",
                'sub_city'             => $request->sub_city ?? "Client Address",
                'postcode'             => $request->postcode ?? null,
                'CRN'                  => $request->CRN ?? null,
            ]);

            // توليد رقم الحساب التالي في شجرة الحسابات للعملاء (Account Type: 1, Parent: 2)
            $nextAccountNumber = FinancialAccount::where('account_type', 1)
                ->where('orginal_type', 1)
                ->max('account_number') + 1;

            // إنشاء الحساب المالي المرتبط بالعميل في شجرة الحسابات
            FinancialAccount::create([
                'name'                  => $request->name,
                'account_type'          => 1,
                'parent_account_number' => 2, // الحساب الأب للعملاء
                'account_number'        => $nextAccountNumber,
                'start_balance'         => 0,
                'current_balance'       => 0,
                'start_balance_status'  => 3,
                'other_table_FK'        => NULL,
                'notes'                 => NULL,
                'added_by'              => Auth::id() ?? 1,
                'updated_by'            => NULL,
                'com_code'              => 1,
                'date'                  => Carbon::now('Asia/Riyadh'),
                'active'                => 1,
                'is_parent'             => 0,
                'orginal_id'            => $newCustomer->id,
                'orginal_type'          => 1, // نوع الأصل يعبر عن عميل
            ]);

            return $newCustomer;
        });

        // 3. طريقة الإرجاع (سواء كنت تفضل JSON أو Redirect)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $customer->id,
                'name' => $customer->name,
                'message' => __('تم اضافة العميل بنجاح')
            ]);
        }

        $message = app()->getLocale() == 'ar' ? 'تم اضافة العميل بنجاح' : 'Client added successfully';
        session()->flash('newcustomer', $message);

        return redirect()->back();
    }

    /**
     * إضافة منتج سريعة من فورم الفاتورة (بدون مغادرة الصفحة).
     * المنتج بيتربط تلقائيًا بفرع المستخدم الحالي (نفس منطق
     * ProductController@store، بس بدون مغادرة شاشة الفاتورة).
     */
    public function quickStoreProduct(Request $request)
    {
        $branchId = $request->input('branch_id') ?? Auth::user()?->branch_id;
        $request->merge(['branch_id' => $branchId, 'status' => 'active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = Auth::id();

        $product = Product::create($validated);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sale_price' => $product->sale_price,
            'purchase_price' => $product->purchase_price,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }

    public function store(Request $request)
    {$items = json_decode((string) $request->input('items_json'), true) ?: [];
$request->merge(['items' => $items]);

$validated = Validator::make($request->all(), [
    'customer_id' => ['required', 'exists:customers,id'],
    'branch_id' => ['required', 'exists:branches,id'],
    'payment_method' => ['required', 'in:cash,bank_transfer,card,credit,split'],
    'cash_amount' => ['nullable', 'numeric', 'min:0'],
    'bank_amount' => ['nullable', 'numeric', 'min:0'],
    'note' => ['nullable', 'string'],
    'purchase_order_number' => ['nullable', 'string', 'max:255'],
    'invoice_level_discount' => ['nullable', 'numeric', 'min:0'],
    'is_finalized' => ['required', 'boolean'],
    'items' => ['required', 'array', 'min:1'],
    'items.*.product_id' => ['required', 'exists:products,id'],
    'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
    'items.*.unit_price' => ['required', 'numeric', 'min:0'],
    'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
    'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
])->validate();

$invoice = DB::transaction(function () use ($validated, $request) {
    // إعادة حساب الإجماليات من السيرفر لضمان الدقة
    $subtotal = 0;
    $taxTotal = 0;
    $discountTotal = 0;
    $totalCost = 0; // لحساب تكلفة البضاعة المباعة

    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);
        $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
        $subtotal += $lineSubtotal;
        $taxTotal += $lineSubtotal * $item['tax_rate'];
        $discountTotal += $item['discount_amount'] ?? 0;

        // حساب التكلفة الإجمالية بناءً على سعر الشراء للمنتج
        if ($product) {
            $totalCost += ($product->purchase_price ?? 0) * $item['quantity'];
        }
    }

    $invoiceLevelDiscount = min($validated['invoice_level_discount'] ?? 0, $subtotal + $taxTotal);
    $grandTotal = $subtotal + $taxTotal - $invoiceLevelDiscount;
    $totalQuantity = array_sum(array_column($validated['items'], 'quantity'));

    $cashAmount = 0;
    $bankAmount = 0;
    $creditAmount = 0;

    switch ($validated['payment_method']) {
        case 'cash':
            $cashAmount = $grandTotal;
            break;
        case 'bank_transfer':
        case 'card':
            $bankAmount = $grandTotal;
            break;
        case 'credit':
            $creditAmount = $grandTotal;
            break;
        case 'split':
            $cashAmount = $validated['cash_amount'] ?? 0;
            $bankAmount = $validated['bank_amount'] ?? 0;
            $creditAmount = max(0, $grandTotal - ($cashAmount + $bankAmount));
            break;
    }

    // 1. إنشاء الفاتورة
    $invoice = Invoice::create([
        'customer_id' => $validated['customer_id'],
        'branch_id' => $validated['branch_id'],
        'created_by' => Auth::id(),
        'subtotal' => $subtotal,
        'tax_amount' => $taxTotal,
        'discount_amount' => $discountTotal,
        'total_quantity' => $totalQuantity,
        'payment_method' => $validated['payment_method'],
        'has_multiple_payment_methods' => $validated['payment_method'] === 'split',
        'cash_amount' => $cashAmount,
        'bank_amount' => $bankAmount,
        'credit_amount' => $creditAmount,
        'is_finalized' => $validated['is_finalized'],
        'note' => $validated['note'] ?? null,
        'purchase_order_number' => $validated['purchase_order_number'] ?? null,
        'invoice_level_discount' => $invoiceLevelDiscount,
        'issue_date' => now()->toDateString(),
        'issue_time' => now()->toTimeString(),
    ]);

    $invoice->update(['invoice_number' => (string) $invoice->id]);

    // 2. إنشاء تفاصيل الفاتورة وتحديث المخزون
    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);
        $lineSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
        $lineTax = $lineSubtotal * $item['tax_rate'];

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $item['product_id'],
            'branch_id' => $validated['branch_id'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'discount_amount' => $item['discount_amount'] ?? 0,
            'tax_amount' => $lineTax,
            'tax_rate' => $item['tax_rate'],
            'product_name_snapshot' => $product?->name,
            'is_finalized' => $validated['is_finalized'],
            'created_by' => Auth::id(),
            'remaining_quantity' => $item['quantity'],
        ]);

        if ($product && $validated['is_finalized']) {
            $product->decrement('stock_quantity', $item['quantity']);
            $product->increment('total_sold', $item['quantity']);
        }
    }

    // 3. القيود المحاسبية والحركات المالية (في حال كانت الفاتورة معتمدة)
    if ($validated['is_finalized'] && $grandTotal > 0) {
        $confirmInvoice = $invoice;
        $customerId = $validated['customer_id'];
        $customerData = Customer::find($customerId);

        // تجهيز بيانات المبالغ لتتوافق مع القيود المضافة
        $pData = [
            'cashamount' => $cashAmount,
            'Bank_transfer' => ($validated['payment_method'] === 'bank_transfer' || $validated['payment_method'] === 'card') ? $bankAmount : 0,
            'bankamount' => ($validated['payment_method'] === 'split') ? $bankAmount : 0,
            'creaditamount' => $creditAmount,
            'created_at' => Carbon::now('Asia/Riyadh'),
        ];

        // أ. القيود المحاسبية لـ Cash
        if ($pData['cashamount']) {
            $financialAccount = FinancialAccount::where('parent_account_number', 5)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($financialAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $financialAccount->id,
                    'recive_amount' => $pData['cashamount'],
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $financialAccount->current_balance + $pData['cashamount'],
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $pData['cashamount'],
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }

        // ب. القيود المحاسبية للشبكة والتحويل البنكي
        $totalBank = $pData['Bank_transfer'] + $pData['bankamount'];
        if ($totalBank) {
            $financialAccount = FinancialAccount::where('parent_account_number', 4)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($financialAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $financialAccount->id,
                    'recive_amount' => $totalBank,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $financialAccount->current_balance + $totalBank,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $totalBank,
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }

            $customerAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerAccount->id,
                    'recive_amount' => 0,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $customerAccount->current_balance + $pData['creaditamount'],
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }

        // ج. تحديث حساب ضريبة القيمة المضافة والإيرادات
        $totalValue = $pData['Bank_transfer'] + $pData['creaditamount'] + $pData['bankamount'] + $pData['cashamount'];
        $vatValue = $totalValue - ($totalValue * 100 / 115);
        $netRevenue = $totalValue * 100 / 115;

        // حساب الضريبة (102)
        $vatAccount = FinancialAccount::where('parent_account_number', 102)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
        if ($vatAccount) {
            $vatAccount->update([
                'current_balance' => $vatAccount->current_balance + $vatValue,
                'creditor_current' => $vatAccount->creditor_current + $vatValue,
            ]);

            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $vatAccount->id,
                'recive_amount' => $vatValue,
                'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                'pay_method' => $validated['payment_method'],
                'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                'currentblance' => $vatAccount->current_balance,
                'Pay_Method_Name' => ucfirst($validated['payment_method']),
                'created_at' => $pData['created_at'],
                'updated_at' => Carbon::now('Asia/Riyadh'),
                'creditor' => $vatValue,
                'vat' => 1,
                'name' => $customerData->name ?? '',
                'tax' => $customerData->tax_no ?? '',
                'operation_type' => 1,
                'invoice_number' => $confirmInvoice->invoice_number,
            ]);
        }

        // حساب المبيعات والإيرادات (112)
        $revenueAccount = FinancialAccount::where('parent_account_number', 112)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
        if ($revenueAccount) {
            CreditTransaction::create([
                'user_id' => Auth::id(),
                'customer_id' => $revenueAccount->id,
                'recive_amount' => $netRevenue,
                'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                'pay_method' => $validated['payment_method'],
                'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                'currentblance' => $revenueAccount->current_balance + $netRevenue,
                'Pay_Method_Name' => ucfirst($validated['payment_method']),
                'created_at' => $pData['created_at'],
                'updated_at' => Carbon::now('Asia/Riyadh'),
                'creditor' => $netRevenue,
                'operation_type' => 1,
                'invoice_number' => $confirmInvoice->invoice_number,
            ]);
        }

        // د. حساب تكلفة البضاعة المباعة (183) والمخزن (181)
        if ($totalCost > 0) {
            $costAccount = FinancialAccount::where('parent_account_number', 183)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($costAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $costAccount->id,
                    'recive_amount' => $totalCost,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $costAccount->current_balance + $totalCost,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $totalCost,
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }

            $inventoryAccount = FinancialAccount::where('parent_account_number', 181)->where('branchs_id', Auth::user()->branchs_id ?? $validated['branch_id'])->first();
            if ($inventoryAccount) {
                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $inventoryAccount->id,
                    'recive_amount' => $totalCost,
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $inventoryAccount->current_balance - $totalCost,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'creditor' => $totalCost,
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }

        // هـ. في حال وجود مبالغ آجلة يتم تحديث رصيد العميل المحاسبي
        if ($pData['creaditamount'] != 0 && $customerData) {
            $customerData->increment('Balance', $pData['creaditamount']);

            $customerFinancialAccount = FinancialAccount::where('orginal_type', 1)->where('orginal_id', $customerId)->first();
            if ($customerFinancialAccount) {
                $customerFinancialAccount->update([
                    'current_balance' => $customerFinancialAccount->current_balance + $pData['creaditamount'],
                    'debtor_current' => $customerFinancialAccount->debtor_current + $pData['creaditamount'],
                ]);

                CreditTransaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerFinancialAccount->id,
                    'recive_amount' => $pData['creaditamount'],
                    'branchs_id' => Auth::user()->branchs_id ?? $validated['branch_id'],
                    'pay_method' => $validated['payment_method'],
                    'note' => 'فاتورة مبيعات رقم :' . $confirmInvoice->id,
                    'currentblance' => $customerFinancialAccount->current_balance,
                    'Pay_Method_Name' => ucfirst($validated['payment_method']),
                    'created_at' => $pData['created_at'],
                    'updated_at' => Carbon::now('Asia/Riyadh'),
                    'debtor' => $pData['creaditamount'],
                    'operation_type' => 1,
                    'invoice_number' => $confirmInvoice->invoice_number,
                ]);
            }
        }
    }

    return $invoice;
});

return redirect()->route('invoices.show', $invoice)
    ->with('success', __('invoices.created_successfully'));
    
    }
}
