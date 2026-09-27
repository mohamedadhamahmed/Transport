<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * سند الصيانة = سند صرف عادي + الشاحنة اللي اتصرف عليها + نوع المصروف
     * (صيانة / قطع غيار / كفرات / وقود ...). بيستخدم في تقرير الشاحنات.
     */
    public function up(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('truck_id')->nullable()->after('branch_id');
            $table->string('expense_category', 30)->nullable()->after('truck_id');
            $table->index('truck_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table) {
            $table->dropIndex(['truck_id']);
            $table->dropColumn(['truck_id', 'expense_category']);
        });
    }
};
