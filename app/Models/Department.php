<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /** قسم السائقين - بيتختار تلقائي للموظف اللي بيتعمل من شاشة السائقين */
    public const DRIVERS = 'السائقين';

    protected $fillable = ['name', 'name_en', 'notes', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department', 'name');
    }

    /** أسماء الأقسام النشطة للاختيار (+ القسم الحالي للموظف لو اتقفل) */
    public static function options(?string $include = null)
    {
        $names = static::query()
            ->where(fn ($q) => $q->where('is_active', true)->when($include, fn ($qq) => $qq->orWhere('name', $include)))
            ->orderByRaw('name = ? desc', [self::DRIVERS])
            ->orderBy('name')
            ->pluck('name');

        // قسم قديم (مكتوب يدوي/من الاستيراد) ومش في القائمة: يفضل ظاهر للموظف ده
        if ($include && !$names->contains($include)) {
            $names->push($include);
        }

        return $names;
    }

    public static function drivers(): self
    {
        return static::firstOrCreate(['name' => self::DRIVERS], ['name_en' => 'Drivers', 'is_active' => true]);
    }
}
