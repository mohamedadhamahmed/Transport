<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
public function productGroup()
{
    // تحديد اسم الموديل واسم الجدول الوسيط والمفتاح الأجنبي بدقة
    return $this->belongsTo(\App\Models\ProductGroup::class, 'product_group_id');
}

    /**
     * "البدائل" - منتجات تانية ممكن تتباع بدل المنتج ده (زرار "البدائل" في
     * مودال اختيار منتج). العلاقة many-to-many لإن نفس المنتج البديل ممكن
     * يبقى بديل لأكتر من منتج أساسي واحد في نفس الوقت - علشان كده جدول
     * وسيط (product_alternates) مش عمود FK بسيط على المنتج.
     * هنا: $this هو "الأساسي" (primary_product_id) والنتيجة هي البدائل
     * بتاعته (alternate_product_id).
     */
    public function alternates(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_alternates',
            'primary_product_id',
            'alternate_product_id'
        )->withTimestamps();
    }

    /**
     * العكس: المنتجات الأساسية اللي المنتج ده بديل ليها (لو اتحدد كده من
     * شاشة إنشاء/تعديل المنتج). منتج من غير أي صف هنا يبقى "أساسي" بشكل
     * افتراضي - مفيش عمود "type" منفصل، الحالة مستنتجة من وجود/عدم وجود
     * صفوف في الجدول الوسيط.
     */
    public function primaryProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_alternates',
            'alternate_product_id',
            'primary_product_id'
        )->withTimestamps();
    }
}
