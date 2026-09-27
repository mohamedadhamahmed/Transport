<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * بيانات الشاحنة الأساسية + وثائقها: الاستمارة، التأمين، كرت التشغيل،
     * الفحص الدوري. تواريخ الانتهاء بيطلع عليها تنبيه في جرس الإشعارات.
     */
    public function up(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->string('color')->nullable()->after('model_year');
            $table->string('chassis_number')->nullable()->after('color');          // رقم الهيكل
            $table->string('serial_number')->nullable()->after('chassis_number');  // الرقم التسلسلي
            $table->string('registration_number')->nullable()->after('serial_number'); // رقم الاستمارة
            $table->string('insurance_company')->nullable()->after('insurance_expiry');
            $table->string('insurance_policy_number')->nullable()->after('insurance_company');
            $table->string('operating_card_number')->nullable()->after('insurance_policy_number'); // كرت التشغيل
            $table->date('operating_card_expiry')->nullable()->after('operating_card_number');
            $table->date('inspection_expiry')->nullable()->after('operating_card_expiry');          // الفحص الدوري
        });
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn([
                'color', 'chassis_number', 'serial_number', 'registration_number',
                'insurance_company', 'insurance_policy_number', 'operating_card_number',
                'operating_card_expiry', 'inspection_expiry',
            ]);
        });
    }
};
