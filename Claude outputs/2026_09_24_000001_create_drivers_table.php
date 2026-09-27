<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول السائقين - قسم النقليات.
     */
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('id_number')->nullable();          // رقم الهوية / الإقامة
            $table->string('nationality')->nullable();
            $table->string('license_number')->nullable();     // رقم رخصة القيادة
            $table->date('license_expiry')->nullable();       // تاريخ انتهاء الرخصة
            $table->date('id_expiry')->nullable();            // تاريخ انتهاء الهوية/الإقامة
            $table->decimal('salary', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
