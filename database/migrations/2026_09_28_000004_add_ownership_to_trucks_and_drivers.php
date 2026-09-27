<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - السائق: موظف عند الشركة (مربوط بالموارد البشرية) أو سائق خارجي.
     * - الشاحنة: ملك الشركة (قيمتها بتتسجل في الأصول الثابتة تحت
     *   "الشاحنات") أو شاحنة خارجية (بيانات صاحبها).
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->enum('driver_type', ['company', 'external'])->default('company')->after('name');
            $table->foreignId('employee_id')->nullable()->after('driver_type')->constrained('employees')->nullOnDelete();
        });

        Schema::table('trucks', function (Blueprint $table) {
            $table->enum('ownership', ['owned', 'external'])->default('owned')->after('type');
            $table->decimal('purchase_value', 14, 2)->nullable()->after('ownership');   // قيمة الشاحنة (أصل ثابت)
            $table->date('purchase_date')->nullable()->after('purchase_value');
            $table->string('owner_name')->nullable()->after('purchase_date');          // صاحب الشاحنة الخارجية
            $table->string('owner_phone')->nullable()->after('owner_name');
            $table->unsignedBigInteger('financial_account_id')->nullable()->after('owner_phone'); // حساب الأصل في الشجرة
        });
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn(['ownership', 'purchase_value', 'purchase_date', 'owner_name', 'owner_phone', 'financial_account_id']);
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropColumn('driver_type');
        });
    }
};
