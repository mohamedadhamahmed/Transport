<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * الفترات المقفولة (إقفال السنة المالية): أي تسجيل أو تعديل بتاريخ في سنة
 * اتقفلت بيترفض، عشان أرقام السنة المقفولة و"الأرباح المرحّلة" متتغيرش.
 */
class ClosedPeriod
{
    /** قيد إقفال السنة نفسه (مسموح له يتسجل بتاريخ يوم الإقفال). */
    public const CLOSING_OPERATION_TYPE = 20;

    private static bool $loaded = false;

    private static ?Carbon $closedUntil = null;

    /** آخر يوم مقفول (null = مفيش إقفال). */
    public static function closedUntil(): ?Carbon
    {
        if (!self::$loaded) {
            self::$loaded = true;
            try {
                if (Schema::hasTable('fiscal_year_closings')) {
                    $date = DB::table('fiscal_year_closings')->max('closing_date');
                    self::$closedUntil = $date ? Carbon::parse($date)->endOfDay() : null;
                }
            } catch (\Throwable $e) {
                self::$closedUntil = null;
            }
        }

        return self::$closedUntil;
    }

    /** لازم تتنادي بعد أي إقفال/إلغاء إقفال في نفس الطلب. */
    public static function refresh(): void
    {
        self::$loaded = false;
        self::$closedUntil = null;
    }

    public static function isClosed($date): bool
    {
        $until = self::closedUntil();
        if (!$until || $date === null || $date === '') {
            return false;
        }

        try {
            $when = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse((string) $date);
        } catch (\Throwable $e) {
            return false;
        }

        return $when->lte($until);
    }

    public static function message(): string
    {
        $until = self::closedUntil();
        $d = $until ? $until->format('Y-m-d') : '';

        return app()->getLocale() === 'ar'
            ? "السنة المالية مقفولة لحد {$d} - مينفعش تسجّل أو تعدّل أو تحذف أي عملية بتاريخ فيها. سجّل بتاريخ بعده، أو ألغِ الإقفال من شاشة إقفال السنة المالية."
            : "The fiscal year is closed up to {$d} - you can't add, edit or delete transactions dated in it. Use a later date, or reopen the year from the Fiscal year closing screen.";
    }

    public static function ensureOpen($date): void
    {
        if (self::isClosed($date)) {
            throw ValidationException::withMessages(['closed_period' => self::message()]);
        }
    }
}
