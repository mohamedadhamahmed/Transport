<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * الموديل CreditTransaction بيستخدم جدول "credittransaction" (مفرد - الاسم
     * القديم من النظام الأصلي)، بس الـ migration القديمة بتعمل "credittransactions"
     * (جمع). على ويندوز (XAMPP) الجدول المفرد كان موجود من الداتا القديمة، لكن
     * على سيرفر لينكس بقاعدة جديدة (migrate:fresh) مكانش موجود، فأي فاتورة أو
     * سند كان بيقع بخطأ "Table credittransaction doesn't exist".
     *
     * الحل: لو الجدول المفرد مش موجود والجمع موجود -> نغيّر الاسم للمفرد.
     * لو المفرد موجود أصلًا (زي جهاز التطوير) مفيش أي تغيير.
     */
    public function up(): void
    {
        if (!Schema::hasTable('credittransaction') && Schema::hasTable('credittransactions')) {
            Schema::rename('credittransactions', 'credittransaction');
        }
    }

    public function down(): void
    {
        // مفيش رجوع - الاسم المفرد هو اللي الكود بيستخدمه.
    }
};
