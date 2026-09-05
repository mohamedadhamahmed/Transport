<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('roles.manage');

        $roles = Role::withCount('users')->orderByDesc('is_super')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('roles.manage');

        return view('roles.create', [
            'modules' => PermissionRegistry::modules(),
            'checkedKeys' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('roles.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? null,
            'is_super' => false, // مدير عام بيتحدد يدويًا في قاعدة البيانات بس - مش من الشاشة دي
        ]);

        $this->syncPermissions($role, $validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', __('roles.created_success'));
    }

    public function edit(Role $role)
    {
        $this->authorize('roles.manage');

        return view('roles.edit', [
            'role' => $role,
            'modules' => PermissionRegistry::modules(),
            'checkedKeys' => $role->is_super ? [] : $role->permissions()->pluck('key')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('roles.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? null,
        ]);

        if (! $role->is_super) {
            $this->syncPermissions($role, $validated['permissions'] ?? []);
        }

        return redirect()->route('roles.index')->with('success', __('roles.updated_success'));
    }

    public function destroy(Role $role)
    {
        $this->authorize('roles.manage');

        if ($role->users()->exists()) {
            return back()->with('error', __('roles.cannot_delete_in_use'));
        }

        if ($role->is_super && Role::where('is_super', true)->count() <= 1) {
            return back()->with('error', __('roles.cannot_delete_last_super'));
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', __('roles.deleted_success'));
    }

    private function syncPermissions(Role $role, array $keys): void
    {
        $permissionIds = Permission::whereIn('key', $keys)->pluck('id', 'key');
        $ids = [];

        foreach ($keys as $key) {
            if (isset($permissionIds[$key])) {
                $ids[] = $permissionIds[$key];
            }
        }

        $role->permissions()->sync($ids);
    }
}
