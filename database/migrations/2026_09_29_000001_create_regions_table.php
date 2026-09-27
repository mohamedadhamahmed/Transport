<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * المناطق المضافة (غير مناطق المملكة الأساسية الـ 13 اللي في
     * config/saudi_regions.php). المفتاح (key) هو اللي بيتخزن في
     * truck_loads.from_region / to_region و trucks.current_region...
     * فالمنطقة المضافة بتظهر في كل الاختيارات على طول.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->nullable()->unique();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
