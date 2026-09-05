<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * لحد دلوقتي postMonth كان بيفرض اختيار حساب خزينة إجباري ويصرف صافي
 * الراتب منه فورًا وقت الترحيل - يعني مفيش دعم لحالة "الراتب اتأخر"
 * (اعتراف بالمصروف/الالتزام دلوقتي، والدفع الفعلي بعدين). دلوقتي
 * treasury_account_id بقى اختياري فعليًا (كان أصلاً nullable في
 * الجدول - العمود ده كان بس متفروض إجباري من الكونترولر): لو الأدمن
 * سابه فاضي وقت الترحيل، صافي الراتب بيتقيّد على حساب "مستحقات رواتب
 * الموظفين" (HrAccountService::payrollPayableAccountId) بدل الخزينة،
 * وبعدين تقدر تسدده من شاشة الرواتب لما الفلوس تتوفر (راجع
 * PayrollController@payAccruedPosting).
 *
 *  - funding_source: 'treasury' (اتصرف فورًا وقت الترحيل - نفس السلوك
 *    القديم) أو 'payable' (اتقيّد كمستحق لسه ما اتصرفش).
 *  - paid_at: تاريخ الدفع الفعلي - فاضي معناه لسه مستحق وما اتسددش.
 *    الترحيلات القديمة (قبل الميزة دي) كانت بتتدفع فورًا وقت إنشائها،
 *    فبنعمل لها backfill بـ created_at عشان تفضل متسقة مع المنطق الجديد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_postings', function (Blueprint $table) {
            $table->string('funding_source', 20)->default('treasury')->after('treasury_account_id');
            $table->timestamp('paid_at')->nullable()->after('total_net');
        });

        DB::table('payroll_postings')->whereNull('paid_at')->update([
            'paid_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('payroll_postings', function (Blueprint $table) {
            $table->dropColumn(['funding_source', 'paid_at']);
        });
    }
};
