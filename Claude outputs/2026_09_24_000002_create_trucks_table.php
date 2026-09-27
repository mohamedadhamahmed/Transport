<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الشاحنات - قسم النقليات. كل شاحنة ممكن يكون ليها سائق
     * أساسي (driver_id) بيتحدد من شاشة الشاحنات.
     */
    public function up(): void
    {
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();         // رقم اللوحة
            $table->string('name')->nullable();               // اسم/كود داخلي للشاحنة
            $table->string('type')->nullable();               // النوع: تريلا، دينا، سطحة...
            $table->string('brand')->nullable();              // الماركة
            $table->string('model_year', 10)->nullable();     // سنة الصنع
            $table->decimal('capacity', 10, 2)->nullable();   // الحمولة (طن)
            $table->decimal('default_trip_price', 12, 2)->default(0); // سعر النقلة الافتراضي
            $table->date('registration_expiry')->nullable();  // انتهاء الاستمارة
            $table->date('insurance_expiry')->nullable();     // انتهاء التأمين
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
