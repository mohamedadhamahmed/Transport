<?php

namespace App\Support;

/**
 * غلاف صغير حوالين config('permissions.php') - المصدر الوحيد لأي صلاحية
 * في النظام. بيستخدمه Gate::before (AppServiceProvider) عشان يعرف هل
 * الـ ability اللي بيتفحص أصلًا صلاحية معرّفة عندنا (وإلا سيب Laravel
 * يتصرف عادي)، وبيستخدمه RoleController لبناء شاشة مصفوفة الصلاحيات.
 */
class PermissionRegistry
{
    /**
     * كل مجموعات الصلاحيات زي ما هي في config/permissions.php، مقسّمة
     * حسب القسم (module) - جاهزة للعرض في شاشة إدارة الأدوار.
     */
    public static function modules(): array
    {
        return config('permissions', []);
    }

    /**
     * كل مفاتيح الصلاحيات مسطّحة في مصفوفة واحدة (key => ['module' =>,
     * 'label' =>, 'label_en' =>]).
     */
    public static function all(): array
    {
        $flat = [];

        foreach (static::modules() as $moduleKey => $module) {
            foreach (($module['permissions'] ?? []) as $key => $meta) {
                $flat[$key] = [
                    'module' => $moduleKey,
                    'label' => $meta['label'] ?? $key,
                    'label_en' => $meta['label_en'] ?? $key,
                ];
            }
        }

        return $flat;
    }

    /**
     * هل المفتاح ده صلاحية معرّفة فعليًا في config/permissions.php؟
     * بيستخدمها Gate::before عشان يفرّق بين صلاحياتنا وأي ability تاني
     * مش تابع لنظام الصلاحيات (يسيبه لـ Laravel يقرر فيه عادي).
     */
    public static function exists(string $key): bool
    {
        return array_key_exists($key, static::all());
    }

    /**
     * كل مفاتيح الصلاحيات كمصفوفة بسيطة من النصوص - مفيدة للسيدر ولأي
     * دور عايز ياخد كل الصلاحيات دفعة واحدة.
     */
    public static function keys(): array
    {
        return array_keys(static::all());
    }
}
