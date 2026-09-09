<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * التكاليف غير المباشرة لأمر التصنيع: أي تكلفة مش مادة خام مباشرة
 * (كهرباء، عمالة، صيانة... إلخ) بتتضاف لإجمالي تكلفة الأمر.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_order_indirect_costs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('manufacturing_order_id');
            $table->foreign('manufacturing_order_id', 'mo_indirect_costs_mo_id_foreign')
                  ->references('id')->on('manufacturing_orders')
                  ->cascadeOnDelete();

            $table->string('name');
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_indirect_costs');
    }
};