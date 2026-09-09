<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول وسيط many-to-many بين منتج "أساسي" ومنتجاته "البديلة" - ميزة
 * "البدائل" في مودال اختيار منتج (الفواتير/المشتريات): زرار يفتح قايمة
 * منتجات بديلة ممكن تتباع بدل المنتج الأصلي لو مش متوفر.
 *
 * جدول منفصل (مش عمود FK بسيط على products) لإن نفس المنتج البديل ممكن
 * يبقى بديل لأكتر من منتج أساسي واحد في نفس الوقت (many-to-many حقيقي)،
 * وده مختلف عن عمود parent_product_id القديم الموجود بالفعل في جدول
 * products (كان مخصص لعلاقة one-to-many بسيطة ومحدودة، ومحدش بيستخدمه
 * فعليًا دلوقتي).
 *
 * منتج من غير أي صف هنا (لا كأساسي ولا كبديل) يفضل "أساسي" بشكل افتراضي
 * - مفيش عمود "type" منفصل على المنتج نفسه، الحالة مستنتجة من وجود/عدم
 * وجود صفوف في الجدول ده (شوف Product::alternates()/primaryProducts()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_alternates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('alternate_product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            // منتج مايتكررش كبديل لنفس المنتج الأساسي مرتين
            $table->unique(['primary_product_id', 'alternate_product_id'], 'product_alternates_unique_pair');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_alternates');
    }
};
