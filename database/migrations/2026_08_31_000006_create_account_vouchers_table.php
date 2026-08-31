<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * سندات القبض والصرف (App\Models\AccountVoucher). سند القبض =
     * فلوس داخلة لحساب خزينة/بنك (treasury_account_id) من حساب تاني
     * (counterpart_account_id - عميل/مورد/حساب عام)، وسند الصرف عكسه
     * بالظبط. كل سند بينتج عنه سطرين في credittransaction (مدين
     * ودائن) بنفس منطق reversal المستخدم في باقي الشاشات.
     */
    public function up(): void
    {
        Schema::create('account_vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('voucher_number')->unique();
            // receipt = سند قبض / payment = سند صرف
            $table->string('type', 10);
            $table->date('voucher_date');

            // حساب الخزينة/البنك (اللي بتدخل أو بتخرج منه الفلوس فعليًا)
            $table->unsignedBigInteger('treasury_account_id');

            // الحساب التاني الطرف في السند (عميل، مورد، أو أي حساب عام)
            $table->unsignedBigInteger('counterpart_account_id');

            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('voucher_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_vouchers');
    }
};
