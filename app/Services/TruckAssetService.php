<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * تسجيل الشاحنات المملوكة للشركة في شجرة الحسابات:
 *   الأصول الثابتة (74)  ←  الشاحنات (حساب أب)  ←  شاحنة <رقم اللوحة>
 * رصيد حساب الشاحنة = قيمتها (رصيد افتتاحي مدين، بنفس طريقة إنشاء
 * حساب برصيد افتتاحي من شاشة الحسابات).
 */
class TruckAssetService
{
    public const FIXED_ASSETS_ACCOUNT_ID = 74;
    public const ORGINAL_TYPE = 'truck';

    public function sync(Truck $truck): void
    {
        $account = $truck->financial_account_id ? FinancialAccount::find($truck->financial_account_id) : null;
        $account ??= FinancialAccount::where('orginal_type', self::ORGINAL_TYPE)->where('orginal_id', $truck->id)->first();

        $value = $truck->ownership === 'owned' ? round((float) $truck->purchase_value, 2) : 0.0;

        if (!$account && $value <= 0) {
            return;
        }

        if (!$account) {
            $parent = $this->trucksParent();
            if (!$parent) {
                return; // شجرة الحسابات مش متجهزة (مفيش حساب أصول ثابتة)
            }

            $account = FinancialAccount::create([
                'name' => 'شاحنة ' . $truck->plate_number . ($truck->name ? ' - ' . $truck->name : ''),
                'account_type' => $parent->account_category_id,
                'account_category_id' => $parent->account_category_id,
                'parent_account_number' => $parent->id,
                'account_number' => $this->nextNumber($parent),
                'start_balance' => $value,
                'current_balance' => $value,
                'added_by' => Auth::id(),
                'com_code' => 1,
                'date' => $truck->purchase_date ?? Carbon::now('Asia/Riyadh'),
                'active' => 1,
                'is_parent' => 0,
                'orginal_id' => $truck->id,
                'orginal_type' => self::ORGINAL_TYPE,
                'debtor_current' => $value,
                'creditor_current' => 0,
                'notes' => 'أصل ثابت - شاحنة (من قسم الشاحنات)',
            ]);
        } else {
            // تعديل القيمة: نحرّك الرصيد بالفرق بس، عشان أي حركات تانية
            // على الحساب (إهلاك مثلاً) متتلخبطش.
            $delta = $value - (float) $account->start_balance;
            $account->update([
                'name' => 'شاحنة ' . $truck->plate_number . ($truck->name ? ' - ' . $truck->name : ''),
                'start_balance' => $value,
                'current_balance' => (float) $account->current_balance + $delta,
                'debtor_current' => (float) $account->debtor_current + $delta,
                'active' => $value > 0 ? 1 : 0,
            ]);
        }

        if ((int) $truck->financial_account_id !== (int) $account->id) {
            $truck->forceFill(['financial_account_id' => $account->id])->saveQuietly();
        }
    }

    /** حساب "الشاحنات" تحت الأصول الثابتة (بيتعمل أول مرة لو مش موجود) */
    public function trucksParent(): ?FinancialAccount
    {
        $fixed = FinancialAccount::find(self::FIXED_ASSETS_ACCOUNT_ID)
            ?? FinancialAccount::where('name', 'like', '%الثابت%')->where('is_parent', 1)->first();

        if (!$fixed) {
            return null;
        }

        return FinancialAccount::where('parent_account_number', $fixed->id)->where('name', 'الشاحنات')->first()
            ?? FinancialAccount::create([
                'name' => 'الشاحنات',
                'name_en' => 'Trucks',
                'account_type' => $fixed->account_category_id,
                'account_category_id' => $fixed->account_category_id,
                'parent_account_number' => $fixed->id,
                'account_number' => $this->nextNumber($fixed),
                'start_balance' => 0,
                'current_balance' => 0,
                'added_by' => Auth::id(),
                'com_code' => 1,
                'date' => Carbon::now('Asia/Riyadh'),
                'active' => 1,
                'is_parent' => 1,
            ]);
    }

    private function nextNumber(FinancialAccount $parent): string
    {
        $max = FinancialAccount::where('parent_account_number', $parent->id)->max('account_number');

        return $max ? (string) ((int) $max + 1) : ((string) $parent->account_number) . '01';
    }
}
