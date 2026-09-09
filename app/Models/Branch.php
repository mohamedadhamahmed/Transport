<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'location',
        'name_en',
        'type',
        'parent_branch_id',
    ];

    public function parentBranch()
    {
        return $this->belongsTo(Branch::class, 'parent_branch_id');
    }

    public function subBranches()
    {
        return $this->hasMany(Branch::class, 'parent_branch_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * الفواتير اللي الفرع ده استلمها (فواتير تحويل بين الفروع)
     */
    public function receivedInvoices()
    {
        return $this->hasMany(Invoice::class, 'receiving_branch_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceReturns()
    {
        return $this->hasMany(InvoiceReturn::class);
    }
}
