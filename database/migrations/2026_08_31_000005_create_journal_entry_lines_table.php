<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * سطور القيد اليومي (App\Models\JournalEntryLine). كل سطر بيحدد
     * حساب من شجرة الحسابات (financial_account_id) ومبلغ مدين أو دائن
     * (واحد منهم بس غير صفر في العادة). account_id بيشاور على
     * financialaccount.id (الجدول الفعلي بتاع App\Models\FinancialAccount)
     * من غير foreign key صريح، بنفس منطق باقي المشروع (تفادي لمشكلة
     * الـ FK constraint الموضحة في ميجريشن financialaccount القديمة).
     */
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->unsignedBigInteger('account_id');

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
