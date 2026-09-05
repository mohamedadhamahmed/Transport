<?php

namespace App\Services\Hr;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HrSetting;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * قراءة ملف بصمة إكسيل (بشكل AttendanceTemplateExporter: employee_number,
 * date, check_in, check_out) وتحويل كل صف لسجل حضور محسوب بالكامل
 * (late_minutes/overtime_hours/overtime_amount/discount_amount) عن
 * طريق AttendanceCalculator - نفس الحاسبة المستخدمة في الإدخال اليدوي
 * (AttendanceController@store) عشان النتيجة تفضل متطابقة.
 *
 * المطابقة بـ employee_number أولاً (زي القالب)، ولو مش موجود بنجرب
 * national_id (لبعض أجهزة البصمة اللي بتصدّر الرقم القومي بدل رقم
 * الموظف الداخلي). الصف اللي يوم/موظفه اتسجل قبل كده (نفس employee_id
 * ونفس date) بيتحدّث بدل ما يتكرر (unique index على الجدول أصلاً).
 */
class AttendanceImporter
{
    public function __construct(private AttendanceCalculator $calculator)
    {
    }

    /**
     * @return array{imported: int, not_found: array, skipped: array}
     */
    public function import(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return ['imported' => 0, 'not_found' => [], 'skipped' => []];
        }

        $headerRow = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $colIndex = array_flip($headerRow);

        $get = function (array $row, string $key) use ($colIndex) {
            return isset($colIndex[$key]) ? ($row[$colIndex[$key]] ?? null) : null;
        };

        $imported = 0;
        $notFound = [];
        $skipped = [];
        /** @var array<int, HrSetting> $settingsByBranch */
        $settingsByBranch = [];

        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $employeeRef = trim((string) ($get($row, 'employee_number') ?? ''));
            $dateRaw = $get($row, 'date');

            if ($employeeRef === '' && empty($dateRaw)) {
                continue; // صف فاضي
            }

            $date = $this->parseDate($dateRaw);
            if ($employeeRef === '' || !$date) {
                $skipped[] = 'صف رقم ' . ($rowIndex + 2);
                continue;
            }

            $employee = Employee::where('employee_number', $employeeRef)
                ->orWhere('national_id', $employeeRef)
                ->first();

            if (!$employee) {
                $notFound[] = $employeeRef;
                continue;
            }

            $checkIn = $this->parseTime($get($row, 'check_in'));
            $checkOut = $this->parseTime($get($row, 'check_out'));

            $branchKey = $employee->branch_id ?? 0;
            if (!isset($settingsByBranch[$branchKey])) {
                $settingsByBranch[$branchKey] = HrSetting::forBranch($employee->branch_id);
            }
            $setting = $settingsByBranch[$branchKey];

            $computed = $this->calculator->calculate($employee, $date, $checkIn, $checkOut, $setting);

            DB::transaction(function () use ($employee, $date, $checkIn, $checkOut, $computed, $setting) {
                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date->toDateString()],
                    array_merge($computed, [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'source' => Attendance::SOURCE_IMPORT,
                        'created_by' => Auth::id(),
                    ])
                );

                // لو اليوم ده غياب غير مصرح به - نطبّق/نحدّث خصم أيام
                // الإجازة الأسبوعية/الرسمية المتصلة بيه (راجع تعليق
                // AttendanceCalculator::syncConnectedOffDayPenalty).
                $this->calculator->syncConnectedOffDayPenalty($employee, $date, $setting);
            });

            $imported++;
        }

        return ['imported' => $imported, 'not_found' => array_values(array_unique($notFound)), 'skipped' => $skipped];
    }

    private function parseDate($raw): ?Carbon
    {
        if (empty($raw)) {
            return null;
        }

        try {
            if (is_numeric($raw)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($raw));
            }

            return Carbon::parse((string) $raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseTime($raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            if (is_numeric($raw)) {
                // وقت إكسيل رقمي (كسر من اليوم، زي 0.34 = 08:09) - أجهزة
                // البصمة اللي بتصدّر وقت مفصول عن التاريخ بتحفظه كده لو
                // الخلية متنسقة "وقت" أو "رقم".
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $raw))->format('H:i:s');
            }

            return Carbon::parse((string) $raw)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
