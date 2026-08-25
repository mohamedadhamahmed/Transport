<?php

namespace App\Http\Controllers;

use App\Models\DraftInvoice;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| DraftInvoiceController
|--------------------------------------------------------------------------
| شاشة "المسودات السابقة" - قايمة الفواتير اللي اتحفظت "كمسودة" (لسه ملهاش
| رقم فاتورة رسمي)، مع زرار "فتح" بيودّي لصفحة إنشاء الفاتورة وبيملى كل
| الحقول منها (العميل، طريقة الدفع، البنود...)، وزرار "حذف" لو عايزة
| تشيليها نهائي.
*/
class DraftInvoiceController extends Controller
{
    public function index()
    {
        $drafts = DraftInvoice::with(['customer', 'branch'])
            ->where('branch_id', Auth::user()->branch_id)
            ->latest()
            ->get();

        return view('invoices.drafts', compact('drafts'));
    }

    public function destroy(DraftInvoice $draft)
    {
        $draft->delete();

        return redirect()->route('invoices.drafts.index')
            ->with('success', __('invoices.draft_deleted_successfully'));
    }
}
