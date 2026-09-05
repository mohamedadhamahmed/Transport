<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * عمود تمييزي لصفوف "الإجازة الأسبوعية/الرسمية" اللي اتخصم لها يوم كامل
 * بسبب اتصالها بغياب غير مصرح به (راجع AttendanceCalculator::syncConnectedOffDayPenalty).
 * لازم نميزها عن أي discount_amount تاني عشان نقدر نشيلها لوحدها لو
 * الغياب اتغطى بإجازة معتمدة بعد كده من غير ما نأثر على خصومات تانية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_connected_penalty')->default(false)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('is_connected_penalty');
        });
    }
};
