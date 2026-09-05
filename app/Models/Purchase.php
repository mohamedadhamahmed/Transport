<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'created_by',
        'payment_account_id',
        'supplier_invoice_number',
        'purchase_number',
        'warehouse_name',
        'cost_center',
        'cost_center_id',
        'shipping_fee',
        'subtotal',
        'discount_amount',
        'invoice_level_discount',
        'tax_amount',
        'grand_total',
        'total_quantity',
        'note',
        'issue_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'payment_account_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * كل عمليات مرتجع المشتريات (كليًا أو جزئيًا) اللي اتعملت على
     * الفاتورة دي.
     */
    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function isCredit(): bool
    {
        return is_null($this->payment_account_id);
    }

    /**
     * الفاتورة قابلة للتعديل بس لو:
     * 1) كل بند فيها متسجل بنظام الـ "snapshot" (stock_before /
     *    purchase_price_before / average_cost_before) - أي فاتورة قديمة
     *    اتسجلت قبل إضافة الأعمدة دي معندهاش بيانات كافية نرجّع بيها
     *    تكلفة المنتج بأمان، فمش هتبقى قابلة للتعديل.
     * 2) مفيش أي بند فيها اترجع منه أي كمية (مرتجع مشتريات) - عشان
     *    منطق التعديل والإرجاع مش متزامنين مع بعض.
     * 3) الفاتورة دي هي آخر فاتورة مشتريات أثرت على تكلفة كل منتج من
     *    منتجاتها - لو فيه فاتورة تانية (بتاريخ/ID أحدث) اتسجلت بعدها
     *    لنفس المنتج، يبقى متوسط التكلفة وسعر الشراء اتغيروا بعد كده،
     *    ومينفعش نرجعهم لقيمة الـ snapshot القديمة من غير ما نكسر
     *    الفاتورة التانية دي.
     */
    public function isEditable(): bool
    {
        $items = $this->items()->get();

        if ($items->isEmpty()) {
            return false;
        }

        foreach ($items as $item) {
            if (is_null($item->stock_before) || is_null($item->purchase_price_before) || is_null($item->average_cost_before)) {
                return false;
            }

            if ((float) $item->returned_quantity > 0) {
                return false;
            }

            $laterExists = PurchaseItem::where('product_id', $item->product_id)
                ->where('id', '!=', $item->id)
                ->where(function ($q) use ($item) {
                    $q->where('created_at', '>', $item->created_at)
                        ->orWhere(function ($q2) use ($item) {
                            $q2->where('created_at', $item->created_at)
                                ->where('id', '>', $item->id);
                        });
                })
                ->exists();

            if ($laterExists) {
                return false;
            }
        }

        return true;
    }
}
