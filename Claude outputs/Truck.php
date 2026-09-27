<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    protected $fillable = [
        'plate_number', 'name', 'type', 'brand', 'model_year', 'capacity',
        'default_trip_price', 'registration_expiry', 'insurance_expiry',
        'driver_id', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'default_trip_price' => 'decimal:2',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /** كل النقلات اللي اتعملت بالشاحنة دي في فواتير النقليات */
    public function trips()
    {
        return $this->hasMany(TransportInvoiceItem::class);
    }

    /** الاسم اللي بيظهر في القوائم والفواتير: "رقم اللوحة - الاسم" */
    public function getDisplayNameAttribute(): string
    {
        return trim($this->plate_number . ($this->name ? ' - ' . $this->name : ''));
    }
}
