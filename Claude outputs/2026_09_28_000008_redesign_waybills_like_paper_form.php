<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * البوليصة بقت بنفس شكل بوليصة الشحن الورقي (زي مشروع ahl):
     * المكرم/السادة، المدينة المتجهة إليها البضاعة، بيانات السائق والسيارة
     * (رخصة القيادة/مالك السيارة/رخصة التشغيل/نوع السيارة/الحمولة)،
     * جدول بضاعة (الراسل: الاسم + الأجرة، المرسل إليه، نوع البضاعة، الوزن)،
     * تاريخ المغادرة، تدفع الأجرة من قبل، مدة التوصيل.
     *
     * الأعمدة القديمة (الشاحن/المستلم/المناطق/المواعيد) بقت اختيارية
     * وبتتملى تلقائي من البيانات الجديدة عشان لوحة الشاحنات والفواتير
     * والبحث يفضلوا شغالين.
     */
    public function up(): void
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->string('customer_display_name')->nullable()->after('customer_id');   // المكرم / السادة
            $table->string('destination_city')->nullable()->after('customer_display_name'); // المدينة المتجهة إليها البضاعة
            $table->string('driver_name')->nullable();
            $table->string('driver_license_number')->nullable();   // رقم رخصة القيادة
            $table->string('driver_license_date')->nullable();     // تاريخ صدورها
            $table->string('driver_license_issuer')->nullable();   // جهتها
            $table->string('vehicle_owner_name')->nullable();      // اسم مالك السيارة
            $table->string('vehicle_plate')->nullable();           // رقم السيارة
            $table->string('operating_license_number')->nullable(); // رقم رخصة التشغيل
            $table->string('operating_license_issuer')->nullable(); // جهة صدورها
            $table->string('vehicle_type')->nullable();            // نوع السيارة
            $table->string('total_load')->nullable();              // الحمولة الإجمالية
            $table->date('departure_date')->nullable();            // تاريخ المغادرة
            $table->string('responsible_name')->nullable();        // توقيع المسؤول (الاسم)
            $table->string('fare_paid_by')->nullable();            // تدفع الأجرة من قبل
            $table->string('delivery_within')->nullable();         // يجب إيصال البضاعة خلال
        });

        // الأعمدة القديمة بقت اختيارية
        Schema::table('waybills', function (Blueprint $table) {
            $table->string('shipper_name')->nullable()->change();
            $table->string('consignee_name')->nullable()->change();
            $table->string('from_region', 50)->nullable()->change();
            $table->string('to_region', 50)->nullable()->change();
            $table->string('goods_description')->nullable()->change();
            $table->dateTime('loaded_at')->nullable()->change();
            $table->dateTime('expected_unload_at')->nullable()->change();
        });

        // الشاحنة اختيارية (رقم السيارة ممكن يتكتب يدوي)
        Schema::table('waybills', function (Blueprint $table) {
            $table->dropForeign(['truck_id']);
        });
        Schema::table('waybills', function (Blueprint $table) {
            $table->unsignedBigInteger('truck_id')->nullable()->change();
            $table->foreign('truck_id')->references('id')->on('trucks')->nullOnDelete();
        });

        Schema::create('waybill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waybill_id')->constrained('waybills')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('sender_name')->nullable();     // الراسل - الاسم
            $table->decimal('fare', 12, 2)->default(0);    // الراسل - الأجرة
            $table->string('consignee_name')->nullable();  // اسم المرسل إليه
            $table->string('goods_type')->nullable();      // نوع البضاعة
            $table->string('goods_weight')->nullable();    // وزن البضاعة
            $table->timestamps();
        });

        // البوالص القديمة: سطر بضاعة واحد من بياناتها الحالية
        $now = now();
        DB::table('waybills')->orderBy('id')->chunkById(200, function ($rows) use ($now) {
            foreach ($rows as $w) {
                DB::table('waybill_items')->insert([
                    'waybill_id' => $w->id,
                    'sort' => 1,
                    'sender_name' => $w->shipper_name,
                    'fare' => $w->freight_amount ?? 0,
                    'consignee_name' => $w->consignee_name,
                    'goods_type' => $w->goods_description,
                    'goods_weight' => $w->weight !== null ? rtrim(rtrim((string) $w->weight, '0'), '.') : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('waybills')->where('id', $w->id)->update([
                    'customer_display_name' => $w->shipper_name,
                    'destination_city' => $w->to_city,
                    'departure_date' => $w->loaded_at ? substr((string) $w->loaded_at, 0, 10) : null,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waybill_items');
        Schema::table('waybills', function (Blueprint $table) {
            $table->dropColumn([
                'customer_display_name', 'destination_city', 'driver_name', 'driver_license_number',
                'driver_license_date', 'driver_license_issuer', 'vehicle_owner_name', 'vehicle_plate',
                'operating_license_number', 'operating_license_issuer', 'vehicle_type', 'total_load',
                'departure_date', 'responsible_name', 'fare_paid_by', 'delivery_within',
            ]);
        });
    }
};
