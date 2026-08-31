<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * هيدر القيد اليومي اليدوي (App\Models\JournalEntry). كل قيد هنا
     * ليه سطرين أو أكتر في journal_entry_lines (مدين/دائن)، ولازم
     * إجمالي المدين = إجمالي الدائن (بيتم التأكد من كده في الكونترولر
     * قبل الحفظ). كل سطر بيتسجل كمان كصف في جدول credittransaction
     * الأصلي (operation_type = 7) عشان يظهر في كشف حساب أي حساب
     * اتأثر بالقيد.
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->timestamps();

            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
