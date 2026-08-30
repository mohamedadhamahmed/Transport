<?php

namespace App\Services\Purchases;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * مسؤولة عن بناء وتنزيل قالب الإكسيل الفاضي (اللي بيتعبى وبعدين يترفع
 * تاني في PurchaseItemsImporter) - نفس شكل الملف اللي بعتيهولي بالظبط:
 * product_name_ar, product_name_en, product_code, sale_price, price,
 * quantity, location, refnumber.
 *
 * ده نفس الكود اللي كان جوه PurchaseController@downloadItemsTemplate
 * بالظبط - اتنقل هنا بس لتنظيم الكود (مفيش أي تغيير في السلوك أو
 * الشكل النهائي للملف).
 */
class PurchaseItemsTemplateExporter
{
    public const HEADERS = [
        'product_name_ar',
        'product_name_en',
        'product_code',
        'sale_price',
        'price',
        'quantity',
        'location',
        'refnumber',
    ];

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(self::HEADERS, null, 'A1');

        // صف مثال توضيحي - المستخدم بيمسحه أو يستبدله ببياناته الفعلية قبل الرفع.
        $sheet->fromArray(
            ['مثال منتج', 'Example Product', 'EX-001', 120, 100, 5, 'المخزن الرئيسي', 'REF-001'],
            null,
            'A2'
        );

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function download(string $filename = 'purchase-items-template.xlsx')
    {
        $writer = new Xlsx($this->build());

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
