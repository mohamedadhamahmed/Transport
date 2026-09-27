<?php

namespace App\Models;

use App\Support\SaudiRegions;
use Illuminate\Database\Eloquent\Model;

class Waybill extends Model
{
    protected $fillable = [
        'waybill_number', 'issue_date', 'truck_id', 'driver_id', 'customer_id',
        'shipper_name', 'shipper_phone', 'consignee_name', 'consignee_phone',
        'from_region', 'from_city', 'from_address', 'to_region', 'to_city', 'to_address',
        'goods_description', 'packages_count', 'weight',
        'loaded_at', 'expected_unload_at', 'delivered_at',
        'freight_amount', 'freight_payer', 'status',
        'truck_load_id', 'transport_invoice_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'loaded_at' => 'datetime',
        'expected_unload_at' => 'datetime',
        'delivered_at' => 'datetime',
        'weight' => 'decimal:2',
        'freight_amount' => 'decimal:2',
    ];

    public function truck() { return $this->belongsTo(Truck::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function truckLoad() { return $this->belongsTo(TruckLoad::class, 'truck_load_id'); }
    public function invoice() { return $this->belongsTo(TransportInvoice::class, 'transport_invoice_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function getFromLabelAttribute(): string
    {
        return SaudiRegions::name($this->from_region) . ($this->from_city ? ' - ' . $this->from_city : '');
    }

    public function getToLabelAttribute(): string
    {
        return SaudiRegions::name($this->to_region) . ($this->to_city ? ' - ' . $this->to_city : '');
    }

    /**
     * إيميل وجوال الشركة اللي بيتطبعوا على البوليصة - من شاشة
     * الإعدادات (جدول settings: email_address / mobile).
     */
    public static function companyContact(): array
    {
        $s = null;
        try {
            $s = Setting::find(1) ?? Setting::query()->first();
        } catch (\Throwable $e) {
        }

        return [
            'email' => $s?->email_address,
            'phone' => $s?->mobile,
        ];
    }
}
