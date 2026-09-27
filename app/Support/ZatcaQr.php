<?php

namespace App\Support;

/**
 * QR الفاتورة الإلكترونية - المرحلة الأولى (ZATCA Phase 1 / TLV Base64).
 *
 * الـ QR = Base64 لسلسلة TLV فيها 5 حقول بالظبط:
 *   1 اسم البائع، 2 الرقم الضريبي (15 رقم)، 3 وقت الإصدار (ISO 8601)،
 *   4 الإجمالي شامل الضريبة، 5 قيمة الضريبة.
 *
 * كل حقل = [رقم الحقل بايت واحد][الطول بالبايت بايت واحد][القيمة UTF-8].
 * الطول لازم يكون بالبايت (strlen) مش بعدد الحروف - الاسم العربي
 * الحرف فيه 2 بايت.
 *
 * ملاحظات على الكود القديم:
 *  - الحقول 6-9 بتتبعت فاضية (طول 0) زي الكود القديم.
 *  - الوقت لازم يكون وقت إصدار الفاتورة، مش وقت الطباعة (now()).
 *  - المبالغ بمنزلتين عشريتين (2800.00).
 */
class ZatcaQr
{
    public static function phaseOne(string $seller, string $vatNumber, string $timestamp, float $total, float $vat): string
    {
        $fields = [
            1 => $seller,
            2 => $vatNumber,
            3 => $timestamp,
            4 => number_format($total, 2, '.', ''),
            5 => number_format($vat, 2, '.', ''),
            // الحقول 6-9 فاضية (نفس شكل الكود القديم المعتمد عندكم)
            6 => '',
            7 => '',
            8 => '',
            9 => '',
        ];

        $tlv = '';
        foreach ($fields as $tag => $value) {
            $value = (string) $value;
            // الطول بايت واحد (أقصى 255) - نقص الاسم لو أطول من كده من غير ما نكسر حرف عربي
            if (strlen($value) > 255) {
                $value = mb_strcut($value, 0, 255, 'UTF-8');
            }
            $tlv .= chr($tag) . chr(strlen($value)) . $value;
        }

        return base64_encode($tlv);
    }

    /** فك QR للمراجعة: [رقم الحقل => القيمة] */
    public static function decode(string $base64): array
    {
        $bin = base64_decode($base64, true) ?: '';
        $out = [];
        $i = 0;
        while ($i + 2 <= strlen($bin)) {
            $tag = ord($bin[$i]);
            $len = ord($bin[$i + 1]);
            $out[$tag] = substr($bin, $i + 2, $len);
            $i += 2 + $len;
        }

        return $out;
    }
}
