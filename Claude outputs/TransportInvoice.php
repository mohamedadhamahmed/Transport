<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportInvoice extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'branch_id', 'created_by',
        'issue_date', 'issue_time',
        'trips_count', 'trips_total', 'transfers_total', 'discount_amount',
        'subtotal', 'tax_rate', 'tax_amount', 'total',
        'payment_method', 'cash_amount', 'bank_amount', 'credit_amount',
        'po_number', 'note',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'tax_rate' => 'float',
        'trips_total' => 'decimal:2',
        'transfers_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'bank_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

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
        return $this->hasMany(TransportInvoiceItem::class);
    }
}
