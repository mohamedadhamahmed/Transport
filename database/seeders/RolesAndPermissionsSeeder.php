<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * بيزرع كل صلاحيات النظام (من config/permissions.php) + دورين
     * افتراضيين:
     * - "مدير عام" (is_super = true): بياخد كل الصلاحيات تلقائيًا
     *   (Gate::before)، وهو بس اللي يقدر ينشئ مستخدمين/فروع جديدة.
     * - "مستخدم عادي": بياخد كل الصلاحيات التشغيلية (عملاء، فواتير،
     *   مشتريات...) إلا إدارة المستخدمين/الفروع/الأدوار - عشان أي حساب
     *   حالي يفضل شغال بالظبط زي ما هو من غير ما يتقفل فجأة، لحد ما
     *   يتراجع يدويًا من شاشة الأدوار الجديدة.
     *
     * أول حساب اتعمل في النظام (أقل id في جدول users) بياخد "مدير عام"
     * تلقائيًا، وأي حساب تاني من غير دور بياخد "مستخدم عادي".
     *
     * آمن تشغّله أكتر من مرة - updateOrCreate/sync في كل حتة، ومبيغيّرش
     * دور أي مستخدم already تم تحديد دوره يدويًا.
     */
    public function run(): void
    {
        $permissionIds = [];
        $adminOnlyModules = ['administration'];

        foreach (PermissionRegistry::modules() as $moduleKey => $module) {
            foreach (($module['permissions'] ?? []) as $key => $meta) {
                $permission = Permission::updateOrCreate(
                    ['key' => $key],
                    [
                        'module' => $moduleKey,
                        'label' => $meta['label'] ?? $key,
                        'label_en' => $meta['label_en'] ?? $key,
                    ]
                );

                if (! in_array($moduleKey, $adminOnlyModules, true)) {
                    $permissionIds[] = $permission->id;
                }
            }
        }

        $superRole = Role::updateOrCreate(
            ['is_super' => true],
            ['name' => 'مدير عام', 'name_en' => 'Super Admin']
        );

        $standardRole = Role::firstOrCreate(
            ['name' => 'مستخدم عادي', 'is_super' => false],
            ['name_en' => 'Standard User']
        );
        $standardRole->permissions()->sync($permissionIds);

        $firstUser = User::oldest('id')->first();

        if ($firstUser && ! $firstUser->role_id) {
            $firstUser->update(['role_id' => $superRole->id]);
        }

        User::whereNull('role_id')
            ->when($firstUser, fn ($q) => $q->where('id', '!=', $firstUser->id))
            ->update(['role_id' => $standardRole->id]);
    }
}
