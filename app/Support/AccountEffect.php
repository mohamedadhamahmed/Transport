<?php

namespace App\Support;

use App\Models\FinancialAccount;

/**
 * منطق تطبيق/عكس أثر مبلغ مدين أو دائن على حساب - نفس الحساب
 * المستخدم في VoucherController وJournalEntryController (كان
 * مكرر جوه كل كونترولر لحاله، بعد إضافة التعديل (edit/update) بقى
 * محتاج يتعاد استخدامه في أكتر من حتة فاتّجمّع هنا في كلاس واحد
 * عشان مفيش نسختين ممكن يختلفوا مع بعض بالغلط لاحقًا).
 */
class AccountEffect
{
    /**
     * يضيف مبلغ مدين/دائن جديد على حساب ويحدّث current_balance حسب
     * طبيعته (FinancialAccount::balanceAfter) - بيرجع الرصيد الجديد.
     */
    public static function apply(FinancialAccount $account, float $debit, float $credit): float
    {
        $newDebtorTotal = (float) $account->debtor_current + $debit;
        $newCreditorTotal = (float) $account->creditor_current + $credit;
        $newBalance = $account->balanceAfter($newDebtorTotal, $newCreditorTotal);

        $account->update([
            'current_balance' => $newBalance,
            'debtor_current' => $newDebtorTotal,
            'creditor_current' => $newCreditorTotal,
        ]);

        return $newBalance;
    }

    /**
     * عكس apply() بالظبط - بيشيل مبلغ مدين/دائن كان اتسجل قبل كده على
     * الحساب (مستخدمة في تعديل سند/قيد موجود عشان نرجّع أثره القديم
     * قبل ما نطبّق البيانات الجديدة).
     */
    public static function reverse(FinancialAccount $account, float $debit, float $credit): float
    {
        return self::apply($account, -$debit, -$credit);
    }
}
