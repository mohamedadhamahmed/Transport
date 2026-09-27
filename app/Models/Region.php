<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** منطقة مضافة من شاشة المناطق (غير مناطق المملكة الأساسية) */
class Region extends Model
{
    protected $fillable = ['key', 'name', 'name_en', 'created_by'];
}
