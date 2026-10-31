<?php

namespace App\Services\Document\Extractors;

class MedicalDocumentExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [
            __('document_ocr.warnings.medical_no_diagnosis'),
        ];

        $patient = $this->findLabeled($text, ['Nama Pesakit', 'Pesakit', 'Patient Name', 'Patient']);
        $fields['patient_name'] = $this->makeField('patient_name', $patient['value'] ?? null, 'text', $context, $patient['page'] ?? null, $patient['label'] ?? null);

        $hospital = $this->findLabeled($text, ['Hospital', 'Klinik', 'Clinic', 'Healthcare Facility']);
        $fields['hospital_clinic'] = $this->makeField('hospital_clinic', $hospital['value'] ?? null, 'text', $context, $hospital['page'] ?? null, $hospital['label'] ?? null);

        $date = $this->dateAfter($text, ['Tarikh Dokumen', 'Tarikh Rawatan', 'Document Date', 'Visit Date', 'Date']);
        $fields['document_date'] = $this->makeField('document_date', $date['value'] ?? null, 'date', $context, $date['page'] ?? null, $date['label'] ?? null);

        $expenses = $this->moneyAfter($text, ['Jumlah Bayaran', 'Kos Rawatan', 'Jumlah Kos', 'Total Amount', 'Amount Paid', 'Total']);
        $fields['medical_expenses'] = $this->makeField('medical_expenses', $expenses['value'] ?? null, 'amount', $context, $expenses['page'] ?? null, $expenses['label'] ?? null);

        $notes = $this->findLabeled($text, ['Catatan', 'Notes', 'Remarks']);
        $fields['notes'] = $this->makeField('notes', $notes['value'] ?? null, 'textarea', $context, $notes['page'] ?? null, $notes['label'] ?? null);
        if (is_string($fields['notes']['value'] ?? null)) {
            $fields['notes']['value'] = mb_substr($fields['notes']['value'], 0, 1000);
        }

        return $this->result($fields, $warnings, ['document_type' => 'medical_document']);
    }
}
