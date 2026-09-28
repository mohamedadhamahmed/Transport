<?php

namespace App\Console\Commands;

use App\Support\ChartOfAccountsRepair;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Console\Command;

/**
 * php artisan accounts:repair --dry-run   ← يعرض التغييرات من غير ما يحفظ
 * php artisan accounts:repair             ← ينفّذ التصحيح فعليًا
 *
 * راجع App\Support\ChartOfAccountsRepair لتفاصيل كل خطوة.
 */
class RepairChartOfAccounts extends Command
{
    protected $signature = 'accounts:repair {--dry-run : عرض التغييرات بدون حفظ}';

    protected $description = 'تصحيح شجرة الحسابات (الحسابات في أماكن غلط + تصنيف أصول/خصوم/إيرادات/مصروفات/حقوق ملكية)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (!$dryRun && !$this->confirm('هيتم تعديل شجرة الحسابات. خدت نسخة احتياطية من قاعدة البيانات؟', false)) {
            $this->warn('اتلغى. شغّل الأمر بـ --dry-run الأول لو عايز تشوف التغييرات.');

            return self::FAILURE;
        }

        $log = (new ChartOfAccountsRepair($dryRun))->run(ChartOfAccountsSeeder::ACCOUNTS);

        foreach ($log as $line) {
            str_starts_with($line, 'تحذير') ? $this->warn($line) : $this->line('• ' . $line);
        }

        $this->newLine();
        $dryRun
            ? $this->info('(تجربة فقط - ما اتحفظش أي تغيير. شغّل الأمر من غير --dry-run للتنفيذ)')
            : $this->info('تم تصحيح شجرة الحسابات.');

        return self::SUCCESS;
    }
}
