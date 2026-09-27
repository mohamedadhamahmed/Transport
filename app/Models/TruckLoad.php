<?php

namespace App\Models;

use App\Support\SaudiRegions;
use Illuminate\Database\Eloquent\Model;

class TruckLoad extends Model
{
    protected $fillable = [
        'truck_id', 'active_truck_id', 'driver_id', 'customer_id',
        'from_region', 'from_city', 'to_region', 'to_city',
        'load_type', 'weight', 'price', 'waybill_number', 'transport_invoice_id',
        'loaded_at', 'expected_unload_at', 'unloaded_at', 'unload_recorded_at',
        'status', 'notes', 'created_by', 'unloaded_by',
    ];

    protected $casts = [
        'loaded_at' => 'datetime',
        'expected_unload_at' => 'datetime',
        'unloaded_at' => 'datetime',
        'unload_recorded_at' => 'datetime',
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** الفاتورة اللي الحمل اتفوتر فيها */
    public function invoice()
    {
        return $this->belongsTo(TransportInvoice::class, 'transport_invoice_id');
    }

    /** بوليصة الشحن المربوطة بالحمل (لو فيه) */
    public function waybill()
    {
        return $this->hasOne(Waybill::class, 'truck_load_id');
    }

    /**
     * الأحمال اللي لسه متفوترتش: مش ملغية، ومش مربوطة بفاتورة، ومفيش
     * بوليصة مربوطة بيها اتفوترت (من "إنشاء فاتورة" في البوليصة).
     * $exceptInvoiceId: وقت تعديل فاتورة، أحمالها نفسها تفضل ظاهرة.
     */
    public function scopeUnbilled($q, ?int $exceptInvoiceId = null)
    {
        return $q->where('truck_loads.status', '!=', 'cancelled')
            ->where(function ($w) use ($exceptInvoiceId) {
                $w->whereNull('truck_loads.transport_invoice_id');
                if ($exceptInvoiceId) {
                    $w->orWhere('truck_loads.transport_invoice_id', $exceptInvoiceId);
                }
            })
            ->whereNotExists(function ($sub) use ($exceptInvoiceId) {
                $sub->selectRaw('1')->from('waybills')
                    ->whereColumn('waybills.truck_load_id', 'truck_loads.id')
                    ->whereNotNull('waybills.transport_invoice_id');
                if ($exceptInvoiceId) {
                    $sub->where('waybills.transport_invoice_id', '!=', $exceptInvoiceId);
                }
            });
    }

    /** سعر الحمل للفوترة: السعر المسجل على الحمل، وإلا أجرة البوليصة، وإلا null (من غير سعر) */
    public function getBillingPriceAttribute(): ?float
    {
        $price = (float) $this->price;
        if ($price <= 0 && $this->relationLoaded('waybill') && $this->waybill) {
            $price = (float) $this->waybill->freight_amount;
        }

        return $price > 0 ? $price : null;
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'loaded');
    }

    /** محمّلة وعدّى معاد التنزيل المتوقع */
    public function isOverdue(): bool
    {
        return $this->status === 'loaded' && $this->expected_unload_at && $this->expected_unload_at->isPast();
    }

    /** اتفرّغت بعد المعاد المتوقع */
    public function wasLate(): bool
    {
        return $this->status === 'unloaded' && $this->unloaded_at && $this->expected_unload_at
            && $this->unloaded_at->gt($this->expected_unload_at);
    }

    public function getFromLabelAttribute(): string
    {
        return SaudiRegions::name($this->from_region) . ($this->from_city ? ' - ' . $this->from_city : '');
    }

    public function getToLabelAttribute(): string
    {
        return SaudiRegions::name($this->to_region) . ($this->to_city ? ' - ' . $this->to_city : '');
    }

    /** "فاضل 5 س 20 د" / "متأخرة 3 س" - نص قصير للعدّاد */
    public function remainingText(): string
    {
        if (!$this->expected_unload_at) {
            return '';
        }

        $mins = (int) now()->diffInMinutes($this->expected_unload_at, false);
        $abs = abs($mins);
        $d = intdiv($abs, 1440);
        $h = intdiv($abs % 1440, 60);
        $m = $abs % 60;
        $parts = array_filter([$d ? $d . ' ي' : null, $h ? $h . ' س' : null, (!$d && $m) ? $m . ' د' : null]);
        $txt = $parts ? implode(' ', $parts) : '0 د';

        return $mins >= 0 ? 'فاضل ' . $txt : 'متأخرة ' . $txt;
    }
}
