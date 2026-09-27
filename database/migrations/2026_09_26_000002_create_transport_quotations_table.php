<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * عروض أسعار النقليات: كل سطر = مسار (من منطقة إلى منطقة) + نوع
     * الشاحنة + عدد النقلات + سعر النقلة + تحويلة اختيارية.
     */
    public function up(): void
    {
        Schema::create('transport_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('issue_date');
            $table->date('valid_until')->nullable();

            $table->decimal('trips_total', 14, 2)->default(0);
            $table->decimal('transfers_total', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_rate', 6, 4)->default(0.15);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'converted'])->default('draft');
            $table->foreignId('transport_invoice_id')->nullable()->constrained('transport_invoices')->nullOnDelete();

            $table->text('terms')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_quotation_id')->constrained('transport_quotations')->cascadeOnDelete();
            $table->string('truck_type')->nullable();
            $table->string('from_region', 50)->nullable();
            $table->string('from_city')->nullable();
            $table->string('to_region', 50)->nullable();
            $table->string('to_city')->nullable();
            $table->string('load_type')->nullable();
            $table->unsignedInteger('trips_count')->default(1);
            $table->decimal('trip_price', 12, 2)->default(0);
            $table->boolean('has_transfer')->default(false);
            $table->string('transfer_location')->nullable();
            $table->decimal('transfer_price', 12, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);   // عدد النقلات × (السعر + التحويلة)
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ربط الفاتورة بعرض السعر اللي اتحولت منه
        Schema::table('transport_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('transport_quotation_id')->nullable()->after('po_number');
        });
    }

    public function down(): void
    {
        Schema::table('transport_invoices', function (Blueprint $table) {
            $table->dropColumn('transport_quotation_id');
        });
        Schema::dropIfExists('transport_quotation_items');
        Schema::dropIfExists('transport_quotations');
    }
};
