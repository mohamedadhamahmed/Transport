<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'name', 'mobile', 'trn', 'crn',
        'street_name', 'building_number', 'plot_identification',
        'region', 'city', 'postal_number',
        'egs_serial_number', 'business_category',
        'common_name', 'organization_unit_name', 'organization_name',
        'country_name', 'registered_address', 'otp', 'email_address',
        'invoice_type', 'is_production',
        'company_id', 'branchs_id',
    ];

    // الحقول دي (الشهادات والمفاتيح) بتتولد أوتوماتيك من نظام الفوترة الإلكترونية
    // ومش متعمولة للتعديل اليدوي من الشاشة، فمقصودة بره $fillable
}