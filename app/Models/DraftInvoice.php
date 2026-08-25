<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| DraftInvoice
|--------------------------------------------------------------------------
| فاتورة "مسودة" - مش لها رقم فاتورة رسمي (invoice_number) ومش موجودة في
| جدول invoices خالص، لحد ما تتأكد (is_finalized = true) من InvoiceController@store
| وساعتها بس بتتحول لصف حقيقي في جدول invoices وبتتمسح من هنا.
*/
class DraftInvoice extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id',
        'created_by',
        'payment_method',
        'cash_amount',
        'bank_amount',
        'note',
        'purchase_order_number',
        'invoice_level_discount',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
        'cash_amount' => 'float',
        'bank_amount' => 'float',
        'invoice_level_discount' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * إجمالي المسودة - محسوب من مصفوفة items المخزنة (نفس منطق حساب
     * إجمالي الفاتورة الحقيقية: سعر الوحدة × الكمية - الخصم + الضريبة،
     * مطروح منه خصم الفاتورة الإضافي).
     */
    public function getTotalAttribute(): float
    {
        $subtotal = 0;
        $tax = 0;

        foreach ($this->items ?? [] as $item) {
            $lineSubtotal = ((float) ($item['unit_price'] ?? 0) * (float) ($item['quantity'] ?? 0))
                - (float) ($item['discount_amount'] ?? 0);
            $subtotal += $lineSubtotal;
            $tax += $lineSubtotal * (float) ($item['tax_rate'] ?? 0);
        }

        $total = $subtotal + $tax - (float) ($this->invoice_level_discount ?? 0);

        return max(0, $total);
    }

    public function getItemsCountAttribute(): int
    {
        return count($this->items ?? []);
    }
}
