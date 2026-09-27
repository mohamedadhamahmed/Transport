<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الحسابات المالية (شجرة الحسابات) - نفس أعمدة الموديل
     * القديم اللي بعتها، وبيتسجل عليه App\Models\FinancialAccount.
     * كل حساب (عميل، مورد، خزينة، بنك...) بيتسجل هنا كسطر، وعليه
     * بيتم تسجيل حركات القيد في جدول credittransactions
     * (App\Models\CreditTransaction).
     */
    public function up(): void
    {
        Schema::create('financialaccount', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // نوع الحساب (يشاور على جدول acounts_type - لسه مش متبني في
            // المشروع ده، فمسيبينها من غير foreign key حالياً).
            $table->unsignedBigInteger('account_type')->nullable();

            // رقم الحساب الأب - يشاور على id لحساب تاني في نفس الجدول
            // (شجرة حسابات: حساب رئيسي وتحته حسابات فرعية).
            $table->unsignedBigInteger('parent_account_number')->nullable();

            $table->string('account_number', 50)->nullable();

            $table->decimal('start_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            // FK عام لأي جدول تاني الحساب ده اتعمل بسببه (مرن زي القديم)
            $table->unsignedBigInteger('other_table_FK')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->string('com_code')->nullable();
            $table->date('date')->nullable();

            $table->boolean('active')->default(true);
            $table->boolean('is_parent')->default(false);
            $table->string('start_balance_status', 20)->nullable();

            // مصدر الحساب: عميل / مورد / خزينة / بنك... إلخ
            $table->unsignedBigInteger('orginal_id')->nullable();
            $table->string('orginal_type', 50)->nullable();
            $table->unsignedBigInteger('orginal_supplier')->nullable();

            $table->decimal('debtor_end', 15, 2)->default(0);
            $table->decimal('creditor_end', 15, 2)->default(0);
            $table->decimal('debtor_current', 15, 2)->default(0);
            $table->decimal('creditor_current', 15, 2)->default(0);
            $table->decimal('debtor_opening', 15, 2)->default(0);
            $table->decimal('creditor_opening', 15, 2)->default(0);

            $table->unsignedBigInteger('branchs_id')->nullable();

            $table->string('tax_no')->nullable();

            $table->timestamps();

            $table->index('account_type');
            $table->index(['orginal_id', 'orginal_type']);

            // ملحوظة: FK على parent_account_number بس (نفس الجدول، فمضمون
            // إن النوع متطابق). محطتش foreign key صريحة على branchs_id/
            // added_by/updated_by لإنها بترجع لجداول (branches, users)
            // مش أنا اللي عامل الـ migration بتاعتها، وأي فرق بسيط في
            // نوع أو توقيع العمود (signed/unsigned, int/bigint) بيسبب
            // بالظبط الخطأ اللي ظهرلك (Foreign key constraint is
            // incorrectly formed). خليتها index عادي بس - العلاقة في
            // الموديل (belongsTo) شغالة برضه من غير ما تحتاج constraint
            // فعلي في قاعدة البيانات.
            $table->index('branchs_id');
            $table->index('added_by');
            $table->index('updated_by');

            $table->foreign('parent_account_number')
                ->references('id')->on('financialaccount')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financialaccount');
    }
};
