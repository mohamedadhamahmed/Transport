<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturing_order_id',
        'product_id',
        'required_quantity',
        'consumed_quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'required_quantity' => 'float',
        'consumed_quantity' => 'float',
        'unit_cost' => 'float',
        'total_cost' => 'float',
    ];

    public function manufacturingOrder()
    {
        return $this->belongsTo(ManufacturingOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
