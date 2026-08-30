<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * جدول cost_centers - بنفس بنية الجدول اللي بعتّه لي (id, cost_center_ar,
 * cost_center_en, expensesAvt, created_at, updated_at). الجدول بيتم
 * إنشاؤه عن طريق ميجريشن 2026_08_27_000007_create_cost_centers_table
 * لو مش موجود عندك بالفعل (بتتخطى نفسها لو الجدول موجود).
 */
class CostCenter extends Model
{
    use HasFactory;

    protected $table = 'cost_centers';

    protected $fillable = [
        'cost_center_ar',
        'cost_center_en',
        'expensesAvt',
    ];

    /**
     * الاسم المناسب للغة الحالية - بيرجع العربي أو الإنجليزي حسب
     * app()->getLocale()، مع رجوع للعربي لو الإنجليزي فاضي أو '-'.
     */
    public function getDisplayNameAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->cost_center_en && $this->cost_center_en !== '-') {
            return $this->cost_center_en;
        }

        return $this->cost_center_ar;
    }
}
