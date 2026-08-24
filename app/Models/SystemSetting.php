<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'name_ar', 'name_en', 'SR', 'Tax', 'logo',
        'address_ar', 'address_en',
        'serviceCost', 'deliveryCost',
        'descriptionarbic', 'descriptionenglish',
        'discount_on_invoice', 'max_employee_discount', 'requires_approval_above',
        'bank_acount_iban', 'bank_acount_number', 'bankname',
        'branchs_id',
    ];
}