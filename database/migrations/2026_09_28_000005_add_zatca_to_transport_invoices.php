<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** مسودة + حقول الفوترة الإلكترونية (زاتكا) لفواتير النقليات - نفس حقول فواتير المبيعات */
    public function up(): void
    {
        Schema::table('transport_invoices', function (Blueprint $table) {
            // مسودة: متتسجلش في الحسابات ومتتبعتش للزكاة لحد ما تتعتمد
            $table->boolean('is_draft')->default(false)->after('note');
            $table->boolean('is_sent_to_zatca')->default(false)->after('is_draft');
            $table->string('zatca_status', 20)->nullable()->after('is_sent_to_zatca');
            $table->string('zatca_invoice_uuid')->nullable()->after('zatca_status');
            $table->string('zatca_document_type', 20)->nullable()->after('zatca_invoice_uuid');
            $table->dateTime('zatca_signed_at')->nullable()->after('zatca_document_type');
            $table->string('zatca_hash', 256)->nullable()->after('zatca_signed_at');
            $table->text('zatca_message')->nullable()->after('zatca_hash');
            $table->longText('zatca_invoice_xml')->nullable()->after('zatca_message');
            $table->longText('zatca_cleared_invoice_xml')->nullable()->after('zatca_invoice_xml');
            $table->index('is_sent_to_zatca');
        });
    }

    public function down(): void
    {
        Schema::table('transport_invoices', function (Blueprint $table) {
            $table->dropIndex(['is_sent_to_zatca']);
            $table->dropColumn(['is_draft', 'is_sent_to_zatca', 'zatca_status', 'zatca_invoice_uuid', 'zatca_document_type', 'zatca_signed_at', 'zatca_hash', 'zatca_message', 'zatca_invoice_xml', 'zatca_cleared_invoice_xml']);
        });
    }
};
