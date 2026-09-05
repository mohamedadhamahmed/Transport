<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * "أنواع الحسابات" - الفروع الخمسة الرئيسية لشجرة الحسابات بمعناها
 * المحاسبي الحقيقي (الأصول/الخصوم/الإيرادات/المصروفات/حقوق الملكية).
 * كل حساب في financialaccount بياخد نوعه من هنا عبر account_category_id
 * (عمود مستقل تمامًا عن account_type القديم - راجع تعليق ميجريشن
 * 2026_09_01_000024/2026_09_01_000025 للتفاصيل والسبب).
 */
class AccountType extends Model
{
    protected $fillable = ['name', 'active', 'is_protected'];

    protected $casts = [
        'active' => 'boolean',
        'is_protected' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(FinancialAccount::class, 'account_category_id');
    }
}
