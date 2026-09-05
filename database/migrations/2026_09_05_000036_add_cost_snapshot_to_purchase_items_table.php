<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| إضافة أعمدة "snapshot" لجدول purchase_items - عشان تعديل فاتورة مشتريات
|--------------------------------------------------------------------------
| قبل ما نطبّق تأثير أي بند شراء على المنتج (تحديث المخزون ومتوسط
| التكلفة/سعر الشراء بمنطق المتوسط المرجّح)، بنسجّل هنا "صورة" لقيم
| المنتج زي ما كانت بالظبط قبل البند ده مباشرة:
|   - stock_before: المخزون قبل إضافة كمية البند.
|   - purchase_price_before: سعر الشراء قبل تحديثه بالبند ده.
|   - average_cost_before: متوسط التكلفة قبل تحديثه بالبند ده.
|
| الهدف: لو حبينا نعدّل فاتورة المشتريات دي بعدين، نقدر "نرجع" تكلفة/سعر
| شراء المنتج لقيمتها الصح قبل الفاتورة دي بالظبط (مش نعيد حساب المتوسط
| المرجّح من الآخر، اللي ممكن يجيب نتيجة مختلفة لو حصل أي تغيير تاني في
| الوسط). المخزون (stock_quantity) نفسه بيترجع بطرح الكمية فقط (delta)
| مش بالـ snapshot، عشان لو حصلت عمليات بيع على المنتج بعد الفاتورة دي
| تفضل متسجلة صح - راجعي Purchase::isEditable() و
| PurchaseController::reversePurchaseEffects() لتفاصيل الاستخدام.
|
| الأعمدة nullable عمدًا: أي بند شراء قديم اتسجل قبل الميجريشن ده مش هيكون
| ليه قيم هنا، وده بالظبط اللي بنستخدمه في isEditable() عشان نمنع تعديل
| فواتير قديمة مفيش عندها البيانات الكافية للإرجاع الآمن.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('stock_before', 12, 2)->nullable()->after('returned_quantity');
            $table->decimal('purchase_price_before', 12, 2)->nullable()->after('stock_before');
            $table->decimal('average_cost_before', 12, 2)->nullable()->after('purchase_price_before');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['stock_before', 'purchase_price_before', 'average_cost_before']);
        });
    }
};
