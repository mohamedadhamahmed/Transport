<?php

namespace App\Services\Hr;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * بناء وتنزيل قالب إكسيل فاضي لرفع دفعة موظفين - نفس أسلوب
 * PurchaseItemsTemplateExporter بالظبط (هيدر + صف مثال + auto-size).
 */
class EmployeesTemplateExporter
{
    public const HEADERS = [
        'name',
        'name_en',
        'national_id',
        'phone',
        'email',
        'job_title',
        'department',
        'branch_name',
        'hire_date',
        'basic_salary',
        'allowances',
        'pay_method',
        'bank_name',
        'iban',
    ];

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(self::HEADERS, null, 'A1');

        $sheet->fromArray(
            ['أحمد محمد', 'Ahmed Mohamed', '1012345678', '0501234567', 'ahmed@example.com', 'محاسب', 'المالية', 'الفرع الرئيسي', '2026-01-01', 5000, 500, 'Cash', '', ''],
            null,
            'A2'
        );

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function download(string $filename = 'employees-template.xlsx')
    {
        $writer = new Xlsx($this->build());

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
