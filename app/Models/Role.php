<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'is_super',
    ];

    protected $casts = [
        'is_super' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * هل الدور ده عنده مفتاح صلاحية معيّن؟ (المدير العام is_super بياخد
     * كل حاجة تلقائيًا من غير ما نحتاج نحطها في permission_role).
     */
    public function hasPermission(string $key): bool
    {
        if ($this->is_super) {
            return true;
        }

        return $this->permissions()->where('key', $key)->exists();
    }
}
