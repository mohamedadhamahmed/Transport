<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إشعارات دائنة على فواتير النقليات (تصحيح غلط في فاتورة / تخفيض / إلغاء).
     * كل إشعار مربوط بفاتورة أصلية، وبيتبعت للزكاة كمستند 381 مرجعه رقم
     * الفاتورة الأصلية - بنفس سلسلة الهاش والعدّاد بتوع الفواتير.
     */
    public function up(): void
    {
        Schema::create('transport_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->nullable()->index();
            $table->foreignId('transport_invoice_id')->constrained('transport_invoices')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('issue_date');
            $table->time('issue_time')->nullable();
            $table->string('reason', 500);                 // سبب الإشعار (بيتبعت للزكاة)
            $table->boolean('release_loads')->default(false); // الأحمال رجعت "غير مفوترة"

            $table->decimal('subtotal', 14, 2)->default(0);  // قبل الضريبة
            $table->decimal('tax_rate', 6, 4)->default(0);
            $table->string('tax_type', 20)->nullable();
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            // الزكاة
            $table->boolean('is_sent_to_zatca')->default(false)->index();
            $table->string('zatca_status', 20)->nullable();
            $table->string('zatca_invoice_uuid')->nullable();
            $table->string('zatca_document_type', 20)->nullable();
            $table->dateTime('zatca_signed_at')->nullable();
            $table->string('zatca_hash', 256)->nullable();
            $table->text('zatca_message')->nullable();
            $table->longText('zatca_invoice_xml')->nullable();
            $table->longText('zatca_cleared_invoice_xml')->nullable();

            $table->timestamps();
        });

        Schema::create('transport_credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_credit_note_id')->constrained('transport_credit_notes')->cascadeOnDelete();
            $table->foreignId('transport_invoice_item_id')->nullable()->constrained('transport_invoice_items')->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 14, 2)->default(0);   // المبلغ المخصوم قبل الضريبة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_credit_note_items');
        Schema::dropIfExists('transport_credit_notes');
    }
};
