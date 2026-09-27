<?php

namespace App\Support;

/**
 * أنواع العمليات (عمود operation_type في جدول credittransaction).
 *
 * الأرقام من 1 لـ 4 دي مش أنا اللي حطيتها - دي القيم اللي فعليًا
 * مستخدمة في الكود بتاعك بالفعل (PurchaseController -> 3، الشحن
 * والمورد والمخزون والضريبة كلهم operation_type = 3، و
 * PurchaseReturnController -> self::OPERATION_TYPE = 4). لاحظ إن ده
 * بيخالف تعليق الميجريشن القديمة اللي كانت مكتوب فيها "1=مبيعات،
 * 2=مشتريات، 3=سند قبض..." - يعني فيه تعارض بين التعليق والكود
 * الفعلي، وأنا هنا ماشي مع الكود الفعلي الشغال مش التعليق.
 *
 * القيم الفعلية المؤكدة من الكود الحالي:
 *   1 = فاتورة مبيعات   (InvoiceController)
 *   2 = مرتجع مبيعات    (InvoiceReturnController)
 *   3 = فاتورة مشتريات  (PurchaseController)
 *   4 = مرتجع مشتريات   (PurchaseReturnController)
 *
 * والأرقام الجديدة اللي طلبتيها بالظبط لقسم الحسابات والقيود (5 و6
 * متسيبوش فاضيين عن قصد - محجوزين حسب طلبك):
 *   7 = سند قبض
 *   8 = سند صرف
 *   9 = قيد يومية (يدوي)
 *   10 = قيد افتتاحي (محجوز لاستخدام مستقبلي - مش مستخدم في الشاشات دي حاليًا)
 *
 * والأرقام الجديدة الخاصة بقسم الموارد البشرية (EmployeeLoanController/
 * EndOfServiceController لاحقًا) - أرقام جديدة كليًا مش مستخدمة في أي
 * حتة تانية في المشروع وقت كتابة الكود ده:
 *   11 = صرف سلفة/عهدة لموظف
 *   12 = تسوية/استرداد سلفة أو عهدة
 *   13 = قيد مكافأة نهاية الخدمة
 *   14 = ترحيل رواتب شهرية (PayrollController@postMonth)
 *   15 = فاتورة نقليات (TransportInvoiceController)
 *   16 = إشعار دائن نقليات (TransportCreditNoteController)
 */
class OperationType
{
    public const SALE_INVOICE = 1;

    public const SALE_RETURN = 2;

    public const PURCHASE_INVOICE = 3;

    public const PURCHASE_RETURN = 4;

    public const RECEIPT_VOUCHER = 7;

    public const PAYMENT_VOUCHER = 8;

    public const JOURNAL_ENTRY = 9;

    public const OPENING_ENTRY = 10;

    public const EMPLOYEE_LOAN = 11;

    public const EMPLOYEE_LOAN_SETTLEMENT = 12;

    public const END_OF_SERVICE = 13;

    public const PAYROLL = 14;

    public const TRANSPORT_INVOICE = 15;

    public const TRANSPORT_CREDIT_NOTE = 16;

    public const LABELS = [
        self::SALE_INVOICE => 'فاتورة مبيعات',
        self::SALE_RETURN => 'مرتجع مبيعات',
        self::PURCHASE_INVOICE => 'فاتورة مشتريات',
        self::PURCHASE_RETURN => 'مرتجع مشتريات',
        self::RECEIPT_VOUCHER => 'سند قبض',
        self::PAYMENT_VOUCHER => 'سند صرف',
        self::JOURNAL_ENTRY => 'قيد يومية',
        self::OPENING_ENTRY => 'قيد افتتاحي',
        self::EMPLOYEE_LOAN => 'صرف سلفة/عهدة لموظف',
        self::EMPLOYEE_LOAN_SETTLEMENT => 'تسوية سلفة/عهدة',
        self::END_OF_SERVICE => 'مكافأة نهاية الخدمة',
        self::PAYROLL => 'ترحيل رواتب شهرية',
        self::TRANSPORT_INVOICE => 'فاتورة نقليات',
        self::TRANSPORT_CREDIT_NOTE => 'إشعار دائن نقليات',
    ];

    public static function label(?int $type): ?string
    {
        return $type !== null ? (self::LABELS[$type] ?? null) : null;
    }
}
