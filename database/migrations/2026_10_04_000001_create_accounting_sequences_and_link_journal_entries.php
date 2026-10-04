<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول التسلسل المحاسبي الموحد وربط القيود اليومية بالحركات والمستندات.
     * يضمن:
     * 1. تسلسل موحد ومحمي من التكرار عبر accounting_sequences.
     * 2. ربط القيود بمصادرها (فواتير بيع، شراء، مرتجعات، سندات) عبر source_type و source_id و is_auto.
     * 3. ربط سطور credittransactions برقم ومعرف القيد اليومي الموحد (journal_entry_id و entry_number).
     */
    public function up(): void
    {
        if (! Schema::hasTable('accounting_sequences')) {
            Schema::create('accounting_sequences', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique();
                $table->unsignedBigInteger('current_value')->default(0);
                $table->string('prefix', 20)->default('JE-');
                $table->unsignedInteger('padding')->default(6);
                $table->timestamps();
            });
        }

        Schema::table('journal_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('journal_entries', 'source_type')) {
                $table->string('source_type')->nullable()->after('cost_center_id');
            }
            if (! Schema::hasColumn('journal_entries', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('journal_entries', 'is_auto')) {
                $table->boolean('is_auto')->default(false)->after('source_id');
                $table->index(['source_type', 'source_id']);
                $table->index('is_auto');
            }
        });

        $creditTable = Schema::hasTable('credittransactions')
            ? 'credittransactions'
            : (Schema::hasTable('credittransaction') ? 'credittransaction' : null);

        if ($creditTable) {
            Schema::table($creditTable, function (Blueprint $table) use ($creditTable) {
                if (! Schema::hasColumn($creditTable, 'journal_entry_id')) {
                    $table->unsignedBigInteger('journal_entry_id')->nullable()->after('id');
                    $table->index('journal_entry_id');
                }
                if (! Schema::hasColumn($creditTable, 'entry_number')) {
                    $table->string('entry_number', 50)->nullable()->after('journal_entry_id');
                    $table->index('entry_number');
                }
            });
        }
    }

    public function down(): void
    {
        $creditTable = Schema::hasTable('credittransactions')
            ? 'credittransactions'
            : (Schema::hasTable('credittransaction') ? 'credittransaction' : null);

        if ($creditTable) {
            Schema::table($creditTable, function (Blueprint $table) use ($creditTable) {
                if (Schema::hasColumn($creditTable, 'entry_number')) {
                    $table->dropIndex(['entry_number']);
                    $table->dropColumn('entry_number');
                }
                if (Schema::hasColumn($creditTable, 'journal_entry_id')) {
                    $table->dropIndex(['journal_entry_id']);
                    $table->dropColumn('journal_entry_id');
                }
            });
        }

        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'is_auto')) {
                $table->dropIndex(['is_auto']);
                $table->dropIndex(['source_type', 'source_id']);
                $table->dropColumn(['is_auto', 'source_id', 'source_type']);
            }
        });

        Schema::dropIfExists('accounting_sequences');
    }
};
