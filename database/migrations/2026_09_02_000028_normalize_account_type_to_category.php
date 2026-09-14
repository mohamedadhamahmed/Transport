<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * إعادة استخدام العمود القديم account_type (بدل ما نستبدله بعمود جديد)
 * عشان يحمل نفس معنى account_category_id بالظبط (1=الأصول، 2=الخصوم،
 * 3=الإيرادات، 4=المصروفات، 5=حقوق الملكية) - ده طلب صريح من العميل
 * عشان يُستخدم في ميزان المراجعة والقوائم المالية، بعد ما اتفقنا إن
 * القيم الحالية بتاعته متضاربة (كانت بتتحمل بمعاني تانية: نوع العميل/
 * المورد/الموظف - راجع تعليقات InvoiceController@quickStoreCustomer و
 * PurchaseController@quickStoreSupplier و SupplierController@store و
 * DeliveryNoteController و HrAccountService القديمة قبل هذه الميجريشن).
 *
 * القيمة الوحيدة اللي معتمد عليها فعليًا وهنسيبها زي ما هي هي
 * FinancialAccount::isCreditNormal() - دي بتستخدم orginal_type مش
 * account_type، فمش متأثرة بالتطبيع ده خالص.
 *
 * الاستراتيجية (بنفس أسلوب resolveNearestAncestorCategoryId المُستخدم في
 * ميجريشن 2026_09_02_000027):
 *   1. أي حساب معاه account_category_id فعلاً (والغالبية العظمى كده بعد
 *      BFS ميجريشن 2026_09_01_000025) → account_type بياخد نفس القيمة
 *      دي مباشرة.
 *   2. أي حساب لسه account_category_id فاضي عنده (حساب اتعمل بعد
 *      000025 من غير ما يتوسم وقتها، أو جذر يتيم) → بندوّر على أقرب جد
 *      ليه معاه تصنيف، ونحط نفس القيمة في العمودين (account_category_id
 *      و account_type) مع بعض.
 *   3. أي حساب لسه من غير أي تصنيف حتى بعد الخطوتين دول (مفيش جد ليه
 *      تصنيف خالص - يعني جذر منفصل تمامًا عن الفروع الخمسة الرئيسية) →
 *      بيتسجل تحذير في اللوج وبيتسيب زي ما هو من غير تخمين، عشان
 *      يتراجع يدويًا من شاشة "شجرة الحسابات".
 *
 * بعد الميجريشن دي، أي كود بيعمل حساب جديد لازم يحط account_type بنفس
 * قيمة account_category_id مباشرة (مش قيمة تانية ثابتة) - راجع التعديلات
 * المصاحبة في InvoiceController/PurchaseController/SupplierController/
 * DeliveryNoteController/AccountController/HrAccountService.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // الخطوة 1: نسخ مباشر لأي حساب معاه تصنيف بالفعل.
        DB::table('financialaccount')
            ->whereNotNull('account_category_id')
            ->update([
                'account_type' => DB::raw('account_category_id'),
                'updated_at' => $now,
            ]);

        // الخطوة 2: أي حساب لسه من غير تصنيف - ندوّر على أقرب جد ليه
        // تصنيف ونطبّقه على العمودين مع بعض.
        $unresolved = [];

        $accountsWithoutCategory = DB::table('financialaccount')
            ->whereNull('account_category_id')
            ->get(['id', 'parent_account_number']);

        foreach ($accountsWithoutCategory as $account) {
            $categoryId = $this->resolveNearestAncestorCategoryId($account->parent_account_number);

            if ($categoryId === null) {
                $unresolved[] = $account->id;

                continue;
            }

            DB::table('financialaccount')->where('id', $account->id)->update([
                'account_category_id' => $categoryId,
                'account_type' => $categoryId,
                'updated_at' => $now,
            ]);
        }

        if (!empty($unresolved)) {
            Log::warning('normalize_account_type_to_category migration: accounts left without any classification (no ancestor category found) - review manually from the accounts tree screen. IDs: ' . implode(', ', $unresolved));
        }
    }

    public function down(): void
    {
        // ميجريشن تطبيع بيانات على عمود قديم موجود بالفعل (مش عمود جديد
        // اتعمل هنا) - مفيش down آمن ليها لإن القيم القديمة المتضاربة
        // بتاعت account_type (نوع عميل/مورد/موظف) ضاعت فعليًا من وقت ما
        // account_category_id بقى مصدرها. لو محتاج ترجع، الأنسب نسخة
        // احتياطية من قاعدة البيانات قبل تشغيل الميجريشن دي.
    }

    private function resolveNearestAncestorCategoryId(?int $parentId): ?int
    {
        $guard = 0;

        while ($parentId !== null && $guard < 50) {
            $parent = DB::table('financialaccount')->where('id', $parentId)->first();
            if (!$parent) {
                return null;
            }

            if ($parent->account_category_id !== null) {
                return (int) $parent->account_category_id;
            }

            $parentId = $parent->parent_account_number;
            $guard++;
        }

        return null;
    }
};
