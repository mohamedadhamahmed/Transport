<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company_name',
        'address',
        'notes',
        'credit_limit',
        'balance',
        'grace_period_days',
        'tax_number',
        'accounting_account_id',
        'opening_balance',
        'postal_code',
        'district',
        'street_name',
        'building_number',
        'plot_identification',
        'commercial_registration_number',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
