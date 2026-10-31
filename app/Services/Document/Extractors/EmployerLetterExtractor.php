<?php

namespace App\Services\Document\Extractors;

class EmployerLetterExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [];

        $employee = $this->findLabeled($text, ['Nama Pekerja', 'Nama Kakitangan', 'Employee Name', 'Name']);
        $fields['employee_name'] = $this->makeField('employee_name', $employee['value'] ?? null, 'text', $context, $employee['page'] ?? null, $employee['label'] ?? null);

        $employer = $this->findLabeled($text, ['Nama Syarikat', 'Majikan', 'Syarikat', 'Employer', 'Company']);
        $fields['employer_name'] = $this->makeField('employer_name', $employer['value'] ?? null, 'text', $context, $employer['page'] ?? null, $employer['label'] ?? null);

        $position = $this->findLabeled($text, ['Jawatan', 'Position', 'Designation']);
        $fields['position'] = $this->makeField('position', $position['value'] ?? null, 'text', $context, $position['page'] ?? null, $position['label'] ?? null);

        $salary = $this->moneyAfter($text, ['Gaji Bulanan', 'Pendapatan Bulanan', 'Monthly Salary', 'Salary', 'Gaji']);
        $fields['monthly_salary'] = $this->makeField('monthly_salary', $salary['value'] ?? null, 'amount', $context, $salary['page'] ?? null, $salary['label'] ?? null);

        $start = $this->dateAfter($text, ['Tarikh Mula Kerja', 'Tarikh Mula', 'Employment Start Date', 'Start Date']);
        $fields['employment_start_date'] = $this->makeField('employment_start_date', $start['value'] ?? null, 'date', $context, $start['page'] ?? null, $start['label'] ?? null);

        if (($fields['employee_name']['value'] ?? null) === null || ($fields['monthly_salary']['value'] ?? null) === null) {
            $warnings[] = __('document_ocr.warnings.employer_letter_incomplete');
        }

        return $this->result($fields, $warnings, ['document_type' => 'employer_letter']);
    }
}
