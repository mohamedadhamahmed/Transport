<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id', 'employee_contracts_employee_id_foreign')
                  ->references('id')->on('employees')
                  ->cascadeOnDelete();

            $table->string('contract_type');              // سنوي / موسمي / مؤقت ...
            $table->date('start_date');
            $table->date('end_date');
            $table->date('residency_expiry')->nullable();     // انتهاء الإقامة
            $table->date('work_permit_expiry')->nullable();   // انتهاء رخصة العمل
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};

