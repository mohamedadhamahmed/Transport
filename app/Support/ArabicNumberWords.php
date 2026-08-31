<?php

namespace App\Support;

/**
 * تحويل مبلغ رقمي لصيغة الكتابة بالحروف (عربي) - مستخدم في طباعة
 * سندات القبض والصرف زي السندات الورقية التقليدية ("المبلغ وقدره
 * ... فقط لا غير"). ده تبسيط عملي شائع الاستخدام في أنظمة محاسبية
 * عربية كتير - مش قواعد نحوية كاملة 100% لكل حالة (زي التذكير/التأنيث
 * أو صيغة المثنى في كل الحالات)، لكنه بيغطي الأرقام لحد المليارات
 * وبيدي نتيجة مقروءة وصحيحة عمليًا.
 */
class ArabicNumberWords
{
    private const ONES = [
        '', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة',
    ];

    private const TEN_TO_NINETEEN = [
        10 => 'عشرة', 11 => 'أحد عشر', 12 => 'اثنا عشر', 13 => 'ثلاثة عشر',
        14 => 'أربعة عشر', 15 => 'خمسة عشر', 16 => 'ستة عشر', 17 => 'سبعة عشر',
        18 => 'ثمانية عشر', 19 => 'تسعة عشر',
    ];

    private const TENS = [
        2 => 'عشرون', 3 => 'ثلاثون', 4 => 'أربعون', 5 => 'خمسون',
        6 => 'ستون', 7 => 'سبعون', 8 => 'ثمانون', 9 => 'تسعون',
    ];

    private const HUNDREDS = [
        1 => 'مائة', 2 => 'مئتان', 3 => 'ثلاثمائة', 4 => 'أربعمائة',
        5 => 'خمسمائة', 6 => 'ستمائة', 7 => 'سبعمائة', 8 => 'ثمانمائة', 9 => 'تسعمائة',
    ];

    /**
     * "المبلغ وقدره: [كذا] ريال سعودي [و كذا هللة] فقط لا غير".
     */
    public static function amountToWords(float $amount, string $currency = 'ريال سعودي', string $subCurrency = 'هللة'): string
    {
        $amount = round(abs($amount), 2);
        $integerPart = (int) floor($amount);
        $fractionPart = (int) round(($amount - $integerPart) * 100);

        $sentence = ($integerPart > 0 ? self::convert($integerPart) : 'صفر') . ' ' . $currency;

        if ($fractionPart > 0) {
            $sentence .= ' و' . self::convert($fractionPart) . ' ' . $subCurrency;
        }

        return $sentence . ' فقط لا غير';
    }

    /**
     * يحول أي عدد صحيح موجب (0 لحد مليارات) لصيغته بالحروف العربية.
     */
    public static function convert(int $number): string
    {
        if ($number <= 0) {
            return 'صفر';
        }

        // بنقسم الرقم لمجموعات كل واحدة 3 خانات: [آحاد/مئات، آلاف، ملايين، مليارات]
        $groups = [];
        $n = $number;
        while ($n > 0) {
            $groups[] = $n % 1000;
            $n = intdiv($n, 1000);
        }

        $parts = [];
        for ($index = count($groups) - 1; $index >= 0; $index--) {
            $value = $groups[$index];
            if ($value === 0) {
                continue;
            }

            if ($index === 0) {
                $parts[] = self::threeDigits($value);
            } elseif ($index === 1) {
                $parts[] = self::scalePhrase($value, 'ألف', 'ألفان', 'آلاف');
            } elseif ($index === 2) {
                $parts[] = self::scalePhrase($value, 'مليون', 'مليونان', 'ملايين');
            } else {
                $parts[] = self::scalePhrase($value, 'مليار', 'ملياران', 'مليارات');
            }
        }

        return implode(' و', $parts);
    }

    /**
     * يحول رقم من 1 لـ 999 لصيغته بالحروف (من غير اسم الفئة - ألف/مليون).
     */
    private static function threeDigits(int $value): string
    {
        $parts = [];

        $hundreds = intdiv($value, 100);
        $remainder = $value % 100;

        if ($hundreds > 0) {
            $parts[] = self::HUNDREDS[$hundreds];
        }

        if ($remainder > 0) {
            if ($remainder < 10) {
                $parts[] = self::ONES[$remainder];
            } elseif ($remainder < 20) {
                $parts[] = self::TEN_TO_NINETEEN[$remainder];
            } else {
                $ones = $remainder % 10;
                $tens = intdiv($remainder, 10);
                // العربي بيقول "واحد وعشرون" مش "عشرون وواحد"
                $parts[] = $ones > 0
                    ? self::ONES[$ones] . ' و' . self::TENS[$tens]
                    : self::TENS[$tens];
            }
        }

        return implode(' و', $parts);
    }

    /**
     * بيضيف اسم الفئة (ألف/مليون/مليار) على رقم المجموعة (1-999)،
     * مع صيغة مبسطة للمفرد (=1) والمثنى (=2) والجمع (3 فأكثر).
     */
    private static function scalePhrase(int $value, string $singular, string $dual, string $plural): string
    {
        if ($value === 1) {
            return $singular;
        }

        if ($value === 2) {
            return $dual;
        }

        $words = self::threeDigits($value);

        // من 3 لـ 10 بيتقال "كذا آلاف" (جمع)، وبعد كده "كذا ألف" (مفرد
        // منصوب) - تبسيط عملي شائع في الأنظمة المحاسبية العربية.
        return $value <= 10 ? "{$words} {$plural}" : "{$words} {$singular}";
    }
}
