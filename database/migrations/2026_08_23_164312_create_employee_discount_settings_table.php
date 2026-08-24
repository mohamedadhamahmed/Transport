<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_discount_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('branchs_id')
                  ->constrained('branches')   // <-- كان branchs، صح branches
                  ->cascadeOnDelete();

            $table->double('max_discount', 8, 2);

            $table->timestamps();

            $table->unique(['user_id', 'branchs_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_discount_settings');
    }
};