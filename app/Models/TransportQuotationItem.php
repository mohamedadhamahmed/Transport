<?php

namespace App\Models;

use App\Support\SaudiRegions;
use Illuminate\Database\Eloquent\Model;

class TransportQuotationItem extends Model
{
    protected $fillable = [
        'transport_quotation_id', 'truck_type',
        'from_region', 'from_city', 'to_region', 'to_city', 'load_type',
        'trips_count', 'trip_price', 'has_transfer', 'transfer_location', 'transfer_price',
        'line_total', 'note',
    ];

    protected $casts = [
        'has_transfer' => 'boolean',
    ];

    public function quotation()
    {
        return $this->belongsTo(TransportQuotation::class, 'transport_quotation_id');
    }

    public function getFromLabelAttribute(): string
    {
        return trim(SaudiRegions::name($this->from_region) . ($this->from_city ? ' - ' . $this->from_city : ''));
    }

    public function getToLabelAttribute(): string
    {
        return trim(SaudiRegions::name($this->to_region) . ($this->to_city ? ' - ' . $this->to_city : ''));
    }
}
