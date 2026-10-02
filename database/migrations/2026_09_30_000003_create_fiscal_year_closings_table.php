<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إقفال السنة المالية:
 * - fiscal_year_closings: كل إقفال (تاريخه، إجمالي الإيرادات/المصروفات،
 *   صافي الربح اللي اترحّل لـ "الأرباح المرحّلة"، ورقم القيد).
 * - fiscal_year_opening_balances: أرصدة حسابات الميزانية (أصول/خصوم/حقوق
 *   ملكية) بعد الإقفال = الأرصدة الافتتاحية للسنة الجديدة.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fiscal_year_closings')) {
            Schema::create('fiscal_year_closings', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('fiscal_year');
                $table->date('date_from')->nullable();
                $table->date('closing_date')->unique();
                $table->string('reference', 50)->unique();
                $table->decimal('total_revenue', 18, 2)->default(0);
                $table->decimal('total_expenses', 18, 2)->default(0);
                $table->decimal('net_income', 18, 2)->default(0);
                $table->unsignedBigInteger('retained_account_id')->nullable();
                $table->unsignedInteger('closed_accounts_count')->default(0);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // لو تشغيل سابق وقف في النص (الجدول اتعمل والـ index فشل) - نكمل الناقص بس.
        if (Schema::hasTable('fiscal_year_opening_balances')) {
            $hasIndex = collect(\Illuminate\Support\Facades\DB::select('SHOW INDEX FROM fiscal_year_opening_balances'))
                ->contains(function ($i) {
                    $arr = (array) $i;
                    $key = $arr['Key_name'] ?? $arr['key_name'] ?? '';
                    return $key === 'fyob_closing_category_idx';
                });
            if (!$hasIndex) {
                Schema::table('fiscal_year_opening_balances', function (Blueprint $table) {
                    $table->index(['fiscal_year_closing_id', 'category'], 'fyob_closing_category_idx');
                });
            }
        }

        if (!Schema::hasTable('fiscal_year_opening_balances')) {
            Schema::create('fiscal_year_opening_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fiscal_year_closing_id')->constrained('fiscal_year_closings', indexName: 'fyob_closing_fk')->cascadeOnDelete();
                $table->unsignedBigInteger('account_id');
                $table->string('account_number', 50)->nullable();
                $table->string('account_name')->nullable();
                $table->unsignedTinyInteger('category')->nullable();
                $table->unsignedBigInteger('branchs_id')->nullable();
                $table->decimal('debit', 18, 2)->default(0);
                $table->decimal('credit', 18, 2)->default(0);
                $table->timestamps();

                // اسم قصير: الاسم التلقائي أطول من 64 حرف (حد MySQL).
                $table->index(['fiscal_year_closing_id', 'category'], 'fyob_closing_category_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_year_opening_balances');
        Schema::dropIfExists('fiscal_year_closings');
    }
};
