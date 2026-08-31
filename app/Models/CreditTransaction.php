<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $table = 'credittransaction';

    // السماح لكل الحقول بالإدخال والتعديل دفعة واحدة
    protected $guarded = [];

    protected $casts = [
        'operation_type' => 'integer',
    ];

    /**
     * الحساب المالي اللي الحركة دي مسجلة عليه (customer_id بيشاور على
     * financialaccount.id - نفس التسمية القديمة من النظام الأصلي).
     */
    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
