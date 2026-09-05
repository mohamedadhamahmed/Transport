<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * بيانات جاهزة لاختبار إرسال "فاتورة ضريبية مبسطة" للزكاة بسرعة: عميل
 * نقدي (من غير رقم ضريبي - وده بالظبط اللي بيخلي ZatcaController يحدد
 * نوع الفاتورة 'simplified' مش 'standard'، راجع performSend()) + منتج
 * واحد جاهز للإضافة على الفاتورة فورًا.
 *
 * لو "عميل نقدي" موجود بالفعل (اتعمل من ChartOfAccountsSeeder) بنستخدمه
 * زي ما هو من غير أي تعديل - العميل ده مفيش عنده tax_number أصلاً.
 *
 * السيدر ده مش متضاف في DatabaseSeeder الأساسي عمدًا (بيانات اختبار مش
 * بيانات إنتاج) - شغّله لما تحتاجه بـ:
 *   php artisan db:seed --class=CashSaleTestDataSeeder
 */
class CashSaleTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('name', 'الرياض')->first();

        if (! $branch) {
            $this->command?->warn('فرع "الرياض" مش موجود - شغّل BranchSeeder الأول.');
            return;
        }

        $customer = Customer::firstOrCreate(
            ['name' => 'عميل نقدي'],
            [
                'phone' => '0000000000',
                'credit_limit' => 0,
                'balance' => 0,
                'grace_period_days' => 0,
                'opening_balance' => 0,
                'notes' => 'عميل افتراضي للمبيعات النقدية (من غير رقم ضريبي) - مناسب لاختبار الفاتورة الضريبية المبسطة.',
                // tax_number مقصود سيبها فاضية: طولها لازم يبقى 15 رقم بالظبط
                // عشان ZatcaController يعتبر الفاتورة "standard"، وأي حاجة
                // غير كده (فاضية أو أقل من 15 رقم) بترجع 'simplified' تلقائيًا.
            ]
        );

        Product::firstOrCreate(
            ['name' => 'منتج اختبار الفاتورة المبسطة', 'branch_id' => $branch->id],
            [
                'code' => 'TEST-SIMPLIFIED-001',
                'purchase_price' => 50,
                'sale_price' => 100,
                'stock_quantity' => 100,
                'status' => 'active',
                'tax_value' => 0.15, // 15% ضريبة قيمة مضافة (القيمة نسبة عشرية، مش نسبة مئوية)
                'unit' => 'piece',
                'low_stock_alert_quantity' => 10,
            ]
        );

        $this->command?->info('تم تجهيز "عميل نقدي" ومنتج اختبار لفاتورة ضريبية مبسطة على فرع الرياض.');
    }
}
