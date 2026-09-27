<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name', 'phone', 'id_number', 'nationality',
        'license_number', 'license_expiry', 'id_expiry',
        'salary', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'id_expiry' => 'date',
        'salary' => 'decimal:2',
    ];

    /** الشاحنات اللي السائق ده مسجل عليها كسائق أساسي */
    public function trucks()
    {
        return $this->hasMany(Truck::class);
    }

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }

    /** الرخصة هتنتهي خلال 30 يوم */
    public function isLicenseExpiringSoon(): bool
    {
        return $this->license_expiry
            && ! $this->license_expiry->isPast()
            && $this->license_expiry->lte(now()->addDays(30));
    }
}
