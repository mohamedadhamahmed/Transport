<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    protected $fillable = [
        'employee_id', 'contract_type', 'start_date', 'end_date',
        'residency_expiry', 'work_permit_expiry', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'residency_expiry'   => 'date',
        'work_permit_expiry' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // العقود اللي أي تاريخ فيها (نهاية عقد / إقامة / رخصة) هيخلص خلال $days يوم
    public function scopeExpiringWithin($query, int $days)
    {
        $today = now()->startOfDay();
        $limit = now()->addDays($days)->endOfDay();

        return $query->where(function ($q) use ($today, $limit) {
            $q->whereBetween('end_date', [$today, $limit])
              ->orWhereBetween('residency_expiry', [$today, $limit])
              ->orWhereBetween('work_permit_expiry', [$today, $limit]);
        });
    }
}