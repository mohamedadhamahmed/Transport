<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManufacturingOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'product_id',
        'bill_of_material_id',
        'production_plan_id',
        'workstation_id',
        'customer_id',
        'quantity',
        'date_start',
        'date_end',
        'direct_materials_cost',
        'indirect_costs_total',
        'total_cost',
        'status_id',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'date_start' => 'date',
        'date_end' => 'date',
        'direct_materials_cost' => 'float',
        'indirect_costs_total' => 'float',
        'total_cost' => 'float',
        'completed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function productionPlan()
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    public function status()
    {
        return $this->belongsTo(ManufacturingOrderStatus::class, 'status_id');
    }

    public function items()
    {
        return $this->hasMany(ManufacturingOrderItem::class);
    }

    public function indirectCosts()
    {
        return $this->hasMany(ManufacturingOrderIndirectCost::class);
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
     * بتبني بنود المواد الخام تلقائيًا من قائمة مواد الإنتاج (BOM) على
     * أساس نسبة كمية الأمر لكمية إنتاج القائمة الأصلية. مثال: BOM بتنتج
     * 10 وحدات وبتستهلك 2 كجم سكر، وأمر التصنيع مطلوب فيه 25 وحدة،
     * فهيتسحب 5 كجم سكر (2 × 25 / 10).
     */
    public function buildItemsFromBom(): void
    {
        if (! $this->billOfMaterial) {
            return;
        }

        $ratio = $this->quantity / max((float) $this->billOfMaterial->production_quantity, 0.0001);
        $direct = 0;

        $this->items()->delete();

        foreach ($this->billOfMaterial->items as $bomItem) {
            $requiredQty = round($bomItem->quantity * $ratio, 3);
            $lineTotal = round($requiredQty * $bomItem->unit_cost, 2);
            $direct += $lineTotal;

            $this->items()->create([
                'product_id' => $bomItem->product_id,
                'required_quantity' => $requiredQty,
                'unit_cost' => $bomItem->unit_cost,
                'total_cost' => $lineTotal,
            ]);
        }

        $this->recalculateTotals($direct);
    }

    public function recalculateTotals(?float $direct = null): void
    {
        $direct = $direct ?? $this->items()->sum('total_cost');
        $indirect = $this->indirectCosts()->sum('amount');

        $this->update([
            'direct_materials_cost' => $direct,
            'indirect_costs_total' => $indirect,
            'total_cost' => $direct + $indirect,
        ]);
    }

    /**
     * بتنفذ اكتمال أمر التصنيع فعليًا: بتتحقق إن كل المواد الخام متوفرة
     * بالكمية المطلوبة، بعدين بتخصم الكميات المستهلكة من رصيد كل مادة
     * خام (stock_quantity) وتضيف الكمية المنتجة لرصيد المنتج التام،
     * كل ده جوه معاملة واحدة (transaction) عشان نضمن إن العملية تتم
     * بالكامل أو تترجع بالكامل لو حصل خطأ.
     */
    public function complete(): void
    {
        if ($this->completed_at) {
            throw ValidationException::withMessages([
                'status' => 'أمر التصنيع ده مكتمل بالفعل.',
            ]);
        }

        DB::transaction(function () {
            foreach ($this->items as $item) {
                $rawMaterial = $item->product()->lockForUpdate()->first();
                $consumed = $item->consumed_quantity > 0 ? $item->consumed_quantity : $item->required_quantity;

                if ($rawMaterial->stock_quantity < $consumed) {
                    throw ValidationException::withMessages([
                        'items' => "الكمية المتاحة من \"{$rawMaterial->name}\" غير كافية لإتمام أمر التصنيع.",
                    ]);
                }

                $rawMaterial->decrement('stock_quantity', $consumed);
                $item->update(['consumed_quantity' => $consumed]);
            }

            $this->product()->lockForUpdate()->first()->increment('stock_quantity', $this->quantity);

            $this->update(['completed_at' => now()]);
        });
    }
}
