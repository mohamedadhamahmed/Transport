<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * بنود سند القبض/الصرف (App\Models\AccountVoucherLine) - جزء من تحويل
 * السندات من "سطر واحد بس لكل سند" لـ "سند واحد بعدد بنود حر" (طلب:
 * "عوز السندات تكون متعددة"، بنفس فكرة تعدد أسطر القيد اليومي
 * بالظبط). كل بند بيمثل طرف تاني (counterpart_account) ومبلغ مستقل،
 * ومعاه بياناته الخاصة (مركز تكلفة/بيان/ضريبة) - بينما حساب الخزينة/
 * البنك (treasury_account_id) وتاريخ السند وفرعه بيفضلوا على مستوى
 * السند نفسه (account_vouchers) لإنهم واحد بس لكل سند مهما كان عدد
 * البنود.
 *
 * الحقول هنا هي بالظبط الحقول اللي كانت على مستوى السند نفسه قبل كده
 * (راجع 2026_08_31_000006_create_account_vouchers_table.php
 * و2026_09_02_000033_add_tax_fields_to_account_vouchers_table.php) -
 * الميجريشن اللي بعد دي (2026_09_03_000035) بتنقل البيانات القديمة
 * من هناك لهنا (بند واحد لكل سند قديم) وبعدين تشيل الأعمدة دي من
 * account_vouchers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_voucher_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_voucher_id')->constrained('account_vouchers')->cascadeOnDelete();

            // الحساب التاني الطرف في البند (عميل، مورد، أو أي حساب عام)
            $table->unsignedBigInteger('counterpart_account_id');

            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();

            // نفس حقول الضريبة اللي كانت على مستوى السند - بقت لكل بند
            // على حدة عشان كل بند يقدر يكون خاضع لضريبة مختلفة عن التاني.
            $table->boolean('is_taxable')->default(false);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->unsignedBigInteger('vat_account_id')->nullable();

            $table->timestamps();

            $table->index('counterpart_account_id');
            $table->index('cost_center_id');
            $table->index('tax_id');
            $table->index('vat_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_voucher_lines');
    }
};
