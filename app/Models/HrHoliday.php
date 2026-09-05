<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrHoliday extends Model
{
    protected $table = 'hr_holidays';

    protected $fillable = [
        'date',
        'name',
        'branchs_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * الإجازة لو مرتبطة بفرع معيّن (branchs_id فاضي = إجازة لكل الفروع).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branchs_id');
    }
}
