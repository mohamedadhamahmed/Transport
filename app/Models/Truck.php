<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    protected $fillable = [
        'plate_number', 'name', 'type', 'ownership', 'purchase_value', 'purchase_date', 'owner_name', 'owner_phone', 'financial_account_id', 'brand', 'model_year', 'capacity',
        'default_trip_price', 'registration_expiry', 'insurance_expiry',
        'driver_id', 'status', 'current_region', 'notes', 'created_by',
        'color', 'chassis_number', 'serial_number', 'registration_number',
        'insurance_company', 'insurance_policy_number', 'operating_card_number',
        'operating_card_expiry', 'inspection_expiry',
        'current_odometer', 'last_oil_change_odometer', 'next_oil_change_odometer', 'last_oil_change_date',
    ];

    /** التنبيه قبل انتهاء أي وثيقة بكام يوم */
    public const EXPIRY_ALERT_DAYS = 30;

    /** وثائق الشاحنة اللي ليها تاريخ انتهاء (عمود التاريخ => مفتاح الترجمة) */
    public const DOCUMENTS = [
        'registration_expiry' => 'doc_registration',
        'insurance_expiry' => 'doc_insurance',
        'operating_card_expiry' => 'doc_operating_card',
        'inspection_expiry' => 'doc_inspection',
    ];

    /**
     * وثائق مش إلزامية للشاحنة الخارجية (مش ملك الشركة): الاستمارة والتأمين
     * وكرت التشغيل - ممكن تتسجل عادي، بس مش بيطلع عليها أي تنبيه.
     */
    public const OPTIONAL_FOR_EXTERNAL = ['registration_expiry', 'insurance_expiry', 'operating_card_expiry'];

    /** الوثيقة دي إلزامية للشاحنة دي؟ */
    public function isDocumentRequired(string $column): bool
    {
        return $this->isOwned() || !in_array($column, self::OPTIONAL_FOR_EXTERNAL, true);
    }

    protected $casts = [
        'capacity' => 'decimal:2',
        'default_trip_price' => 'decimal:2',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
        'purchase_date' => 'date',
        'purchase_value' => 'decimal:2',
        'operating_card_expiry' => 'date',
        'inspection_expiry' => 'date',
        'current_odometer' => 'integer',
        'last_oil_change_odometer' => 'integer',
        'next_oil_change_odometer' => 'integer',
        'last_oil_change_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /** كل النقلات اللي اتعملت بالشاحنة دي في فواتير النقليات */
    public function trips()
    {
        return $this->hasMany(TransportInvoiceItem::class);
    }

    /** كل الحمولات (حركة الشاحنة) */
    public function loads()
    {
        return $this->hasMany(TruckLoad::class);
    }

    /** الحمولة الشغالة حاليًا (لو الشاحنة محمّلة) */
    public function activeLoad()
    {
        return $this->hasOne(TruckLoad::class, 'active_truck_id');
    }

    /** سندات الصيانة / المصروفات على الشاحنة (سندات صرف مربوطة بيها) */
    public function expenseVouchers()
    {
        return $this->hasMany(AccountVoucher::class);
    }

    public function isOwned(): bool
    {
        return ($this->ownership ?? 'owned') === 'owned';
    }

    /**
     * حالة كل وثيقة: expired (منتهية) / soon (هتنتهي خلال EXPIRY_ALERT_DAYS) / ok / none
     * بترجع [عمود => ['label', 'date', 'status', 'days']]
     */
    public function documentsStatus(): array
    {
        $today = now()->startOfDay();
        $out = [];
        foreach (self::DOCUMENTS as $col => $key) {
            $d = $this->{$col};
            $days = $d ? (int) $today->diffInDays($d->copy()->startOfDay(), false) : null;
            $out[$col] = [
                'label' => __('transport.' . $key),
                'date' => $d,
                'days' => $days,
                // وثيقة اختيارية (شاحنة خارجية): بتتعرض بس من غير تنبيه
                'status' => $d === null ? 'none'
                    : (!$this->isDocumentRequired($col) ? 'optional'
                    : ($days < 0 ? 'expired' : ($days <= self::EXPIRY_ALERT_DAYS ? 'soon' : 'ok'))),
            ];
        }

        return $out;
    }

    /** شاحنات عندها وثيقة منتهية أو هتنتهي قريب (لعمود واحد أو كل الوثائق) */
    public function scopeDocumentsAlert($query, ?string $column = null, string $mode = 'any')
    {
        $limit = now()->addDays(self::EXPIRY_ALERT_DAYS)->toDateString();
        $today = now()->toDateString();
        $cols = $column && isset(self::DOCUMENTS[$column]) ? [$column] : array_keys(self::DOCUMENTS);

        return $query->where(function ($q) use ($cols, $limit, $today, $mode) {
            foreach ($cols as $c) {
                $q->orWhere(function ($qq) use ($c, $limit, $today, $mode) {
                    $qq->whereNotNull($c);
                    // الاستمارة/التأمين/كرت التشغيل مش إلزامية للشاحنات الخارجية
                    if (in_array($c, self::OPTIONAL_FOR_EXTERNAL, true)) {
                        $qq->where(fn ($o) => $o->where('ownership', '!=', 'external')->orWhereNull('ownership'));
                    }
                    if ($mode === 'expired') {
                        $qq->whereDate($c, '<', $today);
                    } else {
                        $qq->whereDate($c, '<=', $limit);
                    }
                });
            }
        });
    }

    /** الاسم اللي بيظهر في القوائم والفواتير: "رقم اللوحة - الاسم" */
    public function getDisplayNameAttribute(): string
    {
        return trim($this->plate_number . ($this->name ? ' - ' . $this->name : ''));
    }
}
