<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول employees (الموظفين)
|--------------------------------------------------------------------------
| البداية الفعلية لقسم الموارد البشرية - نفس فكرة جدول suppliers من ناحية
| الأسلوب (حقول مسطحة بسيطة + created_by + index على الاسم) لكن بحقول
| خاصة بالموظف بدل المورد: بيانات وظيفية (job_title/department/branch_id)
| وبيانات لازمة لحساب مكافأة نهاية الخدمة لاحقًا (hire_date/basic_salary)
| وبيانات صرف الراتب (pay_method/bank_name/iban) هتتستخدم كمان مع
| السلف (loans) زي ما بيحصل بالظبط مع pay_method في سندات القبض/الصرف.
|
| employee_number: رقم تسلسلي شكله EMP-000001 (بيتحسب في الكونترولر
| بنفس منطق أرقام السندات/القيود: max(id)+1 مع str_pad) - مش عمود
| auto_increment تاني عشان نقدر نتحكم في الصيغة وقت العرض.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_number', 20)->unique();

            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('national_id', 30)->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('job_title')->nullable();
            $table->string('department')->nullable();

            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();

            $table->date('hire_date')->nullable();

            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);

            // Cash / Bank - نفس القيم المستخدمة مع pay_method في السلف
            // وسندات القبض والصرف في باقي المشروع.
            $table->string('pay_method', 20)->default('Cash');
            $table->string('bank_name')->nullable();
            $table->string('iban', 40)->nullable();

            $table->string('national_address')->nullable();

            // active / inactive / terminated - لازم لاحقًا لتمييز الموظف
            // اللي اتصرفله مكافأة نهاية خدمة عن الموظف الحالي.
            $table->string('status', 20)->default('active');
            $table->date('termination_date')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('national_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
