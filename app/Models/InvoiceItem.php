<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * صنف داخل فاتورة (سطر واحد من أسطر الفاتورة).
 * ده الجدول اللي كان اسمه "sales" في النظام القديم.
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'branch_id',
        'unit_price',
        'quantity',
        'discount_amount',
        'tax_amount',
        'tax_rate',
        'returned_quantity',
        'returned_discount_amount',
        'remaining_quantity',
        'product_name_snapshot',
        'is_finalized',
        'unit',
        'note',
        'created_by',
    ];

    protected $casts = [
        'is_finalized' => 'boolean',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function productData()
{
    return $this->belongsTo(Product::class, 'product_id'); // تأكد من مطابقة اسم مفتاح الربط
}

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
