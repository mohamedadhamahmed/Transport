<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $table = 'credittransaction';
    
    // السماح لكل الحقول بالإدخال والتعديل دفعة واحدة
    protected $guarded = []; 
}