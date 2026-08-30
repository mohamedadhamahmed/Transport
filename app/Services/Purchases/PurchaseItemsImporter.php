<?php

namespace App\Services\Purchases;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * مسؤولة عن قراءة ملف إكسيل أصناف فاتورة المشتريات (بنفس شكل القالب اللي
 * بينزّله PurchaseItemsTemplateExporter) وتحويل كل صف فيه لصنف جاهز
 * يتضاف لجدول الأصناف بالفورم - بدل الإضافة يدويًا منتج منتج.
 *
 * آلية المطابقة لكل صف:
 *   1) بالكود (product_code) أولاً.
 *   2) لو مش لاقياه، بالاسم العربي (product_name_ar).
 *   3) لو برضو مش لاقياه خالص، بيتضاف تلقائيًا كمنتج جديد في جدول
 *      المنتجات فورًا ببيانات الصف.
 *
 * ده نفس الكود اللي كان جوه PurchaseController@importItems بالظبط -
 * اتنقل هنا بس لتنظيم الكود (مفيش أي تغيير في السلوك).
 */
class PurchaseItemsImporter
{
    /**
     * @return array{items: array, created: array, skipped: array}
     */
    public function import(UploadedFile $file, int $branchId): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return ['items' => [], 'created' => [], 'skipped' => []];
        }

        // خريطة اسم العمود (من صف الهيدر الأول) -> رقم العمود، عشان نقبل
        // أي ترتيب أعمدة طالما الأسماء زي القالب بالظبط (بحروف صغيرة،
        // مسافات زيادة متجاهلة).
        $headerRow = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $colIndex = array_flip($headerRow);

        $get = function (array $row, string $key) use ($colIndex) {
            return isset($colIndex[$key]) ? ($row[$colIndex[$key]] ?? null) : null;
        };

        $items = [];
        $created = [];
        $skipped = [];

        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $nameAr = trim((string) ($get($row, 'product_name_ar') ?? ''));
            $nameEn = trim((string) ($get($row, 'product_name_en') ?? ''));
            $code = trim((string) ($get($row, 'product_code') ?? ''));
            $salePriceRaw = $get($row, 'sale_price');
            $priceRaw = $get($row, 'price');
            $quantityRaw = $get($row, 'quantity');
            $salePrice = is_numeric($salePriceRaw) ? (float) $salePriceRaw : null;
            $price = is_numeric($priceRaw) ? (float) $priceRaw : null;
            $quantity = is_numeric($quantityRaw) ? (float) $quantityRaw : null;
            $location = trim((string) ($get($row, 'location') ?? ''));
            $refNumber = trim((string) ($get($row, 'refnumber') ?? ''));

            if ($nameAr === '' && $code === '') {
                // صف فاضي تمامًا - بنتجاهله من غير ما نعتبره "متخطى" بالغلط.
                continue;
            }

            $product = null;
            if ($code !== '') {
                $product = Product::where('code', $code)->first();
            }
            if (!$product && $nameAr !== '') {
                $product = Product::where('name', $nameAr)->first();
            }

            if (!$product) {
                if ($nameAr === '') {
                    $skipped[] = $code !== '' ? $code : ('صف رقم ' . ($rowIndex + 2));
                    continue;
                }

                $product = Product::create([
                    'name' => $nameAr,
                    'name_en' => $nameEn ?: null,
                    'code' => $code ?: null,
                    'location' => $location ?: null,
                    'reference_number' => $refNumber ?: null,
                    'purchase_price' => $price ?? 0,
                    'sale_price' => $salePrice ?? 0,
                    'stock_quantity' => 0,
                    'branch_id' => $branchId,
                ]);
                $created[] = ['id' => $product->id, 'name' => $product->name, 'code' => $product->code];
            }

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'quantity' => $quantity ?? 1,
                'unit_price' => $price ?? ($product->purchase_price ?? 0),
                'sale_price' => $salePrice ?? ($product->sale_price ?? 0),
                'discount_amount' => 0,
                'tax_rate' => 0.15,
            ];
        }

        return [
            'items' => $items,
            'created' => $created,
            'skipped' => $skipped,
        ];
    }
}