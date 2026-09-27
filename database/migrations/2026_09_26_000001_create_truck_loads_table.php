<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * حركة الشاحنات: كل صف = حمولة على شاحنة (من منطقة إلى منطقة).
     *
     * active_truck_id: بيبقى = truck_id طول ما الحمولة "محمّلة"، وبيتصفّر
     * لما تتفرّغ أو تتلغي. عليه unique، فقاعدة البيانات نفسها بتمنع إن
     * يبقى فيه حمولتين شغالين على نفس الشاحنة حتى لو اتنين ضغطوا
     * "تحميل" في نفس اللحظة.
     */
    public function up(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->string('current_region', 50)->nullable()->after('status'); // مكان الشاحنة الفاضية
        });

        Schema::create('truck_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained('trucks')->restrictOnDelete();
            $table->unsignedBigInteger('active_truck_id')->nullable()->unique();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('from_region', 50);
            $table->string('from_city')->nullable();
            $table->string('to_region', 50);
            $table->string('to_city')->nullable();

            $table->string('load_type');                       // نوع التحميل
            $table->decimal('weight', 10, 2)->nullable();      // الوزن (طن)
            $table->string('waybill_number')->nullable();      // رقم البوليصة

            $table->dateTime('loaded_at');                     // معاد التحميل
            $table->dateTime('expected_unload_at');            // معاد التنزيل المتوقع
            $table->dateTime('unloaded_at')->nullable();       // معاد التفريغ الفعلي

            $table->enum('status', ['loaded', 'unloaded', 'cancelled'])->default('loaded');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('unloaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'expected_unload_at']);
            $table->index(['from_region', 'to_region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_loads');

        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn('current_region');
        });
    }
};
