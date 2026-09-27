<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportQuotation extends Model
{
    protected $fillable = [
        'quotation_number', 'customer_id', 'branch_id', 'created_by',
        'issue_date', 'valid_until',
        'trips_total', 'transfers_total', 'discount_amount', 'subtotal',
        'tax_rate', 'tax_type', 'tax_amount', 'total',
        'status', 'transport_invoice_id', 'terms', 'note',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'tax_rate' => 'float',
    ];

    public const STATUSES = ['draft', 'sent', 'accepted', 'rejected', 'converted'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(TransportQuotationItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(TransportInvoice::class, 'transport_invoice_id');
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->endOfDay()->isPast()
            && !in_array($this->status, ['converted', 'rejected'], true);
    }
}
