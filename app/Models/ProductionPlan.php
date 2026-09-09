<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'product_id',
        'bill_of_material_id',
        'customer_id',
        'quantity',
        'date_start',
        'date_end',
        'source',
        'status_id',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function status()
    {
        return $this->belongsTo(ManufacturingOrderStatus::class, 'status_id');
    }

    public function manufacturingOrders()
    {
        return $this->hasMany(ManufacturingOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCode(): string
    {
        $last = static::withTrashed()->orderByDesc('id')->first();
        $next = $last ? ((int) preg_replace('/\D/', '', $last->code)) + 1 : 1;

        return str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
