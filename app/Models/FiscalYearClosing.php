<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalYearClosing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_from' => 'date',
        'closing_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_income' => 'decimal:2',
    ];

    public function openingBalances()
    {
        return $this->hasMany(FiscalYearOpeningBalance::class);
    }

    public function retainedAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'retained_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
