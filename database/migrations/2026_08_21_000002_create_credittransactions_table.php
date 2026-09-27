<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول حركات القيد (دائن/مدين) - نفس أعمدة الموديل القديم اللي
     * بعتها، وبيتسجل عليه App\Models\CreditTransaction (اسم الجدول
     * الفعلي فضل "credittransactions" من غير underscore عشان
     * $table اتحددت صريحة في الموديل). كل عملية مالية (بيع، تحصيل،
     * دفع...) بتسجل هنا سطر مرتبط بحساب في financialaccount
     * (App\Models\FinancialAccount).
     */
    public function up(): void
    {
        Schema::create('credittransactions', function (Blueprint $table) {
            $table->id();

            // بيشاور على id في جدول financialaccount (مش على جدول عملاء)
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->decimal('recive_amount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->decimal('currentblance', 15, 2)->default(0);

            $table->string('pay_method', 50)->nullable();
            $table->unsignedBigInteger('branchs_id')->nullable();
            $table->string('Pay_Method_Name')->nullable();

            $table->text('attachments')->nullable();

            // مصدر الحركة: فاتورة بيع، فاتورة شراء، سند قبض... إلخ
            $table->string('orginal_type', 50)->nullable();
            $table->unsignedBigInteger('orginal_id')->nullable();

            $table->boolean('dely_record')->default(false);
            $table->unsignedBigInteger('parent_dely_record')->nullable();

            $table->decimal('debtor', 15, 2)->default(0);
            $table->decimal('creditor', 15, 2)->default(0);
            $table->decimal('vat', 15, 2)->default(0);

            $table->string('name')->nullable();
            $table->decimal('tax', 15, 2)->default(0);

            $table->string('decument_id')->nullable();
            $table->string('type_decument', 50)->nullable();

            $table->boolean('save')->default(true);

            $table->boolean('Opening_entry')->default(false);
            $table->unsignedBigInteger('parent_Opening_entry')->nullable();

            $table->date('date_export')->nullable();

            $table->unsignedInteger('sent_abd_count')->default(0);
            $table->unsignedInteger('sent_serf_count')->default(0);

            $table->string('type', 30)->nullable();

            // بيشاور على جدول Cost_centers - لسه مش متبني في المشروع ده،
            // فمسيبينها من غير foreign key حالياً.
            $table->unsignedBigInteger('cost_center')->nullable();

            $table->timestamps();

            $table->index('customer_id');
            $table->index(['orginal_id', 'orginal_type']);
            $table->index('type_decument');

            // نفس ملحوظة financialaccount: سبنا foreign key فعلي بس على
            // customer_id (بيرجع لـ financialaccount اللي أنا عامل
            // الـ migration بتاعتها فمضمون تطابق النوع). branchs_id و
            // user_id بيرجعوا لجداول مش أنا عاملها (branches, users)
            // فسبناهم index عادي عشان منقعش في نفس مشكلة الـ FK.
            $table->index('branchs_id');
            $table->index('user_id');

            $table->foreign('customer_id')
                ->references('id')->on('financialaccount')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credittransactions');
    }
};
