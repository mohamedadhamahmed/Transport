<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $table = 'financialaccount';

    // السماح لكل الحقول بالإدخال والتعديل دفعة واحدة
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'is_parent' => 'boolean',
    ];

    /**
     * الحساب الأب في شجرة الحسابات (لو موجود).
     */
    public function parentAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'parent_account_number');
    }

    /**
     * الحسابات الفرعية تحت الحساب ده مباشرة.
     */
    public function childAccounts()
    {
        return $this->hasMany(FinancialAccount::class, 'parent_account_number');
    }

    /**
     * كل حركات القيد (credittransaction) المسجلة على الحساب ده.
     */
    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class, 'customer_id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /**
     * طبيعة الحساب (مدين/دائن) لحساب اتجاه current_balance في القيد
     * اليومي وسندات القبض/الصرف وكشف الحساب.
     *
     * القاعدة دي معمولة بناءً على orginal_type بدل account_type، لإن
     * account_type=1 في بياناتك الفعلية مستخدم للعميل والمورد مع
     * بعض (PurchaseController@quickStoreSupplier و
     * InvoiceController@quickStoreCustomer بيدوا الاتنين نفس القيمة)،
     * فمينفعش نعتمد عليه لتحديد مدين/دائن. orginal_type=2 (مورد) هو
     * الحالة الوحيدة المؤكدة اللي طبيعتها دائن؛ أي حاجة تانية (عميل،
     * أو حساب عام زي خزينة/بنك/مخزون/ضريبة من غير orginal_type خالص)
     * بتتعامل كمدين افتراضيًا.
     *
     * ملحوظة: ده معناه إن حساب "الإيرادات" (parent_account_number=112
     * في PurchaseController/InvoiceController) هيتعامل كمدين برضه
     * لإنه مالوش orginal_type - وده تبسيط معروف ومقصود (مش قاعدة
     * محاسبية كاملة لكل نوع حساب)، مش خطأ سهيت عنه.
     */
    public function isCreditNormal(): bool
    {
        return (int) $this->orginal_type === 2;
    }

    /**
     * يحسب current_balance الصحيح بعد إضافة مبلغ مدين/دائن جديد،
     * بناءً على طبيعة الحساب (isCreditNormal). يفترض إن debtor_current
     * و creditor_current لسه معملهاش تحديث للمبلغ الجديد ده (يعني
     * بتتحسب من القيم القديمة قبل أي increment).
     */
    public function balanceAfter(float $newDebtorTotal, float $newCreditorTotal): float
    {
        return $this->isCreditNormal()
            ? $newCreditorTotal - $newDebtorTotal
            : $newDebtorTotal - $newCreditorTotal;
    }
}
