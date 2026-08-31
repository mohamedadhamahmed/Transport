<?php

namespace App\Support;

/**
 * أنواع العمليات (عمود operation_type في جدول credittransaction).
 *
 * الأرقام من 1 لـ 4 دي مش أنا اللي حطيتها - دي القيم اللي فعليًا
 * مستخدمة في الكود بتاعك بالفعل (PurchaseController -> 3، الشحن
 * والمورد والمخزون والضريبة كلهم operation_type = 3، و
 * PurchaseReturnController -> self::OPERATION_TYPE = 4). لاحظي إن ده
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

    public const LABELS = [
        self::SALE_INVOICE => 'فاتورة مبيعات',
        self::SALE_RETURN => 'مرتجع مبيعات',
        self::PURCHASE_INVOICE => 'فاتورة مشتريات',
        self::PURCHASE_RETURN => 'مرتجع مشتريات',
        self::RECEIPT_VOUCHER => 'سند قبض',
        self::PAYMENT_VOUCHER => 'سند صرف',
        self::JOURNAL_ENTRY => 'قيد يومية',
        self::OPENING_ENTRY => 'قيد افتتاحي',
    ];

    public static function label(?int $type): ?string
    {
        return $type !== null ? (self::LABELS[$type] ?? null) : null;
    }
}
