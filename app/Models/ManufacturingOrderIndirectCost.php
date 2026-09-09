<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingOrderIndirectCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturing_order_id',
        'name',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function manufacturingOrder()
    {
        return $this->belongsTo(ManufacturingOrder::class);
    }
}
