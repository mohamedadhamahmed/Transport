<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->nullable()
                ->constrained('customers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();
            // الفرع المستلم - يُستخدم في فواتير التحويل بين الفروع
            $table->foreignId('receiving_branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();

            // المبالغ الأساسية
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_quantity', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('invoice_level_discount', 12, 2)->default(0);
            $table->decimal('product_level_discount', 12, 2)->default(0);

            // الدفع
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'credit', 'split'])
                ->default('cash');
            $table->boolean('has_multiple_payment_methods')->default(false);
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('bank_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('wire_transfer_amount', 12, 2)->default(0);
            $table->decimal('customer_balance_after', 12, 2)->default(0);

            // الحالة
            // ملحوظة: القيم الرقمية بالظبط لحالة الفاتورة مش موثقة من النظام
            // القديم - لو عندك معاني محددة (مثلاً 0=مسودة، 1=مؤكدة...) قوللي
            // عشان أعملها enum واضح.
            $table->unsignedTinyInteger('status')->default(0);
            $table->boolean('is_finalized')->default(false);
            $table->text('note')->nullable();

            // ترقيم الفاتورة
            $table->string('invoice_number')->nullable();
            $table->integer('display_sequence_number')->default(1);
            $table->string('local_uuid')->nullable();
            $table->string('purchase_order_number')->nullable();
            // معنى دقيق غير موثق - كان اسمها NOTICE_Number
            $table->bigInteger('notice_number')->default(0);
            $table->string('return_payment_method')->nullable();

            // تواريخ الإصدار
            $table->time('issue_time')->nullable();
            $table->date('issue_date')->nullable();

            // ===== حقول الفوترة الإلكترونية (ZATCA) - الفاتورة الأصلية =====
            $table->boolean('is_sent_to_zatca')->default(false);
            $table->text('zatca_status')->nullable();
            $table->string('zatca_invoice_uuid')->nullable();
            $table->dateTime('zatca_signed_at')->nullable();
            $table->longText('zatca_invoice_xml')->nullable();
            $table->enum('zatca_document_type', ['simplified', 'standard'])->nullable();
            $table->enum('zatca_invoice_type', ['388', '383', '381'])->nullable();
            $table->bigInteger('zatca_invoice_counter')->nullable();
            $table->string('zatca_hash', 256)->nullable();
            $table->text('zatca_qr_code')->nullable();
            $table->text('zatca_xml_tags')->nullable();
            $table->longText('zatca_cleared_invoice_xml')->nullable();

            // ===== نفس حقول ZATCA لكن لنسخة إشعار الدائن/المرتجع =====
            $table->date('credit_note_issue_date')->nullable();
            $table->time('credit_note_issue_time')->nullable();
            $table->text('credit_note_zatca_xml_tags')->nullable();
            $table->text('credit_note_zatca_xml')->nullable();
            $table->text('credit_note_zatca_hash')->nullable();
            $table->text('credit_note_zatca_status')->nullable();
            $table->text('credit_note_zatca_qr_code')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
