<?php

namespace App\Http\Controllers;

use App\Models\AccountType;

/**
 * شاشة "أنواع الحسابات" - عرض/تفعيل/تعطيل الفروع الخمسة الرئيسية
 * لشجرة الحسابات (الأصول/الخصوم/الإيرادات/المصروفات/حقوق الملكية).
 * الأنواع دي أساسية ومربوطة فعليًا بالفروع الرئيسية في شجرة الحسابات
 * (راجع ميجريشن 2026_09_01_000024/000025) - فمفيش إضافة أو حذف من
 * هنا، بس تفعيل/تعطيل. تعطيل نوع بيمنعه من الظهور كخيار عند إنشاء/
 * تعديل حساب جديد (راجع AccountController::create/edit) من غير ما
 * يأثر على الحسابات المرتبطة بيه بالفعل.
 */
class AccountTypeController extends Controller
{
    public function index()
    {
        $this->authorize('accounts.view');

        $accountTypes = AccountType::orderBy('id')->get();

        return view('account-types.index', compact('accountTypes'));
    }

    public function toggleActive(AccountType $accountType)
    {
        $this->authorize('accounts.edit');

        $accountType->update(['active' => ! $accountType->active]);

        return back()->with('success', $accountType->active
            ? __('account_types.activated_successfully')
            : __('account_types.deactivated_successfully'));
    }
}
