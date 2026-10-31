<?php

namespace App\Services\Document\Extractors;

class GenericDocumentExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [__('document_ocr.warnings.generic_document')];

        $title = $this->findLabeled($text, ['Tajuk Dokumen', 'Tajuk', 'Document Title', 'Title', 'Subject', 'Perkara']);
        $fields['title'] = $this->makeField('title', $title['value'] ?? null, 'text', $context, $title['page'] ?? null, $title['label'] ?? null);

        $reference = $this->findLabeled($text, ['No. Rujukan', 'No Rujukan', 'Reference No', 'Reference Number', 'Ref']);
        $fields['reference_no'] = $this->makeField('reference_no', $reference['value'] ?? null, 'text', $context, $reference['page'] ?? null, $reference['label'] ?? null);

        $date = $this->dateAfter($text, ['Tarikh Dokumen', 'Tarikh', 'Document Date', 'Date']);
        $fields['document_date'] = $this->makeField('document_date', $date['value'] ?? null, 'date', $context, $date['page'] ?? null, $date['label'] ?? null);

        $amount = $this->moneyAfter($text, ['Jumlah', 'Jumlah Bayaran', 'Total', 'Amount']);
        $fields['amount'] = $this->makeField('amount', $amount['value'] ?? null, 'amount', $context, $amount['page'] ?? null, $amount['label'] ?? null);

        $notes = $this->findLabeled($text, ['Catatan', 'Notes', 'Remarks']);
        $fields['notes'] = $this->makeField('notes', $notes['value'] ?? null, 'textarea', $context, $notes['page'] ?? null, $notes['label'] ?? null);
        if (is_string($fields['notes']['value'] ?? null)) {
            $fields['notes']['value'] = mb_substr($fields['notes']['value'], 0, 1000);
        }

        return $this->result($fields, $warnings, ['document_type' => 'other']);
    }
}
