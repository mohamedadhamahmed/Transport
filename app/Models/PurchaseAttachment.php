<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function url(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
    }
}
