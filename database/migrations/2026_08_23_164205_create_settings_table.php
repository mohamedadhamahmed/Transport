<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile');
            $table->unsignedBigInteger('trn')->comment('Tax Registration Number');
            $table->unsignedBigInteger('crn')->comment('Commercial Registration Number');
            $table->string('street_name');
            $table->integer('building_number');
            $table->integer('plot_identification');
            $table->string('region');
            $table->string('city');
            $table->integer('postal_number');
            $table->string('egs_serial_number');
            $table->enum('business_category', ['IT', 'Food', 'Film Festivals']);
            $table->string('common_name');
            $table->string('organization_unit_name');
            $table->string('organization_name');
            $table->string('country_name')->default('SA')->comment('Country code');
            $table->string('registered_address');
            $table->string('otp');
            $table->string('email_address');
            $table->enum('invoice_type', ['1100', '0100', '1000']);
            $table->boolean('is_production')->default(false);
            $table->longText('cnf')->nullable();
            $table->longText('private_key')->nullable();
            $table->longText('public_key')->nullable();
            $table->longText('csr_request')->nullable();
            $table->longText('certificate')->nullable();
            $table->string('secret')->nullable();
            $table->string('csid')->nullable();
            $table->longText('production_certificate')->nullable();
            $table->string('production_secret')->nullable();
            $table->string('production_csid')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->integer('invoices_count')->default(1);
            $table->text('previous_hash_invoice')->nullable();
            $table->integer('branchs_id')->default(0);
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};