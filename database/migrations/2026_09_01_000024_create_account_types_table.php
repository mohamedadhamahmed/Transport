<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * جدول "أنواع الحسابات" (شاشة إدارية جديدة): الفروع الخمسة الرئيسية
 * لشجرة الحسابات بمعناها المحاسبي الحقيقي - الأصول / الخصوم /
 * الإيرادات / المصروفات / حقوق الملكية.
 *
 * ⚠️ ده جدول وحقل مستقلين تمامًا عن عمود account_type الموجود بالفعل
 * على financialaccount. الحقل القديم ده مُستخدم فعليًا بقيم 1-4 لغرض
 * مختلف كليًا (نوع الكيان المرتبط بالحساب - مش تصنيفه المحاسبي):
 *   - account_type=1: حساب عميل (InvoiceController/DeliveryNoteController)
 *     أو حساب مورد (PurchaseController@quickStoreSupplier - نفس القيمة!).
 *   - account_type=2: حساب مورد (SupplierController@store - قيمة مختلفة
 *     عن PurchaseController لنفس الغرض! تضارب موجود بالفعل في الكود
 *     الأصلي، مش من صنعي).
 *   - account_type=3: حساب موظف (HrAccountService::ACCOUNT_TYPE_EMPLOYEE).
 *   - account_type=4: حساب عام/تصنيفي (HrAccountService::ACCOUNT_TYPE_HR_GENERAL) -
 *     مُستخدم لكل حسابات المجموعات والفروع الرئيسية بغض النظر عن
 *     طبيعتها الحقيقية (حتى فرع "المصروفات" نفسه بياخد account_type=4).
 *
 * لو استخدمنا نفس العمود بمعنى جديد (1=أصول...5=حقوق ملكية) كان
 * هيحصل تعارض مباشر مع كل الأماكن دي، خصوصًا توليد رقم الحساب التالي
 * اللي بيفلتر بـ account_type. فبدل كده، عمود جديد تمامًا
 * (account_category_id على financialaccount - راجع الميجريشن اللي
 * بعد دي) بيربط كل حساب بنوعه من هنا.
 *
 * is_protected: الأنواع الخمسة دي أساسية ومربوطة فعليًا بالفروع
 * الرئيسية الخمسة في شجرة الحسابات (راجع ميجريشن
 * 2026_09_01_000023) - فمن شاشة "أنواع الحسابات" ينفع تتفعّل/تتعطّل
 * بس، مش تتمسح.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->boolean('is_protected')->default(false);
            $table->timestamps();
        });

        $now = now();

        DB::table('account_types')->insert([
            ['id' => 1, 'name' => 'الأصول', 'active' => true, 'is_protected' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'الخصوم', 'active' => true, 'is_protected' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'الإيرادات', 'active' => true, 'is_protected' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'المصروفات', 'active' => true, 'is_protected' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'حقوق الملكية', 'active' => true, 'is_protected' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
