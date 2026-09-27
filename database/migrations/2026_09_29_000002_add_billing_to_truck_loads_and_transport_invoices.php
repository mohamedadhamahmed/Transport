<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * فوترة الأحمال:
     * - truck_loads: سعر الحمل + رقم الفاتورة اللي اتفوتر فيها (الحمل
     *   اللي transport_invoice_id بتاعه فاضي = "حمل غير مفوتر").
     * - transport_invoice_items: السطر ممكن يبقى حمل (truck_load_id) أو
     *   بند يدوي (وصف × كمية × سعر) من غير شاحنة.
     * - transport_invoices: فترة التوريد + هل الأسعار المكتوبة شاملة الضريبة.
     */
    public function up(): void
    {
        Schema::table('truck_loads', function (Blueprint $table) {
            if (!Schema::hasColumn('truck_loads', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('truck_loads', 'transport_invoice_id')) {
                $table->foreignId('transport_invoice_id')->nullable()->after('status')
                    ->constrained('transport_invoices')->nullOnDelete();
            }
        });

        Schema::table('transport_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('truck_id')->nullable()->change();

            if (!Schema::hasColumn('transport_invoice_items', 'truck_load_id')) {
                $table->foreignId('truck_load_id')->nullable()->after('truck_id')
                    ->constrained('truck_loads')->nullOnDelete();
            }
            if (!Schema::hasColumn('transport_invoice_items', 'description')) {
                $table->string('description')->nullable()->after('truck_snapshot');
                $table->decimal('quantity', 10, 2)->default(1)->after('description');
                $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            }
        });

        Schema::table('transport_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_invoices', 'supply_from')) {
                $table->date('supply_from')->nullable()->after('issue_time');
                $table->date('supply_to')->nullable()->after('supply_from');
            }
            if (!Schema::hasColumn('transport_invoices', 'prices_include_tax')) {
                $table->boolean('prices_include_tax')->default(false)->after('tax_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_invoices', function (Blueprint $table) {
            $table->dropColumn(['supply_from', 'supply_to', 'prices_include_tax']);
        });

        Schema::table('transport_invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('truck_load_id');
            $table->dropColumn(['description', 'quantity', 'unit_price']);
        });

        Schema::table('truck_loads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transport_invoice_id');
            $table->dropColumn('price');
        });
    }
};
