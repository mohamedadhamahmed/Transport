<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * إعادة تسمية الجداول القديمة (بالاسم المُضلِّل withoud_tax) إلى أسماء
 * نضيفة وواضحة، بدون فقدان أي بيانات - Schema::rename() بيغيّر اسم
 * الجدول فقط ويحافظ على كل الصفوف والأعمدة والعلاقات زي ما هي.
 *
 * delivery_to_customer_withoud_tax_invoices  →  delivery_note
 * sales_withoud_taxes                        →  delivery_note_item
 *
 * ⚠️ شغّل الـ migration دي فقط لو عندك الجداول القديمة بالفعل (بأسمائها
 * الأصلية) في قاعدة بياناتك - وهو وضعك الحالي بالظبط بما إن عندك بيانات
 * حقيقية مسجلة فعلاً. لو مشروع جديد من الصفر، الجداول هتتاخد بالاسم
 * الجديد مباشرة من migrations رقم 000001 و 000002 المُحدَّثة، ومتحتاجش
 * تشغّل الـ migration دي خالص.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('delivery_to_customer_withoud_tax_invoices') && !Schema::hasTable('delivery_note')) {
            Schema::rename('delivery_to_customer_withoud_tax_invoices', 'delivery_note');
        }

        if (Schema::hasTable('sales_withoud_taxes') && !Schema::hasTable('delivery_note_item')) {
            Schema::rename('sales_withoud_taxes', 'delivery_note_item');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('delivery_note') && !Schema::hasTable('delivery_to_customer_withoud_tax_invoices')) {
            Schema::rename('delivery_note', 'delivery_to_customer_withoud_tax_invoices');
        }

        if (Schema::hasTable('delivery_note_item') && !Schema::hasTable('sales_withoud_taxes')) {
            Schema::rename('delivery_note_item', 'sales_withoud_taxes');
        }
    }
};
