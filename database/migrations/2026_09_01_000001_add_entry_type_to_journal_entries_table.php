<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة entry_type لجدول journal_entries عشان نفرّق بين القيد
     * اليومي العادي (daily) والقيد الافتتاحي (opening) من غير ما نعمل
     * جدول/موديل/كونترولر منفصل - بالظبط زي AccountVoucher اللي
     * بيفرّق بين receipt وpayment بعمود type واحد. القيمة الافتراضية
     * "daily" عشان أي صف قديم اتسجل قبل الإضافة دي يفضل يتصنف صح.
     */
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('entry_type')->default('daily')->after('entry_date');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn('entry_type');
        });
    }
};
