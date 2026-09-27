<?php

use App\Support\PermissionRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تسجيل صلاحيات الأقسام الجديدة (النقليات، الشاحنات، الصيانة، الأقسام،
     * مخطط العهد...) في جدول permissions عشان تظهر وتتحفظ في شاشة الأدوار.
     *
     * + الأدوار الموجودة بتاخد الصلاحيات الجديدة المقابلة للي كانت معاها:
     *   فواتير المبيعات → فواتير النقليات، السندات → سندات الصيانة،
     *   تعديل الموظفين → الأقسام، السلف → مخطط العهد، تقرير الأحمال → تقرير الشاحنات.
     * + دور "مستخدم عادي" بياخد أي صلاحية جديدة (غير الإدارة) زي السيدر.
     *
     * الملف ده لازم يشتغل قبل 2026_09_28_000006 (مسح صلاحيات المبيعات).
     */
    private array $map = [
        'invoices.view' => ['transport_invoices.view'],
        'invoices.create' => ['transport_invoices.create'],
        'invoices.edit' => ['transport_invoices.edit'],
        'invoices.delete' => ['transport_invoices.delete'],
        'vouchers.view' => ['maintenance.view'],
        'vouchers.create' => ['maintenance.create'],
        'vouchers.edit' => ['maintenance.edit'],
        'employees.view' => ['departments.view'],
        'employees.edit' => ['departments.create', 'departments.edit', 'departments.delete'],
        'employee_loans.view' => ['employee_custody.view'],
        'truck_loads.report' => ['transport_reports.fleet'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $now = now();
        $existing = DB::table('permissions')->pluck('id', 'key');
        $newIds = [];
        $hasTimestamps = Schema::hasColumn('permissions', 'created_at');

        foreach (PermissionRegistry::modules() as $moduleKey => $module) {
            foreach (($module['permissions'] ?? []) as $key => $meta) {
                $row = [
                    'module' => $moduleKey,
                    'label' => $meta['label'] ?? $key,
                    'label_en' => $meta['label_en'] ?? $key,
                ];
                if (isset($existing[$key])) {
                    DB::table('permissions')->where('id', $existing[$key])->update($row + ($hasTimestamps ? ['updated_at' => $now] : []));
                } else {
                    $id = DB::table('permissions')->insertGetId(['key' => $key] + $row + ($hasTimestamps ? ['created_at' => $now, 'updated_at' => $now] : []));
                    $existing[$key] = $id;
                    if ($moduleKey !== 'administration') {
                        $newIds[] = $id;
                    }
                }
            }
        }

        if (!Schema::hasTable('permission_role') || !Schema::hasTable('roles')) {
            return;
        }

        $roles = DB::table('roles')->where('is_super', false)->get(['id', 'name']);

        foreach ($roles as $role) {
            $has = DB::table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $role->id)
                ->pluck('permissions.key')
                ->flip();

            $add = [];
            foreach ($this->map as $from => $tos) {
                if (isset($has[$from])) {
                    foreach ($tos as $to) {
                        if (isset($existing[$to]) && !isset($has[$to])) {
                            $add[$existing[$to]] = true;
                        }
                    }
                }
            }

            if ($role->name === 'مستخدم عادي') {
                foreach ($newIds as $id) {
                    $add[$id] = true;
                }
            }

            foreach (array_keys($add) as $pid) {
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $pid, 'role_id' => $role->id]);
            }
        }
    }

    public function down(): void
    {
        // الصلاحيات بتفضل (بيانات أدوار) - مفيش رجوع.
    }
};
