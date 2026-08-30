<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'company_name',
        'phone',
        'email',
        'tax_no',
        'crn',
        'balance',
        'credit_limit',
        'address',
        'sub_city',
        'street_name',
        'building_number',
        'plot_identification',
        'postcode',
        'notes',
        'created_by',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
