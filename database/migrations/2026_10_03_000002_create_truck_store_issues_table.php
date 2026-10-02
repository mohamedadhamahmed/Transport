<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة حقول العداد وغيار الزيت لجدول الشاحنات
        Schema::table('trucks', function (Blueprint $table) {
            if (!Schema::hasColumn('trucks', 'current_odometer')) {
                $table->unsignedBigInteger('current_odometer')->nullable()->after('capacity');
            }
            if (!Schema::hasColumn('trucks', 'last_oil_change_odometer')) {
                $table->unsignedBigInteger('last_oil_change_odometer')->nullable()->after('current_odometer');
            }
            if (!Schema::hasColumn('trucks', 'next_oil_change_odometer')) {
                $table->unsignedBigInteger('next_oil_change_odometer')->nullable()->after('last_oil_change_odometer');
            }
            if (!Schema::hasColumn('trucks', 'last_oil_change_date')) {
                $table->date('last_oil_change_date')->nullable()->after('next_oil_change_odometer');
            }
        });

        // 2. جدول أذونات صرف قطع الغيار والزيوت للشاحنات
        if (!Schema::hasTable('truck_store_issues')) {
            Schema::create('truck_store_issues', function (Blueprint $table) {
                $table->id();
                $table->string('issue_number', 50)->unique();
                $table->date('issue_date');
                $table->foreignId('truck_id')->constrained('trucks')->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
                $table->unsignedBigInteger('cost_center_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('expense_category', 50)->default('maintenance'); // oil, tires, spare_parts, maintenance, other
                $table->unsignedBigInteger('current_odometer')->nullable();
                $table->unsignedBigInteger('oil_change_interval_km')->nullable();
                $table->unsignedBigInteger('next_oil_change_odometer')->nullable();
                $table->unsignedBigInteger('inventory_account_id')->nullable();
                $table->unsignedBigInteger('expense_account_id')->nullable();
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('issue_date');
                $table->index('expense_category');
            });
        }

        // 3. جدول أصناف إذن الصرف (الإطارات، الزيوت، الفلاتر، قطع الغيار)
        if (!Schema::hasTable('truck_store_issue_items')) {
            Schema::create('truck_store_issue_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('truck_store_issue_id')->constrained('truck_store_issues')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('unit_cost', 12, 2)->default(0);
                $table->decimal('total_cost', 14, 2)->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->index('product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_store_issue_items');
        Schema::dropIfExists('truck_store_issues');

        Schema::table('trucks', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('trucks', 'last_oil_change_date') ? 'last_oil_change_date' : null,
                Schema::hasColumn('trucks', 'next_oil_change_odometer') ? 'next_oil_change_odometer' : null,
                Schema::hasColumn('trucks', 'last_oil_change_odometer') ? 'last_oil_change_odometer' : null,
                Schema::hasColumn('trucks', 'current_odometer') ? 'current_odometer' : null,
            ]);
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
