<?php

namespace App\Services\Document\Extractors;

class MyKadExtractor extends AbstractDocumentExtractor
{
    public function extract(string $text, array $context): array
    {
        $fields = [];
        $warnings = [];

        $name = $this->findLabeled($text, ['Nama Penuh', 'Nama', 'Full Name', 'Name']);
        $fields['full_name'] = $this->makeField(
            'full_name',
            $name['value'] ?? null,
            'text',
            $context,
            $name['page'] ?? null,
            $name['label'] ?? null,
        );

        $ic = $this->findLabeled($text, [
            'No. Kad Pengenalan', 'No Kad Pengenalan', 'No. K/P', 'No. KP',
            'No. IC', 'No IC', 'NRIC', 'IC Number', 'MyKad',
        ]);

        $icValue = $ic['value'] ?? null;
        if ($icValue === null && preg_match('/\b\d{6}[\s-]?\d{2}[\s-]?\d{4}\b/u', $text, $m)) {
            $icValue = $m[0];
            $ic = ['value' => $icValue, 'page' => null, 'label' => 'IC pattern'];
        }

        $fields['ic_number'] = $this->makeField(
            'ic_number',
            $icValue,
            'ic',
            $context,
            $ic['page'] ?? null,
            $ic['label'] ?? null,
        );

        if ($fields['ic_number']['value'] !== null) {
            $icDigits = $fields['ic_number']['value'];
            if ($this->isInvalidIcDate($icDigits)) {
                $warnings[] = __('document_ocr.warnings.invalid_ic_date');
            }
        }

        $address = $this->findLabeled($text, [
            'Alamat Surat Menyurat', 'Alamat Penuh', 'Alamat Rumah', 'Alamat', 'Address', 'Full Address',
        ], true);
        $fields['address'] = $this->makeField(
            'address',
            $address['value'] ?? null,
            'textarea',
            $context,
            $address['page'] ?? null,
            $address['label'] ?? null,
        );

        $postcode = $this->findLabeled($text, ['Poskod', 'Postcode', 'Postal Code', 'Zip Code']);
        if (($postcode['value'] ?? null) === null && ($address['value'] ?? null) !== null
            && preg_match('/\b\d{5}\b/', $address['value'], $m)) {
            $postcode = ['value' => $m[0], 'page' => $address['page'] ?? null, 'label' => 'Postcode'];
        }
        $fields['postcode'] = $this->makeField(
            'postcode',
            $postcode['value'] ?? null,
            'postcode',
            $context,
            $postcode['page'] ?? null,
            $postcode['label'] ?? null,
        );

        $state = $this->findLabeled($text, ['Negeri', 'State']);
        if (($state['value'] ?? null) === null) {
            $state = $this->findState($text);
        }
        $fields['state'] = $this->makeField(
            'state',
            $state['value'] ?? null,
            'text',
            $context,
            $state['page'] ?? null,
            $state['label'] ?? null,
        );

        if (($fields['full_name']['value'] ?? null) === null) {
            $warnings[] = __('document_ocr.warnings.name_not_detected');
        }
        if (($fields['ic_number']['value'] ?? null) === null) {
            $warnings[] = __('document_ocr.warnings.ic_not_detected');
        }

        return $this->result($fields, $warnings, [
            'document_type' => 'mykad',
            'sensitive_fields' => ['ic_number'],
        ]);
    }

    private function isInvalidIcDate(string $ic): bool
    {
        if (strlen($ic) !== 12) {
            return true;
        }

        $year = (int) substr($ic, 0, 2);
        $month = (int) substr($ic, 2, 2);
        $day = (int) substr($ic, 4, 2);
        $year += $year > 30 ? 1900 : 2000;

        return ! checkdate($month, $day, $year);
    }

    /**
     * @return array{value:string, page:int, label:string}|null
     */
    private function findState(string $text): ?array
    {
        $states = [
            'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
            'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor',
            'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan',
        ];

        foreach ($this->lines($text) as $line) {
            foreach ($states as $state) {
                if (mb_stripos($line['text'], $state) !== false) {
                    return ['value' => $state, 'page' => $line['page'], 'label' => 'State'];
                }
            }
        }

        return null;
    }
}
