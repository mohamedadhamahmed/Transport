<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سطر واحد في فاتورة النقليات = نقلة واحدة (شاحنة + من/إلى + سعر
 * + تحويلة اختيارية).
 */
class TransportInvoiceItem extends Model
{
    protected $fillable = [
        'transport_invoice_id', 'truck_id', 'truck_snapshot',
        'trip_date', 'from_location', 'to_location', 'waybill_number',
        'trip_price', 'has_transfer', 'transfer_location', 'transfer_price',
        'line_total', 'note',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'has_transfer' => 'boolean',
        'trip_price' => 'decimal:2',
        'transfer_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(TransportInvoice::class, 'transport_invoice_id');
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
