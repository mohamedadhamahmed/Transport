<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * حالة قابلة للتخصيص لأوامر التصنيع/خطط الإنتاج (زي "حالات الأوامر"
 * في Daftra) - بدل ما تكون enum ثابتة، المستخدم يقدر يضيف/يعدل حالاته.
 */
class ManufacturingOrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'color',
        'sort_order',
        'is_default',
        'type',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function manufacturingOrders()
    {
        return $this->hasMany(ManufacturingOrder::class, 'status_id');
    }

    public function productionPlans()
    {
        return $this->hasMany(ProductionPlan::class, 'status_id');
    }
}
