<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;
protected $table = 'productgroup';
  
    // الحقول المسموح بالتعبئة الجماعية (Mass Assignment)
    protected $fillable = [
        'group_ar',
        'group_en',
    ];

    /**
     * العلاقة العكسية مع نموذج Product (المجموعة تحتوي على عدة منتجات)
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'product_group_id');
    }
}