<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_note', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('user_id');
            $table->double('Price')->default(0);
            $table->double('Added_Value')->default(0);
            $table->double('Number_of_Quantity')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('Pay')->default('Shabka');
            $table->unsignedBigInteger('branchs_id')->default(1);
            $table->decimal('discount', 8, 2)->default(0.00);
            $table->double('discountOnInvoice')->default(0);
            $table->double('discountOnProduct')->default(0);
            $table->string('note')->default('-');
            $table->integer('status')->default(0);
            $table->integer('save')->default(0);
            $table->double('cashamount', 15, 2)->default(0.00);
            $table->double('bankamount', 15, 2)->default(0.00);
            $table->double('creaditamount', 15, 2)->default(0.00);
            $table->integer('morepayment_way')->default(0);
            $table->double('Bank_transfer')->default(0);
            $table->dateTime('signing_time')->nullable();
            $table->longText('xml')->nullable();
            $table->enum('document_type', ['simplified', 'standard'])->nullable();
            $table->enum('invoice_type', ['388', '383', '381'])->nullable();
            $table->integer('sent_to_zatca')->default(0);
            $table->text('sent_to_zatca_status')->nullable();
            $table->text('invoiceUUid')->nullable();
            $table->text('invoice_number')->nullable();
            $table->bigInteger('invoice_counter')->nullable();
            $table->time('issue_time')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('hash', 256)->nullable();
            $table->text('qr_zatca')->nullable();
            $table->text('xmltags')->nullable();
            $table->double('currentblance')->default(0);
            $table->bigInteger('NOTICE_Number')->default(0);
            $table->text('xmltags_return')->nullable();
            $table->text('xml_return')->nullable();
            $table->text('hash_return')->nullable();
            $table->text('sent_to_zatca_status_return')->nullable();
            $table->text('qr_zatca_return')->nullable();
            $table->date('issue_date_return')->nullable();
            $table->time('issue_time_return')->nullable();
            $table->integer('display_number')->default(1);
            $table->text('payment_return')->nullable();
            $table->longText('clearedInvoice')->nullable();
            $table->longText('uuid')->nullable();

            $table->index('user_id', 'invoices_user_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note');
    }
};
