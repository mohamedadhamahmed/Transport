<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckStoreIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'truck_store_issue_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function issue()
    {
        return $this->belongsTo(TruckStoreIssue::class, 'truck_store_issue_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
