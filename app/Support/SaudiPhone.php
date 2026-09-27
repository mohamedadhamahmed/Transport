<?php

namespace App\Support;

/**
 * تنظيف والتحقق من أرقام الجوال السعودية.
 * الصيغة المعتمدة اللي بتتخزن: 05XXXXXXXX (10 أرقام).
 * بيقبل وقت الإدخال: أرقام عربية/فارسية، مسافات وشرط، +966 / 00966 / 966 / 5XXXXXXXX.
 */
class SaudiPhone
{
    public static function normalize(?string $phone): string
    {
        $phone = (string) $phone;
        $phone = strtr($phone, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($phone, '+966')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '00966')) {
            $phone = '0' . substr($phone, 5);
        } elseif (str_starts_with($phone, '966') && strlen($phone) === 12) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '5') && strlen($phone) === 9) {
            $phone = '0' . $phone;
        }

        return str_replace('+', '', $phone);
    }

    public static function isValid(?string $phone): bool
    {
        return (bool) preg_match('/^05\d{8}$/', self::normalize($phone));
    }

    /** رقم دولي للواتساب: 9665XXXXXXXX */
    public static function international(?string $phone): ?string
    {
        $p = self::normalize($phone);

        return self::isValid($p) ? '966' . substr($p, 1) : null;
    }

    public static function whatsappUrl(?string $phone, string $text = ''): ?string
    {
        $intl = self::international($phone);
        if (!$intl) {
            return null;
        }

        return 'https://wa.me/' . $intl . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }

    public static function telUrl(?string $phone): ?string
    {
        return self::isValid($phone) ? 'tel:' . self::normalize($phone) : null;
    }
}
