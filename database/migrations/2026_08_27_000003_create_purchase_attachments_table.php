<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول purchase_attachments (مرفقات فاتورة المشتريات: PDF أو صورة)
|--------------------------------------------------------------------------
| فاتورة مشتريات ممكن يكون ليها أكتر من مرفق (مثلاً صورة الفاتورة
| الورقية + ملف PDF)، فحطينا جدول منفصل بدل عمود واحد بس.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_attachments');
    }
};
