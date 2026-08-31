<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة returned_quantity لجدول purchase_items
|--------------------------------------------------------------------------
| عمود واحد بس بيتراكم عليه إجمالي الكمية اللي اترجعت من السطر ده (ممكن
| ترجعي نفس السطر أكتر من مرة على دفعات - كل مرة بيتزود عليه). "الكمية
| المتاح إرجاعها" بتتحسب مباشرة (quantity - returned_quantity) وقت
| الحاجة، بدل ما نخزن عمود remaining_quantity منفصل ممكن يفقد التزامن
| مع quantity لو اتعدلت لاحقًا - نفس الفكرة المتبعة في whereColumn داخل
| PurchaseReturnController.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('returned_quantity', 12, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('returned_quantity');
        });
    }
};
