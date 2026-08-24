<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'branch_id',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'status',
        'created_by',
        'location',
        'code',
        'tax_value',
        'total_sold',
        'name_en',
        'notes',
        'unit',
        'low_stock_alert_quantity',
        'parent_product_id',
        'reference_number',
        'opening_balance',
        'average_cost',
        'wholesale_price',
        'photo',
        'product_mix_id',
        'product_group_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceReturns()
    {
        return $this->hasMany(InvoiceReturn::class);
    }
}
