<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * عهدة أصول (لابتوب، عربية، موبايل...) - منفصلة تمامًا عن جدول
 * employee_loans (السلف/العهد المالية) لإن دي عهدة "أصل" بيرجع للشركة
 * زي ما هو، مش مبلغ مالي بيترد أو يتخصم من الراتب. من غير أثر محاسبي
 * تلقائي (تسجيل تشغيلي/إداري بحت) - لو الأصل اتفقد أو اتلف وعايزة خصم
 * مالي فعلي، ده بيتسجل يدويًا كسلفة عادية من شاشة السلف والعهد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_custodies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('category')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('condition_on_issue')->default('good');
            $table->string('condition_on_return')->nullable();
            $table->date('issued_date');
            $table->date('expected_return_date')->nullable();
            $table->date('returned_date')->nullable();
            $table->string('status')->default('with_employee');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_custodies');
    }
};
