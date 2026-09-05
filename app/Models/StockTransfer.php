<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سند تحويل مخزون بين فرعين (صرف من فرع + استلام في فرع تاني) - قسم
 * المستودعات. ميزة جديدة تمامًا، منفصلة عن DeliveryNote (تسليم منتج
 * لعميل) وعن Invoice/receiving_branch_id (فواتير ضريبية حقيقية بالـ
 * ZATCA) - الموديل ده مخصص بس لحركة مخزون داخلية بين فروع نفس الشركة.
 */
class StockTransfer extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_RECEIVED = 'received';

    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'sender_user_id',
        'receiver_user_id',
        'status',
        'notes',
        'transfer_date',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }
}
