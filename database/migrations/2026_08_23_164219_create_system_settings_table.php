<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->default('empty');
            $table->string('name_en')->default('empty');
            $table->string('SR')->default('empty');
            $table->string('Tax')->default('empty');
            $table->string('logo')->default('empty');
            $table->string('address_ar')->default('empty');
            $table->string('address_en')->default('empty');
            $table->double('serviceCost', 8, 2)->default(0);
            $table->double('deliveryCost', 8, 2)->default(0);
            $table->text('descriptionarbic')->nullable();
            $table->text('descriptionenglish')->nullable();
            $table->double('discount_on_invoice')->default(100);

            // الأعمدة الجديدة الخاصة بالخصم المسموح للموظف
            $table->double('max_employee_discount', 8, 2)->default(0);
            $table->double('requires_approval_above', 8, 2)->nullable();

            $table->text('bank_acount_iban')->nullable();
            $table->text('bank_acount_number')->nullable();
            $table->text('bankname')->nullable();
            $table->integer('branchs_id')->default(1);
            $table->text('previous_hash_invoice')->nullable();
            $table->integer('invoices_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};