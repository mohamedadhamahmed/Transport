<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles_name',
        'active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'branch_id', // تأكد أن الاسم يطابق قاعدة البيانات
        'role_id',
        'current_team_id',
        'profile_photo_path',
        'discount_allow_limit',
        'name_en',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'integer',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * العلاقة مع جدول الفروع (Branch)
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * العلاقة مع جدول الأدوار (Role) - كل مستخدم بياخد دور واحد بس،
     * وكل صلاحياته جاية من الدور ده (شوفي hasPermission تحت).
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * مدير عام؟ (بياخد كل الصلاحيات تلقائيًا بغض النظر عن جدول
     * permission_role - شوفي Gate::before في AppServiceProvider).
     */
    public function isSuperAdmin(): bool
    {
        return (bool) ($this->role?->is_super);
    }

    /**
     * مفاتيح صلاحيات دور المستخدم، متجابة مرة واحدة بس لكل نسخة User
     * (عادةً auth()->user() نفس النسخة طول الـ request) عشان منضربش
     * نفس الاستعلام كل ما نعمل @can في نفس الصفحة.
     */
    protected ?array $permissionKeysCache = null;

    public function permissionKeys(): array
    {
        if ($this->permissionKeysCache !== null) {
            return $this->permissionKeysCache;
        }

        if (! $this->role_id) {
            return $this->permissionKeysCache = [];
        }

        return $this->permissionKeysCache = $this->role
            ? $this->role->permissions()->pluck('key')->all()
            : [];
    }

    /**
     * هل المستخدم عنده صلاحية معيّنة؟ (المدير العام دايمًا true).
     */
    public function hasPermission(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($key, $this->permissionKeys(), true);
    }
}