<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول employee_loans (السلف والعهد)
|--------------------------------------------------------------------------
| صف واحد لكل سلفة نقدية (loan) أو عهدة (custody) بتُصرف لموظف. القيد
| المحاسبي بيتم بنفس أسلوب VoucherController@store بالظبط (سند صرف):
| مدين حساب الموظف (ذمم الموظف - EmployeeController/HrAccountService)
| / دائن حساب الخزينة (treasury_account_id، لازم يكون تحت parent_account_number
| 4 أو 5 زي أي سند صرف عادي). التسوية (settle) بتعمل القيد العكسي:
| مدين الخزينة / دائن حساب الموظف.
|
| type بس تصنيف/تسمية - القيد المحاسبي نفسه واحد في الحالتين (سلفة أو
| عهدة، الاتنين "مبلغ الموظف مسؤول عنه وهيرجعه"). لو عهدة أصل (مش نقدية)
| من غير حركة خزينة خالص، سيب amount = 0 ومفيش قيد هيتسجل.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();

            $table->string('document_number', 20)->unique();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();

            // loan = سلفة نقدية (بتتخصم من الراتب عادة) / custody = عهدة
            $table->string('type', 20)->default('loan');

            $table->unsignedBigInteger('treasury_account_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->text('description')->nullable();

            // active = لسه على ذمة الموظف / settled = اترجعت أو اتخصمت بالكامل
            $table->string('status', 20)->default('active');
            $table->date('settled_at')->nullable();
            $table->unsignedBigInteger('settled_treasury_account_id')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index('employee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
