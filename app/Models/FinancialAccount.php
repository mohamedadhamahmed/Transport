<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $table = 'financialaccount';
    
    // السماح لكل الحقول بالإدخال والتعديل دفعة واحدة
    protected $guarded = []; 
}