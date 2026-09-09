<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workstation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'description',
        'cost',
        'status',
        'created_by',
    ];

    protected $casts = [
        'cost' => 'float',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function manufacturingOrders()
    {
        return $this->hasMany(ManufacturingOrder::class);
    }

    /**
     * بيولّد كود تسلسلي جديد زي #000001, #000002... مطابق لنفس أسلوب
     * الترقيم الظاهر في شاشات Daftra.
     */
    public static function generateCode(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        $next = $last ? ((int) preg_replace('/\D/', '', $last->code)) + 1 : 1;

        return str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
