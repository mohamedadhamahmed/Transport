<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تحويل بيانات السندات القديمة (كل سند = بند واحد بس) لبنية "سند بعدد
 * بنود حر" (account_voucher_lines - راجع الميجريشن اللي قبل دي).
 *
 * up(): بننسخ بيانات كل سند قديم (الطرف التاني/المبلغ/مركز التكلفة/
 * الضريبة) كبند واحد في account_voucher_lines، بعدين نشيل الأعمدة دي
 * من account_vouchers نفسها (بقت ملكية البند مش السند). عمود
 * description فضل في السند نفسه (بيان عام اختياري للسند كله) - وبننسخه
 * كمان كبيان مبدئي للبند المُنشأ عشان السندات القديمة تفضل عارضة نفس
 * النص اللي كانت عليه بالظبط.
 *
 * down(): بيرجّع الأعمدة القديمة وبينسخلها بيانات "أول بند" بس لكل سند
 * (أفضل مجهود ممكن - لو السند بقى فيه أكتر من بند بعد الترقية، باقي
 * البنود هتتفقد عند التراجع؛ ده متوقع لإن التراجع مقصود بس كخطوة أمان
 * فورية بعد الترقية مباشرة، مش كمسار طويل المدى).
 */
return new class extends Migration
{
    public function up(): void
    {
        $vouchers = DB::table('account_vouchers')->get([
            'id', 'counterpart_account_id', 'amount', 'description', 'cost_center_id',
            'is_taxable', 'tax_id', 'tax_rate', 'net_amount', 'tax_amount', 'vat_account_id',
        ]);

        $now = now();
        foreach ($vouchers as $voucher) {
            DB::table('account_voucher_lines')->insert([
                'account_voucher_id' => $voucher->id,
                'counterpart_account_id' => $voucher->counterpart_account_id,
                'amount' => $voucher->amount,
                'description' => $voucher->description,
                'cost_center_id' => $voucher->cost_center_id,
                'is_taxable' => $voucher->is_taxable,
                'tax_id' => $voucher->tax_id,
                'tax_rate' => $voucher->tax_rate,
                'net_amount' => $voucher->net_amount,
                'tax_amount' => $voucher->tax_amount,
                'vat_account_id' => $voucher->vat_account_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('account_vouchers', function (Blueprint $table) {
            $table->dropIndex(['tax_id']);
            $table->dropIndex(['vat_account_id']);
            $table->dropColumn([
                'counterpart_account_id', 'amount', 'cost_center_id',
                'is_taxable', 'tax_id', 'tax_rate', 'net_amount', 'tax_amount', 'vat_account_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('counterpart_account_id')->nullable()->after('treasury_account_id');
            $table->decimal('amount', 15, 2)->default(0)->after('counterpart_account_id');
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('branch_id');
            $table->boolean('is_taxable')->default(false)->after('amount');
            $table->unsignedBigInteger('tax_id')->nullable()->after('is_taxable');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax_id');
            $table->decimal('net_amount', 15, 2)->nullable()->after('tax_rate');
            $table->decimal('tax_amount', 15, 2)->nullable()->after('net_amount');
            $table->unsignedBigInteger('vat_account_id')->nullable()->after('tax_amount');

            $table->index('tax_id');
            $table->index('vat_account_id');
        });

        $firstLines = DB::table('account_voucher_lines')
            ->orderBy('id')
            ->get()
            ->groupBy('account_voucher_id')
            ->map(fn ($lines) => $lines->first());

        foreach ($firstLines as $voucherId => $line) {
            DB::table('account_vouchers')->where('id', $voucherId)->update([
                'counterpart_account_id' => $line->counterpart_account_id,
                'amount' => $line->amount,
                'cost_center_id' => $line->cost_center_id,
                'is_taxable' => $line->is_taxable,
                'tax_id' => $line->tax_id,
                'tax_rate' => $line->tax_rate,
                'net_amount' => $line->net_amount,
                'tax_amount' => $line->tax_amount,
                'vat_account_id' => $line->vat_account_id,
            ]);
        }
    }
};
