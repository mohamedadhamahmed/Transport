<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('credit_limit', 10, 2)->default(10000);
            $table->decimal('balance', 10, 2)->default(0);
            $table->unsignedInteger('grace_period_days')->default(30);
            $table->string('tax_number')->nullable();
            // ربط اختياري بحساب في نظام محاسبي خارجي (كان اسمه mantob_account_id)
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->string('postal_code')->nullable();
            $table->string('district')->nullable();
            $table->string('street_name')->nullable();
            $table->string('building_number')->nullable();
            $table->string('plot_identification')->nullable();
            $table->string('commercial_registration_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
