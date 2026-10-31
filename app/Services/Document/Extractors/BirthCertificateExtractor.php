<?php

namespace App\Services\Document\Extractors;

class BirthCertificateExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];

        $child = $this->findLabeled($text, ['Nama Kanak-Kanak', 'Nama Anak', 'Child Name', 'Name of Child', 'Nama']);
        $fields['child_name'] = $this->makeField('child_name', $child['value'] ?? null, 'text', $context, $child['page'] ?? null, $child['label'] ?? null);

        $ic = $this->findLabeled($text, ['No. Kad Pengenalan', 'No Kad Pengenalan', 'No. IC', 'NRIC', 'Identification Number']);
        if (($ic['value'] ?? null) === null && preg_match('/\b\d{6}[\s-]?\d{2}[\s-]?\d{4}\b/u', $text, $m)) {
            $ic = ['value' => $m[0], 'page' => null, 'label' => 'IC pattern'];
        }
        $fields['child_ic'] = $this->makeField('child_ic', $ic['value'] ?? null, 'ic', $context, $ic['page'] ?? null, $ic['label'] ?? null);

        $dob = $this->dateAfter($text, ['Tarikh Lahir', 'Date of Birth', 'Birth Date']);
        $fields['date_of_birth'] = $this->makeField('date_of_birth', $dob['value'] ?? null, 'date', $context, $dob['page'] ?? null, $dob['label'] ?? null);

        $place = $this->findLabeled($text, ['Tempat Lahir', 'Place of Birth']);
        $fields['place_of_birth'] = $this->makeField('place_of_birth', $place['value'] ?? null, 'text', $context, $place['page'] ?? null, $place['label'] ?? null);

        $father = $this->findLabeled($text, ['Nama Bapa', 'Bapa', 'Father Name', 'Father']);
        $fields['father_name'] = $this->makeField('father_name', $father['value'] ?? null, 'text', $context, $father['page'] ?? null, $father['label'] ?? null);

        $mother = $this->findLabeled($text, ['Nama Ibu', 'Ibu', 'Mother Name', 'Mother']);
        $fields['mother_name'] = $this->makeField('mother_name', $mother['value'] ?? null, 'text', $context, $mother['page'] ?? null, $mother['label'] ?? null);

        return $this->result($fields, [], ['document_type' => 'birth_certificate']);
    }
}
