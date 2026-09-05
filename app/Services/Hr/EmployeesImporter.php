<?php

namespace App\Services\Hr;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * قراءة ملف إكسيل موظفين (بنفس شكل قالب EmployeesTemplateExporter)
 * وإضافة/تحديث كل صف كموظف - نفس أسلوب PurchaseItemsImporter بالظبط
 * (خريطة هيدر -> رقم عمود عشان نقبل أي ترتيب أعمدة، طالما الأسماء
 * مطابقة للقالب).
 *
 * المطابقة لكل صف: بالرقم القومي/الإقامة (national_id) أولاً لو موجود
 * ومتسجل قبل كده -> تحديث الموظف الموجود. غير كده -> موظف جديد (بنفس
 * منطق EmployeeController@store: رقم موظف تسلسلي + حساب مالي تلقائي).
 */
class EmployeesImporter
{
    public function __construct(private HrAccountService $accounts)
    {
    }

    /**
     * @return array{created: array, updated: array, skipped: array}
     */
    public function import(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return ['created' => [], 'updated' => [], 'skipped' => []];
        }

        $headerRow = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $colIndex = array_flip($headerRow);

        $get = function (array $row, string $key) use ($colIndex) {
            return isset($colIndex[$key]) ? ($row[$colIndex[$key]] ?? null) : null;
        };

        $created = [];
        $updated = [];
        $skipped = [];

        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $name = trim((string) ($get($row, 'name') ?? ''));
            $nationalId = trim((string) ($get($row, 'national_id') ?? ''));

            if ($name === '' && $nationalId === '') {
                continue; // صف فاضي تمامًا
            }

            if ($name === '') {
                $skipped[] = $nationalId !== '' ? $nationalId : ('صف رقم ' . ($rowIndex + 2));
                continue;
            }

            $branchName = trim((string) ($get($row, 'branch_name') ?? ''));
            $branchId = $branchName !== ''
                ? optional(Branch::where('name', $branchName)->first())->id
                : null;

            $hireDateRaw = $get($row, 'hire_date');
            $hireDate = $this->parseDate($hireDateRaw);

            $payMethodRaw = strtolower(trim((string) ($get($row, 'pay_method') ?? '')));
            $payMethod = $payMethodRaw === 'bank' ? 'Bank' : 'Cash';

            $data = [
                'name' => $name,
                'name_en' => trim((string) ($get($row, 'name_en') ?? '')) ?: null,
                'national_id' => $nationalId ?: null,
                'phone' => trim((string) ($get($row, 'phone') ?? '')) ?: null,
                'email' => trim((string) ($get($row, 'email') ?? '')) ?: null,
                'job_title' => trim((string) ($get($row, 'job_title') ?? '')) ?: null,
                'department' => trim((string) ($get($row, 'department') ?? '')) ?: null,
                'branch_id' => $branchId,
                'hire_date' => $hireDate,
                'basic_salary' => is_numeric($get($row, 'basic_salary')) ? (float) $get($row, 'basic_salary') : 0,
                'allowances' => is_numeric($get($row, 'allowances')) ? (float) $get($row, 'allowances') : 0,
                'pay_method' => $payMethod,
                'bank_name' => trim((string) ($get($row, 'bank_name') ?? '')) ?: null,
                'iban' => trim((string) ($get($row, 'iban') ?? '')) ?: null,
            ];

            DB::transaction(function () use ($data, $nationalId, &$created, &$updated) {
                $existing = $nationalId !== ''
                    ? Employee::where('national_id', $nationalId)->first()
                    : null;

                if ($existing) {
                    $existing->update($data);
                    $this->accounts->ensureAllEmployeeAccounts($existing);
                    $updated[] = ['id' => $existing->id, 'name' => $existing->name];

                    return;
                }

                $employee = Employee::create(array_merge($data, [
                    'employee_number' => Employee::nextEmployeeNumber(),
                    'status' => Employee::STATUS_ACTIVE,
                    'created_by' => Auth::id(),
                ]));

                $this->accounts->ensureAllEmployeeAccounts($employee);
                $created[] = ['id' => $employee->id, 'name' => $employee->name];
            });
        }

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    private function parseDate($raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        if (is_numeric($raw)) {
            // تاريخ إكسيل رقمي (serial date) - phpspreadsheet بيرجعه كرقم
            // لو الخلية متنسقة كـ "رقم" بدل "تاريخ" في ملف المستخدم.
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse((string) $raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
