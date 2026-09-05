<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * نسبة الضريبة الافتراضية اللي المفروض تبقى مختارة تلقائيًا في شاشات
     * إنشاء فاتورة المبيعات/المشتريات وعروض الأسعار (كنسبة عشرية زي 0.15
     * مش 15) - هي أعلى ضريبة مفعّلة أولوية (priority الأصغر = الأولوية
     * الأعلى، زي ترتيبها في TaxController::index() ونفس ترتيب قايمة
     * الاختيار @foreach(Tax::orderBy('priority','asc')...) في شاشات
     * الإنشاء). قبل الإصلاح ده كانت كل الشاشات بتفتكس نسبة 15% كقيمة
     * ابتدائية في متغير Alpine.js (defaultTaxRate: 0.15) من غير أي علاقة
     * بجدول الضرائب فعليًا - فتغيير الأولوية من شاشة الضرائب مكانش بيأثر
     * على القيمة الافتراضية اللي بتظهر هنا خالص.
     */
    public static function defaultRateFraction(): float
    {
        $rate = static::where('is_active', true)->orderBy('priority', 'asc')->value('rate');

        return $rate !== null ? round(((float) $rate) / 100, 4) : 0.15;
    }
}