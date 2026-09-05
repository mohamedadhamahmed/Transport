<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BranchAccountsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index()
    {
        $this->authorize('branches.view');

        $branches = Branch::with('parentBranch:id,name')
            ->orderBy('name')
            ->paginate(20);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $this->authorize('branches.create');

        $parentOptions = Branch::where('type', 'main')->orderBy('name')->pluck('name', 'id');

        return view('branches.create', compact('parentOptions'));
    }

    public function store(Request $request)
    {
        $this->authorize('branches.create');

        $validated = $this->validated($request);

        // أول ما يتعمل فرع جديد، بنعمل معاه فورًا الحسابات الفرعية
        // السبعة (خزينة/بنك/مبيعات/مخزون/ضريبة/تكلفة مبيعات/مردود
        // مبيعات) تحت الحسابات الأساسية بتاعتها - عشان الفواتير
        // والمشتريات على الفرع ده تسجل صح من أول لحظة، بدل ما تتجاهل
        // بصمت لحد ما حد يعمل الحسابات دي يدويًا (راجع
        // App\Services\BranchAccountsService للتفاصيل).
        $branch = DB::transaction(function () use ($validated) {
            $branch = Branch::create($validated);

            app(BranchAccountsService::class)->ensureDefaultAccountsForBranch($branch);

            return $branch;
        });

        return redirect()->route('branches.index')->with('success', __('branches.created_success'));
    }

    public function edit(Branch $branch)
    {
        $this->authorize('branches.edit');

        $parentOptions = Branch::where('type', 'main')
            ->where('id', '!=', $branch->id)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('branches.edit', compact('branch', 'parentOptions'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('branches.edit');

        $validated = $this->validated($request, $branch);

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', __('branches.updated_success'));
    }

    public function destroy(Branch $branch)
    {
        $this->authorize('branches.delete');

        $inUse = $branch->products()->exists()
            || $branch->invoices()->exists()
            || $branch->receivedInvoices()->exists()
            || $branch->subBranches()->exists();

        if ($inUse) {
            return back()->with('error', __('branches.cannot_delete_in_use'));
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', __('branches.deleted_success'));
    }

    private function validated(Request $request, ?Branch $branch = null): array
    {
        $parentRules = ['nullable', 'required_if:type,sub', 'exists:branches,id'];

        if ($branch) {
            // فرع مينفعش يبقى فرع فرعي تابع لنفسه
            $parentRules[] = 'not_in:' . $branch->id;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:main,sub'],
            'parent_branch_id' => $parentRules,
        ]);
    }
}
