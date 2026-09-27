<?php

namespace App\Support;

use App\Models\Region;

/**
 * مناطق الشاحنات: مناطق المملكة الأساسية (config/saudi_regions.php) +
 * المناطق المضافة من شاشة المناطق (جدول regions). أي منطقة مضافة بتظهر
 * تلقائيًا في كل الاختيارات لأن كل الشاشات بتقرا من options()/keys().
 */
class SaudiRegions
{
    private static ?array $custom = null;

    /** مناطق المملكة الأساسية [key => ['ar' =>, 'en' =>]] */
    public static function base(): array
    {
        return config('saudi_regions', []);
    }

    /** المناطق المضافة [key => ['ar' =>, 'en' =>]] */
    public static function custom(): array
    {
        if (self::$custom !== null) {
            return self::$custom;
        }

        try {
            self::$custom = Region::orderBy('id')->get(['key', 'name', 'name_en'])
                ->filter(fn ($r) => $r->key)
                ->mapWithKeys(fn ($r) => [$r->key => ['ar' => $r->name, 'en' => $r->name_en ?: $r->name]])
                ->all();
        } catch (\Throwable $e) {
            // الجدول لسه مش موجود (قبل php artisan migrate)
            self::$custom = [];
        }

        return self::$custom;
    }

    public static function flush(): void
    {
        self::$custom = null;
    }

    /** [key => الاسم باللغة الحالية] */
    public static function options(): array
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

        return collect(self::base() + self::custom())
            ->map(fn ($r) => $r[$locale] ?? $r['ar'])
            ->all();
    }

    public static function keys(): array
    {
        return array_keys(self::base() + self::custom());
    }

    public static function isBase(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::base());
    }

    public static function name(?string $key): string
    {
        if (!$key) {
            return '-';
        }

        return self::options()[$key] ?? $key;
    }
}
