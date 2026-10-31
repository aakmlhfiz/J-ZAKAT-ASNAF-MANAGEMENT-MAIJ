<?php

namespace App\Services\Document\Extractors;

class SalarySlipExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [];

        $definitions = [
            'employee_name' => [
                'labels' => ['Nama Pekerja', 'Nama Kakitangan', 'Employee Name', 'Nama'],
                'type' => 'text',
            ],
            'employer_name' => [
                'labels' => ['Nama Majikan', 'Majikan', 'Syarikat', 'Employer', 'Company'],
                'type' => 'text',
            ],
            'salary_month' => [
                'labels' => ['Bulan Gaji', 'Bulan', 'Salary Month', 'Pay Period'],
                'type' => 'text',
            ],
            'basic_salary' => [
                'labels' => ['Gaji Pokok', 'Basic Salary', 'Basic Pay', 'Basic'],
                'type' => 'amount',
            ],
            'allowances' => [
                'labels' => ['Elaun', 'Elaun-elaun', 'Allowance', 'Allowances'],
                'type' => 'amount',
            ],
            'overtime' => [
                'labels' => ['Kerja Lebih Masa', 'Overtime', 'OT'],
                'type' => 'amount',
            ],
            'gross_salary' => [
                'labels' => ['Gaji Kasar', 'Jumlah Pendapatan', 'Gross Salary', 'Gross Pay', 'Gross'],
                'type' => 'amount',
            ],
            'epf' => [
                'labels' => ['KWSP', 'EPF'],
                'type' => 'amount',
            ],
            'socso' => [
                'labels' => ['PERKESO', 'SOCSO'],
                'type' => 'amount',
            ],
            'eis' => [
                'labels' => ['EIS', 'SIP'],
                'type' => 'amount',
            ],
            'other_deductions' => [
                'labels' => ['Potongan Lain', 'Other Deductions', 'Lain-lain Potongan'],
                'type' => 'amount',
            ],
            'net_salary' => [
                'labels' => ['Gaji Bersih', 'Net Salary', 'Net Pay', 'Jumlah Bersih'],
                'type' => 'amount',
            ],
        ];

        foreach ($definitions as $key => $definition) {
            $found = $definition['type'] === 'amount'
                ? $this->moneyAfter($text, $definition['labels'])
                : $this->findLabeled($text, $definition['labels']);

            $fields[$key] = $this->makeField(
                $key,
                $found['value'] ?? null,
                $definition['type'],
                $context,
                $found['page'] ?? null,
                $found['label'] ?? null,
            );
        }

        $this->validateArithmetic($fields, $warnings);

        if (($fields['gross_salary']['value'] ?? null) === null
            && ($fields['net_salary']['value'] ?? null) === null
            && ($fields['basic_salary']['value'] ?? null) === null) {
            $warnings[] = __('document_ocr.warnings.no_salary_amount');
        }

        return $this->result($fields, $warnings, [
            'document_type' => 'salary_slip',
        ]);
    }

    private function validateArithmetic(array $fields, array &$warnings): void
    {
        $amount = fn (string $key): ?float => is_numeric($fields[$key]['value'] ?? null)
            ? (float) $fields[$key]['value']
            : null;

        $basic = $amount('basic_salary');
        $allowances = $amount('allowances') ?? 0.0;
        $overtime = $amount('overtime') ?? 0.0;
        $gross = $amount('gross_salary');
        $net = $amount('net_salary');
        $deductions = array_sum(array_filter([
            $amount('epf'),
            $amount('socso'),
            $amount('eis'),
            $amount('other_deductions'),
        ], fn ($value) => $value !== null));

        if ($basic !== null && $gross !== null && $gross + 1 < ($basic + $allowances + $overtime)) {
            $warnings[] = __('document_ocr.warnings.gross_salary_mismatch');
        }

        if ($gross !== null && $net !== null && $deductions > 0 && abs(($gross - $deductions) - $net) > 1) {
            $warnings[] = __('document_ocr.warnings.net_salary_mismatch');
        }

        if ($gross !== null && $net !== null && $net > $gross + 1) {
            $warnings[] = __('document_ocr.warnings.net_exceeds_gross');
        }
    }
}
