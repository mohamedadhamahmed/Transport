<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'product_id',
        'production_quantity',
        'total_cost',
        'status',
        'is_default',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'production_quantity' => 'float',
        'total_cost' => 'float',
        'is_default' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(BomItem::class);
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

    /**
     * بتستبدل بنود القائمة بالكامل (زي syncUnits في موديل Product) وبتعيد
     * حساب التكلفة الإجمالية من مجموع تكلفة كل مادة خام. مقبول هنا لإن
     * دي بيانات إعداد نادرة التغيير (وصفة إنتاج) مش سجل معاملات.
     */
    public function syncItems(array $items): void
    {
        $this->items()->delete();
        $total = 0;

        foreach ($items as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                continue;
            }

            $unitCost = (float) ($item['unit_cost'] ?? 0);
            $lineTotal = $unitCost * (float) $item['quantity'];
            $total += $lineTotal;

            $this->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $unitCost,
                'total_cost' => $lineTotal,
            ]);
        }

        $this->update(['total_cost' => $total]);
    }
}
