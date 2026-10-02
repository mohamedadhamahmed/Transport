<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\CreditTransaction;
use App\Models\Driver;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Truck;
use App\Models\TruckStoreIssue;
use App\Models\TruckStoreIssueItem;
use App\Support\AccountEffect;
use App\Support\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TruckStoreIssueController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('trucks.view');

        $query = TruckStoreIssue::with(['truck:id,plate_number,name', 'driver:id,name', 'costCenter:id,cost_center_ar', 'creator:id,name'])
            ->withCount('items')
            ->when($request->filled('truck_id'), fn ($q) => $q->where('truck_id', $request->input('truck_id')))
            ->when($request->filled('category'), fn ($q) => $q->where('expense_category', $request->input('category')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->input('date_to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->input('search'));
                $q->where(function ($qq) use ($s) {
                    $qq->where('issue_number', 'like', "%{$s}%")
                        ->orWhere('notes', 'like', "%{$s}%")
                        ->orWhereHas('truck', fn ($t) => $t->where('plate_number', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
                });
            });

        $issues = $query->orderByDesc('issue_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $trucks = Truck::orderBy('plate_number')->get(['id', 'plate_number', 'name']);

        return view('transport.store-issues.index', compact('issues', 'trucks'));
    }

    public function create()
    {
        $this->authorize('maintenance.create');

        $trucks = Truck::orderBy('plate_number')->get([
            'id', 'plate_number', 'name', 'driver_id', 'current_odometer',
            'last_oil_change_odometer', 'next_oil_change_odometer', 'status'
        ]);

        $drivers = Driver::where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']);
        $costCenters = CostCenter::orderBy('cost_center_ar')->get();

        $products = Product::orderBy('name')->get([
            'id', 'name', 'code', 'stock_quantity', 'purchase_price', 'average_cost', 'unit'
        ]);

        $inventoryAccounts = FinancialAccount::where(function ($q) {
            $q->where('id', 181)
                ->orWhere('parent_account_number', 181)
                ->orWhere('name', 'like', '%مخزون%');
        })->orderBy('name')->get(['id', 'name', 'account_number']);

        $expenseAccounts = FinancialAccount::where(function ($q) {
            $q->where('name', 'like', '%صيانة%')
                ->orWhere('name', 'like', '%زيت%')
                ->orWhere('name', 'like', '%قطع غيار%')
                ->orWhere('name', 'like', '%كفرات%')
                ->orWhere('id', 67);
        })->orWhere(function ($q) {
            $q->where('parent_account_number', 6)->where('account_type', 4);
        })->orderBy('name')->get(['id', 'name', 'account_number']);

        $defaultInventoryId = FinancialAccount::where('id', 181)->value('id')
            ?? ($inventoryAccounts->first()?->id);

        $defaultExpenseId = FinancialAccount::where('id', 67)->value('id')
            ?? ($expenseAccounts->first()?->id);

        return view('transport.store-issues.create', compact(
            'trucks', 'drivers', 'costCenters', 'products',
            'inventoryAccounts', 'expenseAccounts', 'defaultInventoryId', 'defaultExpenseId'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('maintenance.create');

        $validated = $request->validate([
            'issue_date' => ['required', 'date'],
            'truck_id' => ['required', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'expense_category' => ['required', 'string', 'in:oil,tires,spare_parts,maintenance,fuel,other'],
            'current_odometer' => ['nullable', 'numeric', 'min:0'],
            'oil_change_interval_km' => ['nullable', 'numeric', 'min:0'],
            'next_oil_change_odometer' => ['nullable', 'numeric', 'min:0'],
            'inventory_account_id' => ['nullable', 'exists:financialaccount,id'],
            'expense_account_id' => ['nullable', 'exists:financialaccount,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ], [
            'items.required' => 'يجب إضافة صنف واحد على الأقل في إذن الصرف',
            'items.min' => 'يجب إضافة صنف واحد على الأقل في إذن الصرف',
        ]);

        $issue = DB::transaction(function () use ($validated) {
            $truck = Truck::lockForUpdate()->findOrFail($validated['truck_id']);

            // حساب الإجمالي والتحقق من الكميات
            $totalAmount = 0;
            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = round($qty * $unitCost, 2);
                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }
            $totalAmount = round($totalAmount, 2);

            // تحديد حسابات القيد
            $invAccountId = ($validated['inventory_account_id'] ?? null)
                ?: (FinancialAccount::where('id', 181)->value('id') ?? FinancialAccount::where('name', 'like', '%مخزون%')->value('id'));
            $expAccountId = ($validated['expense_account_id'] ?? null)
                ?: (FinancialAccount::where('id', 67)->value('id') ?? FinancialAccount::where('name', 'like', '%صيانة%')->value('id'));


            $currentOdo = !empty($validated['current_odometer']) ? (int) $validated['current_odometer'] : null;
            $intervalKm = !empty($validated['oil_change_interval_km']) ? (int) $validated['oil_change_interval_km'] : null;
            $nextOdo = !empty($validated['next_oil_change_odometer']) ? (int) $validated['next_oil_change_odometer'] : null;

            if ($currentOdo && $intervalKm && !$nextOdo) {
                $nextOdo = $currentOdo + $intervalKm;
            }

            // إنشاء إذن الصرف
            $issue = TruckStoreIssue::create([
                'issue_number' => TruckStoreIssue::nextNumber(),
                'issue_date' => $validated['issue_date'],
                'truck_id' => $truck->id,
                'driver_id' => $validated['driver_id'] ?? $truck->driver_id,
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'branch_id' => $truck->branch_id ?? null,
                'expense_category' => $validated['expense_category'],
                'current_odometer' => $currentOdo,
                'oil_change_interval_km' => $intervalKm,
                'next_oil_change_odometer' => $nextOdo,
                'inventory_account_id' => $invAccountId,
                'expense_account_id' => $expAccountId,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // إنشاء البنود وخصم المخزون
            foreach ($itemsData as $data) {
                $issue->items()->create($data);
                Product::where('id', $data['product_id'])->decrement('stock_quantity', $data['quantity']);
            }

            // تحديث قراءات الشاحنة
            $truckUpdates = [];
            if ($currentOdo) {
                $truckUpdates['current_odometer'] = $currentOdo;
            }
            if ($validated['expense_category'] === 'oil' || $nextOdo) {
                if ($currentOdo) {
                    $truckUpdates['last_oil_change_odometer'] = $currentOdo;
                }
                if ($nextOdo) {
                    $truckUpdates['next_oil_change_odometer'] = $nextOdo;
                }
                $truckUpdates['last_oil_change_date'] = $validated['issue_date'];
            }
            if (!empty($truckUpdates)) {
                $truck->update($truckUpdates);
            }

            // إنشاء القيد المحاسبي المزدوج (مدين المصروف / دائن المخزون) إذا وُجدت الحسابات وقيمة > 0
            if ($invAccountId && $expAccountId && $totalAmount > 0) {
                $nextJE = (int) (JournalEntry::where('entry_type', JournalEntry::TYPE_DAILY)->max('id') ?? 0) + 1;
                $entryNumber = 'JE-' . str_pad((string) $nextJE, 6, '0', STR_PAD_LEFT);

                $desc = "إذن صرف قطع غيار وزيوت رقم {$issue->issue_number} للشاحنة ({$truck->plate_number})";

                $entry = JournalEntry::create([
                    'entry_number' => $entryNumber,
                    'entry_date' => $validated['issue_date'],
                    'entry_type' => JournalEntry::TYPE_DAILY,
                    'description' => $desc,
                    'branch_id' => $truck->branch_id ?? null,
                    'cost_center_id' => $validated['cost_center_id'] ?? null,
                    'created_by' => Auth::id(),
                    'total_debit' => $totalAmount,
                    'total_credit' => $totalAmount,
                ]);

                // 1. الطرف المدين: مصروف صيانة الشاحنة
                $expAccount = FinancialAccount::lockForUpdate()->find($expAccountId);
                if ($expAccount) {
                    $newExpBalance = AccountEffect::apply($expAccount, $totalAmount, 0);
                    $entry->lines()->create([
                        'account_id' => $expAccount->id,
                        'debit' => $totalAmount,
                        'credit' => 0,
                        'note' => $desc,
                    ]);
                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $expAccount->id,
                        'recive_amount' => $totalAmount,
                        'note' => $desc,
                        'currentblance' => $newExpBalance,
                        'branchs_id' => $expAccount->branchs_id ?? null,
                        'debtor' => $totalAmount,
                        'creditor' => 0,
                        'invoice_number' => $entry->entry_number,
                        'operation_type' => OperationType::TRUCK_STORE_ISSUE,
                        'date_export' => $validated['issue_date'],
                    ]);
                }

                // 2. الطرف الدائن: المخزون السلعي (نقصان المخزون)
                $invAccount = FinancialAccount::lockForUpdate()->find($invAccountId);
                if ($invAccount) {
                    $newInvBalance = AccountEffect::apply($invAccount, 0, $totalAmount);
                    $entry->lines()->create([
                        'account_id' => $invAccount->id,
                        'debit' => 0,
                        'credit' => $totalAmount,
                        'note' => $desc,
                    ]);
                    CreditTransaction::create([
                        'user_id' => Auth::id(),
                        'customer_id' => $invAccount->id,
                        'recive_amount' => $totalAmount,
                        'note' => $desc,
                        'currentblance' => $newInvBalance,
                        'branchs_id' => $invAccount->branchs_id ?? null,
                        'debtor' => 0,
                        'creditor' => $totalAmount,
                        'invoice_number' => $entry->entry_number,
                        'operation_type' => OperationType::TRUCK_STORE_ISSUE,
                        'date_export' => $validated['issue_date'],
                    ]);
                }

                $issue->update(['journal_entry_id' => $entry->id]);
            }

            return $issue;
        });

        return redirect()->route('transport.store-issues.show', $issue)
            ->with('success', 'تم حفظ إذن الصرف وخصم الكميات من المخزون وإنشاء القيد المحاسبي بنجاح');
    }

    public function show(TruckStoreIssue $storeIssue)
    {
        $this->authorize('trucks.view');

        $storeIssue->load([
            'truck', 'driver', 'costCenter', 'branch',
            'inventoryAccount', 'expenseAccount', 'journalEntry.lines.account',
            'creator', 'items.product'
        ]);

        return view('transport.store-issues.show', ['issue' => $storeIssue]);
    }

    public function destroy(TruckStoreIssue $storeIssue)
    {
        $this->authorize('maintenance.create');

        DB::transaction(function () use ($storeIssue) {
            // إرجاع الكميات للمخزن
            foreach ($storeIssue->items as $item) {
                Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
            }

            // حذف القيد وحركاته إذا وُجد
            if ($storeIssue->journal_entry_id && $storeIssue->journalEntry) {
                $je = $storeIssue->journalEntry;
                foreach ($je->lines as $line) {
                    if ($line->account) {
                        AccountEffect::reverse($line->account, (float)$line->debit, (float)$line->credit);
                    }
                }
                CreditTransaction::where('invoice_number', $je->entry_number)->delete();
                $je->lines()->delete();
                $je->delete();
            }

            $storeIssue->delete();
        });


        return redirect()->route('transport.store-issues.index')
            ->with('success', 'تم إلغاء إذن الصرف وإعادة الكميات إلى المخزون بنجاح');
    }

    public function report(Request $request)
    {
        $this->authorize('trucks.view');

        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        $issuesQuery = TruckStoreIssue::with(['truck', 'driver', 'costCenter', 'items.product'])
            ->whereDate('issue_date', '>=', $from)
            ->whereDate('issue_date', '<=', $to)
            ->when($request->filled('truck_id'), fn ($q) => $q->where('truck_id', $request->input('truck_id')))
            ->when($request->filled('category'), fn ($q) => $q->where('expense_category', $request->input('category')))
            ->when($request->filled('product_id'), function ($q) use ($request) {
                $q->whereHas('items', fn ($it) => $it->where('product_id', $request->input('product_id')));
            });

        $issues = $issuesQuery->orderByDesc('issue_date')->get();

        // إحصائيات عامة
        $totalCost = (float) $issues->sum('total_amount');
        $totalItemsCount = (int) $issues->sum(fn ($i) => $i->items->sum('quantity'));
        $oilCost = (float) $issues->where('expense_category', 'oil')->sum('total_amount');
        $tiresCost = (float) $issues->where('expense_category', 'tires')->sum('total_amount');
        $sparePartsCost = (float) $issues->where('expense_category', 'spare_parts')->sum('total_amount');

        // حالة غيار الزيت للشاحنات
        $trucks = Truck::orderBy('plate_number')->get();
        $oilStatusList = $trucks->map(function ($truck) {
            $current = $truck->current_odometer;
            $next = $truck->next_oil_change_odometer;
            $remaining = ($current && $next) ? ($next - $current) : null;

            $status = 'unknown';
            if ($remaining !== null) {
                if ($remaining <= 0) {
                    $status = 'overdue'; // حان موعد الغيار أو متأخر
                } elseif ($remaining <= 500) {
                    $status = 'soon'; // اقترب موعد الغيار
                } else {
                    $status = 'ok';
                }
            }

            return [
                'truck' => $truck,
                'current' => $current,
                'next' => $next,
                'remaining' => $remaining,
                'status' => $status,
                'last_date' => $truck->last_oil_change_date,
            ];
        })->sortBy(function ($row) {
            if ($row['status'] === 'overdue') return 0;
            if ($row['status'] === 'soon') return 1;
            if ($row['status'] === 'ok') return 2;
            return 3;
        })->values();

        $allProducts = Product::orderBy('name')->get(['id', 'name']);

        return view('transport.store-issues.report', compact(
            'issues', 'from', 'to', 'totalCost', 'totalItemsCount',
            'oilCost', 'tiresCost', 'sparePartsCost', 'oilStatusList', 'trucks', 'allProducts'
        ));
    }
}
