<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول suppliers (الموردين)
|--------------------------------------------------------------------------
| نفس فكرة جدول العملاء (customers) بالظبط - قولتي "هنعمله زي بيانات
| العميل" فحطيت نفس الأعمدة اللي شفتها مستخدمة مع العميل في
| InvoiceController@quickStoreCustomer (الاسم، الهاتف، الرقم الضريبي،
| العنوان...إلخ) لكن للمورد. عمود balance = المبلغ اللي إحنا مديونين
| بيه للمورد (زي In_debt في النظام القديم).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('tax_no')->nullable();
            $table->string('crn')->nullable();
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->string('address')->nullable();
            $table->string('sub_city')->nullable();
            $table->string('street_name')->nullable();
            $table->string('building_number')->nullable();
            $table->string('plot_identification')->nullable();
            $table->string('postcode')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
