<?php

namespace App\Services\Hr;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * قالب إكسيل بصمة الحضور والانصراف - نفس شكل تصدير أغلب أجهزة البصمة
 * (رقم الموظف + التاريخ + وقت الدخول + وقت الخروج) عشان يبقى سهل تجهيز
 * الملف من أي جهاز بصمة، مش بس إدخال يدوي.
 */
class AttendanceTemplateExporter
{
    public const HEADERS = [
        'employee_number',
        'date',
        'check_in',
        'check_out',
    ];

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(['EMP-000001', '2026-09-01', '08:05', '17:30'], null, 'A2');

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function download(string $filename = 'attendance-template.xlsx')
    {
        $writer = new Xlsx($this->build());

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
