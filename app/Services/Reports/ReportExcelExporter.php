<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * مُصدّر إكسيل عام لكل تقارير "مركز التقارير" الستة - نفس أسلوب
 * App\Services\Hr\EmployeesTemplateExporter (PhpSpreadsheet خام +
 * streamDownload) بس بشكل عام يقبل أي صفوف/عناوين أعمدة بدل ما يبقى
 * مبني على قالب موظفين تحديدًا. كل تقرير بيبعت عناوين الأعمدة (مترجمة
 * بالفعل عن طريق __()) وصفوف البيانات كمصفوفات عادية بس.
 */
class ReportExcelExporter
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function download(array $headers, array $rows, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        $sheet->fromArray($headers, null, 'A1');
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);

        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
