<?php

use App\Models\CreditTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * balance_posted = 1 معناها إن مبلغ الحركة دي (debtor/creditor) اتضاف فعلاً
 * على رصيد الحساب في financialaccount. بيستخدمه تعديل/حذف الفواتير عشان
 * يعكس بالظبط اللي اتسجل، وبيستخدمه أمر accounts:repair-postings عشان
 * ميرحّلش نفس الحركة مرتين.
 */
return new class extends Migration
{
    private function table(): string
    {
        return (new CreditTransaction())->getTable();
    }

    public function up(): void
    {
        $table = $this->table();
        if (Schema::hasTable($table) && !Schema::hasColumn($table, 'balance_posted')) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('balance_posted')->nullable()->default(null);
            });
        }
    }

    public function down(): void
    {
        $table = $this->table();
        if (Schema::hasTable($table) && Schema::hasColumn($table, 'balance_posted')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('balance_posted');
            });
        }
    }
};
