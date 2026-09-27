<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name', 'driver_type', 'employee_id', 'phone', 'id_number', 'nationality',
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

    /** الموظف المربوط بيه في الموارد البشرية (لو سائق تبع الشركة) */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function isExternal(): bool
    {
        return $this->driver_type === 'external';
    }

    public function loads()
    {
        return $this->hasMany(TruckLoad::class);
    }

    /** الرحلة الحالية للسائق (لو في حمولة شغالة مسجلة باسمه) */
    public function activeLoad()
    {
        return $this->hasOne(TruckLoad::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('status', 'loaded'));
    }

    public function hasValidPhone(): bool
    {
        return \App\Support\SaudiPhone::isValid($this->phone);
    }

    public function whatsappUrl(string $text = ''): ?string
    {
        return \App\Support\SaudiPhone::whatsappUrl($this->phone, $text);
    }

    public function telUrl(): ?string
    {
        return \App\Support\SaudiPhone::telUrl($this->phone);
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
