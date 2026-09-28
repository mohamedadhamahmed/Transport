<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalYearOpeningBalance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function closing()
    {
        return $this->belongsTo(FiscalYearClosing::class, 'fiscal_year_closing_id');
    }
}
