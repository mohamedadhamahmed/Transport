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
}