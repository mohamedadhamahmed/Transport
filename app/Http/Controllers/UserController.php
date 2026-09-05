<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('users.view');

        $users = User::with(['role:id,name,is_super', 'branch:id,name'])
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('users.create');

        return view('users.create', [
            'roles' => Role::orderBy('name')->pluck('name', 'id'),
            'branches' => Branch::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('users.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'] ?? null,
            // عمود branch_id في قاعدة البيانات NOT NULL (افتراضيًا 1) -
            // لو محددتش فرع بنسيبه على القيمة الافتراضية بدل ما نبعت
            // null ونكسر الـ insert.
            'branch_id' => $validated['branch_id'] ?? 1,
            'active' => $request->boolean('active', true) ? 1 : 0,
            'roles_name' => '[]',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', __('users.created_success'));
    }

    public function edit(User $user)
    {
        $this->authorize('users.edit');

        return view('users.edit', [
            'targetUser' => $user,
            'roles' => Role::orderBy('name')->pluck('name', 'id'),
            'branches' => Branch::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        // مانعينش المستخدم يقفل نفسه بالغلط (يشيل دوره أو يعطّل حسابه
        // وهو داخل بيه دلوقتي).
        $isSelf = $user->id === Auth::id();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (! $isSelf) {
            $user->role_id = $validated['role_id'] ?? null;
            $user->active = $request->boolean('active', true) ? 1 : 0;
        }

        $user->branch_id = $validated['branch_id'] ?? 1;
        $user->save();

        return redirect()->route('users.index')->with('success', __('users.updated_success'));
    }

    /**
     * بدل الحذف الفعلي (وممكن يكسر ربط بيانات قديمة زي "created_by" في
     * الفواتير والمشتريات)، بنعطّل تسجيل الدخول بس (active = 0).
     */
    public function toggleActive(User $user)
    {
        $this->authorize('users.delete');

        if ($user->id === Auth::id()) {
            return back()->with('error', __('users.cannot_deactivate_self'));
        }

        $user->update(['active' => $user->active ? 0 : 1]);

        return back()->with('success', $user->active ? __('users.activated_success') : __('users.deactivated_success'));
    }
}
