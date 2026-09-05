<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سند تحويل مخزون بين فرعين (رأس السند) - قسم المستودعات. ميزة جديدة
 * تمامًا منفصلة عن delivery_note (تسليم منتج لعميل) وعن عمود
 * invoices.receiving_branch_id (بتاع فواتير ضريبية حقيقية بالـ ZATCA):
 * الجدول ده مخصص بس لحركة مخزون داخلية بين فروع الشركة، من غير أي أثر
 * مالي أو ضريبي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();
            $table->foreignId('sender_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('receiver_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            // draft: لسه مسودة، ماحصلش خصم من مخزون الفرع المرسل.
            // sent: اتصرفت فعليًا وخصمت من مخزون الفرع المرسل، مستنية استلام.
            // received: اتأكد استلامها وضافت لمخزون الفرع المستلم.
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->date('transfer_date');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
